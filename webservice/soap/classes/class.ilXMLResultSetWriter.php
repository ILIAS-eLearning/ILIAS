<?php


  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
   * XML Writer for XMLResultSet
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilXMLResultSet.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */

include_once "./classes/class.ilXmlWriter.php";

class ilXMLResultSetWriter extends ilXmlWriter
{
	var $xmlResultSet;

	function ilXMLResultSetWriter(& $xmlResultSet)
	{
		parent::ilXmlWriter();
		$this->xmlResultSet = $xmlResultSet;
	}


	function start()
	{
		if(!is_object($this->xmlResultSet))
		{
			return false;
		}

		$this->__buildHeader();

		$this->__buildColSpecs();

		$this->__buildRows();

		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}


	// PRIVATE
	function __appendRow(&$xmlResultSetRow)
	{
		$this->xmlStartTag('row',null);

		foreach ($xmlResultSetRow->getColumns() as $value)
		{
			$this->xmlElement('column',null,$value);

		}

		$this->xmlEndTag('row');

	}


	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE result PUBLIC \"-//ILIAS//DTD XMLResultSet//EN\" \"http://www.ilias.uni-koeln.de/download/dtd/ResultSet.dtd\">");
		$this->xmlHeader();

		$this->xmlStartTag("result");

		return true;
	}

	function __buildColSpecs() {
		$this->xmlStartTag("colspecs");

		foreach ($this->xmlResultSet->getColSpecs() as $colSpec) {
			$attr  = array ("idx" => $colSpec->getIndex(), "name" => $colSpec->getName());

			$this->xmlElement("colspec", $attr, null);
		}

		$this->xmlEndTag("colspecs");
	}

	function __buildRows () {
		$this->xmlStartTag("rows");

		foreach($this->xmlResultSet->getRows() as $row)
		{
			$this->__appendRow($row);
		}

		$this->xmlEndTag("rows");
	}

	function __buildFooter()
	{
		$this->xmlEndTag('result');
	}



}


?>
