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
class ilwebresourceTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	 * Link check test
	 * @group IL_Init
	 * @param
	 * @return
	 */
	public function testLinkCheck()
	{
		global $ilDB;
		
		include_once './Services/LinkChecker/classes/class.ilLinkCheckNotify.php';
		
		$not = new ilLinkCheckNotify($ilDB);
		$not->setObjId(99999);
		$not->setUserId(13);
		$ret = $not->addNotifier();
		$this->assertEquals($ret,true);
		
		$status = ilLinkCheckNotify::_getNotifyStatus(13,99999);
		$this->assertEquals($status,true);
		
		$notifiers = ilLinkCheckNotify::_getNotifiers(99999);
		$this->assertEquals($notifiers,array(13));
		
		$del = ilLinkCheckNotify::_deleteObject(99999);
		$this->assertEquals($del,true);
	}

	/**
	 * @group IL_Init
	 */
	public function testWebResourceParameters()
	{
		include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
		
		$appender = new ilParameterAppender(999);
		$appender->setName('first');
		$appender->setValue(1);
		$appender->add(888);
		
		$params = ilParameterAppender::_getParams(888);
		foreach($params as $key => $data)
		{
			$appender->delete($key);
			$this->assertEquals($data['name'],'first');
			$this->assertEquals($data['value'],1);
		}
		
	}
}
?>
