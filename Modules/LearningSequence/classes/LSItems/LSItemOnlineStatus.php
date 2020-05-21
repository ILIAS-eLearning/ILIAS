<?php

declare(strict_types=1);

class LSItemOnlineStatus
{
    const S_LEARNMODULE_IL = "lm";
    const S_LEARNMODULE_HTML = "htlm";
    const S_SAHS = "sahs";
    const S_TEST = "tst";
    const S_SURVEY = "svy";
    const S_CONTENTPAGE = "copa";
    const S_EXERCISE = "exc";
    const S_IND_ASSESSMENT = "iass";
    const S_FILE = "file";

    private static $obj_with_online_status = array(
        self::S_LEARNMODULE_IL,
        self::S_LEARNMODULE_HTML,
        self::S_SAHS,
        self::S_TEST,
        self::S_SURVEY
    );

    public function setOnlineStatus(int $ref_id, bool $status)
    {
        $obj = \ilObjectFactory::getInstanceByRefId($ref_id);
        $obj->setOfflineStatus(!$status);
        $obj->update();
    }

    public function getOnlineStatus(int $ref_id) : bool
    {
        if(!$this->hasOnlineStatus($ref_id)) {
            return true;
        }
        return !\ilObject::lookupOfflineStatus(\ilObject::_lookupObjId($ref_id));
    }

    public function hasOnlineStatus(int $ref_id) : bool
    {
        $type = $this->getObjectTypeFor($ref_id);
        if (in_array($type, self::$obj_with_online_status)) {
            return true;
        }

        return false;
    }

    protected function getObjectTypeFor(int $ref_id) : string
    {
        return \ilObject::_lookupType($ref_id, true);
    }
}
