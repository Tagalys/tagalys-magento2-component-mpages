<?php namespace Tagalys\Mpages\Model\ResourceModel\Mpagescache;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tagalys\Mpages\Model\Mpagescache', 'Tagalys\Mpages\Model\ResourceModel\Mpagescache');
    }

}