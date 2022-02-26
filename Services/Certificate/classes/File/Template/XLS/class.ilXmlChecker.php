<?php declare(strict_types=1);
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
/**
 * XML checker
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @extends ilSaxParser
 * @ingroup ModulesTest
 */
class ilXMLChecker extends ilSaxParser
{
    public int $error_code;
    public int $error_line;
    public int $error_col;
    public string $error_msg;
    public bool $has_error;
    public int $size;
    public int $elements;
    public int $attributes;
    public int $texts;
    public int $text_size;

    public function __construct(string $a_xml_file = '', bool $throw_exception = false)
    {
        parent::__construct($a_xml_file, $throw_exception);
        $this->has_error = false;
    }

    /**
     * @param XmlParser|resource $a_xml_parser
     */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @param XmlParser|resource $a_xml_parser
     */
    public function parse($a_xml_parser, $a_fp = null) : void
    {
        $parseOk = false;
        switch ($this->getInputType()) {
            case 'file':
                while ($data = fread($a_fp, 4096)) {
                    $parseOk = xml_parse($a_xml_parser, $data, feof($a_fp));
                }
                break;

            case 'string':
                $parseOk = xml_parse($a_xml_parser, $this->getXMLContent());
                break;
        }

        if (!$parseOk && (xml_get_error_code($a_xml_parser) !== XML_ERROR_NONE)) {
            $this->error_code = xml_get_error_code($a_xml_parser);
            $this->error_line = xml_get_current_line_number($a_xml_parser);
            $this->error_col = xml_get_current_column_number($a_xml_parser);
            $this->error_msg = xml_error_string($this->error_code);
            $this->has_error = true;
        }
    }

    /**
     * @param XmlParser|resource $a_xml_parser
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        $this->elements++;
        $this->attributes += count($a_attribs);
    }

    /**
     * @param XmlParser|resource $a_xml_parser
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
    }

    /**
     * @param XmlParser|resource $a_xml_parser
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        $this->texts++;
        $this->text_size += strlen($a_data);
    }

    public function getErrorCode() : int
    {
        return $this->error_code;
    }

    public function getErrorLine() : int
    {
        return $this->error_line;
    }

    public function getErrorColumn() : int
    {
        return $this->error_col;
    }

    public function getErrorMessage() : string
    {
        return $this->error_msg;
    }

    public function getFullError() : string
    {
        return "Error: " . $this->error_msg . " at line:" . $this->error_line . " column:" . $this->error_col;
    }

    public function getXMLSize() : int
    {
        return $this->size;
    }

    public function getXMLElements() : int
    {
        return $this->elements;
    }

    public function getXMLAttributes() : int
    {
        return $this->attributes;
    }

    public function getXMLTextSections() : int
    {
        return $this->texts;
    }

    public function getXMLTextSize() : int
    {
        return $this->text_size;
    }

    public function hasError() : bool
    {
        return $this->has_error;
    }
}
