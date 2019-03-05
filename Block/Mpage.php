<?php
namespace Tagalys\Mpages\Block;
 
class Mpage extends \Magento\Framework\View\Element\Template
{
    public function __construct(
      \Magento\Framework\View\Element\Template\Context $context,
      \Magento\Framework\Registry $registry
    )
    {
        $this->storeManager = $context->getStoreManager();
        $this->request = $context->getRequest();
        $this->registry = $registry;
        parent::__construct($context);
    }

    public function getMpageUrlComponent() {
        return $this->registry->registry('mpageUrlComponent');
    }

    public function getCanonicalUrl() {
        return explode('?', $this->storeManager->getStore()->getCurrentUrl())[0];
    }

    public function getPrevAndNextLinks($perPage) {
        $prevAndNextLinks = array('prev' => false, 'next' => false);
        try {
            $totalProducts = $this->registry->registry('mpageTotalProducts');
            $currentPage = $this->registry->registry('mpageCurrentPage');
            if (!is_null($totalProducts) && !is_null($currentPage)) {
                $totalPages = ceil($totalProducts / $perPage);

                $prevPage = false;
                $nextPage = false;
                if ($currentPage > 1) {
                    $prevPage = $currentPage - 1;
                }
                if ($currentPage < $totalPages) {
                    $nextPage = $currentPage + 1;
                }
                $currentBaseUrl = $this->getCanonicalUrl();
                $params = $this->request->getParams();
                $baseQueryStringComponentsForPageLinks = array();
                if (array_key_exists('f', $params)) {
                    array_push($baseQueryStringComponentsForPageLinks, 'f='.$params['f']);
                }
                if (array_key_exists('sort', $params)) {
                    array_push($baseQueryStringComponentsForPageLinks, 'sort='.$params['sort']);
                }
                if ($prevPage !== false) {
                    $queryStringComponentsForPrevPageLink = array_merge($baseQueryStringComponentsForPageLinks, array('page='.$prevPage));
                    $prevAndNextLinks['prev'] = $currentBaseUrl.'?'.implode('&', $queryStringComponentsForPrevPageLink);
                }
                if ($nextPage !== false) {
                    $queryStringComponentsForNextPageLink = array_merge($baseQueryStringComponentsForPageLinks, array('page='.$nextPage));
                    $prevAndNextLinks['next'] = $currentBaseUrl.'?'.implode('&', $queryStringComponentsForNextPageLink);
                }
            }
        } catch (Exception $e) {
            // don't log this as it might happen too often
        }
        return $prevAndNextLinks;
    }
}