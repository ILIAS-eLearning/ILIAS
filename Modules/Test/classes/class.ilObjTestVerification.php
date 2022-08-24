<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Test Verification
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesTest
 */
class ilObjTestVerification extends ilVerificationObject
{
    protected function initType(): void
    {
        $this->type = 'tstv';
    }

    protected function getPropertyMap(): array
    {
        return [
            'issued_on' => self::TYPE_DATE,
            'file' => self::TYPE_STRING
        ];
    }
}
