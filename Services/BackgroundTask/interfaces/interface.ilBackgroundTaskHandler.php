<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Background task handler interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesBackgroundTask
 */
interface ilBackgroundTaskHandler
{	
	public static function getInstanceFromTask(ilBackgroundTask $a_task);
	
	public function getTask();
	
	public function init($params);
	
	public function process();
	
	public function cancel();
	
	public function finish();
}