<?php
/**
* object definition class
* 
* it handles the xml-description of all ilias objects
*
* @author Peter Gabriel <peter@gabriel-online.net>
* @version $Id$
*
* @extends PEAR
* @package ilias-core
*/
class ObjectDefinition extends PEAR
{
	/**
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function ObjectDefinition()
	{
		$this->PEAR();

		//********XML***********************************************
		//objects-typedefinition in XML
		$data = file("objects.xml");
		$data = implode($data,"");
		$this->rawdata = databay_XML2OBJ($data);
		unset($data);
		
		$this->buildList();
		//debug for xml-objectdefinitions
		//echo $this->objDef->ChildNodes[0]->countElements("object");
		//echo nl2br(htmlspecialchars(databay_OBJ2XML($this->objDef)));		
	}

	function buildList()
	{
		$this->data = array();

		for ($i=0; $i<count($this->rawdata->ChildNodes[0]->ChildNodes); $i++)
		{
			$obj = $this->rawdata->ChildNodes[0]->ChildNodes[$i];

			$data["name"] = $obj->getAttr("NAME");
			$data["subobjects"] = array();
			$data["properties"] = array();
			$data["actions"] = array();
			foreach ($obj->ChildNodes as $row)
			{
				if ($row->Name == "SUBOBJ")
				{
					$d = array();
					foreach ($row->getAttrs() as $k => $v)
					{
						$d[strtolower($k)] = $v;
					}
					if ($row->Data)
						$d["lng"] = $row->Data;
					else
						$d["lng"] = $d["name"];
					$data["subobjects"][$d["name"]] = $d;
				}
				if ($row->Name == "PROPERTY")
				{
					$d = array();
					foreach ($row->getAttrs() as $k => $v)
					{
						$d[strtolower($k)] = $v;
					}
					if ($row->Data)
						$d["lng"] = $row->Data;
					else
						$d["lng"] = $d["name"];
					$data["properties"][$d["name"]] = $d;
				}
				if ($row->Name == "ACTION")
				{
					$d = array();
					foreach ($row->getAttrs() as $k => $v)
					{
						$d[strtolower($k)] = $v;
					}
					if ($row->Data)
						$d["lng"] = $row->Data;
					else
						$d["lng"] = $d["name"];
					$data["actions"][$d["name"]] = $d;
				}
			} //foreach
			$this->data[$data["name"]] = $data;
		} //for

	} //function

	
	function getDefinition($a_objname)
	{
		return $this->data[$a_objname];
	}
	
	function getProperties($a_objname)
	{
		return $this->data[$a_objname]["properties"];
	}
		
	function getSubObjects($a_objname)
	{
		return $this->data[$a_objname]["subobjects"];
	}

	function getActions($a_objname)
	{
		return $this->data[$a_objname]["actions"];
	}

	function getFirstProperty($a_objname)
	{
		$data = array_keys($this->data[$a_objname]["properties"]);
		return $data[0];
	}

} // class
?>