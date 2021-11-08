<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserObjectHelper
{
    public function lookupFields($user_id) : array
    {
        return ilObjUser::_lookupFields($user_id);
    }
}
