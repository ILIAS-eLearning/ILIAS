<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived task provider factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
interface ilDerivedTaskProviderFactory
{
	/**
	 * Get providers
	 *
	 * @return ilDerivedTaskProvider[]
	 */
	public function getProviders(): array;

}