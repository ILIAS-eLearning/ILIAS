<?php
/*
 * Created on 26.02.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once "./classes/class.ilSaxParser.php";
include_once "./classes/class.ilSaxParserException.php";
include_once "./Services/CopyWizard/classes/class.ilCopyWizardOptions.php";

class ilCopyWizardSettingsXMLParser extends ilSaxParser
{

  var $options;
  var $source_id;
  var $target_id;
  var $default_action;

  /**
  * constructor
  * @param	string	xml version
  * @param	string	output encoding
  * @param	string	input encoding
  * @access	public
  */
  function ilCopyWizardSettingsXMLParser($xml)
  {
    parent::ilSaxParser('', true);
    $this->setXMLContent($xml);
  }

  /**
  * set event handlers
  *
  * @param	resource	reference to the xml parser
  * @access	private
  */
  function setHandlers($a_xml_parser)
  {
  	xml_set_object($a_xml_parser,$this);
    xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
    xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
  }

  /**
  * handler for begin of element
  *
  * @param	resource	$a_xml_parser		xml parser
  * @param	string		$a_name				element name
  * @param	array		$a_attribs			element attributes array
  */
  function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
  {
  	switch($a_name)
    {
      case 'Settings':
        $this->options = array();
        $this->source_id = (int) $a_attribs["source_id"];
    	if(ilObject::_isInTrash($this->source_id))
		{
			throw new ilSaxParserException("Source id ".$this->source_id." is in trash");
		}
        $this->target_id = (int) $a_attribs["target_id"];
        if(ilObject::_isInTrash($this->target_id))
		{
			throw new ilSaxParserException("target id".$this->target_id." is in trash");
		}
        
        $this->default_action = ilCopyWizardSettingsXMLParser::getActionForString($a_attribs["default_action"]);
        break;
      case 'Option':
      	$id = (int) $a_attribs["id"];
      	if(ilObject::_isInTrash($id))
		{
			throw new ilSaxParserException("Id $id is in trash");
		}      	
		$this->options [$id] = array("type" => ilCopyWizardSettingsXMLParser::getActionForString($a_attribs["action"]));
        break;
    }
  }


  /**
   * read access to options array
   *
   * @return array key is reference id, value is assoc. array with type and action
   */
  function getOptions() {
    return is_array($this->options) ? $this->options : array();
  }
  
  /**
   * read access to source id
   *
   * @return int
   */
  function getSourceId() {
  	return $this->source_id;
  }
  
  /**
   * read access to target id
   *
   * @return int
   */
  	function getTargetId()  {
  		return $this->target_id;
  	}

  	private static function getActionForString($s){
  		if ($s == "COPY")
  			return ilCopyWizardOptions::COPY_WIZARD_COPY;
  		if ($s == "LINK")
  			return ilCopyWizardOptions::COPY_WIZARD_LINK;
  		return ilCopyWizardOptions::COPY_WIZARD_OMIT;
   	}

	function handlerEndTag($a_xml_parser,$a_name)
	{
	}

	/**
	* handler for character data
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_data				character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data) {	
	}
}
?>
