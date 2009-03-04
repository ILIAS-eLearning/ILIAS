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

	/**
	 * test Description 
	 * @return
	 */
	public function testDescription()
	{
		include_once './Services/MetaData/classes/class.ilMDDescription.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDDescription(1,2,'xxx');
		$con->setDescription('date');
		$con->setDescriptionLanguage(new ilMDLanguageItem('en'));
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setDescription('desc2');
		$con->update();
		$con->read();
		$desc = $con->getDescription();
		$this->assertEquals('desc2',$desc);
		
		$con->delete();
	}

	/**
	 * test Educational 
	 * @return
	 */
	public function testEducational()
	{
		include_once './Services/MetaData/classes/class.ilMDEducational.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDEducational(1,2,'xxx');
		$con->setDifficulty('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setDifficulty('Medium');
		$con->update();
		$con->read();
		$desc = $con->getDifficulty();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test Entity 
	 * @return
	 */
	public function testEntity()
	{
		include_once './Services/MetaData/classes/class.ilMDEntity.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDEntity(1,2,'xxx');
		$con->setEntity('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setEntity('Medium');
		$con->update();
		$con->read();
		$desc = $con->getEntity();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test Format 
	 * @return
	 */
	public function testFormat()
	{
		include_once './Services/MetaData/classes/class.ilMDFormat.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDFormat(1,2,'xxx');
		$con->setFormat('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setFormat('Medium');
		$con->update();
		$con->read();
		$desc = $con->getFormat();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test General 
	 * @return
	 */
	public function testGeneral()
	{
		include_once './Services/MetaData/classes/class.ilMDGeneral.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDGeneral(1,2,'xxx');
		$con->setCoverage('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setCoverage('Medium');
		$con->update();
		$con->read();
		$desc = $con->getCoverage();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test Identifier 
	 * @return
	 */
	public function testIdentifier()
	{
		include_once './Services/MetaData/classes/class.ilMDIdentifier.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDIdentifier(1,2,'xxx');
		$con->setCatalog('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setCatalog('Medium');
		$con->update();
		$con->read();
		$desc = $con->getCatalog();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test Identifier_ 
	 * @return
	 */
	public function testIdentifier_()
	{
		include_once './Services/MetaData/classes/class.ilMDIdentifier_.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDIdentifier_(1,2,'xxx');
		$con->setCatalog('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setCatalog('Medium');
		$con->update();
		$con->read();
		$desc = $con->getCatalog();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test Keyword 
	 * @return
	 */
	public function testKeyword()
	{
		include_once './Services/MetaData/classes/class.ilMDKeyword.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDKeyword(1,2,'xxx');
		$con->setKeyword('Easy');
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setKeyword('Medium');
		$con->update();
		$con->read();
		$desc = $con->getKeyword();
		$this->assertEquals('Medium',$desc);
		
		$con->delete();
	}

	/**
	 * test Language 
	 * @return
	 */
	public function testLanguage()
	{
		include_once './Services/MetaData/classes/class.ilMDLanguage.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDLanguage(1,2,'xxx');
		$con->setLanguage(new ilMDLanguageItem('en'));
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setLanguage(new ilMDLanguageItem('de'));
		$con->update();
		$con->read();
		$desc = $con->getLanguageCode();
		$this->assertEquals('de',$desc);
		
		$con->delete();
	}

	/**
	 * test lifecycle
	 * @return
	 */
	public function testLifecycle()
	{
		include_once './Services/MetaData/classes/class.ilMDLifecycle.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDLifecycle(1,2,'xxx');
		$con->setVersion(1);
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setVersion(2);
		$con->update();
		$con->read();
		$desc = $con->getVersion();
		$this->assertEquals(2,$desc);
		
		$con->delete();
	}

	/**
	 * test lifecycle
	 * @return
	 */
	public function testLocation()
	{
		include_once './Services/MetaData/classes/class.ilMDLocation.php';
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		$con = new ilMDLocation(1,2,'xxx');
		$con->setLocation(1);
		$ret = $con->save();		 
		$this->assertGreaterThan(0,$ret);
		
		$con->setLocation(2);
		$con->update();
		$con->read();
		$desc = $con->getLocation();
		$this->assertEquals(2,$desc);
		
		$con->delete();
	}
}
?>
