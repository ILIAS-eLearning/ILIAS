<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class for indexing hmtl ,pdf, txt files and htlm Learning modules.
* This indexer is called by cron.php
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @package ServicesSearch
*/
class ilLuceneIndexer extends ilCronJob
{
    protected int $timeout = 60;

    protected ilLanguage $lng;
    protected ilSetting $setting;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setting = $DIC->settings();
    }
    
    public function getId() : string
    {
        return "src_lucene_indexer";
    }
    
    public function getTitle() : string
    {
        return $this->lng->txt("cron_lucene_index");
    }
    
    public function getDescription() : string
    {
        return $this->lng->txt("cron_lucene_index_info");
    }
    
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }
    
    public function hasAutoActivation() : bool
    {
        return false;
    }
    
    public function hasFlexibleSchedule() : bool
    {
        return true;
    }
    
    public function run() : ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;
        $error_message = null;
        
        try {
            ilRpcClientFactory::factory('RPCIndexHandler', 60)->index(
                CLIENT_ID . '_' . $this->setting->get('inst_id', "0"),
                true
            );
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            
            if ($e instanceof ilRpcClientException && $e->getCode() == 28) {
                ilLoggerFactory::getLogger('src')->info('Connection timed out after ' . $this->timeout . ' seconds. ' .
                    'Indexing will continoue without a proper return message. View ilServer log if you think there are problems while indexing.');
                $error_message = null;
            }
        }
        
        $result = new ilCronJobResult();
        if ($error_message) {
            // #16035 - currently no way to discern the severity of the exception
            $result->setMessage($error_message);
            $status = ilCronJobResult::STATUS_FAIL;
        } else {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result->setStatus($status);
        return $result;
    }
    
    
    /**
     * Update lucene index
     * @param int[] $a_obj_ids
     * @return bool
     */
    public static function updateLuceneIndex(array $a_obj_ids) : bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        if (!ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            return false;
        }
        
        try {
            ilLoggerFactory::getLogger('src')->info('Lucene update index call BEGIN --- ');

            ilRpcClientFactory::factory('RPCIndexHandler', 1)->indexObjects(
                CLIENT_ID . '_' . $ilSetting->get('inst_id', "0"),
                $a_obj_ids
            );
            ilLoggerFactory::getLogger('src')->info('Lucene update index call --- END');
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            ilLoggerFactory::getLogger('src')->error($error_message);
            return false;
        }

        return true;
    }
}
