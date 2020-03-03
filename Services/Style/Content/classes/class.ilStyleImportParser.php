<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
* Style Import Parser
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
*/
class ilStyleImportParser extends ilSaxParser
{
    /**
     * @var ilTree
     */
    protected $tree;


    /**
    * Constructor
    *
    * @param	string		$a_xml_file		xml file
    * @param	int			$a_mode			IL_EXTRACT_ROLES | IL_USER_IMPORT
    *
    * @access	public
    */
    public function __construct($a_xml_file, &$a_style_obj)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();

        $this->style_obj = $a_style_obj;

        parent::__construct($a_xml_file);
    }


    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start the parser
    */
    public function startParsing()
    {
        $this->styles = array();
        parent::startParsing();
        $this->style_obj->setStyle($this->styles);
        $this->style_obj->setCharacteristics($this->chars);
    }


    /**
    * handler for begin of element
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case "Style":
                $this->current_tag = $a_attribs["Tag"];
                $this->current_class = $a_attribs["Class"];
                $this->current_type = $a_attribs["Type"];
                if ($this->current_class == "PageTitle" && $this->current_type == "page_title" && $this->current_tag == "div") {
                    $this->current_tag = "h1";
                }
                if ($this->current_class == "Headline1" && $this->current_tag == "div") {
                    $this->current_tag = "h1";
                    $this->current_type = "heading1";
                }
                if ($this->current_class == "Headline2" && $this->current_tag == "div") {
                    $this->current_tag = "h2";
                    $this->current_type = "heading2";
                }
                if ($this->current_class == "Headline3" && $this->current_tag == "div") {
                    $this->current_tag = "h3";
                    $this->current_type = "heading3";
                }
                $this->current_tags = array();
                $this->chars[] = array("type" => $this->current_type,
                    "class" => $this->current_class);
                break;
                
            case "StyleParameter":
                $this->current_tags[] = array(
                    "tag" => $this->current_tag,
                    "class" => $this->current_class,
                    "parameter" => $a_attribs["Name"],
                    "type" => $this->current_type,
                    "value" => $a_attribs["Value"],
                    "custom" => $a_attribs["Custom"]);
                break;
                
            case "StyleColor":
                $this->style_obj->addColor($a_attribs["Name"], $a_attribs["Code"]);
                break;

            case "StyleTemplate":
                $this->cur_template = array("type" => $a_attribs["Type"],
                    "name" => $a_attribs["Name"]);
                $this->cur_template_classes = array();
                break;
                
            case "StyleTemplateClass":
                $this->cur_template_classes[$a_attribs["ClassType"]] =
                    $a_attribs["Class"];
                break;

        }
        $this->cdata = "";
    }


    /**
    * handler for end of element
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        switch ($a_name) {
            case "Title":
                $this->style_obj->setTitle($this->cdata);
                break;
                
            case "Description":
                $this->style_obj->setDescription($this->cdata);
                break;
                
            case "Style":
                $this->styles[] = $this->current_tags;
                break;
                
            case "StyleTemplate":
                $this->style_obj->addTemplate(
                    $this->cur_template["type"],
                    $this->cur_template["name"],
                    $this->cur_template_classes
                );
                break;

        }
    }

    /**
    * handler for character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        // i don't know why this is necessary, but
        // the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
        // in character data, but we don't want that, because it's the
        // way we mask user html in our content, so we convert back...
        $a_data = str_replace("<", "&lt;", $a_data);
        $a_data = str_replace(">", "&gt;", $a_data);

        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);
        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }
}
