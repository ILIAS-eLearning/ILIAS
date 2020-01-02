<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

/**
 * Class ilSCORM2004Asset
 *
 * Asset class for SCORM 2004 Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004Asset extends ilSCORM2004Node
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public $q_media = null;		// media files in questions

    /**
     * Constructor
     */
    public function __construct($a_slm_object, $a_id = 0)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_slm_object, $a_id);
        $this->setType("ass");
    }

    /**
     * Delete a SCO
     */
    public function delete($a_delete_meta_data = true)
    {
        $node_data = $this->tree->getNodeData($this->getId());
        $this->delete_rec($a_delete_meta_data);
        $this->tree->deleteTree($node_data);
        parent::deleteSeqInfo();
    }

    /**
     * Create asset
     */
    public function create($a_upload = false, $a_template = false)
    {
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
        parent::create($a_upload);
        if (!$a_template) {
            $this->insertDefaultSequencingItem();
        }
    }

    /**
     * Insert default sequencing item
     *
     * @param
     * @return
     */
    public function insertDefaultSequencingItem()
    {
        $seq_item = new ilSCORM2004Item($this->getId());
        $seq_item->insert();
    }


    /**
     * Delete Nested Page Objects
     */
    private function delete_rec($a_delete_meta_data = true)
    {
        $childs = $this->tree->getChilds($this->getId());
        foreach ($childs as $child) {
            $obj = ilSCORM2004NodeFactory::getInstance($this->slm_object, $child["obj_id"], false);
            if (is_object($obj)) {
                if ($obj->getType() == "page") {
                    $obj->delete($a_delete_meta_data);
                }
            }
            unset($obj);
        }
        parent::delete($a_delete_meta_data);
    }

    /**
     * Copy sco
     */
    public function copy($a_target_slm)
    {
        $ass = new ilSCORM2004Asset($a_target_slm);
        $ass->setTitle($this->getTitle());
        if ($this->getSLMId() != $a_target_slm->getId()) {
            $ass->setImportId("il__ass_" . $this->getId());
        }
        $ass->setSLMId($a_target_slm->getId());
        $ass->setType($this->getType());
        $ass->setDescription($this->getDescription());
        $ass->create(true);
        $a_copied_nodes[$this->getId()] = $ass->getId();

        // copy meta data
        include_once("Services/MetaData/classes/class.ilMD.php");
        $md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
        $new_md = $md->cloneMD($a_target_slm->getId(), $ass->getId(), $this->getType());

        return $ass;
    }

    // @todo: more stuff similar to ilSCORM2004Chapter needed...

    public function exportScorm($a_inst, $a_target_dir, $ver, &$expLog)
    {
        copy('./xml/ilias_co_3_7.dtd', $a_target_dir . '/ilias_co_3_7.dtd');
        copy('./Modules/Scorm2004/templates/xsl/sco.xsl', $a_target_dir . '/sco.xsl');

        $a_xml_writer = new ilXmlWriter;
        // MetaData
        //file_put_contents($a_target_dir.'/indexMD.xml','<lom xmlns="http://ltsc.ieee.org/xsd/LOM"><general/><classification/></lom>');
        $this->exportXMLMetaData($a_xml_writer);
        $metadata_xml = $a_xml_writer->xmlDumpMem(false);
        $a_xml_writer->_XmlWriter;
        $xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/metadata.xsl");
        $args = array( '/_xml' => $metadata_xml , '/_xsl' => $xsl );
        $xh = xslt_create();
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, null);
        xslt_free($xh);
        file_put_contents($a_target_dir . '/indexMD.xml', $output);

        $a_xml_writer = new ilXmlWriter;
        // set dtd definition
        $a_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

        // set generated comment
        $a_xml_writer->xmlSetGenCmt("Export of ILIAS Content Module " . $this->getId() . " of installation " . $a_inst . ".");

        // set xml header
        $a_xml_writer->xmlHeader();

        $a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004SCO"));

        $this->exportXMLMetaData($a_xml_writer);

        $this->exportXMLPageObjects($a_target_dir, $a_xml_writer, $a_inst, $expLog);

        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);

        $this->exportHTML($a_inst, $a_target_dir, $expLog);

        //overwrite scorm.js for scrom 1.2
        if ($ver=="12") {
            copy('./Modules/Scorm2004/scripts/scorm_12.js', $a_target_dir . '/js/scorm.js');
        }

        $a_xml_writer->xmlEndTag("ContentObject");

        $a_xml_writer->xmlDumpFile($a_target_dir . '/index.xml', false);

        $a_xml_writer->_XmlWriter;
        
        // export sco data (currently only objective) to sco.xml
        if ($this->getType() == "sco") {
            $objectives_text = "";
            $a_xml_writer = new ilXmlWriter;
            
            $tr_data = $this->getObjectives();
            foreach ($tr_data as $data) {
                $objectives_text.= $data->getObjectiveID();
            }
            $a_xml_writer->xmlStartTag("sco");
            $a_xml_writer->xmlElement("objective", null, $objectives_text);
            $a_xml_writer->xmlEndTag("sco");
            $a_xml_writer->xmlDumpFile($a_target_dir . '/sco.xml', false);
            $a_xml_writer->_XmlWriter;
        }
    }


    public function exportHTML(
        $a_inst,
        $a_target_dir,
        &$expLog,
        $a_asset_type = "sco",
        $a_one_file = ""
    ) {
        $this->slm_object->prepareHTMLExporter($a_target_dir);
        $this->exportHTMLPageObjects(
            $a_inst,
            $a_target_dir,
            $expLog,
            'full',
            $a_asset_type,
            $a_one_file
        );
    }


    public function exportHTML4PDF($a_inst, $a_target_dir, &$expLog)
    {
        ilUtil::makeDir($a_target_dir . '/css');
        ilUtil::makeDir($a_target_dir . '/objects');
        ilUtil::makeDir($a_target_dir . '/images');
        $this->exportHTMLPageObjects($a_inst, $a_target_dir, $expLog, 'pdf');
    }

    public function exportPDF($a_inst, $a_target_dir, &$expLog)
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $a_xml_writer = new ilXmlWriter;
        $a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004SCO"));
        $this->exportPDFPrepareXmlNFiles($a_inst, $a_target_dir, $expLog, $a_xml_writer);
        $a_xml_writer->xmlEndTag("ContentObject");
        include_once 'Services/Transformation/classes/class.ilXML2FO.php';
        $xml2FO = new ilXML2FO();
        $xml2FO->setXSLTLocation('./Modules/Scorm2004/templates/xsl/contentobject2fo.xsl');
        $xml2FO->setXMLString($a_xml_writer->xmlDumpMem());
        $xml2FO->setXSLTParams(array('target_dir' => $a_target_dir));
        $xml2FO->transform();
        $fo_string = $xml2FO->getFOString();
        $fo_xml = simplexml_load_string($fo_string);
        $fo_ext = $fo_xml->xpath("//fo:declarations");
        $fo_ext = $fo_ext[0];
        $results = array();
        include_once "./Services/Utilities/classes/class.ilFileUtils.php";
        ilFileUtils::recursive_dirscan($a_target_dir . "/objects", $results);
        if (is_array($results["file"])) {
            foreach ($results["file"] as $key => $value) {
                $e = $fo_ext->addChild("fox:embedded-file", "", "http://xml.apache.org/fop/extensions");
                $e->addAttribute("src", $results[path][$key] . $value);
                $e->addAttribute("name", $value);
                $e->addAttribute("desc", "");
            }
        }
        $fo_string = $fo_xml->asXML();
        $a_xml_writer->_XmlWriter;
        return $fo_string;
    }

    public function exportPDFPrepareXmlNFiles($a_inst, $a_target_dir, &$expLog, &$a_xml_writer)
    {
        $this->exportHTML4PDF($a_inst, $a_target_dir, $expLog);
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $this->exportXMLPageObjects($a_target_dir, $a_xml_writer, $a_inst, $expLog);
        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
        $this->exportFileItems($a_target_dir, $expLog);

        include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php";
        include_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";

        $tree = new ilTree($this->slm_id);
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        foreach ($tree->getSubTree($tree->getNodeData($this->getId()), true, 'page') as $page) {
            $page_obj = new ilSCORM2004Page($page["obj_id"]);
            
            include_once("./Services/COPage/classes/class.ilPCQuestion.php");
            $q_ids = ilPCQuestion::_getQuestionIdsForPage("sahs", $page["obj_id"]);
            if (count($q_ids) > 0) {
                include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
                foreach ($q_ids as $q_id) {
                    $q_obj = assQuestion::_instanciateQuestion($q_id);
                    $qti_file = fopen($a_target_dir . "/qti_" . $q_id . ".xml", "w");
                    fwrite($qti_file, $q_obj->toXML());
                    fclose($qti_file);
                    $x = file_get_contents($a_target_dir . "/qti_" . $q_id . ".xml");
                    $x = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $x);
                    $a_xml_writer->appendXML($x);
                }
            }
            unset($page_obj);
        }
    }

    /**
     * Export HTML pages of SCO
     */
    public function exportHTMLPageObjects(
        $a_inst,
        $a_target_dir,
        &$expLog,
        $mode,
        $a_asset_type = "sco",
        $a_one_file = "",
        $a_sco_tpl = null
    ) {
        include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php";
        include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModuleGUI.php";
        include_once "./Services/MetaData/classes/class.ilMD.php";

        $tree = new ilTree($this->slm_id);
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");

        // @todo
        // Why is that much HTML code in an application class?
        // Please extract all this HTML to a tpl.<t_name>.html file and use
        // placeholders and the template engine to insert data.
        //
        // There copy/paste code residenting in ilSCORM2004ScoGUI. This
        // should be merged.
        //
        // alex, 4 Apr 09
        //

        //		if ($a_one_file == "")
        //		{
        $sco_tpl = new ilTemplate("tpl.sco.html", true, true, "Modules/Scorm2004");
        //		}
        //		else
        //		{
        //			$sco_tpl = $a_sco_tpl;
        //		}
        
        if ($mode != 'pdf' && $a_one_file == "") {
            include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
            $pg_exp = new ilCOPageHTMLExport($a_target_dir);
            $pg_exp->getPreparedMainTemplate($sco_tpl);
            
            // init and question lang vars
            $lk = ilObjSAHSLearningModule::getAffectiveLocalization($this->slm_id);
            $sco_tpl->setCurrentBlock("init");
            include_once("./Services/COPage/classes/class.ilPCQuestion.php");
            $sco_tpl->setVariable(
                "TXT_INIT_CODE",
                ilPCQuestion::getJSTextInitCode($lk)
            );
            $sco_tpl->parseCurrentBlock();
            
            // (additional) style sheets needed
            $styles = array("./css/yahoo/container.css",
                "./css/question_handling.css");
            foreach ($styles as $style) {
                $sco_tpl->setCurrentBlock("css_file");
                $sco_tpl->setVariable("CSS_FILE", $style);
                $sco_tpl->parseCurrentBlock();
            }
            
            // (additional) scripts needed
            $scripts = array("./js/scorm.js",
                "./js/pager.js", "./js/pure.js",
                "./js/questions_" . $this->getId() . ".js");
            foreach ($scripts as $script) {
                $sco_tpl->setCurrentBlock("js_file");
                $sco_tpl->setVariable("JS_FILE", $script);
                $sco_tpl->parseCurrentBlock();
            }
            
            if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset") {
                self::renderNavigation($sco_tpl, "./images/spacer.png", $lk);
            }

            $sco_tpl->touchBlock("finish");
        }
        // render head
        $sco_tpl->setCurrentBlock("head");
        $sco_tpl->setVariable("SCO_TITLE", $this->getTitle());
        $sco_tpl->parseCurrentBlock();
        $sco_tpl->touchBlock("tail");

        // meta page (meta info at SCO beginning)
        self::renderMetaPage($sco_tpl, $this, $a_asset_type, $mode);
        if ($a_one_file != "") {
            fputs($a_one_file, "<a name='sco" . $this->getId() . "'></a>");
            fputs($a_one_file, $sco_tpl->get("meta_page"));
        }

        //notify Question Exporter of new SCO
        require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
        ilQuestionExporter::indicateNewSco();

        // init export (this initialises glossary template)
        ilSCORM2004PageGUI::initExport();
        $terms = array();
        $terms = $this->getGlossaryTermIds();
        include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
        $pages = $tree->getSubTree($tree->getNodeData($this->getId()), true, 'page');
        $sco_q_ids = array();
        foreach ($pages as $page) {
            //echo(print_r($page));
            $page_obj = new ilSCORM2004PageGUI(
                $this->getType(),
                $page["obj_id"],
                0,
                $this->slm_object->getId()
            );
            $page_obj->setPresentationTitle($page["title"]);
            $page_obj->setOutputMode(IL_PAGE_OFFLINE);
            $page_obj->setStyleId($this->slm_object->getStyleSheetId());
            if (count($terms) > 1) {
                $page_obj->setGlossaryOverviewInfo(
                    ilSCORM2004ScoGUI::getGlossaryOverviewId(),
                    $this
                );
            }

            $page_output = $page_obj->showPage("export");

            // collect media objects
            $mob_ids = $page_obj->getSCORM2004Page()->collectMediaObjects(false);
            foreach ($mob_ids as $mob_id) {
                $this->mob_ids[$mob_id] = $mob_id;
                $media_obj = new ilObjMediaObject($mob_id);
                if ($media_obj->hasFullscreenItem()) {
                    $media_obj->exportMediaFullscreen($a_target_dir, $page_obj->getPageObject());
                }
            }

            // collect glossary items
            $int_links = $page_obj->getPageObject()->getInternalLinks(true);
            include_once("./Services/Link/classes/class.ilInternalLink.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
            if (is_array($int_links)) {
                foreach ($int_links as $k => $e) {
                    // glossary link
                    if ($e["Type"] == "GlossaryItem") {
                        $karr = explode(":", $k);
                        $tid = ilInternalLink::_extractObjIdOfTarget($karr[0]);
                        $dids = ilGlossaryDefinition::getDefinitionList($tid);
                        foreach ($dids as $did) {
                            include_once("./Modules/Glossary/classes/class.ilGlossaryDefPage.php");
                            $def_pg = new ilGlossaryDefPage($did["id"]);
                            $def_pg->buildDom();
                            $mob_ids = $def_pg->collectMediaObjects(false);
                            foreach ($mob_ids as $mob_id) {
                                $this->mob_ids[$mob_id] = $mob_id;
                                //echo "<br>-$mob_id-";
                                $media_obj = new ilObjMediaObject($mob_id);
                                if ($media_obj->hasFullscreenItem()) {
                                    $media_obj->exportMediaFullscreen($a_target_dir, $def_pg);
                                }
                            }
                            include_once("./Services/COPage/classes/class.ilPCFileList.php");
                            $file_ids = ilPCFileList::collectFileItems($def_pg, $def_pg->getDomDoc());

                            foreach ($file_ids as $file_id) {
                                $this->file_ids[$file_id] = $file_id;
                            }
                        }
                    }
                }
            }
            //exit;
            // collect all file items
            include_once("./Services/COPage/classes/class.ilPCFileList.php");
            $file_ids = ilPCFileList::collectFileItems($page_obj->getSCORM2004Page(), $page_obj->getSCORM2004Page()->getDomDoc());
            foreach ($file_ids as $file_id) {
                $this->file_ids[$file_id] = $file_id;
            }

            if ($mode == 'pdf') {
                include_once("./Services/COPage/classes/class.ilPCQuestion.php");
                $q_ids = ilPCQuestion::_getQuestionIdsForPage("sahs", $page["obj_id"]);
                foreach ($q_ids as $q_id) {
                    include_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
                    $q_gui = assQuestionGUI::_getQuestionGUI("", $q_id);
                    $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
                    $q_gui->outAdditionalOutput();
                    $html = $q_gui->getPreview(true);
                    $page_output = preg_replace("/{{{{{Question;il__qst_" . $q_id . "}}}}}/i", $html, $page_output);
                }
                
                $sco_tpl->touchBlock("pdf_pg_break");
            }

            $sco_tpl->setCurrentBlock("page");
            $sco_tpl->setVariable("PAGE", $page_output);
            $sco_tpl->parseCurrentBlock();
            
            // get all question ids of the sco
            if ($a_one_file != "") {
                include_once("./Services/COPage/classes/class.ilPCQuestion.php");
                $q_ids = ilPCQuestion::_getQuestionIdsForPage("sahs", $page["obj_id"]);
                foreach ($q_ids as $i) {
                    if (!in_array($i, $sco_q_ids)) {
                        $sco_q_ids[] = $i;
                    }
                }
            }
        }

        // glossary
        if ($mode!='pdf') {
            $sco_tpl->setVariable(
                "GLOSSARY_HTML",
                ilSCORM2004PageGUI::getGlossaryHTML($this)
            );
        }

        if ($a_one_file == "") {
            $output = $sco_tpl->get();
        } else {
            $output = $sco_tpl->get("page");
        }

        if ($mode=='pdf') {
            $output = preg_replace("/<div class=\"ilc_page_title_PageTitle\">(.*?)<\/div>/i", "<h2>$1</h2>", $output);
        }

        $output = preg_replace("/mobs\/mm_(\d+)\/([^\"]+)/i", "./objects/il_" . IL_INST_ID . "_mob_$1/$2", $output);
        $output = preg_replace("/\.\/files\/file_(\d+)\/([^\"]+)/i", "./objects/il_" . IL_INST_ID . "_file_$1/$2", $output);
        $output = preg_replace("/\.\/Services\/MediaObjects\/flash_mp3_player/i", "./players", $output);
        $output = preg_replace("/file=..\/..\/..\/.\//i", "file=../", $output);

        if ($mode!='pdf') {
            $output = preg_replace_callback("/href=\"&mob_id=(\d+)&pg_id=(\d+)\"/", array(get_class($this), 'fixFullscreeenLink'), $output);
            // this one is for fullscreen in glossary entries
            $output = preg_replace_callback("/href=\"fullscreen_(\d+)\.html\"/", array(get_class($this), 'fixFullscreeenLink'), $output);
            $output = preg_replace_callback("/{{{{{(Question;)(il__qst_[0-9]+)}}}}}/", array(get_class($this), 'insertQuestion'), $output);
            $q_handling = file_get_contents('./Modules/Scorm2004/scripts/questions/question_handling.js');
            fputs(fopen($a_target_dir . '/js/questions_' . $this->getId() . '.js', 'w+'), ilQuestionExporter::questionsJS() . $q_handling);
            copy(
                "./Modules/Scorm2004/templates/default/question_handling.css",
                $a_target_dir . '/css/question_handling.css'
            );

            // hack to get the question js into the file and to display the correct answers
            if ($a_one_file != "") {
                $output = '<script type="text/javascript">' . ilQuestionExporter::questionsJS() . '</script>' . $output;
                if (count($sco_q_ids) > 0) {
                    $output.= '<script type="text/javascript">';
                    foreach ($sco_q_ids as $i) {
                        if ($i > 0) {
                            $output.= "ilias.questions.showCorrectAnswers(" . $i . "); \n";
                        }
                    }
                    $output.= '</script>';
                }
            }

            foreach (ilQuestionExporter::getMobs() as $mob_id) {
                $this->mob_ids[$mob_id] = $mob_id;
            }
        }
        $this->q_media = ilQuestionExporter::getFiles();
        //questions export end

        if ($a_one_file != "") {
            fputs($a_one_file, $output);
        } else {
            fputs(fopen($a_target_dir . '/index.html', 'w+'), $output);
        }
                
        $this->exportFileItems($a_target_dir, $expLog);
    }
    
    /**
     * Render navigation
     *
     * @param object $a_tpl template
     * @param string $a_spacer_img path to spacer image
     */
    public static function renderNavigation($a_tpl, $a_spacer_img = "", $a_lang = "")
    {
        global $DIC;

        $lng = $DIC->language();
        
        if ($a_spacer_img == "") {
            $a_spacer_img = ilUtil::getImagePath("spacer.png");
        }
        if ($a_lang == "") {
            $a_lang = $lng->getLangKey();
        }
        // previous/next navigation
        $a_tpl->setCurrentBlock("ilLMNavigation");
        $a_tpl->setVariable("TXT_PREVIOUS", $lng->txtlng("content", 'scplayer_previous', $a_lang));
        $a_tpl->setVariable("SRC_SPACER", $a_spacer_img);
        $a_tpl->setVariable("TXT_NEXT", $lng->txtlng("content", 'scplayer_next', $a_lang));
        $a_tpl->parseCurrentBlock();
        $a_tpl->setCurrentBlock("ilLMNavigation2");
        $a_tpl->setVariable("TXT_PREVIOUS", $lng->txtlng("content", 'scplayer_previous', $a_lang));
        $a_tpl->setVariable("SRC_SPACER", $a_spacer_img);
        $a_tpl->setVariable("TXT_NEXT", $lng->txtlng("content", 'scplayer_next', $a_lang));
        $a_tpl->parseCurrentBlock();
    }
    
    /**
     * Render meta page (description/objectives at beginning)
     *
     * @param object $a_tpl template
     * @param object $a_sco SCO
     * @param string $a_asset_type asset type
     * @param string $a_mode mode
     */
    public static function renderMetaPage($a_tpl, $a_sco, $a_asset_type = "", $mode = "")
    {
        global $DIC;

        $lng = $DIC->language();
        
        if ($a_sco->getType() != "sco" || $a_sco->getHideObjectivePage()) {
            return;
        }
        
        if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset") {
            $meta = new ilMD($a_sco->getSLMId(), $a_sco->getId(), $a_sco->getType());
            $desc_ids = $meta->getGeneral()->getDescriptionIds();
            $sco_description = $meta->getGeneral()->getDescription($desc_ids[0])->getDescription();
        }
        
        if ($mode != 'pdf') {
            // title
            if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset") {
                $a_tpl->setCurrentBlock("title");
                $a_tpl->setVariable("SCO_TITLE", $a_sco->getTitle());
                $a_tpl->parseCurrentBlock();
            }
        } else {
            // title
            $a_tpl->setCurrentBlock("pdf_title");
            $a_tpl->setVariable("SCO_TITLE", $a_sco->getTitle());
            $a_tpl->parseCurrentBlock();
            $a_tpl->touchBlock("pdf_break");
        }

        // sco description
        if (trim($sco_description) != "") {
            $a_tpl->setCurrentBlock("sco_desc");
            $a_tpl->setVariable("TXT_DESC", $lng->txt("description"));
            include_once("./Services/COPage/classes/class.ilPCParagraph.php");
            $a_tpl->setVariable("VAL_DESC", self::convertLists($sco_description));
            $a_tpl->parseCurrentBlock();
        }

        if ($a_asset_type == "sco") {
            // sco objective(s)
            $objs = $a_sco->getObjectives();
            if (count($objs) > 0) {
                foreach ($objs as $objective) {
                    $a_tpl->setCurrentBlock("sco_obj");
                    $a_tpl->setVariable("VAL_OBJECTIVE", self::convertLists($objective->getObjectiveID()));
                    $a_tpl->parseCurrentBlock();
                }
                $a_tpl->setCurrentBlock("sco_objs");
                $a_tpl->setVariable("TXT_OBJECTIVES", $lng->txt("sahs_objectives"));
                $a_tpl->parseCurrentBlock();
            }
        }
        $a_tpl->setCurrentBlock("meta_page");
        $a_tpl->parseCurrentBlock();
    }
    

    /**
     * Convert * and # to lists
     *
     * @param string $a_text text
     * @return string text
     */
    public static function convertLists($a_text)
    {
        include_once("./Services/COPage/classes/class.ilPCParagraph.php");
        $a_text = nl2br($a_text);
        $a_text = str_replace(array("\n", "\r"), "", $a_text);
        $a_text = str_replace("<br>", "<br />", $a_text);
        $a_text = ilPCParagraph::input2xmlReplaceLists($a_text);
        $a_text = str_replace(
            array("<SimpleBulletList>", "</SimpleBulletList>",
                "<SimpleListItem>", "</SimpleListItem>",
                "<SimpleNumberedList>", "</SimpleNumberedList>"
                ),
            array("<ul class='ilc_list_u_BulletedList'>", "</ul>",
                "<li class='ilc_list_item_StandardListItem'>", "</li>",
                "<ol class='ilc_list_o_NumberedList'>", "</ol>"
                ),
            $a_text
        );
        return $a_text;
    }

    private static function fixFullscreeenLink($matches)
    {
        $media_obj = new ilObjMediaObject($matches[1]);
        if ($media_obj->hasFullscreenItem()) {
            return "href=\"./objects/il_" . IL_INST_ID . "_mob_" . $matches[1] . "/fullscreen.html\"";
            //return "href=\"./objects/il_".IL_INST_ID."_mob_".$matches[1]."/".$media_obj->getMediaItem("Fullscreen")->getLocation()."\"";
        }
    }

    //callback function for question export
    private static function insertQuestion($matches)
    {
        $q_exporter = new ilQuestionExporter();
        
        $ret = $q_exporter->exportQuestion($matches[2], "./objects/", "offline");
        
        return $ret;
    }

    public function exportXMLPageObjects($a_target_dir, &$a_xml_writer, $a_inst, &$expLog)
    {
        include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php";
        include_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";

        $tree = new ilTree($this->slm_id);
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");

        $pages = $tree->getSubTree($tree->getNodeData($this->getId()), true, 'page');
        foreach ($pages as $page) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $page["obj_id"]);

            // export xml to writer object
            $page_obj = new ilSCORM2004Page($page["obj_id"]);
            $page_obj->exportXML($a_xml_writer, "normal", $a_inst);

            //collect media objects
            $mob_ids = $page_obj->getMediaObjectIds();
            foreach ($mob_ids as $mob_id) {
                $this->mob_ids[$mob_id] = $mob_id;
            }

            // collect all file items
            $file_ids = $page_obj->getFileItemIds();
            foreach ($file_ids as $file_id) {
                $this->file_ids[$file_id] = $file_id;
            }

            include_once("./Services/COPage/classes/class.ilPCQuestion.php");
            $q_ids = ilPCQuestion::_getQuestionIdsForPage("sahs", $page["obj_id"]);
            if (count($q_ids) > 0) {
                include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
                foreach ($q_ids as $q_id) {
                    $q_obj = assQuestion::_instantiateQuestion($q_id);
                    // see #16557
                    if (is_object($q_obj)) {
                        $qti_file = fopen($a_target_dir . "/qti_" . $q_id . ".xml", "w");
                        fwrite($qti_file, $q_obj->toXML());
                        fclose($qti_file);
                    }
                }
            }

            unset($page_obj);
        }
    }

    public function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        include_once("./Modules/File/classes/class.ilObjFile.php");
        $linked_mobs = array();
        if (is_array($this->mob_ids)) {
            // mobs directly embedded into pages
            foreach ($this->mob_ids as $mob_id) {
                if ($mob_id > 0) {
                    $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
                    $media_obj = new ilObjMediaObject($mob_id);
                    $media_obj->exportXML($a_xml_writer, $a_inst);
                    $lmobs = $media_obj->getLinkedMediaObjects($this->mob_ids);
                    $linked_mobs = array_merge($linked_mobs, $lmobs);
                    unset($media_obj);
                }
            }

            // linked mobs (in map areas)
            foreach ($linked_mobs as $mob_id) {
                if ($mob_id > 0) {
                    $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
                    $media_obj = new ilObjMediaObject($mob_id);
                    $media_obj->exportXML($a_xml_writer, $a_inst);
                    unset($media_obj);
                }
            }
        }
        if (is_array($this->file_ids)) {
            foreach ($this->file_ids as $file_id) {
                if (ilObject::_lookupType($file_id) == "file") {
                    $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
                    $file_obj = new ilObjFile($file_id, false);
                    $file_obj->export($a_target_dir);
                    unset($file_obj);
                }
            }
        }
    }

    /**
    * export files of file itmes
    *
    */
    public function exportFileItems($a_target_dir, &$expLog)
    {
        include_once("./Modules/File/classes/class.ilObjFile.php");
        if (is_array($this->file_ids)) {
            foreach ($this->file_ids as $file_id) {
                $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
                if (ilObject::_lookupType($file_id) == "file") {
                    $file_obj = new ilObjFile($file_id, false);
                    $file_obj->export($a_target_dir);
                    unset($file_obj);
                } else {
                    $expLog->write(date("[y-m-d H:i:s] ") . "File Item not found, ID: " . $file_id);
                }
            }
        }

        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $linked_mobs = array();
        if (is_array($this->mob_ids)) {
            // mobs directly embedded into pages
            foreach ($this->mob_ids as $mob_id) {
                if ($mob_id > 0 && ilObject::_exists($mob_id)) {
                    $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
                    $media_obj = new ilObjMediaObject($mob_id);
                    $media_obj->exportFiles($a_target_dir, $expLog);
                    $lmobs = $media_obj->getLinkedMediaObjects($this->mob_ids);
                    $linked_mobs = array_merge($linked_mobs, $lmobs);

                    unset($media_obj);
                }
            }

            // linked mobs (in map areas)
            foreach ($linked_mobs as $mob_id) {
                if ($mob_id > 0 && ilObject::_exists($mob_id)) {
                    $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
                    $media_obj = new ilObjMediaObject($mob_id);
                    $media_obj->exportFiles($a_target_dir);
                    unset($media_obj);
                }
            }
        }

        //media files in questions
        foreach ($this->q_media as $media) {
            if ($media !="") {
                error_log($media);
                if (is_file($media)) {
                    copy($media, $a_target_dir . "/objects/" . basename($media));
                }
            }
        }
    }

    /* export content objects meta data to xml (see ilias_co.dtd)
     *
     * @param	object		$a_xml_writer	ilXmlWriter object that receives the
     *										xml data
     */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($this->getSLMId(), $this->getId(), $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function getExportFiles()
    {
        $file = array();

        require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Export.php");

        $export = new ilSCORM2004Export($this);
        foreach ($export->getSupportedExportTypes() as $type) {
            $dir = $export->getExportDirectoryForType($type);
            // quit if import dir not available
            if (!@is_dir($dir) or !is_writeable($dir)) {
                continue;
            }
            // open directory
            $cdir = dir($dir);

            // get files and save the in the array
            while ($entry = $cdir->read()) {
                if ($entry != "." and
                $entry != ".." and
                (
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(" . $this->getType() . "_)" . $this->getId() . "+\.zip\$~", $entry) or
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(" . $this->getType() . "_)" . $this->getId() . "+\.pdf\$~", $entry) or
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(" . $this->getType() . "_)" . $this->getId() . "+\.iso\$~", $entry)
                )) {
                    $file[$entry . $type] = array("type" => $type, "file" => $entry,
                        "size" => filesize($dir . "/" . $entry));
                }
            }

            // close import directory
            $cdir->close();
        }

        // sort files
        ksort($file);
        reset($file);
        return $file;
    }

    /**
     * Get glossary term ids in sco
     *
     * @param
     * @return
     */
    public function getGlossaryTermIds()
    {
        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
        $childs = $this->tree->getChilds($this->getId());
        $ids = array();
        foreach ($childs as $c) {
            $links = ilInternalLink::_getTargetsOfSource(
                "sahs" . ":pg",
                $c["child"]
            );
            foreach ($links as $l) {
                if ($l["type"] == "git" && (int) $l["inst"] == 0 && !isset($ids[$l["id"]])) {
                    $ids[$l["id"]] = ilGlossaryTerm::_lookGlossaryTerm($l["id"]);
                }
            }
        }
        asort($ids);
        return $ids;
    }
}
