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
* Object XML Parser
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilSaxParser
*/

include_once './Services/Xml/classes/class.ilSaxParser.php';
include_once('./webservice/soap/classes/class.ilObjectXMLException.php');

class ilObjectXMLParser extends ilSaxParser
{
    public $object_data = array();
    
    private $ref_id;
    private $parent_id;

    /**
    * Constructor
    *
    * @param	object		$a_content_object	must be of type ilObjContentObject
    *											ilObjTest or ilObjQuestionPool
    * @param	string		$a_xml_file			xml data
    * @param	string		$a_subdir			subdirectory in import directory
    * @access	public
    */
    public function __construct($a_xml_data = '', $throwException = false)
    {
        parent::__construct('', $throwException);
        $this->setXMLContent($a_xml_data);
    }

    public function getObjectData()
    {
        return $this->object_data ? $this->object_data : array();
    }
    
    /**
    * parse xml file
    *
    * @access	private
    * @throws ilSaxParserException
    * @throws ilObjectXMLException
    */
    public function parse($a_xml_parser, $a_fp = null)
    {
        parent::parse($a_xml_parser, $a_fp);
    }
    

    /**
    * set event handlers
    *
    * @param	resource	reference to the xml parser
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    


    /**
    * handler for begin of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    * @param	array		$a_attribs			element attributes array
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case 'Objects':
                $this->curr_obj = -1;
                break;

            case 'Object':
                ++$this->curr_obj;
                
                $this->__addProperty('type', $a_attribs['type']);
                $this->__addProperty('obj_id', is_numeric($a_attribs['obj_id'])?(int) $a_attribs["obj_id"] :  ilUtil::__extractId($a_attribs["obj_id"], IL_INST_ID));
                $this->__addProperty('offline', $a_attribs['offline']);
                break;

            case 'Title':
                break;

            case 'Description':
                break;

            case 'Owner':
                break;

            case 'CreateDate':
                break;

            case 'LastUpdate':
                break;
                
            case 'ImportId':
                break;

            case 'References':
                $this->time_target = array();
                $this->ref_id = $a_attribs["ref_id"];
                $this->parent_id = $a_attribs['parent_id'];
                break;
                
            case 'TimeTarget':
                $this->time_target['timing_type'] = $a_attribs['type'];
                break;
            
            case 'Timing':
                $this->time_target['timing_visibility'] = $a_attribs['visibility'];
                if (isset($a_attribs['starting_time'])) {
                    $this->time_target['starting_time'] = $a_attribs['starting_time'];
                }
                if (isset($a_attribs['ending_time'])) {
                    $this->time_target['ending_time'] = $a_attribs['ending_time'];
                }
                    
                if ($a_attribs['ending_time'] < $a_attribs['starting_time']) {
                    throw new ilObjectXMLException('Starting time must be earlier than ending time.');
                }
                break;
                
            case 'Suggestion':
                $this->time_target['changeable'] = $a_attribs['changeable'];
                
                
                if (isset($a_attribs['starting_time'])) {
                    $this->time_target['suggestion_start'] = $a_attribs['starting_time'];
                }
                if (isset($a_attribs['ending_time'])) {
                    $this->time_target['suggestion_end'] = $a_attribs['ending_time'];
                }
                break;
                
        }
    }

    /**
    * handler for end of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        switch ($a_name) {
            case 'Objects':
                break;

            case 'Object':
                break;

            case 'Title':
                $this->__addProperty('title', trim($this->cdata));
                break;

            case 'Description':
                $this->__addProperty('description', trim($this->cdata));
                break;

            case 'Owner':
                $this->__addProperty('owner', trim($this->cdata));
                break;

            case 'CreateDate':
                $this->__addProperty('create_date', trim($this->cdata));
                break;

            case 'LastUpdate':
                $this->__addProperty('last_update', trim($this->cdata));
                break;
                
            case 'ImportId':
                $this->__addProperty('import_id', trim($this->cdata));
                break;

            case 'References':
                $this->__addReference($this->ref_id, $this->parent_id, $this->time_target);
                break;
        }

        $this->cdata = '';

        return;
    }

    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }

    // PRIVATE
    public function __addProperty($a_name, $a_value)
    {
        $this->object_data[$this->curr_obj][$a_name] = $a_value;
    }

    /**
     * @throws ilObjectXMLException
     */
    public function __addReference($a_ref_id, $a_parent_id, $a_time_target)
    {
        $reference['ref_id'] = $a_ref_id;
        $reference['parent_id'] = $a_parent_id;
        $reference['time_target'] = $a_time_target;

        if (isset($reference['time_target']['changeable']) and $reference['time_target']['changeable']) {
            if (!isset($reference['time_target']['suggestion_start']) or !isset($reference['time_target']['suggestion_end'])) {
                throw new ilObjectXMLException('Missing attributes: "starting_time" and "ending_time" required for attribute "changeable"');
            }
        }
        
        $this->object_data[$this->curr_obj]['references'][] = $reference;
    }
}
