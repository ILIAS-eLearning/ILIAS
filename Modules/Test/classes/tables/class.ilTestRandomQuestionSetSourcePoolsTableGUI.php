<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolsTableGUI extends ilTable2GUI
{
	public function __construct($parentGUI, $parentCMD)
	{
		parent::__construct($parentGUI, $parentCMD);
	}
	
	public function build()
	{
		
	}
}
