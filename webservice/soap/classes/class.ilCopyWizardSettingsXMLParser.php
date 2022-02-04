<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilCopyWizardSettingsXMLParser extends ilSaxParser
{
    private $options;
    private $source_id;
    private $target_id;
    private $default_action;

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct($xml)
    {
        parent::__construct('', true);
        $this->setXMLContent($xml);
    }

    /**
    * set event handlers
    *
    * @param	resource	reference to the xml parser
    * @access	private
    */
    public function setHandlers($a_xml_parser) : void
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
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        switch ($a_name) {
      case 'Settings':
        $this->options = array();
        $this->source_id = (int) $a_attribs["source_id"];
        if (ilObject::_isInTrash($this->source_id)) {
            throw new ilSaxParserException("Source id " . $this->source_id . " is in trash");
        }
        $this->target_id = (int) $a_attribs["target_id"];
        if (ilObject::_isInTrash($this->target_id)) {
            throw new ilSaxParserException("target id" . $this->target_id . " is in trash");
        }

        $this->default_action = ilCopyWizardSettingsXMLParser::getActionForString($a_attribs["default_action"]);
        break;
      case 'Option':
          $id = (int) $a_attribs["id"];
          if (ilObject::_isInTrash($id)) {
              throw new ilSaxParserException("Id $id is in trash");
          }
          if (!$tree->isInTree($id)) {
              throw new ilSaxParserException("Id $id does not exist");
          }

                $action = ilCopyWizardSettingsXMLParser::getActionForString($a_attribs["action"]);
                $type = ilObjectFactory::getTypeByRefId($id);


                switch ($action) {
                    case ilCopyWizardOptions::COPY_WIZARD_COPY:
                            $perm_copy = $ilAccess->checkAccess('copy', '', $id);
                            $copy = $objDefinition->allowCopy($type);

                            if ($perm_copy && $copy) {
                                $this->options [$id] = array("type" => $action);
                            } elseif ($copy && !$perm_copy) {
                                throw new ilSaxParserException("Missing copy permission for object " . $id);
                            } elseif (!$copy) {
                                throw new ilSaxParserException("Copy for object " . $id . " of type " . $type . " is not supported");
                            }
                            break;
                    case ilCopyWizardOptions::COPY_WIZARD_LINK:
                            $perm_link = $ilAccess->checkAccess('write', '', $id);
                            $link = $objDefinition->allowLink($type);

                            if ($perm_link && $link) {
                                $this->options [$id] = array("type" => $action);
                            } elseif ($copy && !$perm_link) {
                                throw new ilSaxParserException("Missing write permission for object " . $id);
                            } elseif (!$link) {
                                throw new ilSaxParserException("Link for object " . $id . " of type " . $type . " is not supported");
                            }
                            break;
                }
    }
    }


    /**
     * read access to options array
     *
     * @return array key is reference id, value is assoc. array with type and action
     */
    public function getOptions() : array
    {
        return is_array($this->options) ? $this->options : array();
    }

    /**
     * read access to source id
     */
    public function getSourceId() : ?int
    {
        return $this->source_id;
    }

    /**
     * read access to target id
     */
    public function getTargetId() : ?int
    {
        return $this->target_id;
    }

    private static function getActionForString($s) : int
    {
        if ($s == "COPY") {
            return ilCopyWizardOptions::COPY_WIZARD_COPY;
        }
        if ($s == "LINK") {
            return ilCopyWizardOptions::COPY_WIZARD_LINK;
        }
        return ilCopyWizardOptions::COPY_WIZARD_OMIT;
    }

    public function handlerEndTag($a_xml_parser, $a_name) : void
    {
    }

    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
    }
}
