<?php

class ilXMLResultSet 
{
		var $colspecs = array();
		var $rows = array();
				
		
		function ilXMLResultSet () 
		{
		}
			
		function addColumn($columname) 
		{
			$this->colspecs [$columname] = new ilXMLResultSetColumn (count($this->colspecs), $columname);
		}
		
		function getColSpecs () 
		{
			return $this->colspecs;
		}
		
		function getRows () {
			return $this->rows;
		}
		
		function addRow (&$row) {
			$this->rows [] = $row;
		}
				
}

class ilXMLResultSetRow {
	var $columns = array();
	
	function setValue ($index, $value)
	{
		$this->columns[$index] = $value;		
	}
	
	function getColumns () {
		return $this->columns;
	}
}

class ilXMLResultSetColumn {
	var $name;
	var $index;
	
	function ilXMLResultSetColumn ($index, $name)
	{
		$this->name = $name;
		$this->index = $index;
	}
	
	function getName () 
	{
		return $this->name;
	}
	
	function getIndex () 
	{
		return $this->index;
	}
}
?>