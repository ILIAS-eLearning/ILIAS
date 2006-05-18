<?php


include_once "./classes/class.ilXmlWriter.php";

class ilXMLResultSetWriter extends ilXmlWriter
{
	var $xmlResultSet;

	function ilXMLResultSetWriter(&$xmlResultSet)
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
