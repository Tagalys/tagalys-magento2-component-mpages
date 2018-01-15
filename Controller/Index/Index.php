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
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Tagalys\Sync\Helper\Api $tagalysApi
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->registry = $registry;
        $this->tagalysApi = $tagalysApi;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $currentUrl = $this->storeManager->getStore()->getCurrentUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mpageUrlComponent = explode('/', explode('?', str_replace($baseUrl, '', $currentUrl))[0])[1];

        $this->registry->register('mpageUrlComponent', $mpageUrlComponent);

        $resultPage->getConfig()->getTitle()->set($mpageUrlComponent);

        try {
            $params = $this->request->getParams();
            if (array_key_exists('f', $params) || array_key_exists('sort', $params) || array_key_exists('page', $params)) {
                $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');
            }

            $response = $this->tagalysApi->storeApiCall($this->storeManager->getStore()->getId().'', '/v1/mpages/'.$mpageUrlComponent, array('request' => array('variables', 'banners')));
            if ($response !== false) {

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