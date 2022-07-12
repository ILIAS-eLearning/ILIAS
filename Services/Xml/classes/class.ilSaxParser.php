<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Base class for sax-based expat parsing
 * extended classes need to overwrite the method setHandlers and implement their own handler methods
 *
 * @deprecated We should move to native XML-Parsing
 */
abstract class ilSaxParser
{
    private const TYPE_FILE = 'file';
    private const TYPE_STRING = 'string';

    /**
     * XML-Content type 'file' or 'string'
     * If you choose file set the filename in constructor
     * If you choose 'String' call the constructor with no argument and use setXMLContent()
     */
    private string $input_type;

    /**
     * XML-Content in case of content type 'string'
     */
    private string $xml_content;

    protected ?ilLanguage $lng = null;

    public string $xml_file;

    public bool $throw_exception = false;


    public function __construct(
        ?string $path_to_file = '',
        ?bool $throw_exception = false
    ) {
        global $DIC;

        if ($path_to_file !== null && $path_to_file !== '') {
            $this->xml_file = $path_to_file;
            $this->input_type = self::TYPE_FILE;
        } else {
            $this->input_type = self::TYPE_STRING;
            $this->xml_content = '';
        }

        $this->throw_exception = $throw_exception ?? false;
        $this->lng = $DIC->isDependencyAvailable('language')
            ? $DIC->language()
            : null;
    }

    public function setXMLContent(string $a_xml_content) : void
    {
        $this->xml_content = $a_xml_content;
        $this->input_type = self::TYPE_STRING;
    }

    public function getXMLContent() : string
    {
        return $this->xml_content;
    }

    public function getInputType() : string
    {
        return $this->input_type;
    }

    /**
     * stores xml data in array
     * @throws ilSaxParserException
     */
    public function startParsing() : void
    {
        $xml_parser = $this->createParser();
        $this->setOptions($xml_parser);
        $this->setHandlers($xml_parser);

        switch ($this->getInputType()) {
            case self::TYPE_FILE:
                $fp = $this->openXMLFile();
                $this->parse($xml_parser, $fp);
                break;

            case self::TYPE_STRING:
                $this->parse($xml_parser);
                break;

            default:
                $this->handleError(
                    "No input type given. Set filename in constructor or choose setXMLContent()"
                );
                break;
        }
        $this->freeParser($xml_parser);
    }

    /**
     * @return resource
     * @throws ilSaxParserException or ILIAS Error
     */
    public function createParser()
    {
        $xml_parser = xml_parser_create("UTF-8");
        if (!is_resource($xml_parser) && !is_object($xml_parser)) {
            $this->handleError("Cannot create an XML parser handle");
        }
        return $xml_parser;
    }


    private function setOptions($a_xml_parser) : void
    {
        xml_parser_set_option($a_xml_parser, XML_OPTION_CASE_FOLDING, false);
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @return void
     */
    abstract public function setHandlers($a_xml_parser) : void;

    /**
     * @return resource
     * @throws ilSaxParserException
     */
    protected function openXMLFile()
    {
        if (!($fp = fopen($this->xml_file, 'r'))) {
            $this->handleError("Cannot open xml file \"" . $this->xml_file . "\"");
        }
        return $fp;
    }

    /**
     * @param resource $a_xml_parser
     * @param resource|null $a_fp
     * @throws ilSaxParserException
     */
    public function parse($a_xml_parser, $a_fp = null) : void
    {
        $parse_status = true;
        switch ($this->getInputType()) {
            case self::TYPE_FILE:
                while ($data = fread($a_fp, 4096)) {
                    $parse_status = xml_parse($a_xml_parser, $data, feof($a_fp));
                }
                break;

            case self::TYPE_STRING:
                $parse_status = xml_parse($a_xml_parser, $this->getXMLContent());
                break;
        }
        $error_code = xml_get_error_code($a_xml_parser);
        if (!$parse_status && ($error_code !== XML_ERROR_NONE)) {
            $error = sprintf(
                "XML Parse Error: %s at line %s, col %s (Code: %s)",
                xml_error_string($error_code),
                xml_get_current_line_number($a_xml_parser),
                xml_get_current_column_number($a_xml_parser),
                $error_code
            );

            $this->handleError($error);
        }
    }

    /**
     * @throws ilSaxParserException
     */
    protected function handleError(string $message) : void
    {
        if ($this->throw_exception) {
            throw new ilSaxParserException($message);
        }
    }

    /**
     * @param resource $a_xml_parser
     * @throws ilSaxParserException
     */
    private function freeParser($a_xml_parser) : void
    {
        if (!xml_parser_free($a_xml_parser)) {
            $this->handleError("Error freeing xml parser handle");
        }
    }


    protected function setThrowException(bool $throw_exception) : void
    {
        $this->throw_exception = $throw_exception;
    }
}
