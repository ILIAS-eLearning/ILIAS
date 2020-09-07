<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserObjectHelper
{
    public function lookupFields($user_id)
    {
        return ilObjUser::_lookupFields($user_id);
    }
}
