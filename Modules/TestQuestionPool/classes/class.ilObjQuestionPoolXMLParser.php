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
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilObjQuestionPoolXMLParser extends ilSaxParser
{
    private \ilObjQuestionPool $poolOBJ;

    private $inSettingsTag;

    private $inMetaDataTag;
    private $inMdGeneralTag;
    private bool $descriptionProcessed = false;
    private string $cdata = "";

    /**
     * @param ilObjQuestionPool $poolOBJ
     * @param $xmlFile
     */
    public function __construct(ilObjQuestionPool $poolOBJ, ?string $xmlFile)
    {
        $this->poolOBJ = $poolOBJ;

        $this->inSettingsTag = false;
        $this->inMetaDataTag = false;
        $this->inMdGeneralTag = false;

        return parent::__construct($xmlFile);
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function handlerBeginTag($xmlParser, $tagName, $tagAttributes): void
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

    public function handlerEndTag($xmlParser, $tagName): void
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

    public function handlerCharacterData($xmlParser, $charData): void
    {
        if ($charData != "\n") {
            // Replace multiple tabs with one space
            $charData = preg_replace("/\t+/", " ", $charData);

            $this->cdata .= $charData;
        }
    }
}
