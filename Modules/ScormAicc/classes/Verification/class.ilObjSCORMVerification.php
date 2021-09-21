<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * SCORM Verification
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesScormAicc
 */
class ilObjSCORMVerification extends ilVerificationObject
{
    protected function initType() : void
    {
        $this->type = 'scov';
    }

    protected function getPropertyMap() : array
    {
        return [
            'issued_on' => self::TYPE_DATE,
            'file' => self::TYPE_STRING
        ];
    }
}
