<?php
 
namespace Tagalys\Mpages\Controller\Index;
 
use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\Registry $registry,
        \Tagalys\Sync\Helper\Api $tagalysApi,
        \Tagalys\Mpages\Helper\Mpages $tagalysMpages
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->request = $context->getRequest();
        $this->registry = $registry;
        $this->tagalysApi = $tagalysApi;
        $this->tagalysMpages = $tagalysMpages;
        $this->pageConfig = $pageConfig;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $currentUrl = $this->storeManager->getStore()->getCurrentUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mpageUrlComponent = explode('/', explode('?', str_replace($baseUrl, '', $currentUrl))[0])[1];

        $this->registry->register('mpageUrlComponent', $mpageUrlComponent);

        $this->registry->register('tagalysPowered', true);

        $resultPage->getConfig()->getTitle()->set($mpageUrlComponent);

        try {
            $params = $this->request->getParams();
            if (array_key_exists('f', $params) || array_key_exists('sort', $params) || array_key_exists('page', $params)) {
                $this->pageConfig->setRobots('NOINDEX,FOLLOW');
            }

            $response = $this->tagalysMpages->getMpageData($this->storeManager->getStore()->getId().'', 0, $mpageUrlComponent);

            if ($response !== false) {

                if (isset($response['total'])) {
                    $this->registry->register('mpageTotalProducts', intval($response['total']));
                }
                if (isset($response['name'])) {
                    $this->registry->register('mpageName', $response['name']);
                }
                if (isset($response['variables'])) {
                    $this->registry->register('mpageVariables', $response['variables']);
                }

                if (isset($response['variables'])) {
                    if (isset($response['variables']['page_title']) && $response['variables']['page_title'] != '' ) {
                        $resultPage->getConfig()->getTitle()->set($response['variables']['page_title']);
                    } else {
                        $resultPage->getConfig()->getTitle()->set($response['name']);
                    }
                    if (isset($response['variables']['meta_keywords'])) {
                        $resultPage->getConfig()->setKeywords($response['variables']['meta_keywords']);
                    }
                    if (isset($response['variables']['meta_description'])) {
                        $resultPage->getConfig()->setDescription($response['variables']['meta_description']);
                    }
                    if (isset($response['variables']['meta_robots'])) {
                        $resultPage->getConfig()->setRobots($response['variables']['meta_robots']);
                    }
                }
            }
        } catch (\Exception $e) {
            
        }

        return $resultPage;
    }
}