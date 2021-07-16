<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserCreationAttributeMappingFilter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserCreationAttributeMappingFilter extends FilterIterator
{
    public function accept() : bool
    {
        return true;
    }
}
