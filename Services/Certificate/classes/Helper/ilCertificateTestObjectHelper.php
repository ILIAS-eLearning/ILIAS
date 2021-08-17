<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTestObjectHelper
{
    public function getResultPass(int $active_id) : ?int
    {
        return ilObjTest::_getResultPass($active_id);
    }
}
