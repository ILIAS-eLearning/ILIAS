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
 * Abstract meta data sax parser
 * This class should be inherited by all classes that want to parse meta data. E.g ContObjParser, CourseXMLParser ...
 * @author  Stefan Meyer <meyer@leifos.com>
 * Inserts Meta data from XML into ILIAS db
 * @extends ilSaxParser
 * @package ilias-core
 */
class ilMDSaxParser extends ilSaxParser
{
    protected bool $md_in_md = false;
    protected string $md_chr_data = '';
    protected ?ilMDIdentifier $md_ide = null;
    protected ?ilMDLanguage $md_lan = null;
    protected ?ilMDDescription $md_des = null;
    protected ?ilMDLifecycle $md_lif = null;
    protected ?ilMDContribute $md_con = null;
    protected ?ilMDEntity $md_ent = null;
    protected ?ilMDMetaMetadata $md_met = null;
    protected ?ilMDTechnical $md_tec = null;
    protected ?ilMDFormat $md_for = null;
    protected ?ilMDLocation $md_loc = null;
    protected ?ilMDRequirement $md_req = null;
    protected ?ilMDOrComposite $md_orc = null;
    protected ?ilMDEducational $md_edu = null;
    protected ?ilMDTypicalAgeRange $md_typ = null;
    protected ?ilMDRights $md_rig = null;
    protected ?ilMDRelation $md_rel = null;
    protected ?ilMDIdentifier_ $md_ide_ = null;
    protected ?ilMDAnnotation $md_ann = null;
    protected ?ilMDClassification $md_cla = null;
    protected ?ilMDTaxonPath $md_taxp = null;
    protected ?ilMDTaxon $md_tax = null;
    protected ?ilMDKeyword $md_key = null;

    /**
     * Array of mixed ilMD objects
     * @var array<object>
     */
    protected array $md_parent = array();

    private bool $md_parsing_enabled;

    protected ?ilMD $md = null;

    protected ?ilMDGeneral $md_gen = null;

    protected ilLogger $meta_log;

    public function __construct(?string $a_xml_file = '')
    {
        global $DIC;

        $this->meta_log = $DIC->logger()->meta();

        // Enable parsing. E.g qpl' s will set this value to false
        $this->md_parsing_enabled = true;

        parent::__construct($a_xml_file);
    }

    public function enableMDParsing(bool $a_status) : void
    {
        $this->md_parsing_enabled = $a_status;
    }

    public function getMDParsingStatus() : bool
    {
        return $this->md_parsing_enabled;
    }

    public function setMDObject(ilMD $md) : void
    {
        $this->md = $md;
    }

    public function getMDObject() : ?ilMD
    {
        return is_object($this->md) ? $this->md : null;
    }

    public function inMetaData() : bool
    {
        return $this->md_in_md;
    }

    /**
     * Set event handlers
     * @param XMLParser|resource reference to the xml parser
     */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        if (!$this->getMDParsingStatus()) {
            return;
        }

        switch ($a_name) {
            case 'MetaData':
                $this->md_in_md = true;
                $this->__pushParent($this->md);
                break;

            case 'General':
                $this->md_gen = $this->md->addGeneral();
                $this->md_gen->setStructure($a_attribs['Structure']);
                $this->md_gen->save();
                $this->__pushParent($this->md_gen);
                break;

            case 'Identifier':
                $par = $this->__getParent();
                $this->md_ide = $par->addIdentifier();
                $this->md_ide->setCatalog($a_attribs['Catalog']);
                $this->md_ide->setEntry($a_attribs['Entry']);
                $this->md_ide->save();
                $this->__pushParent($this->md_ide);
                break;

            case 'Title':
                $par = $this->__getParent();
                $par->setTitleLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Language':
                $par = $this->__getParent();
                $this->md_lan = $par->addLanguage();
                $this->md_lan->setLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_lan->save();
                $this->__pushParent($this->md_lan);
                break;

            case 'Description':
                $par = $this->__getParent();

                if (strtolower(get_class($par)) === 'ilmdrights' ||
                    strtolower(get_class($par)) === 'ilmdannotation' ||
                    strtolower(get_class($par)) === 'ilmdclassification') {
                    $par->setDescriptionLanguage(new ilMDLanguageItem($a_attribs['Language']));
                    break;
                } else {
                    $this->md_des = $par->addDescription();
                    $this->md_des->setDescriptionLanguage(new ilMDLanguageItem($a_attribs['Language']));
                    $this->md_des->save();
                    $this->__pushParent($this->md_des);
                    break;
                }

            // no break
            case 'Keyword':
                $par = $this->__getParent();
                if (!$par instanceof ilMD) {
                    $this->md_key = $par->addKeyword();
                    $this->md_key->setKeywordLanguage(new ilMDLanguageItem($a_attribs['Language']));
                    $this->md_key->save();
                    $this->__pushParent($this->md_key);
                }
                break;

            case 'Coverage':
                $par = $this->__getParent();
                $par->setCoverageLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Lifecycle':
                $par = $this->__getParent();
                $this->md_lif = $par->addLifecycle();
                $this->md_lif->setStatus($a_attribs['Status']);
                $this->md_lif->save();
                $this->__pushParent($this->md_lif);
                break;

            case 'Version':
                $par = $this->__getParent();
                $par->setVersionLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Contribute':
                $par = $this->__getParent();
                $this->md_con = $par->addContribute();
                $this->md_con->setRole($a_attribs['Role']);
                $this->md_con->save();
                $this->__pushParent($this->md_con);
                break;

            case 'Entity':
                $par = $this->__getParent();

                if (strtolower(get_class($par)) === 'ilmdcontribute') {
                    $this->md_ent = $par->addEntity();
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
                $par = $this->__getParent();
                $this->md_met = $par->addMetaMetadata();
                $this->md_met->setMetaDataScheme($a_attribs['MetadataScheme']);
                $this->md_met->setLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_met->save();
                $this->__pushParent($this->md_met);
                break;

            case 'Technical':
                $par = $this->__getParent();
                $this->md_tec = $par->addTechnical();
                $this->md_tec->save();
                $this->__pushParent($this->md_tec);
                break;

            case 'Format':
                $par = $this->__getParent();
                $this->md_for = $par->addFormat();
                $this->md_for->save();
                $this->__pushParent($this->md_for);
                break;

            case 'Size':
                break;

            case 'Location':
                $par = $this->__getParent();
                $this->md_loc = $par->addLocation();
                $this->md_loc->setLocationType($a_attribs['Type']);
                $this->md_loc->save();
                $this->__pushParent($this->md_loc);
                break;

            case 'Requirement':
                $par = $this->__getParent();
                $this->md_req = $par->addRequirement();
                $this->md_req->save();
                $this->__pushParent($this->md_req);
                break;

            case 'OrComposite':
                $par = $this->__getParent();
                $this->md_orc = $par->addOrComposite();
                $this->__pushParent($this->md_orc);
                break;

            case 'Type':
                break;

            case 'OperatingSystem':
                $par = $this->__getParent();
                $par->setOperatingSystemName($a_attribs['Name']);
                $par->setOperatingSystemMinimumVersion($a_attribs['MinimumVersion']);
                $par->setOperatingSystemMaximumVersion($a_attribs['MaximumVersion']);
                break;

            case 'Browser':
                $par = $this->__getParent();
                $par->setBrowserName($a_attribs['Name']);
                $par->setBrowserMinimumVersion($a_attribs['MinimumVersion']);
                $par->setBrowserMaximumVersion($a_attribs['MaximumVersion']);
                break;

            case 'InstallationRemarks':
                $par = $this->__getParent();
                $par->setInstallationRemarksLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'OtherPlatformRequirements':
                $par = $this->__getParent();
                $par->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Duration':
                break;

            case 'Educational':
                $par = $this->__getParent();
                $this->md_edu = $par->addEducational();
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
                $par = $this->__getParent();
                $this->md_typ = $par->addTypicalAgeRange();
                $this->md_typ->setTypicalAgeRangeLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_typ->save();
                $this->__pushParent($this->md_typ);
                break;

            case 'TypicalLearningTime':
                break;

            case 'Rights':
                $par = $this->__getParent();
                $this->md_rig = $par->addRights();
                $this->md_rig->setCosts($a_attribs['Cost']);
                $this->md_rig->setCopyrightAndOtherRestrictions($a_attribs['CopyrightAndOtherRestrictions']);
                $this->md_rig->save();
                $this->__pushParent($this->md_rig);
                break;

            case 'Relation':
                $par = $this->__getParent();
                $this->md_rel = $par->addRelation();
                $this->md_rel->setKind($a_attribs['Kind']);
                $this->md_rel->save();
                $this->__pushParent($this->md_rel);
                break;

            case 'Resource':
                break;

            case 'Identifier_':
                $par = $this->__getParent();
                $this->md_ide_ = $par->addIdentifier_();
                $this->md_ide_->setCatalog($a_attribs['Catalog']);
                $this->md_ide_->setEntry($a_attribs['Entry']);
                $this->md_ide_->save();
                $this->__pushParent($this->md_ide_);
                break;

            case 'Annotation':
                $par = $this->__getParent();
                $this->md_ann = $par->addAnnotation();
                $this->md_ann->save();
                $this->__pushParent($this->md_ann);
                break;

            case 'Classification':
                $par = $this->__getParent();
                $this->md_cla = $par->addClassification();
                $this->md_cla->setPurpose($a_attribs['Purpose']);
                $this->md_cla->save();
                $this->__pushParent($this->md_cla);
                break;

            case 'TaxonPath':
                $par = $this->__getParent();
                $this->md_taxp = $par->addTaxonPath();
                $this->md_taxp->save();
                $this->__pushParent($this->md_taxp);
                break;

            case 'Source':
                $par = $this->__getParent();
                $par->setSourceLanguage(new ilMDLanguageItem($a_attribs['Language']));
                break;

            case 'Taxon':
                $par = $this->__getParent();
                $this->md_tax = $par->addTaxon();
                $this->md_tax->setTaxonLanguage(new ilMDLanguageItem($a_attribs['Language']));
                $this->md_tax->setTaxonId($a_attribs['Id']);
                $this->md_tax->save();
                $this->__pushParent($this->md_tax);
                break;
        }
    }

    /**
     * @param resource $a_xml_parser
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void
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
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Identifier':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Title':
                $par = $this->__getParent();
                $par->setTitle($this->__getCharacterData());
                break;

            case 'Language':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Description':
                $par = $this->__getParent();
                if ($par instanceof ilMDRights) {
                    $par->parseDescriptionFromImport(
                        $this->__getCharacterData()
                    );
                } else {
                    $par->setDescription($this->__getCharacterData());
                }
                $par->update();
                if ($par instanceof ilMDDescription) {
                    $this->__popParent();
                }
                break;

            case 'Keyword':
                $par = $this->__getParent();
                if (!$par instanceof ilMD) {
                    $par->setKeyword($this->__getCharacterData());
                    $this->meta_log->debug("Keyword: " . $this->__getCharacterData());
                    $par->update();
                    $this->__popParent();
                }
                break;

            case 'Coverage':
                $par = $this->__getParent();
                $par->setCoverage($this->__getCharacterData());
                break;

            case 'Lifecycle':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Version':
                $par = $this->__getParent();
                $par->setVersion($this->__getCharacterData());
                break;

            case 'Contribute':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Entity':
                $par = $this->__getParent();

                if (strtolower(get_class($par)) === 'ilmdentity') {
                    $par->setEntity($this->__getCharacterData());
                    $par->update();
                    $this->__popParent();
                } else {
                    // Single element in 'Annotation'
                    $par->setEntity($this->__getCharacterData());
                }
                break;

            case 'Date':
                $par = $this->__getParent();
                $par->setDate($this->__getCharacterData());
                break;

            case 'Meta-Metadata':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Technical':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Format':
                $par = $this->__getParent();
                $par->setFormat($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'Size':
                $par = $this->__getParent();
                $par->setSize($this->__getCharacterData());
                break;

            case 'Location':
                $par = $this->__getParent();
                $par->setLocation($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'Requirement':
                $par = $this->__getParent();
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
                $par = $this->__getParent();
                $par->setInstallationRemarks($this->__getCharacterData());
                break;

            case 'OtherPlatformRequirements':
                $par = $this->__getParent();
                $par->setOtherPlatformRequirements($this->__getCharacterData());
                break;

            case 'Duration':
                $par = $this->__getParent();
                $par->setDuration($this->__getCharacterData());
                break;

            case 'Educational':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'TypicalAgeRange':
                $par = $this->__getParent();
                $par->setTypicalAgeRange($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'TypicalLearningTime':
                $par = $this->__getParent();
                $par->setTypicalLearningTime($this->__getCharacterData());
                break;

            case 'Rights':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Relation':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Resource':
                break;

            case 'Identifier_':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Annotation':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Classification':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'TaxonPath':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'Taxon':
                $par = $this->__getParent();
                $par->setTaxon($this->__getCharacterData());
                $par->update();
                $this->__popParent();
                break;

            case 'Source':
                $par = $this->__getParent();
                $par->setSource($this->__getCharacterData());
                break;

        }
        $this->md_chr_data = '';
    }

    /**
     * @param resource $a_xml_parser
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if (!$this->getMDParsingStatus()) {
            return;
        }

        if ($a_data !== "\n" && $this->inMetaData()) {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->md_chr_data .= $a_data;
        }
    }

    // PRIVATE
    public function __getCharacterData() : string
    {
        return trim($this->md_chr_data);
    }

    public function __pushParent(object $md_obj) : void
    {
        $this->md_parent[] = &$md_obj;
        $this->meta_log->debug('New parent stack (push)...');
        foreach ($this->md_parent as $class) {
            $this->meta_log->debug(get_class($class));
        }
    }

    public function __popParent() : void
    {
        $this->meta_log->debug('New parent stack (pop)....');
        $class = array_pop($this->md_parent);
        foreach ((array) $this->md_parent as $class) {
            $this->meta_log->debug(get_class($class));
        }
        $this->meta_log->debug(is_object($class) ? get_class($class) : 'null');
        unset($class);
    }

    public function __getParent() : object
    {
        return $this->md_parent[count($this->md_parent) - 1];
    }
}
