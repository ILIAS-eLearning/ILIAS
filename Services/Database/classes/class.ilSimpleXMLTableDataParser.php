<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilSimpleXMLTableDataParser extends ilSaxParser
{
	protected $file = null;
	protected $xml = null;
	
	protected $value = '';
	
	public function __construct($a_xml)
	{
		$this->file = $a_xml;
		
		$this->xml = simplexml_load_file($this->file);
	}
	
	public function startParsing()
	{
		global $ilDB;
		
		$table = $this->xml->xpath('/Table');
		foreach($table[0]->attributes() as $k => $v)
		{
			$this->table = $v;
		}

		foreach($this->xml->Row as $row)
		{
			$data = array();
			foreach($row->children() as $value)
			{
				$type = (string) $value['type'];
				$content = (string) $value;
				$data[(string) $value['name']] = array(
					$type,$content);
				
			}
			$ilDB->insert($this->table,$data);
		}
		
	}

}
?>