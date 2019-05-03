<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived task provider
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
interface ilDerivedTaskProvider
{
	/**
	 * Get providers
	 *
	 * @param int $user_id
	 * @return ilDerivedTask[]
	 */
	public function getTasks(int $user_id): array;

	/**
	 * Is provider active?
	 *
	 * @return bool
	 */
	public function isActive(): bool;

}