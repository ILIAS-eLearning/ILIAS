<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarLogWriter
 */
interface ilAptarLogWriter
{
	/**
	 * @param array $message
	 * @return void
	 */
	public function write(array $message);

	/**
	 * @return void
	 */
	public function shutdown();
}