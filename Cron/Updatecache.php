<?php

namespace Tagalys\Mpages\Cron;
 
class Updatecache
{
    public function __construct(
        \Tagalys\Mpages\Helper\Mpages $tagalysMpages,
        \Tagalys\Sync\Helper\Configuration $tagalysConfiguration,
        \Tagalys\Sync\Helper\Api $tagalysApi
    )
    {
        $this->tagalysMpages = $tagalysMpages;
        $this->tagalysConfiguration = $tagalysConfiguration;
        $this->tagalysApi = $tagalysApi;
    }

    public function execute()
    {
        try {
            $utcNow = new \DateTime("now", new \DateTimeZone('UTC'));
            $timeNow = $utcNow->format(\DateTime::ATOM);
            $this->tagalysConfiguration->setConfig("heartbeat:mpagesUpdatecacheCron", $timeNow);
            $this->tagalysMpages->updateMpagesCache();
        } catch (Exception $e) {
            $this->tagalysApi->log('error', 'Exception in mpagesUpdatecacheCron', array('exception_message' => $e->getMessage()));
        }
    }
}