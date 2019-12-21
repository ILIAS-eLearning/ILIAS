<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class for creating an object (new node) by parsing XML code and adding it to an existing DOM object
*
* @author	Jens Conze <jconze@databay.de>
* @version	$Id$
*/

class XMLStruct
{
    public $childs = array();	// child nodes
    public $parent;			// parent node
    public $name;				// tag name
    public $content = array();	// tag content
    public $attrs;				// tag attributes

    /**
    * constructor
    */
    public function __construct($a_name = "", $a_attrs = array())
    {
        $this->name = $a_name;
        $this->attrs = $a_attrs;
    }

    /**
    * append node
    */
    public function append($a_name, $a_attrs)
    {
        $struct = new XMLStruct($a_name, $a_attrs);
        $struct->parent =&$GLOBALS["lastObj"];

        $GLOBALS["lastObj"] =&$struct;
        $this->childs[] =&$struct;
    }

    /**
    * set parent node
    */
    public function setParent()
    {
        $GLOBALS["lastObj"] =&$GLOBALS["lastObj"]->parent;
    }

    /**
    * set content text
    */
    public function setContent($a_data)
    {
        //echo "<br>XMLStruct:setContent-".$this->name."-$a_data-";
        $this->content[] = $a_data;
    }

    /**
    * insert new node in existing DOM object
    * @param	object	$dom	DOM object
    * @param	object	$node	parent node
    */
    public function insert(&$dom, &$node)
    {
        $newNode = $dom->create_element($this->name);
        if ($this->content != "") {
            $newNode->set_content(implode("", $this->content));
        }
        if (is_array($this->attrs)) {
            #vd($this->attrs);
            reset($this->attrs);
            while (list($key, $val) = each($this->attrs)) {
                $newNode->set_attribute($key, $val);
            }
        }
        $node = $node->append_child($newNode);
        for ($j = 0; $j < count($this->childs); $j++) {
            $this->childs[$j]->insert($dom, $node);
        }
        $node = $node->parent_node();
    }
}

class XML2DOM
{
    public function __construct($a_xml)
    {
        $xml_parser = xml_parser_create("UTF-8");
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");

        if (!xml_parse($xml_parser, $a_xml, true)) {
            die(sprintf(
                "XML error: %s at line %d",
                xml_error_string(xml_get_error_code($xml_parser)),
                xml_get_current_line_number($xml_parser)
            ));
        }
        xml_parser_free($xml_parser);
    }

    public function clean(&$attr)
    {
        if (is_array($attr)) {
            foreach ($attr as $key => $value) {
                $attr[$key] = preg_replace("/&(?!amp;|lt;|gt;|quot;)/", "&amp;", $attr[$key]);
                $attr[$key] = preg_replace("/\"/", "&quot;", $attr[$key]);
                $attr[$key] = preg_replace("/</", "&lt;", $attr[$key]);
                $attr[$key] = preg_replace("/>/", "&gt;", $attr[$key]);
            }
        }
        return $attr;
    }


    public function startElement($a_parser, $a_name, $a_attrs)
    {
        if (!is_object($this->xmlStruct)) {
            #vd($a_attrs);
            $this->xmlStruct = new XMLStruct($a_name, $a_attrs);
            $GLOBALS["lastObj"] =&$this->xmlStruct;
        } else {
            $a_attrs = $this->clean($a_attrs);
            #var_dump("<pre>",++$counter," ",$a_name," -> ",$a_attrs,"<pre>");
            $GLOBALS["lastObj"]->append($a_name, $a_attrs);
        }
    }

    public function endElement($a_parser, $a_name)
    {
        $GLOBALS["lastObj"]->setParent();
    }

    public function characterData($a_parser, $a_data)
    {
        $a_data = preg_replace("/&/", "&amp;", $a_data);

        $GLOBALS["lastObj"]->setContent($a_data);
    }

    public function insertNode(&$dom, &$node)
    {
        $node = $this->xmlStruct->insert($dom, $node);
    }
}
