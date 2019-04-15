<?php
namespace Tagalys\Mpages\Helper;

class Mpages extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Tagalys\Mpages\Model\MpagescacheFactory $mpagescacheFactory,
        \Tagalys\Sync\Helper\Api $tagalysApi,
        \Tagalys\Sync\Helper\Configuration $tagalysConfiguration,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->mpagescacheFactory = $mpagescacheFactory;
        $this->tagalysApi = $tagalysApi;
        $this->tagalysConfiguration = $tagalysConfiguration;
        $this->storeManager = $storeManager;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/tagalys.log');
        $this->tagalysLogger = new \Zend\Log\Logger();
        $this->tagalysLogger->addWriter($writer);
    }

    public function updateMpagesCache() {
        try {
            $setup_status = $this->tagalysConfiguration->getConfig('setup_status');
            $setup_complete = ($setup_status == 'completed');
            if ($setup_complete) {
                $stores_for_tagalys = $this->tagalysConfiguration->getStoresForTagalys();
                if ($stores_for_tagalys != null) {
                    foreach ($stores_for_tagalys as $storeId) {
                        $processed = array();
                        $page = 1;
                        $per_page = 50;
                        $response = $this->tagalysApi->storeApiCall($storeId.'', '/v1/mpages', array("include_platform_pages" => true, "page" => $page, "per_page" => $per_page, "request" => ["url_component", "variables"]));
                        $processed_now = $this->processResponse($storeId, $response);
                        $processed = array_merge($processed, $processed_now);
                        $finished = (($page - 1) * $per_page) + count($response['results']);
                        $loop_number = 1;
                        while ($finished < $response['total'] || $loop_number >= 50) {
                            $page += 1;
                            $response = $this->tagalysApi->storeApiCall($storeId.'', '/v1/mpages', array("include_platform_pages" => true, "page" => $page, "per_page" => $per_page, "request" => ["url_component", "variables"]));
                            $processed_now = $this->processResponse($storeId, $response);
                            $processed = array_merge($processed, $processed_now);
                            $finished = (($page - 1) * $per_page) + count($response['results']);
                            $loop_number += 1;
                        }
                        $this->deleteStoreUrlsExcept($storeId, $processed);
                    }
                }
            }
        } catch (Exception $e) {
            $this->tagalysApi->log('error', 'Exception in updateMpagesCache', array('exception_message' => $e->getMessage()));
        }
    }

    protected function processResponse($storeId, $response) {
        $ids = array();
        foreach($response['results'] as $result) {
            $saveResponse = $this->saveMpageCache($storeId, ($result['platform'] ? 1 : 0), $result);
            if ($saveResponse != false) {
                array_push($ids, $saveResponse);
            }
        }
        return $ids;
    }

    public function saveMpageCache($storeId, $platform, $response) {
        try {
            if ($platform === 1 && array_key_exists('id', $response)) {
                $response['url_component'] = $response['id'];
            }
            $mpagescache = $this->mpagescacheFactory->create();
            $mpagecacheId = $mpagescache->checkUrl($storeId, $platform, $response['url_component']);
            if ($mpagecacheId) {
                $found = $mpagescache->load($mpagecacheId);
                $found->setPlatform($platform);
                $found->setCachedata(json_encode($response));
                $found->save();
                return $mpagecacheId;
            } else {
                $mpagescache->setStoreId($storeId);
                $mpagescache->setPlatform($platform);
                $mpagescache->setUrl($response['url_component']);
                $mpagescache->setCachedata(json_encode($response));
                $mpagescache->save();
                return $mpagescache->getId();
            }
        } catch (Exception $e){
            $this->tagalysApi->log('error', 'Exception in saveMpageCache', array('exception_message' => $e->getMessage()));
            return false;
        }
    }

    public function getMpageData($storeId, $platform, $mpage) {
        $mpagescache = $this->mpagescacheFactory->create();
        $mpagecacheId = $mpagescache->checkUrl($storeId, $platform, $mpage);
        if ($mpagecacheId) {
            $found = $mpagescache->load($mpagecacheId);
            $mpage_data = $found->getCachedata();
            $response = json_decode($mpage_data, true);
        } else {
            // page doesn't exist in cache
            $urlForApiCall = '/v1/mpages/'.$mpage;
            if ($platform === 1) {
                $urlForApiCall = '/v1/mpages/_platform/'.$mpage;
            }
            $response = $this->tagalysApi->storeApiCall($storeId.'', $urlForApiCall, array('request' => array('variables')));
            if ($response != false) {
                $response['url_component'] = $mpage;
                $this->saveMpageCache($storeId, $platform, $response);
            }
        }
        return $response;
    }
    public function updateSpecificMpageCache($storeId, $platform, $mpage) {
        $urlForApiCall = '/v1/mpages/'.$mpage;
        if ($platform === 1) {
            $urlForApiCall = '/v1/mpages/_platform/'.$mpage;
        }
        $response = $this->tagalysApi->storeApiCall($storeId.'', $urlForApiCall, array('request' => array('variables')));
        if ($response != false) {
            $response['url_component'] = $mpage;
            $this->saveMpageCache($storeId, $platform, $response);
        }
        return $response;
    }

    public function deleteStoreUrlsExcept($storeId, $ids) {
        $itemsToDelete = $this->mpagescacheFactory->create()->getCollection()->addFieldToFilter('store_id', $storeId)->addFieldToFilter('id', array('nin' => $ids));
        foreach($itemsToDelete as $itemToDelete) {
            $itemToDelete->delete();
        }
    }
}