<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Derived task provider factory
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilDerivedTaskProviderFactory
{
    /**
     * Get providers
     *
     * @return ilDerivedTaskProvider[]
     */
    public function getProviders() : array;
}
