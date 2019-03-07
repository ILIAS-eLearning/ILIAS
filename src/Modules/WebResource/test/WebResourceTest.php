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

namespace ILIAS\Modules\WebResource\Test;

use ILIAS\Modules\WebResource\ParameterAppender;
use ilLinkCheckNotify;
use ilUnitUtil;
use PHPUnit_Framework_TestCase;

/**
 * Class WebResourceTest
 *
 * Unit tests for tree table
 *
 * @package ILIAS\Modules\WebResource\Test
 *
 * @group   needsInstalledILIAS
 *
 * @ingroup ServicesTree
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class WebResourceTest extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;


	protected function setUp() {
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
		global $DIC;

		$ilDB = $DIC['ilDB'];

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
	public function testWebResourceParameters() {
		$appender = new ParameterAppender(999);
		$appender->setName('first');
		$appender->setValue(1);
		$appender->add(888);

		$params = ParameterAppender::_getParams(888);
		foreach ($params as $key => $data) {
			$appender->delete($key);
			$this->assertEquals($data['name'],'first');
			$this->assertEquals($data['value'],1);
		}
		
	}
}
?>
