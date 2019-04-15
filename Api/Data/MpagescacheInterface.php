<?php
namespace Tagalys\Mpages\Api\Data;


interface MpagescacheInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const STORE_ID = 'store_id';
    const PLATFORM = 'platform';
    const URL = 'url';
    const CACHEDATA = 'cachedata';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    public function getStoreId();

    public function getUrl();

    public function getCachedata();


    /**
     * Set ID
     *
     * @param int $id
     * @return \Ashsmith\Blog\Api\Data\PostInterface
     */
    public function setId($id);

    public function setStoreId($storeId);

    public function setUrl($url);

    public function setCachedata($cachedata);
}