<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserUpdateAttributeMappingFilter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserUpdateAttributeMappingFilter extends FilterIterator
{
    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        /** @var $current ilExternalAuthUserAttributeMappingRule */
        $current = parent::current();

        return $current->isAutomaticallyUpdated();
    }
}
