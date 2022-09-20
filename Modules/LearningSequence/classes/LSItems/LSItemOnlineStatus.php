<?php

declare(strict_types=1);

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

    private static array $obj_with_online_status = array(
        self::S_LEARNMODULE_IL,
        self::S_LEARNMODULE_HTML,
        self::S_SAHS,
        self::S_TEST,
        self::S_SURVEY
    );

    public function setOnlineStatus(int $ref_id, bool $status): void
    {
        $obj = \ilObjectFactory::getInstanceByRefId($ref_id);
        $obj->setOfflineStatus(!$status);
        $obj->update();
    }

    public function getOnlineStatus(int $ref_id): bool
    {
        if (!$this->hasOnlineStatus($ref_id)) {
            return true;
        }
        return !\ilObject::lookupOfflineStatus(\ilObject::_lookupObjId($ref_id));
    }

    public function hasOnlineStatus(int $ref_id): bool
    {
        $type = $this->getObjectTypeFor($ref_id);
        return in_array($type, self::$obj_with_online_status);
    }

    protected function getObjectTypeFor(int $ref_id): string
    {
        return \ilObject::_lookupType($ref_id, true);
    }
}
