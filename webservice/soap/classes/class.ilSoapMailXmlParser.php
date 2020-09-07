<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Xml/classes/class.ilSaxParser.php';
include_once './Services/User/classes/class.ilObjUser.php';

/**
* XML  parser for soap mails
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup
*/
class ilSoapMailXmlParser extends ilSaxParser
{
    /**
     * Constructor
     */
    public function __construct($a_xml)
    {
        parent::__construct('', true);
        $this->setThrowException(true);
        $this->setXMLContent($a_xml);
    }
    
    /**
     * Get parsed mails
     * @return
     */
    public function getMails()
    {
        return (array) $this->mails;
    }
    
    /**
     * starts parsing
     *
     * @throws InvalidArgumentException when recipent or sender is invalid.
     * @return boolean true, if no errors happend.
     *
     */
    public function start()
    {
        $this->startParsing();
        return true;
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
            case 'Mail':
                $this->mail = array();
                $this->mail['usePlaceholders'] = $a_attribs['usePlaceholders'] ? true : false;
                $this->mail['type'] = $a_attribs['type'] == 'System' ? 'system' : 'normal';
                break;
                
            case 'To':
                $this->mail['to'] = $this->parseName($a_attribs);
                break;

            case 'Cc':
                $this->mail['cc'] = $this->parseName($a_attribs);
                break;

            case 'Bcc':
                $this->mail['bcc'] = $this->parseName($a_attribs);
                break;
                
            case 'Subject':
                break;
                
            case 'Message':
                $this->lines = array();
                break;
                
            case 'Attachment':
                $this->attachment = array();
                $this->attachment['name'] = $a_attribs['name'];
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
            case 'Mail':
                $this->mails[] = $this->mail;
                break;

            case 'Subject':
                $this->mail['subject'] = $this->cdata;
                break;
                
            case 'Message':
                $this->mail['body'] = (array) $this->lines;
                break;
                
            case 'P':
                $this->lines[] = trim($this->cdata);
                break;
                
            case 'Attachment':
                $this->attachment['content'] = base64_decode(trim($this->cdata));
                $this->mail['attachments'][] = $this->attachment;
                break;
        }
        
        $this->cdata = '';
    }
    
    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($this->in_metadata) {
            parent::handlerCharacterData($a_xml_parser, $a_data);
        }

        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);
            $this->cdata .= $a_data;
        }
    }
    
    /**
     * extract user name
     * @param object $a_attribs
     * @return
     * @throws InvalidArgumentException if recipient, sender is invalid
     */
    protected function parseName($a_attribs)
    {
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        
        if ($a_attribs['obj_id']) {
            $il_id = explode('_', $a_attribs['obj_id']);
            $GLOBALS['DIC']['ilLog']->write('il ID:' . print_r($il_id, true));
            if (!$user = ilObjectFactory::getInstanceByObjId($il_id[3], false)) {
                throw new InvalidArgumentException("Invalid user id given: obj_id => " . $a_attribs['obj_id']);
            }
            return $user->getLogin();
        } else {
            return $a_attribs['name'];
        }
    }
}
