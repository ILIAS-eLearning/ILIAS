<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Abstract meta data sax parser
* This class should be inherited by all classes that want to parse meta data. E.g ContObjParser, CourseXMLParser ...
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* Inserts Meta data from XML into ILIAS db
*
* @extends ilSaxParser
* @package ilias-core
*/
include_once './Services/Xml/classes/class.ilSaxParser.php';

class ilMDSaxParser extends ilSaxParser
{
    public $md_in_md = false;
    public $md_chr_data = '';

    public $md_cur_el = null;

    /*
     * @var boolean enable/disable parsing status.
     */
    public $md_parsing_enabled = null;
    /*
     * @var object ilMD
     */
    public $md = null;

    /*
     * @var object ilMDGeneral
     */
    public $md_gen;

    /**
     * @var ilLogger
     */
    protected $meta_log;

    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct($a_xml_file = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tree = $DIC['tree'];

        $this->meta_log = $DIC->logger()->meta();


        // Enable parsing. E.g qpl' s will set this value to false
        $this->md_parsing_enabled = true;

        parent::__construct($a_xml_file);
    }

    public function enableMDParsing($a_status)
    {
        $this->md_parsing_enabled = (bool) $a_status;
    }
    public function getMDParsingStatus()
    {
        return (bool) $this->md_parsing_enabled;
    }

    public function setMDObject(&$md)
    {
        $this->md =&$md;
    }
    public function &getMDObject()
    {
        return is_object($this->md) ? $this->md : false;
    }

    public function inMetaData()
    {
        return $this->md_in_md;
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
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        if (!$this->getMDParsingStatus()) {
            return;
        }

        switch ($a_name) {
            case 'MetaData':
                $this->md_in_md = true;
                $this->__pushParent($this->md);
                break;

            case 'General':
                $this->md_gen =&$this->md->addGeneral();
                $this->md_gen->setStructure($a_attribs['Structure']);
                $this->md_gen->save();
                $this->__pushParent($this->md_gen);
                break;

            case 'Identifier':
                $par =&$this->__getParent();
                $this->md_ide =&$par->addIdentifier();
                $this->md_ide->setCatalog($a_attribs['Catalog']);
                $this->md_ide->setEntry($a_attribs['Entry']);
                $this->md_ide->save();
                $this->__pushParent($this->md_ide);
                break;

            case 'Title':
                $par =&$this->__getParent();
                $par->setTitleLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Language':
                $par =&$this->__getParent();
                $this->md_lan =&$par->addLanguage();
                $this->md_lan->setLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_lan->save();
                $this->__pushParent($this->md_lan);
                break;

            case 'Description':
                $par =&$this->__getParent();
                
                if (strtolower(get_class($par)) == 'ilmdrights' or
                   strtolower(get_class($par)) == 'ilmdannotation' or
                   strtolower(get_class($par)) == 'ilmdclassification') {
                    $par->setDescriptionLanguage(new ilMDLanguageItem($a_attribs['Language']));
                    break;
                } else {
                    $this->md_des =&$par->addDescription();
                    $this->md_des->setDescriptionLanguage(new ilMDLanguageItem($a_attribs['Language']));
                    $this->md_des->save();
                    $this->__pushParent($this->md_des);
                    break;
                }

                // no break
            case 'Keyword':
                $par =&$this->__getParent();
                if (!in_array(get_class($par), ["ilMD"])) {
                    $this->md_key =&$par->addKeyword();
                    $this->md_key->setKeywordLanguage(new ilMDLanguageItem($a_attribs['Language']));
                    $this->md_key->save();
                    $this->__pushParent($this->md_key);
                }
                break;

            case 'Coverage':
                $par =&$this->__getParent();
                $par->setCoverageLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Lifecycle':
                $par =&$this->__getParent();
                $this->md_lif =&$par->addLifecycle();
                $this->md_lif->setStatus($a_attribs['Status']);
                $this->md_lif->save();
                $this->__pushParent($this->md_lif);
                break;

            case 'Version':
                $par =&$this->__getParent();
                $par->setVersionLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Contribute':
                $par =&$this->__getParent();
                $this->md_con =&$par->addContribute();
                $this->md_con->setRole($a_attribs['Role']);
                $this->md_con->save();
                $this->__pushParent($this->md_con);
                break;

            case 'Entity':
                $par =&$this->__getParent();

                if (strtolower(get_class($par)) == 'ilmdcontribute') {
                    $this->md_ent =&$par->addEntity();
                    $this->md_ent->save();
                    $this->__pushParent($this->md_ent);
                    break;
                } else {
                    // single element in 'Annotation'
                    break;
                }
                // no break
            case 'Date':
                break;

            case 'Meta-Metadata':
                $par =&$this->__getParent();
                $this->md_met =&$par->addMetaMetadata();
                $this->md_met->setMetaDataScheme($a_attribs['MetadataScheme']);
                $this->md_met->setLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_met->save();
                $this->__pushParent($this->md_met);
                break;
                
            case 'Technical':
                $par =&$this->__getParent();
                $this->md_tec =&$par->addTechnical();
                $this->md_tec->save();
                $this->__pushParent($this->md_tec);
                break;

            case 'Format':
                $par =&$this->__getParent();
                $this->md_for =&$par->addFormat();
                $this->md_for->save();
                $this->__pushParent($this->md_for);
                break;

            case 'Size':
                break;

            case 'Location':
                $par =&$this->__getParent();
                $this->md_loc =&$par->addLocation();
                $this->md_loc->setLocationType($a_attribs['Type']);
                $this->md_loc->save();
                $this->__pushParent($this->md_loc);
                break;

            case 'Requirement':
                $par =&$this->__getParent();
                $this->md_req =&$par->addRequirement();
                $this->md_req->save();
                $this->__pushParent($this->md_req);
                break;

            case 'OrComposite':
                $par =&$this->__getParent();
                $this->md_orc =&$par->addOrComposite();
                $this->__pushParent($this->md_orc);
                break;

            case 'Type':
                break;

            case 'OperatingSystem':
                $par =&$this->__getParent();
                $par->setOperatingSystemName($a_attribs['Name']);
                $par->setOperatingSystemMinimumVersion($a_attribs['MinimumVersion']);
                $par->setOperatingSystemMaximumVersion($a_attribs['MaximumVersion']);
                break;

            case 'Browser':
                $par =&$this->__getParent();
                $par->setBrowserName($a_attribs['Name']);
                $par->setBrowserMinimumVersion($a_attribs['MinimumVersion']);
                $par->setBrowserMaximumVersion($a_attribs['MaximumVersion']);
                break;

            case 'InstallationRemarks':
                $par =&$this->__getParent();
                $par->setInstallationRemarksLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'OtherPlatformRequirements':
                $par =&$this->__getParent();
                $par->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Duration':
                break;

            case 'Educational':
                $par =&$this->__getParent();
                $this->md_edu =&$par->addEducational();
                $this->md_edu->setInteractivityType($a_attribs['InteractivityType']);
                $this->md_edu->setLearningResourceType($a_attribs['LearningResourceType']);
                $this->md_edu->setInteractivityLevel($a_attribs['InteractivityLevel']);
                $this->md_edu->setSemanticDensity($a_attribs['SemanticDensity']);
                $this->md_edu->setIntendedEndUserRole($a_attribs['IntendedEndUserRole']);
                $this->md_edu->setContext($a_attribs['Context']);
                $this->md_edu->setDifficulty($a_attribs['Difficulty']);
                $this->md_edu->save();
                $this->__pushParent($this->md_edu);
                break;

            case 'TypicalAgeRange':
                $par =&$this->__getParent();
                $this->md_typ =&$par->addTypicalAgeRange();
                $this->md_typ->setTypicalAgeRangeLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_typ->save();
                $this->__pushParent($this->md_typ);
                break;

            case 'TypicalLearningTime':
                break;

            case 'Rights':
                $par =&$this->__getParent();
                $this->md_rig =&$par->addRights();
                $this->md_rig->setCosts($a_attribs['Cost']);
                $this->md_rig->setCopyrightAndOtherRestrictions($a_attribs['CopyrightAndOtherRestrictions']);
                $this->md_rig->save();
                $this->__pushParent($this->md_rig);
                break;

            case 'Relation':
                $par =&$this->__getParent();
                $this->md_rel =&$par->addRelation();
                $this->md_rel->setKind($a_attribs['Kind']);
                $this->md_rel->save();
                $this->__pushParent($this->md_rel);
                break;

            case 'Resource':
                break;
                
            case 'Identifier_':
                $par =&$this->__getParent();
                $this->md_ide_ =&$par->addIdentifier_();
                $this->md_ide_->setCatalog($a_attribs['Catalog']);
                $this->md_ide_->setEntry($a_attribs['Entry']);
                $this->md_ide_->save();
                $this->__pushParent($this->md_ide_);
                break;

            case 'Annotation':
                $par =&$this->__getParent();
                $this->md_ann =&$par->addAnnotation();
                $this->md_ann->save();
                $this->__pushParent($this->md_ann);
                break;

            case 'Classification':
                $par =&$this->__getParent();
                $this->md_cla =&$par->addClassification();
                $this->md_cla->setPurpose($a_attribs['Purpose']);
                $this->md_cla->save();
                $this->__pushParent($this->md_cla);
                break;

            case 'TaxonPath':
                $par =&$this->__getParent();
                $this->md_taxp =&$par->addTaxonPath();
                $this->md_taxp->save();
                $this->__pushParent($this->md_taxp);
                break;

            case 'Source':
                $par =&$this->__getParent();
                $par->setSourceLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Taxon':
                $par =&$this->__getParent();
                $this->md_tax =&$par->addTaxon();
                $this->md_tax->setTaxonLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_tax->setTaxonId($a_attribs['Id']);
                $this->md_tax->save();
                $this->__pushParent($this->md_tax);
                break;
        }
    }

    /**
    * handler for end of element
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        if (!$this->getMDParsingStatus()) {
            return;
        }

        switch ($a_name) {
            case 'MetaData':
                $this->md_parent = array();
                $this->md_in_md = false;
                break;

            case 'General':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Identifier':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Title':
                $par =&$this->__getParent();
                $par->setTitle($this->__getCharacterData());
                break;

            case 'Language':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Description':
                $par = $this->__getParent();

                if ($par instanceof ilMDRights) {
                    $par->parseDescriptionFromImport(
                        $this->__getCharacterData()
                    );
                    $par->update();
                    $this->__popParent();
                    break;
                } elseif ($par instanceof ilMDDescription) {
                    $par->setDescription($this->__getCharacterData());
                    $par->update();
                    $this->__popParent();
                    break;
                } else {
                    $par->setDescription($this->__getCharacterData());
                    $par->update();
                    $this->__popParent();
                    break;
                }

                // no break
            case 'Keyword':
                $par =&$this->__getParent();
                if (!in_array(get_class($par), ["ilMD"])) {
                    $par->setKeyword($this->__getCharacterData());
                    $this->meta_log->debug("Keyword: " . $this->__getCharacterData());
                    $par->update();
                    $this->__popParent();
                }
                break;

            case 'Coverage':
                $par =&$this->__getParent();
                $par->setCoverage($this->__getCharacterData());
                break;

            case 'Lifecycle':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Version':
                $par =&$this->__getParent();
                $par->setVersion($this->__getCharacterData());
                break;

            case 'Contribute':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Entity':
                $par =&$this->__getParent();

                if (strtolower(get_class($par)) == 'ilmdentity') {
                    $par->setEntity($this->__getCharacterData());
                    $par->update();
                    $this->__popParent();
                } else {
                    // Single element in 'Annotation'
                    $par->setEntity($this->__getCharacterData());
                }
                break;

            case 'Date':
                $par =&$this->__getParent();
                $par->setDate($this->__getCharacterData());
                break;
                
            case 'Meta-Metadata':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Technical':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Format':
                $par =&$this->__getParent();
                $par->setFormat($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'Size':
                $par =&$this->__getParent();
                $par->setSize($this->__getCharacterData());
                break;

            case 'Location':
                $par =&$this->__getParent();
                $par->setLocation($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'Requirement':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'OrComposite':
                $this->__popParent();
                break;

            case 'Type':
                break;

            case 'OperatingSystem':
                break;

            case 'Browser':
                break;

            case 'InstallationRemarks':
                $par =&$this->__getParent();
                $par->setInstallationRemarks($this->__getCharacterData());
                break;

            case 'OtherPlatformRequirements':
                $par =&$this->__getParent();
                $par->setOtherPlatformRequirements($this->__getCharacterData());
                break;

            case 'Duration':
                $par =&$this->__getParent();
                $par->setDuration($this->__getCharacterData());
                break;

            case 'Educational':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'TypicalAgeRange':
                $par =&$this->__getParent();
                $par->setTypicalAgeRange($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'TypicalLearningTime':
                $par =&$this->__getParent();
                $par->setTypicalLearningTime($this->__getCharacterData());
                break;

            case 'Rights':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Relation':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Resource':
                break;
                
            case 'Identifier_':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Annotation':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;
                
            case 'Classification':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'TaxonPath':
                $par =&$this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Taxon':
                $par =&$this->__getParent();
                $par->setTaxon($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'Source':
                $par =&$this->__getParent();
                $par->setSource($this->__getCharacterData());
                break;
                
        }
        $this->md_chr_data = '';
    }

    /**
    * handler for character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if (!$this->getMDParsingStatus()) {
            return;
        }

        if ($this->inMetaData() and $a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->md_chr_data .= $a_data;
        }
    }

        

    // PRIVATE
    public function __getCharacterData()
    {
        return trim($this->md_chr_data);
    }

    public function __pushParent(&$md_obj)
    {
        $this->md_parent[] =&$md_obj;
        #echo '<br />';
        foreach ($this->md_parent as $class) {
            $this->meta_log->debug(get_class($class));
        }
    }
    public function &__popParent()
    {
        $class = array_pop($this->md_parent);
        $this->meta_log->debug(is_object($class) ? get_class($class) : 'null');
        unset($class);
    }
    public function &__getParent()
    {
        return $this->md_parent[count($this->md_parent) - 1];
    }
}
