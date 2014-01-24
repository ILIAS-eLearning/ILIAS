<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Unit tests for tree table
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesTree
*/
class ilTrackingTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}

	/**
	 * change event test
	 * @group IL_Init
	 * @param
	 * @return
	 */
	public function testChangeEvent()
	{
		global $ilUser;
		
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$ret = ilChangeEvent::_deactivate();
		$ret = ilChangeEvent::_activate();

		$res = ilChangeEvent::_lookupUncaughtWriteEvents(9,$ilUser->getId());
		$res = ilChangeEvent::_lookupChangeState(9,$ilUser->getId());
		$res = ilChangeEvent::_lookupInsideChangeState(9,$ilUser->getId());
	}
	
	/**
	 * Test lp object settings 
	 */
	/* this won't work currently because of ilLPStatusWrapper expecting an
	   valid object id, not pseudo-objects like 9999
	public function testLPObjectSettings()
	{
		include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
		
		ilLPObjSettings::_delete(9999);
		$settings = new ilLPObjSettings(9999);
		$settings->setMode(127);
		$settings->obj_type = "xxx";
		$settings->insert();

		$type = $settings->getObjType();
		$this->assertEquals($type,'xxx');
		
		$settings->setVisits(10);
		$settings->update();
		$visits = $settings->getVisits();
		$this->assertEquals($visits,10);
		
		$settings->cloneSettings(9998);
		ilLPObjSettings::_delete(9999);
		
		$settings = new ilLPObjSettings(9998);
		$type = $settings->getObjType();
		$this->assertEquals($type,'xxx');
		
		ilLPObjSettings::_delete(9998);
	}	 
	*/
	
	/**
	 * Test LP marks
	 * @group IL_Init
	 * @param
	 * @return
	 */
	public function testLPMarks()
	{
		include_once './Services/Tracking/classes/class.ilLPMarks.php';
		include_once './Services/Tracking/classes/class.ilLPStatusFactory.php';
		
		$marks = new ilLPMarks(999,888);
		$marks->setMark('Gut');
		$marks->setComment('Weiter so');
		$marks->setCompleted(true);
		$marks->update();
		
		$marks = new ilLPMarks(999,888);
		$mark = $marks->getMark();
		$this->assertEquals($mark,'Gut');
		
		$comment = ilLPMarks::_lookupComment(888,999);
		$this->assertEquals($comment,'Weiter so');
		
		$class = ilLPStatusFactory::_getClassById(999, ilLPObjSettings::LP_MODE_MANUAL);
		$completed = $class::_getCompleted(999);
		$this->assertEquals(array(888),$completed);
		
		ilLPMarks::deleteObject(999);
	}

}
?>
