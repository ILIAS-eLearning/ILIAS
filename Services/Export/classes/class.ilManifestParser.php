<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
 * Manifest parser for ILIAS standard export files
 *
 * @author Aleex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilManifestParser extends ilSaxParser
{
    protected $expfiles = array();
    protected $expsets = array();
    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_file)
    {
        parent::__construct($a_file, true);
        $this->startParsing();
    }

    /**
     * Set Installation ID
     *
     * @param	string	Installation ID
     */
    final public function setInstallId($a_val)
    {
        $this->install_id = $a_val;
    }

    /**
     * Get Installation ID
     *
     * @return	string	Installation ID
     */
    final public function getInstallId()
    {
        return $this->install_id;
    }

    /**
     * Set Installation Url
     *
     * @param	string	Installation Url
     */
    final public function setInstallUrl($a_val)
    {
        $this->install_url = $a_val;
    }

    /**
     * Get Installation Url
     *
     * @return	string	Installation Url
     */
    final public function getInstallUrl()
    {
        return $this->install_url;
    }

    /**
     * Set main entity
     *
     * @param	string	main entity
     */
    public function setMainEntity($a_val)
    {
        $this->main_entity = $a_val;
    }

    /**
     * Get main entity
     *
     * @return	string	main entity
     */
    public function getMainEntity()
    {
        return $this->main_entity;
    }

    /**
     * Set title
     *
     * @param	string	title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }

    /**
     * Get title
     *
     * @return	string	title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set target release
     *
     * @param	string	target release
     */
    public function setTargetRelease($a_val)
    {
        $this->target_release = $a_val;
    }

    /**
     * Get target release
     *
     * @return	string	target release
     */
    public function getTargetRelease()
    {
        return $this->target_release;
    }

    /**
     * Get xml files
     *
     * @return	array of strings	xml file pathes
     */
    public function getExportFiles()
    {
        return $this->expfiles;
    }
    
    public function getExportSets()
    {
        return $this->expsets;
    }
    
    /**
     * Set event handlers
     *
     * @param	resource	reference to the xml parser
     * @access	private
     */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handleBeginTag', 'handleEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
    }

    
    /**
     * Start parser
     */
    public function startParsing()
    {
        parent::startParsing();
    }
    
    /**
     * Begin Tag
     */
    public function handleBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case "Manifest":
                $this->setInstallId($a_attribs["InstallationId"]);
                $this->setInstallUrl($a_attribs["InstallationUrl"]);
                $this->setTitle($a_attribs["Title"]);
                $this->setTargetRelease($a_attribs["TargetRelease"]);
                $this->setMainEntity($a_attribs["MainEntity"]);
                break;

            case "ExportFile":
                $this->expfiles[] = array("component" => $a_attribs["Component"],
                    "path" => $a_attribs["Path"]);
                break;
                
            case "ExportSet":
                $this->expsets[] = array(
                    'path' => $a_attribs['Path'],
                    'type' => $a_attribs['Type']
                );
                break;
        }
    }
    
    /**
     * End Tag
     */
    public function handleEndTag($a_xml_parser, $a_name)
    {
        $this->chr_data = "";
    }
    
    /**
     * End Tag
     */
    public function handleCharacterData($a_xml_parser, $a_data)
    {
        //$a_data = str_replace("<","&lt;",$a_data);
        //$a_data = str_replace(">","&gt;",$a_data);
        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        //$a_data = preg_replace("/\n/","",$a_data);
        //$a_data = preg_replace("/\t+/","",$a_data);

        $this->chr_data .= $a_data;
    }
}
