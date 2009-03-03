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
class ilMDTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function testCopyright()
	{
		include_once './Services/MetaData/classes/class.ilMDCopyrightSelectionEntry.php';
		
		$cpr = new ilMDCopyrightSelectionEntry(0);
		$cpr->setTitle("1");
		$cpr->setDescription("2");
		$cpr->setLanguage('en');
		$cpr->setCopyright("3");
		$cpr->setCosts(true);
		
		$cpr->add();
		
		$entry = $cpr->getEntryId();
		$this->assertGreaterThan(0,$entry);
		
		$cpr = new ilMDCopyrightSelectionEntry($entry);
		$ret = $cpr->getTitle();
		$this->assertEquals($ret,'1');

		$ret = $cpr->getDescription();
		$this->assertEquals($ret,'2');
		
		$ret = $cpr->getCopyright();
		$this->assertEquals($ret,'3');
		
		$ret = $cpr->getLanguage();
		$this->assertEquals($ret,'en');
		
		$cpr->setTitle('11');
		$cpr->update();
		
		$cpr->delete();
	}
	
	/**
	 * test annotation 
	 * @param
	 * @return
	 */
	public function testAnnotation()
	{
		include_once './Services/MetaData/classes/class.ilMDAnnotation.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';
		
		$ann = new ilMDAnnotation(1,2,'xxx');
		$ann->setDescription("desc");
		$ann->setDescriptionLanguage(new ilMDLanguageItem('en'));
		$ann->setEntity('ent');
		$ann->setDate('date');
		$ret = $ann->save();			 
		$this->assertGreaterThan(0,$ret);
		
		$ann->setDescription('desc2');
		$ann->update();
		$ann->read();
		$desc = $ann->getDescription();
		$this->assertEquals('desc2',$desc);
		
		$ann->delete();
	}
	
	/**
	 * test classification 
	 * @param
	 * @return
	 */
	public function testClassification()
	{
		include_once './Services/MetaData/classes/class.ilMDClassification.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';
		
		$ann = new ilMDClassification(1,2,'xxx');
		$ann->setDescription("desc");
		$ann->setDescriptionLanguage(new ilMDLanguageItem('en'));
		$ann->setPurpose('purp');
		$ret = $ann->save();			 
		$this->assertGreaterThan(0,$ret);
		
		$ann->setDescription('desc2');
		$ann->update();
		$ann->read();
		$desc = $ann->getDescription();
		$this->assertEquals('desc2',$desc);
		
		$ann->delete();
	}
	
	/**
	 * test contribute 
	 * @return
	 */
	public function testContribute()
	{
		include_once './Services/MetaData/classes/class.ilMDContribute.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDContribute(1,2,'xxx');
		$con->setDate('date');
		$con->setRole('ScriptWriter');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setDate('desc2');
		$con->update();
		$con->read();
		$desc = $con->getDate();
		$this->assertEquals('desc2',$desc);
		
		$con->delete();
	}
}
?>
