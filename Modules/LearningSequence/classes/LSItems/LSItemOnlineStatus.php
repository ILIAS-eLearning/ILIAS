<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class LSItemOnlineStatus
{
    public const S_LEARNMODULE_IL = "lm";
    public const S_LEARNMODULE_HTML = "htlm";
    public const S_SAHS = "sahs";
    public const S_TEST = "tst";
    public const S_SURVEY = "svy";
    public const S_CONTENTPAGE = "copa";
    public const S_EXERCISE = "exc";
    public const S_IND_ASSESSMENT = "iass";
    public const S_FILE = "file";

    private static array $objs_with_check_for_online_status = [
        self::S_TEST,
        self::S_SURVEY,
    ];

    public function setOnlineStatus(int $ref_id, bool $status): void
    {
        $obj = $this->getObject($ref_id);
        $props = $obj->getObjectProperties()->getPropertyIsOnline();
        $props = $status ? $props->withOnline() : $props->withOffline();
        $obj->getObjectProperties()->storePropertyIsOnline($props);
    }

    public function getOnlineStatus(int $ref_id): bool
    {
        return !\ilObject::lookupOfflineStatus(\ilObject::_lookupObjId($ref_id));
    }

    public function hasChangeableOnlineStatus(int $ref_id): bool
    {
        $obj_type = $this->getObjectTypeFor($ref_id);
        if(! in_array($obj_type, self::$objs_with_check_for_online_status)) {
            return true;
        }

        $obj = $this->getObject($ref_id);
        if($obj_type === self::S_SURVEY) {
            return $obj->hasQuestions();
        }
        if($obj_type === self::S_TEST) {
            return count($obj->getQuestions()) > 0;
        }
        return false;
    }

    protected function getObjectTypeFor(int $ref_id): string
    {
        return \ilObject::_lookupType($ref_id, true);
    }

    protected function getObject(int $ref_id): \ilObject
    {
        return \ilObjectFactory::getInstanceByRefId($ref_id);
    }

}
