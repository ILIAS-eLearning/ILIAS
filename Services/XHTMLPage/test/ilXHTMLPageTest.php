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

class ilXHTMLPageTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}

	/**
	 * @group IL_Init
	 */
	public function testSetGetSettings()
	{
		include_once("./Services/XHTMLPage/classes/class.ilXHTMLPage.php");
		
		$page = new ilXHTMLPage();
		$page->setContent("aaa");
		$page->save();
		$page_id = $page->getId();
		
		// save/read
		$page = new ilXHTMLPage($page_id);
		if ($page->getContent() == "aaa")
		{
			$result.= "saveread-";
		}
		$page->setContent("bbb");
		$page->save();
		
		// lookups
		if (ilXHTMLPage::_lookupContent($page_id) == "bbb")
		{
			$result.= "lookupContent-";
		}

		if (ilXHTMLPage::_lookupSavedContent($page_id) == "aaa")
		{
			$result.= "lookupSavedContent-";
		}
		
		// undo
		$page->undo();
		
		if (ilXHTMLPage::_lookupContent($page_id) == "aaa")
		{
			$result.= "undo1-";
		}

		if (ilXHTMLPage::_lookupSavedContent($page_id) == "bbb")
		{
			$result.= "undo2-";
		}
		
		// clear
		$page->clear();
		if (ilXHTMLPage::_lookupContent($page_id) == "")
		{
			$result.= "clear1-";
		}

		if (ilXHTMLPage::_lookupSavedContent($page_id) == "aaa")
		{
			$result.= "clear2-";
		}

		$this->assertEquals("saveread-lookupContent-lookupSavedContent-undo1-undo2-clear1-clear2-", $result);
	}
	
}
?>
