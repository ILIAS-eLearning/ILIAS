<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
