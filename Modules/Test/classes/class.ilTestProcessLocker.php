<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
abstract class ilTestProcessLocker
{
	public function requestTestStartLockCheckLock()
	{
		// overwrite method in concrete locker if something to do
	}
	
	public function releaseTestStartLockCheckLock()
	{
		// overwrite method in concrete locker if something to do
	}

	public function requestRandomPassBuildLock()
	{
		// overwrite method in concrete locker if something to do
	}

	public function releaseRandomPassBuildLock()
	{
		// overwrite method in concrete locker if something to do
	}
}