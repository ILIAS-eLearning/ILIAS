<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserUpdateAttributeMappingFilter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserUpdateAttributeMappingFilter extends FilterIterator
{
    public function accept() : bool
    {
        /** @var $current ilExternalAuthUserAttributeMappingRule */
        $current = $this->current();

        return $current->isAutomaticallyUpdated();
    }
}
