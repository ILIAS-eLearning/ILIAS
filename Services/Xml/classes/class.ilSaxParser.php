<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Base class for sax-based expat parsing
* extended classes need to overwrite the method setHandlers and implement their own handler methods
*
*
* @author Stefan Meyer <smeyer@databay>
* @version $Id$
*
* @extends PEAR
*/


class ilSaxParser
{
    /**
     * XML-Content type 'file' or 'string'
     * If you choose file set the filename in constructor
     * If you choose 'String' call the constructor with no argument and use setXMLContent()
     * @var string
     * @access private
     */
    public $input_type = null;

    /**
     * XML-Content in case of content type 'string'

     * @var string
     * @access private
     */
    public $xml_content = '';

    /**
     * ilias object
     * @var object ilias
     * @access private
     */
    public $ilias;

    /**
     * language object
     * @var object language
     * @access private
     */
    public $lng;

    /**
     * xml filename
     * @var filename
     * @access private
     */
    public $xml_file;

    /**
     * error handler which handles error messages (used if parsers are called from soap)
     *
     * @var boolean
     */
    public $throwException = false;
    /**
    * Constructor
    * setup ILIAS global object
    * @access	public
    */
    public function __construct($a_xml_file = '', $throwException = false)
    {
        global $ilias, $lng;

        if ($a_xml_file) {
            $this->xml_file = $a_xml_file;
            $this->input_type = 'file';
        }

        $this->throwException = $throwException;
        $this->ilias = &$ilias;
        $this->lng = &$lng;
    }

    public function setXMLContent($a_xml_content)
    {
        $this->xml_content = $a_xml_content;
        $this->input_type = 'string';
    }
    
    public function getXMLContent()
    {
        return $this->xml_content;
    }

    public function getInputType()
    {
        return $this->input_type;
    }

    /**
    * stores xml data in array
    *
    * @access	private
    * @throws ilSaxParserException or ILIAS Error
    */
    public function startParsing()
    {
        $xml_parser = $this->createParser();
        $this->setOptions($xml_parser);
        $this->setHandlers($xml_parser);

        switch ($this->getInputType()) {
            case 'file':
                $fp = $this->openXMLFile();
                $this->parse($xml_parser, $fp);
                break;

            case 'string':
                $this->parse($xml_parser);
                break;

            default:
                $this->handleError(
                    "No input type given. Set filename in constructor or choose setXMLContent()",
                    $this->ilias->error_obj->FATAL
                );
                break;
        }
        $this->freeParser($xml_parser);
    }
    /**
    * create parser
    *
    * @access	private
    * @throws ilSaxParserException or ILIAS Error
    */
    public function createParser()
    {
        $xml_parser = xml_parser_create("UTF-8");

        if ($xml_parser == false) {
            $this->handleError("Cannot create an XML parser handle", $this->ilias->error_obj->FATAL);
        }
        return $xml_parser;
    }
    /**
    * set parser options
    *
    * @access	private
    */
    public function setOptions($a_xml_parser)
    {
        xml_parser_set_option($a_xml_parser, XML_OPTION_CASE_FOLDING, false);
    }
    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        echo 'ilSaxParser::setHandlers() must be overwritten';
    }
    /**
    * open xml file
    *
    * @access	private
    * @throws ilSaxParserException or ILIAS Error
    */
    public function openXMLFile()
    {
        if (!($fp = fopen($this->xml_file, 'r'))) {
            $this->handleError("Cannot open xml file \"" . $this->xml_file . "\"", $this->ilias->error_obj->FATAL);
        }
        return $fp;
    }
    /**
    * parse xml file
    *
    * @access	private
    * @throws ilSaxParserException or ILIAS Error
    */
    public function parse($a_xml_parser, $a_fp = null)
    {
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
        if (!$parseOk
           && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE)) {
            $errorCode = xml_get_error_code($a_xml_parser);
            $line = xml_get_current_line_number($a_xml_parser);
            $col = xml_get_current_column_number($a_xml_parser);
            $this->handleError("XML Parse Error: " . xml_error_string($errorCode) . " at line " . $line . ", col " . $col . " (Code: " . $errorCode . ")", $this->ilias->error_obj->FATAL);
        }
        return true;
    }
    
    /**
     * use given error handler to handle error message or internal ilias error message handle
     *
     * @param string $message
     * @param string $code
     * @throws ilSaxParserException or ILIAS Error
     */
    protected function handleError($message, $code)
    {
        if ($this->throwException) {
            require_once('./Services/Xml/exceptions/class.ilSaxParserException.php');
            throw new ilSaxParserException($message, $code);
        } else {
            if (is_object($this->ilias)) {
                $this->ilias->raiseError($message, $code);
            } else {
                die($message);
            }
        }
        return false;
    }
    /**
    * free xml parser handle
    *
    * @access	private
    */
    public function freeParser($a_xml_parser)
    {
        if (!xml_parser_free($a_xml_parser)) {
            $this->ilias->raiseError("Error freeing xml parser handle ", $this->ilias->error_obj->FATAL);
        }
    }
    
    /**
     * set error handling
     *
     * @param  $error_handler
     */
    public function setThrowException($throwException)
    {
        $this->throwException = $throwException;
    }
}
