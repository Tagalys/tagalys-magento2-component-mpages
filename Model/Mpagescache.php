<?php

namespace Tagalys\Mpages\Model;

use Tagalys\Mpages\Api\Data\MpagescacheInterface;

class Mpagescache  extends \Magento\Framework\Model\AbstractModel implements MpagescacheInterface
{

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'tagalys_mpagescache';

    /**
     * @var string
     */
    protected $_cacheTag = 'tagalys_mpagescache';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'tagalys_mpagescache';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tagalys\Mpages\Model\ResourceModel\Mpagescache');
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }
    public function getPlatform()
    {
        return $this->getData(self::PLATFORM);
    }

    public function getUrl()
    {
        return $this->getData(self::URL);
    }
    public function getCachedata()
    {
        return $this->getData(self::CACHEDATA);
    }
    public function checkUrl($storeId, $platform, $url)
    {
        return $this->_getResource()->checkUrl($storeId, $platform, $url);
    }


    /**
     * Set ID
     *
     * @param int $id
     * @return \Tagalys\Sync\Api\Data\ConfigInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function setStoreId($store_id)
    {
        return $this->setData(self::STORE_ID, $store_id);
    }
    public function setPatform($platform)
    {
        return $this->setData(self::PLATFORM, $platform);
    }

    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    public function setCachedata($cachedata)
    {
        return $this->setData(self::CACHEDATA, $cachedata);
    }
}