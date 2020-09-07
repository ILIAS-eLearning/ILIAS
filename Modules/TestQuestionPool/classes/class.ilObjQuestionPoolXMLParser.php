<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilObjQuestionPoolXMLParser extends ilSaxParser
{
    /**
     * @var ilObjQuestionPool
     */
    private $poolOBJ;
    
    private $inSettingsTag;
    
    private $inMetaDataTag;
    private $inMdGeneralTag;
    private $descriptionProcessed = false;

    /**
     * @param ilObjQuestionPool $poolOBJ
     * @param $xmlFile
     */
    public function __construct(ilObjQuestionPool $poolOBJ, $xmlFile)
    {
        $this->poolOBJ = $poolOBJ;
        
        $this->inSettingsTag = false;
        $this->inMetaDataTag = false;
        $this->inMdGeneralTag = false;
        
        return parent::__construct($xmlFile);
    }
    
    public function setHandlers($xmlParser)
    {
        xml_set_object($xmlParser, $this);
        xml_set_element_handler($xmlParser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($xmlParser, 'handlerCharacterData');
    }
    
    public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
    {
        switch ($tagName) {
            case 'MetaData':
                $this->inMetaDataTag = true;
                break;
            
            case 'General':
                if ($this->inMetaDataTag) {
                    $this->inMdGeneralTag = true;
                }
                break;
            
            case 'Description':
                if ($this->inMetaDataTag && $this->inMdGeneralTag) {
                    $this->cdata = '';
                }
                break;
            
            case 'Settings':
                $this->inSettingsTag = true;
                break;
            
            case 'ShowTaxonomies':
            case 'NavTaxonomy':
            case 'SkillService':
                if ($this->inSettingsTag) {
                    $this->cdata = '';
                }
                break;
        }
    }

    public function handlerEndTag($xmlParser, $tagName)
    {
        switch ($tagName) {
            case 'MetaData':
                $this->inMetaDataTag = false;
                break;

            case 'General':
                if ($this->inMetaDataTag) {
                    $this->inMdGeneralTag = false;
                }
                break;

            case 'Description':
                if ($this->inMetaDataTag && $this->inMdGeneralTag && !$this->descriptionProcessed) {
                    $this->poolOBJ->setDescription($this->cdata);
                    $this->descriptionProcessed = true;
                    $this->cdata = '';
                }
                break;

            case 'Settings':
                $this->inSettingsTag = false;
                break;

            case 'ShowTaxonomies':
                $this->poolOBJ->setShowTaxonomies((bool) $this->cdata);
                $this->cdata = '';
                break;
                
            case 'NavTaxonomy':
                $this->poolOBJ->setNavTaxonomyId((int) $this->cdata);
                $this->cdata = '';
                break;
            
            case 'SkillService':
                $this->poolOBJ->setSkillServiceEnabled((bool) $this->cdata);
                $this->cdata = '';
                break;
        }
    }

    public function handlerCharacterData($xmlParser, $charData)
    {
        if ($charData != "\n") {
            // Replace multiple tabs with one space
            $charData = preg_replace("/\t+/", " ", $charData);

            $this->cdata .= $charData;
        }
    }
}
