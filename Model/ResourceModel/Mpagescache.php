<?php

namespace Tagalys\Mpages\Model\ResourceModel;

class Mpagescache extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tagalys_mpagescache', 'id');
    }

    /**
     * Process config data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {

        // if (!$this->isValidPostUrlKey($object)) {
        //     throw new \Magento\Framework\Exception\LocalizedException(
        //         __('The config URL key contains capital letters or disallowed symbols.')
        //     );
        // }

        // if ($this->isNumericPostUrlKey($object)) {
        //     throw new \Magento\Framework\Exception\LocalizedException(
        //         __('The config URL key cannot be made of only numbers.')
        //     );
        // }

        // if ($object->isObjectNew() && !$object->hasCreationTime()) {
        //     $object->setCreationTime($this->_date->gmtDate());
        // }

        // $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Load an object using 'url_key' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'id';
        }

        return parent::load($object, $value, $field);
    }


    /**
     * Retrieve load select with filter by url_key and activity
     *
     * @param string $url_key
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadByUrlSelect($storeId, $url)
    {
        $select = $this->getConnection()->select()->from(
            ['tmc' => $this->getMainTable()]
        )->where(
            'tmc.store_id = ?',
            $storeId
        )->where(
            'tmc.url = ?',
            $url
        );

        return $select;
    }

    public function checkUrl($storeId, $url)
    {
        $select = $this->_getLoadByUrlSelect($storeId, $url);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('tmc.id')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}