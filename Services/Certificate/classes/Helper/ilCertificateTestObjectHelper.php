<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTestObjectHelper
{
    public function getResultPass($active_id)
    {
        return ilObjTest::_getResultPass($active_id);
    }
}
