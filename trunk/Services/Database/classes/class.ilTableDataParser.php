<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilTableDataParser extends ilSaxParser
{
	protected $cdata = '';
	protected $table = '';
	protected $values = array();
	
	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	public function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}
	
	/**
	 * handler for begin of element
	 */
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
 	{
		switch($a_name)
		{
			case 'Table':
				$this->table = $a_attribs['name'];
				break;
				
			case 'Row':
				$this->values = array();
				$this->num_values = -1;
				break;
						
			case 'Value':
				$this->cdata = null;
				$this->num_values++;
				$this->values[$this->num_values]['name'] = $a_attribs['name'];
				$this->values[$this->num_values]['type'] = $a_attribs['type'];
				break;
		}
	}

	/**
	 * handler for begin of element
	 */
	function handlerEndTag($a_xml_parser, $a_name)
 	{
		global $ilDB;
		
		switch($a_name)
		{
			case 'Table':
				break;
				
			case 'Row':
				
				$val = array();
				foreach($this->values as $key => $data)
				{
					$val[$data['name']] = array($data['type'],$data['val']);
				}				
				$ilDB->insert($this->table,$val);
				break;
					
			case 'Value':
				$this->values[$this->num_values]['val'] = $this->cdata;
				break;
		}
	}
	
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		if($this->cdata != null)
		{
			$this->cdata .= $a_data;
		}
		else
		{
			$this->cdata = $a_data;			
		}
	}
}
