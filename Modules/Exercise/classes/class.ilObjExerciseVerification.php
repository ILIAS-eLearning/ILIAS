<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exercise Verification
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExerciseVerification extends ilVerificationObject
{
    protected function initType() : void
    {
        $this->type = 'excv';
    }

    /**
     * @return array<string, int>
     */
    protected function getPropertyMap() : array
    {
        return [
            'issued_on' => self::TYPE_DATE,
            'file' => self::TYPE_STRING
        ];
    }
}
