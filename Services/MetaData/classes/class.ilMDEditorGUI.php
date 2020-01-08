<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

// @FIXME
define('IL_TLT_MAX_HOURS', 99);


/**
* Meta Data class (element general)
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @package ilias-core
* @version $Id$
*
* @ilCtrl_Calls ilMDEditorGUI: ilFormPropertyDispatchGUI
*/
class ilMDEditorGUI
{
    public $ctrl = null;
    public $lng = null;
    public $tpl = null;
    public $md_obj = null;

    public $observers = array();

    public $rbac_id = null;
    public $obj_id = null;
    public $obj_type = null;

    public function __construct($a_rbac_id, $a_obj_id, $a_obj_type)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC['tpl'];
        $this->tabs_gui = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->md_obj = new ilMD($a_rbac_id, $a_obj_id, $a_obj_type);

        $this->lng->loadLanguageModule('meta');
        
        include_once('Services/MetaData/classes/class.ilMDSettings.php');
        $this->md_settings = ilMDSettings::_getInstance();
    }

    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                // see ilTaxMDGUI / ilTaxSelectInputGUI
                include_once './Services/Form/classes/class.ilFormPropertyDispatchGUI.php';
                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $this->initFilter();
                $item = $this->getFilterItemByPostVar($_GET["postvar"]);
                $form_prop_dispatch->setItem($item);
                return $this->ctrl->forwardCommand($form_prop_dispatch);
            
            default:
                if (!$cmd) {
                    $cmd = "listSection";
                }
                $this->$cmd();
                break;
        }
        return true;
    }


    public function debug()
    {
        include_once 'Services/MetaData/classes/class.ilMD2XML.php';


        $xml_writer = new ilMD2XML($this->md_obj->getRBACId(), $this->md_obj->getObjId(), $this->md_obj->getObjType());
        $xml_writer->startExport();

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        
        $this->__setTabs('meta_general');

        $this->tpl->setVariable("MD_CONTENT", htmlentities($xml_writer->getXML()));

        return true;
    }

    /**
     * @deprecated with release 5_3
     */
    public function listQuickEdit_scorm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        if (!is_object($this->md_section = $this->md_obj->getGeneral())) {
            $this->md_section = $this->md_obj->addGeneral();
            $this->md_section->save();
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        
        $this->__setTabs('meta_quickedit');

        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_quick_edit_scorm.html', 'Services/MetaData');

        $this->ctrl->setReturn($this, 'listGeneral');
        $this->ctrl->setParameter($this, 'section', 'meta_general');
        $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));

        $this->tpl->setVariable("TXT_QUICK_EDIT", $this->lng->txt("meta_quickedit"));
        $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
        $this->tpl->setVariable("TXT_KEYWORD", $this->lng->txt("meta_keyword"));
        $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
        $this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));

        // Language
        $first = true;
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lan = $this->md_section->getLanguage($id);
            
            if ($first) {
                $this->tpl->setCurrentBlock("language_head");
                $this->tpl->setVariable("ROWSPAN_LANG", count($ids));
                $this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                $this->tpl->parseCurrentBlock();
                $first = false;
            }

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_language');

                $this->tpl->setCurrentBlock("language_delete");
                $this->tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("language_loop");
            $this->tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                'gen_language[' . $id . '][language]',
                $md_lan->getLanguageCode()
            ));
            $this->tpl->parseCurrentBlock();
        }

        if ($first) {
            $this->tpl->setCurrentBlock("language_head");
            $this->tpl->setVariable("ROWSPAN_LANG", 1);
            $this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("language_loop");
            $this->tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                'gen_language[][language]',
                ""
            ));
            $this->tpl->parseCurrentBlock();
        }

        // TITLE
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));
        $this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($this->md_section->getTitle()));
        $this->tpl->setVariable("VAL_TITLE_LANGUAGE", $this->__showLanguageSelect(
            'gen_title_language',
            $this->md_section->getTitleLanguageCode()
        ));

        // DESCRIPTION
        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_description');

                $this->tpl->setCurrentBlock("description_delete");
                $this->tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("description_loop");
            $this->tpl->setVariable("DESCRIPTION_LOOP_NO", $id);
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::prepareFormOutput($md_des->getDescription()));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "gen_description[" . $id . '][language]',
                $md_des->getDescriptionLanguageCode()
            ));
            $this->tpl->parseCurrentBlock();
        }

        // KEYWORD
        $first = true;
        $keywords = array();
        foreach ($ids = $this->md_section->getKeywordIds() as $id) {
            $md_key = $this->md_section->getKeyword($id);
            $keywords[$md_key->getKeywordLanguageCode()][]
                = $md_key->getKeyword();
        }
        
        foreach ($keywords as $lang => $keyword_set) {
            if ($first) {
                $this->tpl->setCurrentBlock("keyword_head");
                $this->tpl->setVariable("ROWSPAN_KEYWORD", count($keywords));
                $this->tpl->setVariable("TXT_COMMA_SEP2", $this->lng->txt('comma_separated'));
                $this->tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("keywords"));
                $this->tpl->parseCurrentBlock();
                $first = false;
            }

            $this->tpl->setCurrentBlock("keyword_loop");
            $this->tpl->setVariable("KEYWORD_LOOP_VAL", ilUtil::prepareFormOutput(
                implode($keyword_set, ", ")
            ));
            $this->tpl->setVariable("LANG", $lang);
            $this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "keyword[language][$lang]",
                $lang
            ));
            $this->tpl->parseCurrentBlock();
        }

        if (count($keywords) == 0) {
            $this->tpl->setCurrentBlock("keyword_head");
            $this->tpl->setVariable("ROWSPAN_KEYWORD", 1);
            $this->tpl->setVariable("TXT_COMMA_SEP2", $this->lng->txt('comma_separated'));
            $this->tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("keywords"));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("keyword_loop");
            $this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "keyword[language][$lang]",
                $lang
            ));
        }
        
        // Lifecycle...
        // experts
        $this->tpl->setVariable("TXT_EXPERTS", $lng->txt('meta_subjectmatterexpert'));
        $this->tpl->setVariable("TXT_COMMA_SEP", $this->lng->txt('comma_separated'));
        $this->tpl->setVariable("TXT_SCOPROP_EXPERT", $this->lng->txt('sco_propagate'));
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "SubjectMatterExpert") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str = $ent_str . $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            $this->tpl->setVariable("EXPERTS_VAL", ilUtil::prepareFormOutput($ent_str));
        }
        // InstructionalDesigner
        $this->tpl->setVariable("TXT_DESIGNERS", $lng->txt('meta_instructionaldesigner'));
        $this->tpl->setVariable("TXT_SCOPROP_DESIGNERS", $this->lng->txt('sco_propagate'));
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "InstructionalDesigner") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str = $ent_str . $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            $this->tpl->setVariable("DESIGNERS_VAL", ilUtil::prepareFormOutput($ent_str));
        }
        // Point of Contact
        $this->tpl->setVariable("TXT_POC", $lng->txt('meta_pointofcontact'));
        $this->tpl->setVariable("TXT_SCOPROP_POC", $this->lng->txt('sco_propagate'));
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "PointOfContact") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str = $ent_str . $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            $this->tpl->setVariable("POC_VAL", ilUtil::prepareFormOutput($ent_str));
        }
        
        $this->tpl->setVariable("TXT_STATUS", $this->lng->txt('meta_status'));
        if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $this->md_section = $this->md_obj->addLifecycle();
            $this->md_section->save();
        }
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $this->tpl->setVariable("SEL_STATUS", ilMDUtilSelect::_getStatusSelect(
            $this->md_section->getStatus(),
            "lif_status",
            array(0 => $this->lng->txt('meta_please_select'))
        ));
        }

        // Rights...
        // Copyright
        // smeyer 2018-09-14 not supported

        $tlt = array(0,0,0,0,0);
        $valid = true;
        if (is_object($this->md_section = $this->md_obj->getEducational())) {
            include_once 'Services/MetaData/classes/class.ilMDUtils.php';
            
            if (!$tlt = ilMDUtils::_LOMDurationToArray($this->md_section->getTypicalLearningTime())) {
                if (strlen($this->md_section->getTypicalLearningTime())) {
                    $tlt = array(0,0,0,0,0);
                    $valid = false;
                }
            }
        }
        $this->tpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
        $this->tpl->setVariable("SEL_MONTHS", $this->__buildMonthsSelect($tlt[0]));
        $this->tpl->setVariable("SEL_DAYS", $this->__buildDaysSelect($tlt[1]));
        
        $this->tpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
        $this->tpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));

        $this->tpl->setVariable("TXT_TYPICAL_LEARN_TIME", $this->lng->txt('meta_typical_learning_time'));
        $this->tpl->setVariable("SEL_TLT", ilUtil::makeTimeSelect(
            'tlt',
            $tlt[4] ? false : true,
            $tlt[2],
            $tlt[3],
            $tlt[4],
            false
        ));
        $this->tpl->setVariable("TLT_HINT", $tlt[4] ? '(hh:mm:ss)' : '(hh:mm)');

        if (!$valid) {
            $this->tpl->setCurrentBlock("tlt_not_valid");
            $this->tpl->setVariable("TXT_CURRENT_VAL", $this->lng->txt('meta_current_value'));
            $this->tpl->setVariable("TLT", $this->md_section->getTypicalLearningTime());
            $this->tpl->setVariable("INFO_TLT_NOT_VALID", $this->lng->txt('meta_info_tlt_not_valid'));
            $this->tpl->parseCurrentBlock();
        }
        
    
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
    }
    
    public function listQuickEdit()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        if (!is_object($this->md_section = $this->md_obj->getGeneral())) {
            $this->md_section = $this->md_obj->addGeneral();
            $this->md_section->save();
        }
        
        $this->__setTabs('meta_quickedit');


        $interruptive_modal = $this->getChangeCopyrightModal();
        $interruptive_signal = '';
        $modal_content = '';
        if ($interruptive_modal != null) {
            $interruptive_signal = $interruptive_modal->getShowSignal();
            $modal_content = $this->ui_renderer->render($interruptive_modal);
        }
        $form = $this->initQuickEditForm($interruptive_signal);

        $tpl->setContent(
            $modal_content . $form->getHTML()
        );
    }

    /**
     * Init quick edit form.
     */
    public function initQuickEditForm($a_signal_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setId('ilquickeditform');
        $this->form->setShowTopButtons(false);
    
        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "gen_title");
        $ti->setMaxLength(200);
        $ti->setSize(50);
        if ($this->md_obj->getObjType() != 'sess') {
            $ti->setRequired(true);
        }
        $ti->setValue($this->md_section->getTitle());
        $this->form->addItem($ti);
        
        // description(s)
        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);
            
            $ta = new ilTextAreaInputGUI($this->lng->txt("meta_description"), "gen_description[" . $id . "][description]");
            $ta->setCols(50);
            $ta->setRows(4);
            $ta->setValue($md_des->getDescription());
            if (count($ids) > 1) {
                $ta->setInfo($this->lng->txt("meta_l_" . $md_des->getDescriptionLanguageCode()));
            }

            $this->form->addItem($ta);
        }

        // language(s)
        $first = "";
        $options = ilMDLanguageItem::_getLanguages();
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lan = $this->md_section->getLanguage($id);
            $first_lang = $md_lan->getLanguageCode();
            $si = new ilSelectInputGUI($this->lng->txt("meta_language"), "gen_language[" . $id . "][language]");
            $si->setOptions($options);
            $si->setValue($md_lan->getLanguageCode());
            $this->form->addItem($si);
            $first = false;
        }
        if ($first) {
            $si = new ilSelectInputGUI($this->lng->txt("meta_language"), "gen_language[][language]");
            $si->setOptions($options);
            $this->form->addItem($si);
        }
        
        // keyword(s)
        $first = true;
        $keywords = array();
        foreach ($ids = $this->md_section->getKeywordIds() as $id) {
            $md_key = $this->md_section->getKeyword($id);
            if (trim($md_key->getKeyword()) != "") {
                $keywords[$md_key->getKeywordLanguageCode()][]
                    = $md_key->getKeyword();
            }
        }
        foreach ($keywords as $lang => $keyword_set) {
            $kw = new ilTextInputGUI(
                $this->lng->txt("keywords"),
                "keywords[value][" . $lang . "]"
            );
            $kw->setDataSource($this->ctrl->getLinkTarget($this, "keywordAutocomplete", "", true));
            $kw->setMaxLength(200);
            $kw->setSize(50);
            $kw->setMulti(true);
            if (count($keywords) > 1) {
                $kw->setInfo($this->lng->txt("meta_l_" . $lang));
            }
            $this->form->addItem($kw);
            asort($keyword_set);
            $kw->setValue($keyword_set);
        }
        if (count($keywords) == 0) {
            $kw = new ilTextInputGUI(
                $this->lng->txt("keywords"),
                "keywords[value][" . $first_lang . "]"
            );
            $kw->setDataSource($this->ctrl->getLinkTarget($this, "keywordAutocomplete", "", true));
            $kw->setMaxLength(200);
            $kw->setSize(50);
            $kw->setMulti(true);
            $this->form->addItem($kw);
        }

        // Lifecycle...
        // Authors
        $ta = new ilTextAreaInputGUI(
            $this->lng->txt('authors') . "<br />" .
            "(" . sprintf($this->lng->txt('md_separated_by'), $this->md_settings->getDelimiter()) . ")",
            "life_authors"
        );
        $ta->setCols(50);
        $ta->setRows(2);
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "Author") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str = $ent_str . $sep . $md_ent->getEntity();
                        $sep = $this->md_settings->getDelimiter() . " ";
                    }
                }
            }
            $ta->setValue($ent_str);
        }
        $this->form->addItem($ta);

        // copyright
        $this->listQuickEditCopyright($this->form);


        // typical learning time
        include_once("./Services/MetaData/classes/class.ilTypicalLearningTimeInputGUI.php");
        $tlt = new ilTypicalLearningTimeInputGUI($this->lng->txt("meta_typical_learning_time"), "tlt");
        $edu = $this->md_obj->getEducational();
        if (is_object($edu)) {
            $tlt->setValueByLOMDuration($edu->getTypicalLearningTime());
        }
        $this->form->addItem($tlt);

        $this->form->addCommandButton("updateQuickEdit", $lng->txt("save"), 'button_ilquickeditform');
        $this->form->setTitle($this->lng->txt("meta_quickedit"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));


        if (ilMDSettings::_getInstance()->isCopyrightSelectionActive()) {
            $DIC->ui()->mainTemplate()->addJavaScript(
                'Services/MetaData/js/ilMetaCopyrightListener.js'
            );
            $DIC->ui()->mainTemplate()->addOnLoadCode(
                'il.MetaDataCopyrightListener.init("' .
                $a_signal_id . '","copyright","form_ilquickeditform","button_ilquickeditform");'
            );
        }



        return $this->form;
    }

    /**
     * Show copyright selecetion
     * @param ilPropertyFormGUI $form
     */
    protected function listQuickEditCopyright(ilPropertyFormGUI $form)
    {
        $md_settings = ilMDSettings::_getInstance();
        $oer_settings = ilOerHarvesterSettings::getInstance();

        $cp_entries = ilMDCopyrightSelectionEntry::_getEntries();
        $description = ilMDRights::_lookupDescription(
            $this->md_obj->getRBACId(),
            $this->md_obj->getObjId()
        );

        //current id can be 0 for non predefined copyright.
        //Todo add new DB column with copyright id instead of parse descriptions to get entry ID.
        if ($description) {
            $current_id = ilMDCopyrightSelectionEntry::_extractEntryId($description);
        } else {
            $current_id = ilMDCopyrightSelectionEntry::getDefault();
        }

        if (
            !$this->md_settings->isCopyrightSelectionActive() ||
            !count($cp_entries)
        ) {
            return true;
        }

        $copyright = new ilRadioGroupInputGUI($this->lng->txt('meta_copyright'), 'copyright');
        $copyright->setValue($current_id);


        foreach ($cp_entries as $copyright_entry) {
            $radio_entry = new ilRadioOption(
                $copyright_entry->getTitle(),
                $copyright_entry->getEntryId(),
                $copyright_entry->getDescription()
            );

            if ($copyright_entry->getOutdated()) {
                $radio_entry->setTitle("(" . $this->lng->txt('meta_copyright_outdated') . ") " . $radio_entry->getTitle());
                $radio_entry->setDisabled(true);
            }

            if (
                $oer_settings->supportsHarvesting($this->md_obj->getObjType()) &&
                $oer_settings->isActiveCopyrightTemplate($copyright_entry->getEntryId())
            ) {
                // block harvesting
                $blocked = new ilCheckboxInputGUI(
                    $this->lng->txt('meta_oer_blocked'),
                    'copyright_oer_blocked_' . $copyright_entry->getEntryId()
                );
                $blocked->setInfo($this->lng->txt('meta_oer_blocked_info'));
                $blocked->setValue(1);
                $status = new ilOerHarvesterObjectStatus($this->md_obj->getRBACId());
                if ($status->isBlocked()) {
                    $blocked->setChecked(true);
                }
                $radio_entry->addSubItem($blocked);
            }


            $copyright->addOption($radio_entry);
        }

        // add own selection
        $own_selection = new ilRadioOption(
            $this->lng->txt('meta_cp_own'),
            'copyright_text'
        );
        $own_selection->setValue(0);

        // copyright text
        $own_copyright = new ilTextAreaInputGUI(
            '',
            'copyright_text'
        );
        if ($current_id == 0) {
            $own_copyright->setValue($description);
        }
        $own_selection->addSubItem($own_copyright);
        $copyright->addOption($own_selection);
        $form->addItem($copyright);
    }

    /**
     * Keyword list for autocomplete
     *
     * @param
     * @return
     */
    public function keywordAutocomplete()
    {
        include_once("./Services/MetaData/classes/class.ilMDKeyword.php");
        $res = ilMDKeyword::_getMatchingKeywords(
            ilUtil::stripSlashes($_GET["term"]),
            $this->md_obj->getObjType(),
            $this->md_obj->getRBACId()
        );
        
        $result = array();
        $cnt = 0;
        foreach ($res as $r) {
            if ($cnt++ > 19) {
                continue;
            }
            $entry = new stdClass();
            $entry->value = $r;
            $entry->label = $r;
            $result[] = $entry;
        }

        include_once './Services/JSON/classes/class.ilJsonUtil.php';
        echo ilJsonUtil::encode($result);
        exit;
    }
    
    
    /**
    * update quick edit properties
    */
    public function updateQuickEdit()
    {
        ilLoggerFactory::getLogger('root')->dump($_REQUEST);


        if (!trim($_POST['gen_title'])) {
            if ($this->md_obj->getObjType() != 'sess') {
                ilUtil::sendFailure($this->lng->txt('title_required'));
                $this->listQuickEdit();
                return false;
            }
        }

        // General values
        $this->md_section = $this->md_obj->getGeneral();
        $this->md_section->setTitle(ilUtil::stripSlashes($_POST['gen_title']));
        //		$this->md_section->setTitleLanguage(new ilMDLanguageItem($_POST['gen_title_language']));
        $this->md_section->update();

        // Language
        if (is_array($_POST['gen_language'])) {
            foreach ($_POST['gen_language'] as $id => $data) {
                if ($id > 0) {
                    $md_lan = $this->md_section->getLanguage($id);
                    $md_lan->setLanguage(new ilMDLanguageItem($data['language']));
                    $md_lan->update();
                } else {
                    $md_lan = $this->md_section->addLanguage();
                    $md_lan->setLanguage(new ilMDLanguageItem($data['language']));
                    $md_lan->save();
                }
            }
        }
        // Description
        if (is_array($_POST['gen_description'])) {
            foreach ($_POST['gen_description'] as $id => $data) {
                $md_des = $this->md_section->getDescription($id);
                $md_des->setDescription(ilUtil::stripSlashes($data['description']));
                //				$md_des->setDescriptionLanguage(new ilMDLanguageItem($data['language']));
                $md_des->update();
            }
        }
        
        // Keyword
        if (is_array($_POST["keywords"]["value"])) {
            include_once("./Services/MetaData/classes/class.ilMDKeyword.php");
            ilMDKeyword::updateKeywords($this->md_section, $_POST["keywords"]["value"]);
        }
        $this->callListeners('General');
        
        // Copyright
        if ($_POST['copyright'] || $_POST['copyright_text']) {
            if (!is_object($this->md_section = $this->md_obj->getRights())) {
                $this->md_section = $this->md_obj->addRights();
                $this->md_section->save();
            }
            if ($_POST['copyright'] > 0) {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription('il_copyright_entry__' . IL_INST_ID . '__' . (int) $_POST['copyright']);
            } else {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription(ilUtil::stripSlashes($_POST['copyright_text']));
            }
            $this->md_section->update();

            // update oer status

            $oer_settings = ilOerHarvesterSettings::getInstance();
            if ($oer_settings->supportsHarvesting($this->md_obj->getObjType())) {
                $chosen_copyright = (int) $_POST['copyright'];

                $status = new ilOerHarvesterObjectStatus($this->md_obj->getRBACId());
                $status->setBlocked((int) $_POST['copyright_oer_blocked_' . $chosen_copyright] ? true : false);
                $status->save();
            }
        } else {
            if (is_object($this->md_section = $this->md_obj->getRights())) {
                $this->md_section->setCopyrightAndOtherRestrictions("No");
                $this->md_section->setDescription("");
                $this->md_section->update();
            }
        }
        $this->callListeners('Rights');

        //Educational...
        // Typical Learning Time
        if ($_POST['tlt']['mo'] or $_POST['tlt']['d'] or
           $_POST["tlt"]['h'] or $_POST['tlt']['m'] or $_POST['tlt']['s']) {
            if (!is_object($this->md_section = $this->md_obj->getEducational())) {
                $this->md_section = $this->md_obj->addEducational();
                $this->md_section->save();
            }
            $this->md_section->setPhysicalTypicalLearningTime(
                (int) $_POST['tlt']['mo'],
                (int) $_POST['tlt']['d'],
                (int) $_POST['tlt']['h'],
                (int) $_POST['tlt']['m'],
                (int) $_POST['tlt']['s']
            );
            $this->md_section->update();
        } else {
            if (is_object($this->md_section = $this->md_obj->getEducational())) {
                $this->md_section->setPhysicalTypicalLearningTime(0, 0, 0, 0, 0);
                $this->md_section->update();
            }
        }
        $this->callListeners('Educational');
        //Lifecycle...
        // Authors
        if ($_POST["life_authors"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }
            
            // determine all entered authors
            $auth_arr = explode($this->md_settings->getDelimiter(), $_POST["life_authors"]);
            for ($i = 0; $i < count($auth_arr); $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }
            
            $md_con_author = "";
            
            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "Author") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        
                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr)]);
                        } else {  // existing author has not been entered again -> delete
                            $md_ent->delete();
                        }
                    }
                    $md_con_author = $md_con;
                }
            }
            
            // insert enterd, but not existing authors
            if (count($auth_arr) > 0) {
                if (!is_object($md_con_author)) {
                    $md_con_author = $this->md_section->addContribute();
                    $md_con_author->setRole("Author");
                    $md_con_author->save();
                }
                foreach ($auth_arr as $auth) {
                    $md_ent = $md_con_author->addEntity();
                    $md_ent->setEntity(ilUtil::stripSlashes($auth));
                    $md_ent->save();
                }
            }
        } else {	// nothing has been entered: delete all author contribs
            if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
                foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                    $md_con = $this->md_section->getContribute($con_id);
                    if ($md_con->getRole() == "Author") {
                        $md_con->delete();
                    }
                }
            }
        }
        $this->callListeners('Lifecycle');
        
        // #18563
        /*
        if(!$_REQUEST["wsp_id"])
        {
            // (parent) container taxonomies?
            include_once "Services/Taxonomy/classes/class.ilTaxMDGUI.php";
            $tax_gui = new ilTaxMDGUI($this->md_obj->getRBACId(),$this->md_obj->getObjId(),$this->md_obj->getObjType());
            $tax_gui->updateFromMDForm();
        }*/
        
        // Redirect here to read new title and description
        // Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, 'listSection');
    }

    public function updateQuickEdit_scorm_propagate($request, $type)
    {
        $module_id = $this->md_obj->obj_id;
        if ($this->md_obj->obj_type=='sco') {
            $module_id = $this->md_obj->rbac_id;
        }
        $tree = new ilTree($module_id);
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, 'sco') as $sco) {
            $sco_md = new ilMD($module_id, $sco['obj_id'], 'sco');
            if ($_POST[$request] != "") {
                $sco_md_section;
                if (!is_object($sco_md_section = $sco_md->getLifecycle())) {
                    $sco_md_section = $sco_md->addLifecycle();
                    $sco_md_section->save();
                }
                // determine all entered authors
                $auth_arr = explode(",", $_POST[$request]);
                for ($i = 0; $i < count($auth_arr); $i++) {
                    $auth_arr[$i] = trim($auth_arr[$i]);
                }
                
                $md_con_author = "";
                    
                // update existing author entries (delete if not entered)
                foreach (($ids = $sco_md_section->getContributeIds()) as $con_id) {
                    $md_con = $sco_md_section->getContribute($con_id);
                    if ($md_con->getRole() == $type) {
                        foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                            $md_ent = $md_con->getEntity($ent_id);
    
                            // entered author already exists
                            if (in_array($md_ent->getEntity(), $auth_arr)) {
                                unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr)]);
                            } else {  // existing author has not been entered again -> delete
                                $md_ent->delete();
                            }
                        }
                        $md_con_author = $md_con;
                    }
                }
                
                // insert enterd, but not existing authors
                if (count($auth_arr) > 0) {
                    if (!is_object($md_con_author)) {
                        $md_con_author = $sco_md_section->addContribute();
                        $md_con_author->setRole($type);
                        $md_con_author->save();
                    }
                    foreach ($auth_arr as $auth) {
                        $md_ent = $md_con_author->addEntity();
                        $md_ent->setEntity(ilUtil::stripSlashes($auth));
                        $md_ent->save();
                    }
                }
            } else {	// nothing has been entered: delete all author contribs
                if (is_object($sco_md_section = $sco_md->getLifecycle())) {
                    foreach (($ids = $sco_md_section->getContributeIds()) as $con_id) {
                        $md_con = $sco_md_section->getContribute($con_id);
                        if ($md_con->getRole() == $type) {
                            $md_con->delete();
                        }
                    }
                }
            }
            $sco_md->update();
        }
        $this->updateQuickEdit_scorm();
    }
    
    public function updateQuickEdit_scorm_prop_expert()
    {
        $this->updateQuickEdit_scorm_propagate("life_experts", "SubjectMatterExpert");
    }
    public function updateQuickEdit_scorm_prop_designer()
    {
        $this->updateQuickEdit_scorm_propagate("life_designers", "InstructionalDesigner");
    }
    public function updateQuickEdit_scorm_prop_poc()
    {
        $this->updateQuickEdit_scorm_propagate("life_poc", "PointOfContact");
    }
    /**
    * update quick edit properties - SCORM customization
    */
    public function updateQuickEdit_scorm()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // General values
        $this->md_section = $this->md_obj->getGeneral();
        $this->md_section->setTitle(ilUtil::stripSlashes($_POST['gen_title']));
        $this->md_section->setTitleLanguage(new ilMDLanguageItem($_POST['gen_title_language']));
        $this->md_section->update();

        // Language
        if (is_array($_POST['gen_language'])) {
            foreach ($_POST['gen_language'] as $id => $data) {
                if ($id > 0) {
                    $md_lan = $this->md_section->getLanguage($id);
                    $md_lan->setLanguage(new ilMDLanguageItem($data['language']));
                    $md_lan->update();
                } else {
                    $md_lan = $this->md_section->addLanguage();
                    $md_lan->setLanguage(new ilMDLanguageItem($data['language']));
                    $md_lan->save();
                }
            }
        }
        // Description
        if (is_array($_POST['gen_description'])) {
            foreach ($_POST['gen_description'] as $id => $data) {
                $md_des = $this->md_section->getDescription($id);
                $md_des->setDescription(ilUtil::stripSlashes($data['description']));
                $md_des->setDescriptionLanguage(new ilMDLanguageItem($data['language']));
                $md_des->update();
            }
        }
        
        
        // Keyword
        if (is_array($_POST["keywords"]["value"])) {
            $new_keywords = array();
            foreach ($_POST["keywords"]["value"] as $lang => $keywords) {
                $language = $_POST["keyword"]["language"][$lang];
                $keywords = explode(",", $keywords);
                foreach ($keywords as $keyword) {
                    $new_keywords[$language][] = trim($keyword);
                }
            }
            
            // update existing author entries (delete if not entered)
            foreach ($ids = $this->md_section->getKeywordIds() as $id) {
                $md_key = $this->md_section->getKeyword($id);

                $lang = $md_key->getKeywordLanguageCode();
                
                // entered keyword already exists
                if (is_array($new_keywords[$lang]) &&
                    in_array($md_key->getKeyword(), $new_keywords[$lang])) {
                    unset($new_keywords[$lang]
                        [array_search($md_key->getKeyword(), $new_keywords[$lang])]);
                } else {  // existing keyword has not been entered again -> delete
                    $md_key->delete();
                }
            }
            
            // insert entered, but not existing keywords
            foreach ($new_keywords as $lang => $key_arr) {
                foreach ($key_arr as $keyword) {
                    if ($keyword != "") {
                        $md_key = $this->md_section->addKeyword();
                        $md_key->setKeyword(ilUtil::stripSlashes($keyword));
                        $md_key->setKeywordLanguage(new ilMDLanguageItem($lang));
                        $md_key->save();
                    }
                }
            }
        }
        $this->callListeners('General');
        
        // Copyright
        if ($_POST['copyright_id'] or $_POST['rights_copyright']) {
            if (!is_object($this->md_section = $this->md_obj->getRights())) {
                $this->md_section = $this->md_obj->addRights();
                $this->md_section->save();
            }
            if ($_POST['copyright_id']) {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription('il_copyright_entry__' . IL_INST_ID . '__' . (int) $_POST['copyright_id']);
            } else {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription(ilUtil::stripSlashes($_POST["rights_copyright"]));
            }
            $this->md_section->update();
        } else {
            if (is_object($this->md_section = $this->md_obj->getRights())) {
                $this->md_section->setCopyrightAndOtherRestrictions("No");
                $this->md_section->setDescription("");
                $this->md_section->update();
            }
        }
        $this->callListeners('Rights');

        //Educational...
        // Typical Learning Time
        if ($_POST['tlt']['mo'] or $_POST['tlt']['d'] or
           $_POST["tlt"]['h'] or $_POST['tlt']['m'] or $_POST['tlt']['s']) {
            if (!is_object($this->md_section = $this->md_obj->getEducational())) {
                $this->md_section = $this->md_obj->addEducational();
                $this->md_section->save();
            }
            $this->md_section->setPhysicalTypicalLearningTime(
                (int) $_POST['tlt']['mo'],
                (int) $_POST['tlt']['d'],
                (int) $_POST['tlt']['h'],
                (int) $_POST['tlt']['m'],
                (int) $_POST['tlt']['s']
            );
            $this->md_section->update();
        } else {
            if (is_object($this->md_section = $this->md_obj->getEducational())) {
                $this->md_section->setPhysicalTypicalLearningTime(0, 0, 0, 0, 0);
                $this->md_section->update();
            }
        }
        $this->callListeners('Educational');
        //Lifecycle...
        // experts
        if ($_POST["life_experts"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }
            
            // determine all entered authors
            $auth_arr = explode(",", $_POST["life_experts"]);
            for ($i = 0; $i < count($auth_arr); $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }
            
            $md_con_author = "";
            
            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "SubjectMatterExpert") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        
                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr)]);
                        } else {  // existing author has not been entered again -> delete
                            $md_ent->delete();
                        }
                    }
                    $md_con_author = $md_con;
                }
            }
            
            // insert enterd, but not existing authors
            if (count($auth_arr) > 0) {
                if (!is_object($md_con_author)) {
                    $md_con_author = $this->md_section->addContribute();
                    $md_con_author->setRole("SubjectMatterExpert");
                    $md_con_author->save();
                }
                foreach ($auth_arr as $auth) {
                    $md_ent = $md_con_author->addEntity();
                    $md_ent->setEntity(ilUtil::stripSlashes($auth));
                    $md_ent->save();
                }
            }
        } else {	// nothing has been entered: delete all author contribs
            if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
                foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                    $md_con = $this->md_section->getContribute($con_id);
                    if ($md_con->getRole() == "SubjectMatterExpert") {
                        $md_con->delete();
                    }
                }
            }
        }
        
        // InstructionalDesigner
        if ($_POST["life_designers"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }
            
            // determine all entered authors
            $auth_arr = explode(",", $_POST["life_designers"]);
            for ($i = 0; $i < count($auth_arr); $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }
            
            $md_con_author = "";
            
            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "InstructionalDesigner") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        
                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr)]);
                        } else {  // existing author has not been entered again -> delete
                            $md_ent->delete();
                        }
                    }
                    $md_con_author = $md_con;
                }
            }
            
            // insert enterd, but not existing authors
            if (count($auth_arr) > 0) {
                if (!is_object($md_con_author)) {
                    $md_con_author = $this->md_section->addContribute();
                    $md_con_author->setRole("InstructionalDesigner");
                    $md_con_author->save();
                }
                foreach ($auth_arr as $auth) {
                    $md_ent = $md_con_author->addEntity();
                    $md_ent->setEntity(ilUtil::stripSlashes($auth));
                    $md_ent->save();
                }
            }
        } else {	// nothing has been entered: delete all author contribs
            if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
                foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                    $md_con = $this->md_section->getContribute($con_id);
                    if ($md_con->getRole() == "InstructionalDesigner") {
                        $md_con->delete();
                    }
                }
            }
        }
        
        // Point of Contact
        if ($_POST["life_poc"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }
            
            // determine all entered authors
            $auth_arr = explode(",", $_POST["life_poc"]);
            for ($i = 0; $i < count($auth_arr); $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }
            
            $md_con_author = "";
            
            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() == "PointOfContact") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        
                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr)]);
                        } else {  // existing author has not been entered again -> delete
                            $md_ent->delete();
                        }
                    }
                    $md_con_author = $md_con;
                }
            }
            
            // insert enterd, but not existing authors
            if (count($auth_arr) > 0) {
                if (!is_object($md_con_author)) {
                    $md_con_author = $this->md_section->addContribute();
                    $md_con_author->setRole("PointOfContact");
                    $md_con_author->save();
                }
                foreach ($auth_arr as $auth) {
                    $md_ent = $md_con_author->addEntity();
                    $md_ent->setEntity(ilUtil::stripSlashes($auth));
                    $md_ent->save();
                }
            }
        } else {	// nothing has been entered: delete all author contribs
            if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
                foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                    $md_con = $this->md_section->getContribute($con_id);
                    if ($md_con->getRole() == "PointOfContact") {
                        $md_con->delete();
                    }
                }
            }
        }
        
        $this->md_section = $this->md_obj->getLifecycle();
        $this->md_section->setVersionLanguage(new ilMDLanguageItem($_POST['lif_language']));
        $this->md_section->setVersion(ilUtil::stripSlashes($_POST['lif_version']));
        $this->md_section->setStatus($_POST['lif_status']);
        $this->md_section->update();

        
        $this->callListeners('Lifecycle');
        
        // Redirect here to read new title and description
        // Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, 'listSection');
    }
    
    /*
     * list general sections
     */
    public function listGeneral()
    {
        if (!is_object($this->md_section = $this->md_obj->getGeneral())) {
            $this->md_section = $this->md_obj->addGeneral();
            $this->md_section->save();
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        
        $this->__setTabs('meta_general');

        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_general.html', 'Services/MetaData');

        $this->ctrl->setReturn($this, 'listGeneral');
        $this->ctrl->setParameter($this, 'section', 'meta_general');
        $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));

        $this->__fillSubelements();
        
        $this->tpl->setVariable("TXT_GENERAL", $this->lng->txt("meta_general"));
        $this->tpl->setVariable("TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
        $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
        $this->tpl->setVariable("TXT_KEYWORD", $this->lng->txt("meta_keyword"));
        $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
        $this->tpl->setVariable("TXT_STRUCTURE", $this->lng->txt("meta_structure"));
        $this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
        $this->tpl->setVariable("TXT_ATOMIC", $this->lng->txt("meta_atomic"));
        $this->tpl->setVariable("TXT_COLLECTION", $this->lng->txt("meta_collection"));
        $this->tpl->setVariable("TXT_NETWORKED", $this->lng->txt("meta_networked"));
        $this->tpl->setVariable("TXT_HIERARCHICAL", $this->lng->txt("meta_hierarchical"));
        $this->tpl->setVariable("TXT_LINEAR", $this->lng->txt("meta_linear"));

        // Structure
        $this->tpl->setVariable("STRUCTURE_VAL_" . strtoupper($this->md_section->getStructure()), " selected=selected");

        // Identifier
        $first = true;
        foreach ($ids = $this->md_section->getIdentifierIds() as $id) {
            $md_ide = $this->md_section->getIdentifier($id);

            //
            if ($first) {
                $this->tpl->setCurrentBlock("id_head");
                $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
                $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
                $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
                $this->tpl->setVariable("ROWSPAN_ID", count($ids));
                $this->tpl->parseCurrentBlock();
                $first = false;
            }
            
            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_identifier');

                if ($md_ide->getCatalog() != "ILIAS") {
                    $this->tpl->setCurrentBlock("identifier_delete");
                    $this->tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                    $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                    $this->tpl->parseCurrentBlock();
                }
            }

            $this->tpl->setCurrentBlock("identifier_loop");
            if ($md_ide->getCatalog() == "ILIAS") {
                $this->tpl->setVariable("DISABLE_IDENT", ' disabled="disabled" ');
            }
            $this->tpl->setVariable("IDENTIFIER_LOOP_NO", $id);
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG",
                ilUtil::prepareFormOutput($md_ide->getCatalog())
            );
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY",
                ilUtil::prepareFormOutput($md_ide->getEntry())
            );
            $this->tpl->parseCurrentBlock();
        }


        // Language
        $first = true;
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lan = $this->md_section->getLanguage($id);
            
            if ($first) {
                $this->tpl->setCurrentBlock("language_head");
                $this->tpl->setVariable("ROWSPAN_LANG", count($ids));
                $this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                $this->tpl->parseCurrentBlock();
                $first = false;
            }

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_language');

                $this->tpl->setCurrentBlock("language_delete");
                $this->tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("language_loop");
            $this->tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                'gen_language[' . $id . '][language]',
                $md_lan->getLanguageCode()
            ));
            $this->tpl->parseCurrentBlock();
        }

        // TITLE
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));
        $this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($this->md_section->getTitle()));
        $this->tpl->setVariable("VAL_TITLE_LANGUAGE", $this->__showLanguageSelect(
            'gen_title_language',
            $this->md_section->getTitleLanguageCode()
        ));


        // DESCRIPTION
        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_description');

                $this->tpl->setCurrentBlock("description_delete");
                $this->tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("description_loop");
            $this->tpl->setVariable("DESCRIPTION_LOOP_NO", $id);
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::prepareFormOutput($md_des->getDescription()));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "gen_description[" . $id . '][language]',
                $md_des->getDescriptionLanguageCode()
            ));
            $this->tpl->parseCurrentBlock();
        }

        // KEYWORD
        $first = true;
        foreach ($ids = $this->md_section->getKeywordIds() as $id) {
            $md_key = $this->md_section->getKeyword($id);
            
            if ($first) {
                $this->tpl->setCurrentBlock("keyword_head");
                $this->tpl->setVariable("ROWSPAN_KEYWORD", count($ids));
                $this->tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("meta_keyword"));
                $this->tpl->parseCurrentBlock();
                $first = false;
            }


            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_keyword');

                $this->tpl->setCurrentBlock("keyword_delete");
                $this->tpl->setVariable("KEYWORD_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->setCurrentBlock("keyword_loop");
            $this->tpl->setVariable("KEYWORD_LOOP_NO", $id);
            $this->tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable("KEYWORD_LOOP_VAL", ilUtil::prepareFormOutput($md_key->getKeyword()));
            $this->tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "gen_keyword[" . $id . '][language]',
                $md_key->getKeywordLanguageCode()
            ));

            $this->tpl->parseCurrentBlock();
        }

        // Coverage
        $this->tpl->setVariable("COVERAGE_LOOP_TXT_COVERAGE", $this->lng->txt('meta_coverage'));
        $this->tpl->setVariable("COVERAGE_LOOP_VAL", ilUtil::prepareFormOutput($this->md_section->getCoverage()));
        $this->tpl->setVariable("COVERAGE_LOOP_TXT_LANGUAGE", $this->lng->txt('meta_language'));
        $this->tpl->setVariable("COVERAGE_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
            'gen_coverage_language',
            $this->md_section->getCoverageLanguageCode()
        ));

        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
    }

    /**
    * update general section
    */
    public function updateGeneral()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';
        
        if (!strlen(trim($_POST['gen_title']))) {
            if ($this->md_obj->getObjType() != 'sess') {
                ilUtil::sendFailure($this->lng->txt('title_required'));
                $this->listGeneral();
                return false;
            }
        }
        
        // General values
        $this->md_section = $this->md_obj->getGeneral();
        $this->md_section->setStructure($_POST['gen_structure']);
        $this->md_section->setTitle(ilUtil::stripSlashes($_POST['gen_title']));
        $this->md_section->setTitleLanguage(new ilMDLanguageItem($_POST['gen_title_language']));
        $this->md_section->setCoverage(ilUtil::stripSlashes($_POST['gen_coverage']));
        $this->md_section->setCoverageLanguage(new ilMDLanguageItem($_POST['gen_coverage_language']));
        $this->md_section->update();

        // Identifier
        if (is_array($_POST['gen_identifier'])) {
            foreach ($_POST['gen_identifier'] as $id => $data) {
                $md_ide = $this->md_section->getIdentifier($id);
                $md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog']));
                $md_ide->setEntry(ilUtil::stripSlashes($data['Entry']));
                $md_ide->update();
            }
        }

        // Language
        if (is_array($_POST['gen_language'])) {
            foreach ($_POST['gen_language'] as $id => $data) {
                $md_lan = $this->md_section->getLanguage($id);
                $md_lan->setLanguage(new ilMDLanguageItem($data['language']));
                $md_lan->update();
            }
        }
        // Description
        if (is_array($_POST['gen_description'])) {
            foreach ($_POST['gen_description'] as $id => $data) {
                $md_des = $this->md_section->getDescription($id);
                $md_des->setDescription(ilUtil::stripSlashes($data['description']));
                $md_des->setDescriptionLanguage(new ilMDLanguageItem($data['language']));
                $md_des->update();
            }
        }
        // Keyword
        if (is_array($_POST['gen_keyword'])) {
            foreach ($_POST['gen_keyword'] as $id => $data) {
                $md_key = $this->md_section->getKeyword($id);

                $md_key->setKeyword(ilUtil::stripSlashes($data['keyword']));
                $md_key->setKeywordLanguage(new ilMDLanguageItem($data['language']));
                $md_key->update();
            }
        }
        $this->callListeners('General');

        // Redirect here to read new title and description
        // Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
        $this->ctrl->setParameter($this, "section", "meta_general");
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, 'listSection');
    }

    public function updateTechnical()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // update technical section
        $this->md_section = $this->md_obj->getTechnical();
        $this->md_section->setSize(ilUtil::stripSlashes($_POST['met_size']));
        $this->md_section->setInstallationRemarks(ilUtil::stripSlashes($_POST['met_inst']));
        $this->md_section->setInstallationRemarksLanguage(new ilMDLanguageItem($_POST['inst_language']));
        $this->md_section->setOtherPlatformRequirements(ilUtil::stripSlashes($_POST['met_opr']));
        $this->md_section->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($_POST['opr_language']));
        $this->md_section->setDuration(ilUtil::stripSlashes($_POST['duration']));
        $this->md_section->update();

        // Format
        if (is_array($_POST['met_format'])) {
            foreach ($_POST['met_format'] as $id => $data) {
                $md_for = $this->md_section->getFormat($id);
                $md_for->setFormat(ilUtil::stripSlashes($data['Format']));
                $md_for->update();
            }
        }
        // Location
        if (is_array($_POST['met_location'])) {
            foreach ($_POST['met_location'] as $id => $data) {
                $md_loc = $this->md_section->getLocation($id);
                $md_loc->setLocation(ilUtil::stripSlashes($data['Location']));
                $md_loc->setLocationType(ilUtil::stripSlashes($data['Type']));
                $md_loc->update();
            }
        }
        if (is_array($_POST['met_re'])) {
            foreach ($_POST['met_re'] as $id => $data) {
                $md_re = $this->md_section->getRequirement($id);
                $md_re->setOperatingSystemName(ilUtil::stripSlashes($data['os']['name']));
                $md_re->setOperatingSystemMinimumVersion(ilUtil::stripSlashes($data['os']['MinimumVersion']));
                $md_re->setOperatingSystemMaximumVersion(ilUtil::stripSlashes($data['os']['MaximumVersion']));
                $md_re->setBrowserName(ilUtil::stripSlashes($data['browser']['name']));
                $md_re->setBrowserMinimumVersion(ilUtil::stripSlashes($data['browser']['MinimumVersion']));
                $md_re->setBrowserMaximumVersion(ilUtil::stripSlashes($data['browser']['MaximumVersion']));
                $md_re->update();
            }
        }
        $this->callListeners('Technical');

        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
        return true;
    }
        


    public function listTechnical()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_technical');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_technical.html', 'Services/MetaData');


        $this->ctrl->setParameter($this, "section", "meta_technical");
        if (!is_object($this->md_section = $this->md_obj->getTechnical())) {
            $this->tpl->setCurrentBlock("no_technical");
            $this->tpl->setVariable("TXT_NO_TECHNICAL", $this->lng->txt("meta_no_technical"));
            $this->tpl->setVariable("TXT_ADD_TECHNICAL", $this->lng->txt("meta_add"));
            $this->tpl->setVariable("ACTION_ADD_TECHNICAL", $this->ctrl->getLinkTarget($this, "addSection"));
            $this->tpl->parseCurrentBlock();

            return true;
        }
        $this->ctrl->setReturn($this, 'listTechnical');
        $this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());

        $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_TECHNICAL", $this->lng->txt('meta_technical'));

        // Delete link
        $this->tpl->setVariable(
            "ACTION_DELETE",
            $this->ctrl->getLinkTarget($this, "deleteSection")
        );
        $this->tpl->setVariable("TXT_DELETE", $this->lng->txt('delete'));

        // New element
        $this->__fillSubelements();

        // Format
        foreach ($ids = $this->md_section->getFormatIds() as $id) {
            $md_for =&$this->md_section->getFormat($id);

            $this->tpl->setCurrentBlock("format_loop");

            $this->ctrl->setParameter($this, 'meta_index', $id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_format');
            $this->tpl->setVariable("FORMAT_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
            $this->tpl->setVariable("FORMAT_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("FORMAT_LOOP_NO", $id);
            $this->tpl->setVariable("FORMAT_LOOP_TXT_FORMAT", $this->lng->txt('meta_format'));
            $this->tpl->setVariable("FORMAT_LOOP_VAL", ilUtil::prepareFormOutput($md_for->getFormat()));

            $this->tpl->parseCurrentBlock();
        }
        // Size
        $this->tpl->setVariable("SIZE_TXT_SIZE", $this->lng->txt('meta_size'));
        $this->tpl->setVariable("SIZE_VAL", ilUtil::prepareFormOutput($this->md_section->getSize()));

        // Location
        foreach ($ids = $this->md_section->getLocationIds() as $id) {
            $md_loc =&$this->md_section->getLocation($id);

            $this->tpl->setCurrentBlock("location_loop");

            $this->ctrl->setParameter($this, 'meta_index', $id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_location');
            $this->tpl->setVariable("LOCATION_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
            $this->tpl->setVariable("LOCATION_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("LOCATION_LOOP_TXT_LOCATION", $this->lng->txt('meta_location'));
            $this->tpl->setVariable("LOCATION_LOOP_NO", $id);
            $this->tpl->setVariable("LOCATION_LOOP_TXT_TYPE", $this->lng->txt('meta_type'));
            $this->tpl->setVariable("LOCATION_LOOP_VAL", ilUtil::prepareFormOutput($md_loc->getLocation()));

            $this->tpl->setVariable(
                "SEL_LOCATION_TYPE",
                ilMDUtilSelect::_getLocationTypeSelect(
                                        $md_loc->getLocationType(),
                                        "met_location[" . $id . "][Type]",
                                        array(0 => $this->lng->txt('meta_please_select'))
                                    )
            );
            $this->tpl->parseCurrentBlock();
        }
        // Requirement
        foreach ($ids = $this->md_section->getRequirementIds() as $id) {
            $md_re =&$this->md_section->getRequirement($id);

            $this->tpl->setCurrentBlock("requirement_loop");

            $this->ctrl->setParameter($this, 'meta_index', $id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_requirement');
            $this->tpl->setVariable("REQUIREMENT_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_REQUIREMENT", $this->lng->txt('meta_requirement'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_TYPE", $this->lng->txt('meta_type'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_OPERATINGSYSTEM", $this->lng->txt('meta_operating_system'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_BROWSER", $this->lng->txt('meta_browser'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME", $this->lng->txt('meta_name'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_MINIMUMVERSION", $this->lng->txt('meta_minimum_version'));
            $this->tpl->setVariable("REQUIREMENT_LOOP_TXT_MAXIMUMVERSION", $this->lng->txt('meta_maximum_version'));

            $this->tpl->setVariable("REQUIREMENT_LOOP_NO", $id);
            $this->tpl->setVariable(
                "REQUIREMENT_SEL_OS_NAME",
                ilMDUtilSelect::_getOperatingSystemSelect(
                                        $md_re->getOperatingSystemName(),
                                        "met_re[" . $id . "][os][name]",
                                        array(0 => $this->lng->txt('meta_please_select'))
                                    )
            );
            $this->tpl->setVariable(
                "REQUIREMENT_SEL_BROWSER_NAME",
                ilMDUtilSelect::_getBrowserSelect(
                                        $md_re->getBrowserName(),
                                        "met_re[" . $id . "][browser][name]",
                                        array(0 => $this->lng->txt('meta_please_select'))
                                    )
            );

            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MINIMUMVERSION",
                ilUtil::prepareFormOutput($md_re->getOperatingSystemMinimumVersion())
            );
            
            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MAXIMUMVERSION",
                ilUtil::prepareFormOutput($md_re->getOperatingSystemMaximumVersion())
            );

            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_BROWSER_MINIMUMVERSION",
                ilUtil::prepareFormOutput($md_re->getBrowserMinimumVersion())
            );
            
            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_BROWSER_MAXIMUMVERSION",
                ilUtil::prepareFormOutput($md_re->getBrowserMaximumVersion())
            );
            $this->tpl->parseCurrentBlock();
        }
        // OrComposite
        foreach ($ids = $this->md_section->getOrCompositeIds() as $or_id) {
            $md_or =&$this->md_section->getOrComposite($or_id);
            foreach ($ids = $md_or->getRequirementIds() as $id) {
                $md_re =&$this->md_section->getRequirement($id);

                $this->tpl->setCurrentBlock("orrequirement_loop");

                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_requirement');
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_DELETE", $this->lng->txt('delete'));

                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_REQUIREMENT", $this->lng->txt('meta_requirement'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_TYPE", $this->lng->txt('meta_type'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_OPERATINGSYSTEM", $this->lng->txt('meta_operating_system'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_BROWSER", $this->lng->txt('meta_browser'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_NAME", $this->lng->txt('meta_name'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_MINIMUMVERSION", $this->lng->txt('meta_minimum_version'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_MAXIMUMVERSION", $this->lng->txt('meta_maximum_version'));

                $this->tpl->setVariable("ORREQUIREMENT_LOOP_NO", $id);
                $this->tpl->setVariable(
                    "ORREQUIREMENT_SEL_OS_NAME",
                    ilMDUtilSelect::_getOperatingSystemSelect(
                                            $md_re->getOperatingSystemName(),
                                            "met_re[" . $id . "][os][name]",
                                            array(0 => $this->lng->txt('meta_please_select'))
                                        )
                );
                $this->tpl->setVariable(
                    "ORREQUIREMENT_SEL_BROWSER_NAME",
                    ilMDUtilSelect::_getBrowserSelect(
                                            $md_re->getBrowserName(),
                                            "met_re[" . $id . "][browser][name]",
                                            array(0 => $this->lng->txt('meta_please_select'))
                                        )
                );

                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MINIMUMVERSION",
                    ilUtil::prepareFormOutput($md_re->getOperatingSystemMinimumVersion())
                );
            
                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MAXIMUMVERSION",
                    ilUtil::prepareFormOutput($md_re->getOperatingSystemMaximumVersion())
                );

                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_BROWSER_MINIMUMVERSION",
                    ilUtil::prepareFormOutput($md_re->getBrowserMinimumVersion())
                );
            
                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_BROWSER_MAXIMUMVERSION",
                    ilUtil::prepareFormOutput($md_re->getBrowserMaximumVersion())
                );
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("orcomposite_loop");

            $this->ctrl->setParameter($this, 'meta_index', $or_id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_or_composite');
            $this->ctrl->setParameter($this, 'meta_technical', $this->md_section->getMetaId());
            $this->tpl->setVariable("ORCOMPOSITE_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
            $this->tpl->setVariable("ORCOMPOSITE_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("ORCOMPOSITE_LOOP_TXT_ORCOMPOSITE", $this->lng->txt('meta_or_composite'));
            $this->tpl->parseCurrentBlock();
        }

        // InstallationRemarks
        $this->tpl->setVariable("INSTALLATIONREMARKS_TXT_INSTALLATIONREMARKS", $this->lng->txt('meta_installation_remarks'));
        $this->tpl->setVariable("INSTALLATIONREMARKS_TXT_LANGUAGE", $this->lng->txt('meta_language'));

        $this->tpl->setVariable("INSTALLATIONREMARKS_VAL", ilUtil::prepareFormOutput($this->md_section->getInstallationRemarks()));
        $this->tpl->setVariable(
            "INSTALLATIONREMARKS_VAL_LANGUAGE",
            $this->__showLanguageSelect(
                                    'inst_language',
                                    $this->md_section->getInstallationRemarksLanguageCode()
                                )
        );

        // Other platform requirement
        $this->tpl->setVariable(
            "OTHERPLATTFORMREQUIREMENTS_TXT_OTHERPLATTFORMREQUIREMENTS",
            $this->lng->txt('meta_other_plattform_requirements')
        );
        $this->tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_TXT_LANGUAGE", $this->lng->txt('meta_language'));

        $this->tpl->setVariable(
            "OTHERPLATTFORMREQUIREMENTS_VAL",
            ilUtil::prepareFormOutput($this->md_section->getOtherPlatformRequirements())
        );
        $this->tpl->setVariable(
            "OTHERPLATTFORMREQUIREMENTS_VAL_LANGUAGE",
            $this->__showLanguageSelect(
                                    'opr_language',
                                    $this->md_section->getOtherPlatformRequirementsLanguageCode()
                                )
        );

        // Duration
        $this->tpl->setVariable("DURATION_TXT_DURATION", $this->lng->txt('meta_duration'));
        $this->tpl->setVariable("DURATION_VAL", ilUtil::prepareFormOutput($this->md_section->getDuration()));

        $this->tpl->setCurrentBlock("technical");
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
        $this->tpl->parseCurrentBlock();
    }
    


    public function listLifecycle()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_lifecycle');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_lifecycle.html', 'Services/MetaData');


        $this->ctrl->setParameter($this, "section", "meta_lifecycle");
        if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $this->tpl->setCurrentBlock("no_lifecycle");
            $this->tpl->setVariable("TXT_NO_LIFECYCLE", $this->lng->txt("meta_no_lifecycle"));
            $this->tpl->setVariable("TXT_ADD_LIFECYCLE", $this->lng->txt("meta_add"));
            $this->tpl->setVariable("ACTION_ADD_LIFECYCLE", $this->ctrl->getLinkTarget($this, "addSection"));
            $this->tpl->parseCurrentBlock();

            return true;
        }
        $this->ctrl->setReturn($this, 'listLifecycle');
        $this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());

        $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_LIFECYCLE", $this->lng->txt('meta_lifecycle'));

        // Delete link
        $this->tpl->setVariable(
            "ACTION_DELETE",
            $this->ctrl->getLinkTarget($this, "deleteSection")
        );
        $this->tpl->setVariable("TXT_DELETE", $this->lng->txt('delete'));

        // New element
        $this->__fillSubelements();

        // Status
        $this->tpl->setVariable("TXT_STATUS", $this->lng->txt('meta_status'));
        $this->tpl->setVariable("SEL_STATUS", ilMDUtilSelect::_getStatusSelect(
            $this->md_section->getStatus(),
            "lif_status",
            array(0 => $this->lng->txt('meta_please_select'))
        ));
        // Version
        $this->tpl->setVariable("TXT_VERSION", $this->lng->txt('meta_version'));
        $this->tpl->setVariable("VAL_VERSION", ilUtil::prepareFormOutput($this->md_section->getVersion()));

        $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt('meta_language'));
        $this->tpl->setVariable("VAL_VERSION_LANGUAGE", $this->__showLanguageSelect(
            'lif_language',
            $this->md_section->getVersionLanguageCode()
        ));

        // Contributes
        foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
            $md_con = $this->md_section->getContribute($con_id);

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $con_id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_contribute');
                
                $this->tpl->setCurrentBlock("contribute_delete");
                $this->tpl->setVariable("CONTRIBUTE_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                $this->tpl->parseCurrentBlock();
            }
            // Entities
            foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                $md_ent = $md_con->getEntity($ent_id);
                
                $this->ctrl->setParameter($this, 'meta_path', 'meta_entity');
                
                if (count($ent_ids) > 1) {
                    $this->tpl->setCurrentBlock("contribute_entity_delete");
                    
                    $this->ctrl->setParameter($this, 'meta_index', $ent_id);
                    $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                    $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("contribute_entity_loop");

                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_CONTRIBUTE_NO", $con_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_NO", $ent_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_VAL_ENTITY", ilUtil::prepareFormOutput($md_ent->getEntity()));
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ENTITY", $this->lng->txt('meta_entity'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("contribute_loop");
            
            $this->ctrl->setParameter($this, 'section_element', 'meta_entity');
            $this->ctrl->setParameter($this, 'meta_index', $con_id);
            $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_ADD", $this->ctrl->getLinkTarget($this, 'addSectionElement'));
            $this->tpl->setVariable(
                "CONTRIBUTE_ENTITY_LOOP_TXT_ADD",
                $this->lng->txt('add') . " " . $this->lng->txt('meta_entity')
            );

            $this->tpl->setVariable("CONTRIBUTE_LOOP_ROWSPAN", 2 + count($ent_ids));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_CONTRIBUTE", $this->lng->txt('meta_contribute'));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE", $this->lng->txt('meta_role'));
            $this->tpl->setVariable("SEL_CONTRIBUTE_ROLE", ilMDUtilSelect::_getRoleSelect(
                $md_con->getRole(),
                "met_contribute[" . $con_id . "][Role]",
                array(0 => $this->lng->txt('meta_please_select'))
            ));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_DATE", $this->lng->txt('meta_date'));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_NO", $con_id);
            $this->tpl->setVariable("CONTRIBUTE_LOOP_VAL_DATE", ilUtil::prepareFormOutput($md_con->getDate()));
            
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
    }

    public function updateLifecycle()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // update metametadata section
        $this->md_section = $this->md_obj->getLifecycle();
        $this->md_section->setVersionLanguage(new ilMDLanguageItem($_POST['lif_language']));
        $this->md_section->setVersion(ilUtil::stripSlashes($_POST['lif_version']));
        $this->md_section->setStatus($_POST['lif_status']);
        $this->md_section->update();

        // Identifier
        if (is_array($_POST['met_identifier'])) {
            foreach ($_POST['met_identifier'] as $id => $data) {
                $md_ide = $this->md_section->getIdentifier($id);
                $md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog']));
                $md_ide->setEntry(ilUtil::stripSlashes($data['Entry']));
                $md_ide->update();
            }
        }
        // Contribute
        if (is_array($_POST['met_contribute'])) {
            foreach ($_POST['met_contribute'] as $id => $data) {
                $md_con =&$this->md_section->getContribute($id);
                $md_con->setRole(ilUtil::stripSlashes($data['Role']));
                $md_con->setDate(ilUtil::stripSlashes($data['Date']));
                $md_con->update();

                if (is_array($_POST['met_entity'][$id])) {
                    foreach ($_POST['met_entity'][$id] as $ent_id => $data) {
                        $md_ent =&$md_con->getEntity($ent_id);
                        $md_ent->setEntity(ilUtil::stripSlashes($data['Entity']));
                        $md_ent->update();
                    }
                }
            }
        }
        $this->callListeners('Lifecycle');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
        return true;
    }





    public function listMetaMetaData()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_meta_metadata');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_meta_metadata.html', 'Services/MetaData');


        $this->ctrl->setParameter($this, "section", "meta_meta_metadata");
        if (!is_object($this->md_section = $this->md_obj->getMetaMetadata())) {
            $this->tpl->setCurrentBlock("no_meta_meta");
            $this->tpl->setVariable("TXT_NO_META_META", $this->lng->txt("meta_no_meta_metadata"));
            $this->tpl->setVariable("TXT_ADD_META_META", $this->lng->txt("meta_add"));
            $this->tpl->setVariable("ACTION_ADD_META_META", $this->ctrl->getLinkTarget($this, "addSection"));
            $this->tpl->parseCurrentBlock();

            return true;
        }
        $this->ctrl->setReturn($this, 'listMetaMetaData');
        $this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());

        $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_META_METADATA", $this->lng->txt('meta_meta_metadata'));

        // Delete link
        $this->tpl->setVariable(
            "ACTION_DELETE",
            $this->ctrl->getLinkTarget($this, "deleteSection")
        );
        $this->tpl->setVariable("TXT_DELETE", $this->lng->txt('delete'));

        // New element
        $this->__fillSubelements();

        $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt('meta_language'));

        $this->tpl->setVariable("VAL_LANGUAGE", $this->__showLanguageSelect('met_language', $this->md_section->getLanguageCode()));
        $this->tpl->setVariable("TXT_METADATASCHEME", $this->lng->txt('meta_metadatascheme'));
        $this->tpl->setVariable("VAL_METADATASCHEME", $this->md_section->getMetaDataScheme());


        // Identifier
        foreach ($ids = $this->md_section->getIdentifierIds() as $id) {
            $md_ide = $this->md_section->getIdentifier($id);

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_identifier');
                
                $this->tpl->setCurrentBlock("identifier_delete");
                $this->tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("identifier_loop");
            $this->tpl->setVariable("IDENTIFIER_LOOP_NO", $id);
            $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
            $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG",
                ilUtil::prepareFormOutput($md_ide->getCatalog())
            );
            $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY",
                ilUtil::prepareFormOutput($md_ide->getEntry())
            );
            $this->tpl->parseCurrentBlock();
        }

        // Contributes
        foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
            $md_con = $this->md_section->getContribute($con_id);

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $con_id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_contribute');
                
                $this->tpl->setCurrentBlock("contribute_delete");
                $this->tpl->setVariable("CONTRIBUTE_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                $this->tpl->parseCurrentBlock();
            }
            // Entities
            foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                $md_ent = $md_con->getEntity($ent_id);
                
                $this->ctrl->setParameter($this, 'meta_path', 'meta_entity');
                
                if (count($ent_ids) > 1) {
                    $this->tpl->setCurrentBlock("contribute_entity_delete");
                    
                    $this->ctrl->setParameter($this, 'meta_index', $ent_id);
                    $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
                    $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("contribute_entity_loop");

                $this->ctrl->setParameter($this, 'section_element', 'meta_entity');
                $this->ctrl->setParameter($this, 'meta_index', $con_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_ADD", $this->ctrl->getLinkTarget($this, 'addSectionElement'));
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ADD", $this->lng->txt('add'));


                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_CONTRIBUTE_NO", $con_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_NO", $ent_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_VAL_ENTITY", ilUtil::prepareFormOutput($md_ent->getEntity()));
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ENTITY", $this->lng->txt('meta_entity'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("contribute_loop");
            $this->tpl->setVariable("CONTRIBUTE_LOOP_ROWSPAN", 2 + count($ent_ids));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_CONTRIBUTE", $this->lng->txt('meta_contribute'));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE", $this->lng->txt('meta_role'));
            $this->tpl->setVariable("SEL_CONTRIBUTE_ROLE", ilMDUtilSelect::_getRoleSelect(
                $md_con->getRole(),
                "met_contribute[" . $con_id . "][Role]",
                array(0 => $this->lng->txt('meta_please_select'))
            ));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_TXT_DATE", $this->lng->txt('meta_date'));
            $this->tpl->setVariable("CONTRIBUTE_LOOP_NO", $con_id);
            $this->tpl->setVariable("CONTRIBUTE_LOOP_VAL_DATE", ilUtil::prepareFormOutput($md_con->getDate()));
            
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
    }


    public function updateMetaMetaData()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // update metametadata section
        $this->md_section = $this->md_obj->getMetaMetadata();
        $this->md_section->setLanguage(new ilMDLanguageItem($_POST['met_language']));
        $this->md_section->update();

        // Identifier
        if (is_array($_POST['met_identifier'])) {
            foreach ($_POST['met_identifier'] as $id => $data) {
                $md_ide = $this->md_section->getIdentifier($id);
                $md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog']));
                $md_ide->setEntry(ilUtil::stripSlashes($data['Entry']));
                $md_ide->update();
            }
        }
        // Contribute
        if (is_array($_POST['met_contribute'])) {
            foreach ($_POST['met_contribute'] as $id => $data) {
                $md_con =&$this->md_section->getContribute($id);
                $md_con->setRole(ilUtil::stripSlashes($data['Role']));
                $md_con->setDate(ilUtil::stripSlashes($data['Date']));
                $md_con->update();

                if (is_array($_POST['met_entity'][$id])) {
                    foreach ($_POST['met_entity'][$id] as $ent_id => $data) {
                        $md_ent =&$md_con->getEntity($ent_id);
                        $md_ent->setEntity(ilUtil::stripSlashes($data['Entity']));
                        $md_ent->update();
                    }
                }
            }
        }
        $this->callListeners('MetaMetaData');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
        return true;
    }


    /*
     * list rights section
     */
    public function listRights()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_rights');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_rights.html', 'Services/MetaData');

        if (!is_object($this->md_section = $this->md_obj->getRights())) {
            $this->tpl->setCurrentBlock("no_rights");
            $this->tpl->setVariable("TXT_NO_RIGHTS", $this->lng->txt("meta_no_rights"));
            $this->tpl->setVariable("TXT_ADD_RIGHTS", $this->lng->txt("meta_add"));
            $this->ctrl->setParameter($this, "section", "meta_rights");
            $this->tpl->setVariable(
                "ACTION_ADD_RIGHTS",
                $this->ctrl->getLinkTarget($this, "addSection")
            );
            $this->tpl->parseCurrentBlock();
        } else {
            $this->ctrl->setReturn($this, 'listRights');
            $this->ctrl->setParameter($this, 'section', 'meta_rights');
            $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));

            $this->tpl->setVariable("TXT_RIGHTS", $this->lng->txt("meta_rights"));
            $this->tpl->setVariable("TXT_COST", $this->lng->txt("meta_cost"));
            $this->tpl->setVariable("TXT_COPYRIGHTANDOTHERRESTRICTIONS", $this->lng->txt("meta_copyright_and_other_restrictions"));
            $this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
            $this->tpl->setVariable("TXT_YES", $this->lng->txt("meta_yes"));
            $this->tpl->setVariable("TXT_NO", $this->lng->txt("meta_no"));

            $this->ctrl->setParameter($this, "section", "meta_rights");
            $this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());
            $this->tpl->setVariable(
                "ACTION_DELETE",
                $this->ctrl->getLinkTarget($this, "deleteSection")
            );

            $this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));

            $this->tpl->setVariable("VAL_COST_" . strtoupper($this->md_section->getCosts()), " selected");
            $this->tpl->setVariable("VAL_COPYRIGHTANDOTHERRESTRICTIONS_" .
                strtoupper($this->md_section->getCopyrightAndOtherRestrictions()), " selected");

            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::prepareFormOutput($this->md_section->getDescription()));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->setVariable(
                "DESCRIPTION_LOOP_VAL_LANGUAGE",
                $this->__showLanguageSelect(
                'rights[DescriptionLanguage]',
                $this->md_section->getDescriptionLanguageCode()
            )
            );

            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
    
            $this->tpl->setCurrentBlock("rights");
            $this->tpl->parseCurrentBlock();
        }
    }

    public function updateRights()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // update rights section
        $this->md_section = $this->md_obj->getRights();
        $this->md_section->setCosts($_POST['rights']['Cost']);
        $this->md_section->setCopyrightAndOtherRestrictions($_POST['rights']['CopyrightAndOtherRestrictions']);
        $this->md_section->setDescriptionLanguage(new ilMDLanguageItem($_POST['rights']['DescriptionLanguage']));
        $this->md_section->setDescription(ilUtil::stripSlashes($_POST['rights']['Description']));
        $this->md_section->update();
        
        $this->callListeners('Rights');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    /*
     * list educational section
     */
    public function listEducational()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_educational');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_educational.html', 'Services/MetaData');

        if (!is_object($this->md_section = $this->md_obj->getEducational())) {
            $this->tpl->setCurrentBlock("no_educational");
            $this->tpl->setVariable("TXT_NO_EDUCATIONAL", $this->lng->txt("meta_no_educational"));
            $this->tpl->setVariable("TXT_ADD_EDUCATIONAL", $this->lng->txt("meta_add"));
            $this->ctrl->setParameter($this, "section", "meta_educational");
            $this->tpl->setVariable(
                "ACTION_ADD_EDUCATIONAL",
                $this->ctrl->getLinkTarget($this, "addSection")
            );
            $this->tpl->parseCurrentBlock();
        } else {
            $this->ctrl->setReturn($this, 'listEducational');
            $this->ctrl->setParameter($this, 'section', 'meta_educational');
            $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));

            $this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());
            $this->tpl->setVariable(
                "ACTION_DELETE",
                $this->ctrl->getLinkTarget($this, "deleteSection")
            );

            $this->tpl->setVariable("TXT_EDUCATIONAL", $this->lng->txt("meta_educational"));
            $this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
            $this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
            $this->tpl->setVariable("TXT_TYPICALAGERANGE", $this->lng->txt("meta_typical_age_range"));
            $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
            $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
            $this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));

            $this->tpl->setVariable("TXT_INTERACTIVITYTYPE", $this->lng->txt("meta_interactivity_type"));
            $this->tpl->setVariable("TXT_LEARNINGRESOURCETYPE", $this->lng->txt("meta_learning_resource_type"));
            $this->tpl->setVariable("TXT_INTERACTIVITYLEVEL", $this->lng->txt("meta_interactivity_level"));
            $this->tpl->setVariable("TXT_SEMANTICDENSITY", $this->lng->txt("meta_semantic_density"));
            $this->tpl->setVariable("TXT_INTENDEDENDUSERROLE", $this->lng->txt("meta_intended_end_user_role"));
            $this->tpl->setVariable("TXT_CONTEXT", $this->lng->txt("meta_context"));
            $this->tpl->setVariable("TXT_DIFFICULTY", $this->lng->txt("meta_difficulty"));
            
            $this->tpl->setVariable("VAL_INTERACTIVITYTYPE_" . strtoupper($this->md_section->getInteractivityType()), " selected");
            $this->tpl->setVariable("VAL_LEARNINGRESOURCETYPE_" . strtoupper($this->md_section->getLearningResourceType()), " selected");
            $this->tpl->setVariable("VAL_INTERACTIVITYLEVEL_" . strtoupper($this->md_section->getInteractivityLevel()), " selected");
            $this->tpl->setVariable("VAL_SEMANTICDENSITY_" . strtoupper($this->md_section->getSemanticDensity()), " selected");
            $this->tpl->setVariable("VAL_INTENDEDENDUSERROLE_" . strtoupper($this->md_section->getIntendedEndUserRole()), " selected");
            $this->tpl->setVariable("VAL_CONTEXT_" . strtoupper($this->md_section->getContext()), " selected");
            $this->tpl->setVariable("VAL_DIFFICULTY_" . strtoupper($this->md_section->getDifficulty()), " selected");
            #$this->tpl->setVariable("VAL_TYPICALLEARNINGTIME", ilUtil::prepareFormOutput($this->md_section->getTypicalLearningTime()));
            
            $this->tpl->setVariable("TXT_ACTIVE", $this->lng->txt("meta_active"));
            $this->tpl->setVariable("TXT_EXPOSITIVE", $this->lng->txt("meta_expositive"));
            $this->tpl->setVariable("TXT_MIXED", $this->lng->txt("meta_mixed"));
            $this->tpl->setVariable("TXT_EXERCISE", $this->lng->txt("meta_exercise"));
            $this->tpl->setVariable("TXT_SIMULATION", $this->lng->txt("meta_simulation"));
            $this->tpl->setVariable("TXT_QUESTIONNAIRE", $this->lng->txt("meta_questionnaire"));
            $this->tpl->setVariable("TXT_DIAGRAMM", $this->lng->txt("meta_diagramm"));
            $this->tpl->setVariable("TXT_FIGURE", $this->lng->txt("meta_figure"));
            $this->tpl->setVariable("TXT_GRAPH", $this->lng->txt("meta_graph"));
            $this->tpl->setVariable("TXT_INDEX", $this->lng->txt("meta_index"));
            $this->tpl->setVariable("TXT_SLIDE", $this->lng->txt("meta_slide"));
            $this->tpl->setVariable("TXT_TABLE", $this->lng->txt("meta_table"));
            $this->tpl->setVariable("TXT_NARRATIVETEXT", $this->lng->txt("meta_narrative_text"));
            $this->tpl->setVariable("TXT_EXAM", $this->lng->txt("meta_exam"));
            $this->tpl->setVariable("TXT_EXPERIMENT", $this->lng->txt("meta_experiment"));
            $this->tpl->setVariable("TXT_PROBLEMSTATEMENT", $this->lng->txt("meta_problem_statement"));
            $this->tpl->setVariable("TXT_SELFASSESSMENT", $this->lng->txt("meta_self_assessment"));
            $this->tpl->setVariable("TXT_LECTURE", $this->lng->txt("meta_lecture"));
            $this->tpl->setVariable("TXT_VERYLOW", $this->lng->txt("meta_very_low"));
            $this->tpl->setVariable("TXT_LOW", $this->lng->txt("meta_low"));
            $this->tpl->setVariable("TXT_MEDIUM", $this->lng->txt("meta_medium"));
            $this->tpl->setVariable("TXT_HIGH", $this->lng->txt("meta_high"));
            $this->tpl->setVariable("TXT_VERYHIGH", $this->lng->txt("meta_very_high"));
            $this->tpl->setVariable("TXT_TEACHER", $this->lng->txt("meta_teacher"));
            $this->tpl->setVariable("TXT_AUTHOR", $this->lng->txt("meta_author"));
            $this->tpl->setVariable("TXT_LEARNER", $this->lng->txt("meta_learner"));
            $this->tpl->setVariable("TXT_MANAGER", $this->lng->txt("meta_manager"));
            $this->tpl->setVariable("TXT_SCHOOL", $this->lng->txt("meta_school"));
            $this->tpl->setVariable("TXT_HIGHEREDUCATION", $this->lng->txt("meta_higher_education"));
            $this->tpl->setVariable("TXT_TRAINING", $this->lng->txt("meta_training"));
            $this->tpl->setVariable("TXT_OTHER", $this->lng->txt("meta_other"));
            $this->tpl->setVariable("TXT_VERYEASY", $this->lng->txt("meta_very_easy"));
            $this->tpl->setVariable("TXT_EASY", $this->lng->txt("meta_easy"));
            $this->tpl->setVariable("TXT_DIFFICULT", $this->lng->txt("meta_difficult"));
            $this->tpl->setVariable("TXT_VERYDIFFICULT", $this->lng->txt("meta_very_difficult"));
            $this->tpl->setVariable("TXT_TYPICALLEARNINGTIME", $this->lng->txt("meta_typical_learning_time"));


            // Typical learning time
            $tlt = array(0,0,0,0,0);
            $valid = true;

            include_once 'Services/MetaData/classes/class.ilMDUtils.php';
            
            if (!$tlt = ilMDUtils::_LOMDurationToArray($this->md_section->getTypicalLearningTime())) {
                if (strlen($this->md_section->getTypicalLearningTime())) {
                    $tlt = array(0,0,0,0,0);
                    $valid = false;
                }
            }

            $this->tpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
            $this->tpl->setVariable("SEL_MONTHS", $this->__buildMonthsSelect($tlt[0]));
            $this->tpl->setVariable("SEL_DAYS", $this->__buildDaysSelect($tlt[1]));
        
            $this->tpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
            $this->tpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));

            $this->tpl->setVariable("TXT_TYPICAL_LEARN_TIME", $this->lng->txt('meta_typical_learning_time'));
            $this->tpl->setVariable("SEL_TLT", ilUtil::makeTimeSelect(
                'tlt',
                $tlt[4] ? false : true,
                $tlt[2],
                $tlt[3],
                $tlt[4],
                false
            ));
            $this->tpl->setVariable("TLT_HINT", $tlt[4] ? '(hh:mm:ss)' : '(hh:mm)');

            if (!$valid) {
                $this->tpl->setCurrentBlock("tlt_not_valid");
                $this->tpl->setVariable("TXT_CURRENT_VAL", $this->lng->txt('meta_current_value'));
                $this->tpl->setVariable("TLT", $this->md_section->getTypicalLearningTime());
                $this->tpl->setVariable("INFO_TLT_NOT_VALID", $this->lng->txt('meta_info_tlt_not_valid'));
                $this->tpl->parseCurrentBlock();
            }


            /* TypicalAgeRange */
            $first = true;
            foreach ($ids = $this->md_section->getTypicalAgeRangeIds() as $id) {
                $md_age = $this->md_section->getTypicalAgeRange($id);
                
                // extra test due to bug 5316 (may be due to eLaix import)
                if (is_object($md_age)) {
                    if ($first) {
                        $this->tpl->setCurrentBlock("agerange_head");
                        $this->tpl->setVariable(
                            "TYPICALAGERANGE_LOOP_TXT_TYPICALAGERANGE",
                            $this->lng->txt("meta_typical_age_range")
                        );
                        $this->tpl->setVariable("ROWSPAN_AGERANGE", count($ids));
                        $this->tpl->parseCurrentBlock();
                        $first = false;
                    }
                        
                    
                    $this->ctrl->setParameter($this, 'meta_index', $id);
                    $this->ctrl->setParameter($this, 'meta_path', 'educational_typical_age_range');
        
                    $this->tpl->setCurrentBlock("typicalagerange_delete");
                    $this->tpl->setVariable(
                        "TYPICALAGERANGE_LOOP_ACTION_DELETE",
                        $this->ctrl->getLinkTarget($this, "deleteElement")
                    );
                    $this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                    $this->tpl->parseCurrentBlock();
    
                    $this->tpl->setCurrentBlock("typicalagerange_loop");
                    $this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
                    $this->tpl->setVariable("TYPICALAGERANGE_LOOP_VAL", ilUtil::prepareFormOutput($md_age->getTypicalAgeRange()));
                    $this->tpl->setVariable("TYPICALAGERANGE_LOOP_NO", $id);
                    $this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                    $this->tpl->setVariable(
                        "TYPICALAGERANGE_LOOP_VAL_LANGUAGE",
                        $this->__showLanguageSelect(
                            'educational[TypicalAgeRange][' . $id . '][Language]',
                            $md_age->getTypicalAgeRangeLanguageCode()
                        )
                    );
                    $this->ctrl->setParameter($this, "section_element", "educational_typical_age_range");
                    $this->tpl->setVariable(
                        "TYPICALAGERANGE_LOOP_ACTION_ADD",
                        $this->ctrl->getLinkTarget($this, "addSectionElement")
                    );
                    $this->tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                    $this->tpl->parseCurrentBlock();
                }
            }

            /* Description */
            $first = true;
            foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
                if ($first) {
                    $this->tpl->setCurrentBlock("desc_head");
                    $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
                    $this->tpl->setVariable("ROWSPAN_DESC", count($ids));
                    $this->tpl->parseCurrentBlock();
                    $first = false;
                }

                $md_des = $this->md_section->getDescription($id);
                
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'educational_description');
                
                $this->tpl->setCurrentBlock("description_loop");
                $this->tpl->setVariable("DESCRIPTION_LOOP_NO", $id);
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
                $this->tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::prepareFormOutput($md_des->getDescription()));
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                $this->tpl->setVariable(
                    "DESCRIPTION_LOOP_VAL_LANGUAGE",
                    $this->__showLanguageSelect(
                        'educational[Description][' . $id . '][Language]',
                        $md_des->getDescriptionLanguageCode()
                    )
                );
                $this->tpl->setVariable(
                    "DESCRIPTION_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, "deleteElement")
                );
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->ctrl->setParameter($this, "section_element", "educational_description");
                $this->tpl->setVariable(
                    "DESCRIPTION_LOOP_ACTION_ADD",
                    $this->ctrl->getLinkTarget($this, "addSectionElement")
                );
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                $this->tpl->parseCurrentBlock();
            }


            /* Language */
            $first = true;
            foreach ($ids = $this->md_section->getLanguageIds() as $id) {
                if ($first) {
                    $this->tpl->setCurrentBlock("language_head");
                    $this->tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                    $this->tpl->setVariable("ROWSPAN_LANG", count($ids));
                    $this->tpl->parseCurrentBlock();
                    $first = false;
                }
                
                $md_lang = $this->md_section->getLanguage($id);
                
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'educational_language');

                $this->tpl->setCurrentBlock("language_loop");
                $this->tpl->setVariable(
                    "LANGUAGE_LOOP_VAL_LANGUAGE",
                    $this->__showLanguageSelect(
                        'educational[Language][' . $id . ']',
                        $md_lang->getLanguageCode()
                    )
                );

                $this->tpl->setVariable(
                    "LANGUAGE_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, "deleteElement")
                );
                $this->tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->ctrl->setParameter($this, "section_element", "educational_language");
                $this->tpl->setVariable(
                    "LANGUAGE_LOOP_ACTION_ADD",
                    $this->ctrl->getLinkTarget($this, "addSectionElement")
                );
                $this->tpl->setVariable("LANGUAGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

            $this->tpl->setCurrentBlock("educational");
            $this->tpl->parseCurrentBlock();
        }
    }

    public function updateEducational()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // update rights section
        $this->md_section = $this->md_obj->getEducational();
        $this->md_section->setInteractivityType($_POST['educational']['InteractivityType']);
        $this->md_section->setLearningResourceType($_POST['educational']['LearningResourceType']);
        $this->md_section->setInteractivityLevel($_POST['educational']['InteractivityLevel']);
        $this->md_section->setSemanticDensity($_POST['educational']['SemanticDensity']);
        $this->md_section->setIntendedEndUserRole($_POST['educational']['IntendedEndUserRole']);
        $this->md_section->setContext($_POST['educational']['Context']);
        $this->md_section->setDifficulty($_POST['educational']['Difficulty']);


        // TLT
        
        if ($_POST['tlt']['mo'] or $_POST['tlt']['d'] or
           $_POST['tlt']['h'] or $_POST['tlt']['m'] or $_POST['tlt']['s']) {
            $this->md_section->setPhysicalTypicalLearningTime(
                (int) $_POST['tlt']['mo'],
                (int) $_POST['tlt']['d'],
                (int) $_POST['tlt']['h'],
                (int) $_POST['tlt']['m'],
                (int) $_POST['tlt']['s']
            );
        } else {
            $this->md_section->setTypicalLearningTime('');
        }
        $this->callListeners('Educational');


        /* TypicalAgeRange */
        foreach ($ids = $this->md_section->getTypicalAgeRangeIds() as $id) {
            $md_age = $this->md_section->getTypicalAgeRange($id);
            $md_age->setTypicalAgeRange(ilUtil::stripSlashes($_POST['educational']['TypicalAgeRange'][$id]['Value']));
            $md_age->setTypicalAgeRangeLanguage(
                new ilMDLanguageItem($_POST['educational']['TypicalAgeRange'][$id]['Language'])
            );
            $md_age->update();
        }

        /* Description */
        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);
            $md_des->setDescription(ilUtil::stripSlashes($_POST['educational']['Description'][$id]['Value']));
            $md_des->setDescriptionLanguage(
                new ilMDLanguageItem($_POST['educational']['Description'][$id]['Language'])
            );
            $md_des->update();
        }

        /* Language */
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lang = $this->md_section->getLanguage($id);
            $md_lang->setLanguage(
                new ilMDLanguageItem($_POST['educational']['Language'][$id])
            );
            $md_lang->update();
        }
        
        $this->md_section->update();
        
        $this->callListeners('Educational');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    /*
     * list relation section
     */
    public function listRelation()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_relation');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_relation.html', 'Services/MetaData');

        $rel_ids = $this->md_obj->getRelationIds();
        if (!is_array($rel_ids) || count($rel_ids) == 0) {
            $this->tpl->setCurrentBlock("no_relation");
            $this->tpl->setVariable("TXT_NO_RELATION", $this->lng->txt("meta_no_relation"));
            $this->tpl->setVariable("TXT_ADD_RELATION", $this->lng->txt("meta_add"));
            $this->ctrl->setParameter($this, "section", "meta_relation");
            $this->tpl->setVariable(
                "ACTION_ADD_RELATION",
                $this->ctrl->getLinkTarget($this, "addSection")
            );
            $this->tpl->parseCurrentBlock();
        } else {
            foreach ($rel_ids as $rel_id) {
                $this->md_section = $this->md_obj->getRelation($rel_id);
                
                $this->ctrl->setParameter($this, 'meta_index', $rel_id);
                $this->ctrl->setParameter($this, "section", "meta_relation");
                
                /* Identifier_ */
                $res_ids = $this->md_section->getIdentifier_Ids();
                foreach ($res_ids as $res_id) {
                    $ident = $this->md_section->getIdentifier_($res_id);
                    $this->ctrl->setParameter($this, "meta_index", $res_id);

                    if (count($res_ids) > 1) {
                        $this->tpl->setCurrentBlock("identifier_delete");
                        $this->ctrl->setParameter($this, "meta_path", "relation_resource_identifier");
                        $this->tpl->setVariable(
                            "IDENTIFIER_LOOP_ACTION_DELETE",
                            $this->ctrl->getLinkTarget($this, "deleteElement")
                        );
                        $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                        $this->tpl->parseCurrentBlock();
                    }

                    $this->tpl->setCurrentBlock("identifier_loop");

                    $this->tpl->setVariable("IDENTIFIER_LOOP_NO", $res_id);
                    $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
                    $this->ctrl->setParameter($this, 'meta_index', $rel_id);
                    $this->ctrl->setParameter($this, "section_element", "relation_resource_identifier");
                    $this->tpl->setVariable(
                        "IDENTIFIER_LOOP_ACTION_ADD",
                        $this->ctrl->getLinkTarget($this, "addSectionElement")
                    );
                    $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                    $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
                    $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
                    $this->tpl->setVariable(
                        "IDENTIFIER_LOOP_VAL_CATALOG",
                        ilUtil::prepareFormOutput($ident->getCatalog())
                    );
                    $this->tpl->setVariable(
                        "IDENTIFIER_LOOP_VAL_ENTRY",
                        ilUtil::prepareFormOutput($ident->getEntry())
                    );
                    $this->tpl->parseCurrentBlock();
                }
    
                /* Description */
                $res_dess = $this->md_section->getDescriptionIds();
                foreach ($res_dess as $res_des) {
                    $des = $this->md_section->getDescription($res_des);
                    $this->ctrl->setParameter($this, "meta_index", $res_des);

                    if (count($res_dess) > 1) {
                        $this->tpl->setCurrentBlock("description_delete");
                        $this->ctrl->setParameter($this, "meta_path", "relation_resource_description");
                        $this->tpl->setVariable(
                            "DESCRIPTION_LOOP_ACTION_DELETE",
                            $this->ctrl->getLinkTarget($this, "deleteElement")
                        );
                        $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                        $this->tpl->parseCurrentBlock();
                    }
    
                    $this->tpl->setCurrentBlock("description_loop");
                    $this->tpl->setVariable("DESCRIPTION_LOOP_NO", $res_des);
                    $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
                    $this->ctrl->setParameter($this, 'meta_index', $rel_id);
                    $this->ctrl->setParameter($this, "section_element", "relation_resource_description");
                    $this->tpl->setVariable(
                        "DESCRIPTION_LOOP_ACTION_ADD",
                        $this->ctrl->getLinkTarget($this, "addSectionElement")
                    );
                    $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                    $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
                    $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                    $this->tpl->setVariable(
                        "DESCRIPTION_LOOP_VAL",
                        ilUtil::prepareFormOutput($des->getDescription())
                    );
                    $this->tpl->setVariable(
                        "DESCRIPTION_LOOP_VAL_LANGUAGE",
                        $this->__showLanguageSelect(
                            'relation[Resource][Description][' . $res_des . '][Language]',
                            $des->getDescriptionLanguageCode()
                        )
                    );
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("relation_loop");
                $this->tpl->setVariable("REL_ID", $rel_id);
                $this->tpl->setVariable("TXT_RELATION", $this->lng->txt("meta_relation"));
                $this->ctrl->setParameter($this, "meta_index", $this->md_section->getMetaId());
                $this->tpl->setVariable(
                    "ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, "deleteSection")
                );
                $this->ctrl->setParameter($this, "section", "meta_relation");
                $this->tpl->setVariable(
                    "ACTION_ADD",
                    $this->ctrl->getLinkTarget($this, "addSection")
                );
                $this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
                $this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
                $this->tpl->setVariable("TXT_KIND", $this->lng->txt("meta_kind"));
                $this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
                $this->tpl->setVariable("TXT_ISPARTOF", $this->lng->txt("meta_is_part_of"));
                $this->tpl->setVariable("TXT_HASPART", $this->lng->txt("meta_has_part"));
                $this->tpl->setVariable("TXT_ISVERSIONOF", $this->lng->txt("meta_is_version_of"));
                $this->tpl->setVariable("TXT_HASVERSION", $this->lng->txt("meta_has_version"));
                $this->tpl->setVariable("TXT_ISFORMATOF", $this->lng->txt("meta_is_format_of"));
                $this->tpl->setVariable("TXT_HASFORMAT", $this->lng->txt("meta_has_format"));
                $this->tpl->setVariable("TXT_REFERENCES", $this->lng->txt("meta_references"));
                $this->tpl->setVariable("TXT_ISREFERENCEDBY", $this->lng->txt("meta_is_referenced_by"));
                $this->tpl->setVariable("TXT_ISBASEDON", $this->lng->txt("meta_is_based_on"));
                $this->tpl->setVariable("TXT_ISBASISFOR", $this->lng->txt("meta_is_basis_for"));
                $this->tpl->setVariable("TXT_REQUIRES", $this->lng->txt("meta_requires"));
                $this->tpl->setVariable("TXT_ISREQUIREDBY", $this->lng->txt("meta_is_required_by"));
                $this->tpl->setVariable("TXT_RESOURCE", $this->lng->txt("meta_resource"));
                $this->tpl->setVariable("VAL_KIND_" . strtoupper($this->md_section->getKind()), " selected");
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->setCurrentBlock("relation");
            $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function updateRelation()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // relation
        foreach ($ids = $this->md_obj->getRelationIds() as $id) {
            // kind
            $relation = $this->md_obj->getRelation($id);
            $relation->setKind($_POST['relation'][$id]['Kind']);
            
            $relation->update();
            
            // identifiers
            $res_idents = $relation->getIdentifier_Ids();
            foreach ($res_idents as $res_id) {
                $ident = $relation->getIdentifier_($res_id);
                $ident->setCatalog(ilUtil::stripSlashes($_POST['relation']['Resource']['Identifier'][$res_id]['Catalog']));
                $ident->setEntry(ilUtil::stripSlashes($_POST['relation']['Resource']['Identifier'][$res_id]['Entry']));
                $ident->update();
            }
            
            // descriptions
            $res_dess = $relation->getDescriptionIds();
            foreach ($res_dess as $res_des) {
                $des = $relation->getDescription($res_des);
                $des->setDescription(ilUtil::stripSlashes($_POST['relation']['Resource']['Description'][$res_des]['Value']));
                $des->setDescriptionLanguage(
                    new ilMDLanguageItem($_POST['relation']['Resource']['Description'][$res_des]['Language'])
                );
                $des->update();
            }
        }
        
        $this->callListeners('Relation');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    /*
     * list annotation section
     */
    public function listAnnotation()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_annotation');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_annotation.html', 'Services/MetaData');

        $anno_ids = $this->md_obj->getAnnotationIds();
        if (!is_array($anno_ids) || count($anno_ids) == 0) {
            $this->tpl->setCurrentBlock("no_annotation");
            $this->tpl->setVariable("TXT_NO_ANNOTATION", $this->lng->txt("meta_no_annotation"));
            $this->tpl->setVariable("TXT_ADD_ANNOTATION", $this->lng->txt("meta_add"));
            $this->ctrl->setParameter($this, "section", "meta_annotation");
            $this->tpl->setVariable(
                "ACTION_ADD_ANNOTATION",
                $this->ctrl->getLinkTarget($this, "addSection")
            );
            $this->tpl->parseCurrentBlock();
        } else {
            foreach ($anno_ids as $anno_id) {
                $this->ctrl->setParameter($this, 'meta_index', $anno_id);
                $this->ctrl->setParameter($this, "section", "meta_annotation");

                $this->md_section = $this->md_obj->getAnnotation($anno_id);
                                
                $this->tpl->setCurrentBlock("annotation_loop");
                $this->tpl->setVariable("ANNOTATION_ID", $anno_id);
                $this->tpl->setVariable("TXT_ANNOTATION", $this->lng->txt("meta_annotation"));
                $this->ctrl->setParameter($this, "meta_index", $anno_id);
                $this->tpl->setVariable(
                    "ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, "deleteSection")
                );
                $this->ctrl->setParameter($this, "section", "meta_annotation");
                $this->tpl->setVariable(
                    "ACTION_ADD",
                    $this->ctrl->getLinkTarget($this, "addSection")
                );
                $this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
                
                $this->tpl->setVariable("TXT_ENTITY", $this->lng->txt("meta_entity"));
                $this->tpl->setVariable("VAL_ENTITY", ilUtil::prepareFormOutput($this->md_section->getEntity()));
                $this->tpl->setVariable("TXT_DATE", $this->lng->txt("meta_date"));
                $this->tpl->setVariable("VAL_DATE", ilUtil::prepareFormOutput($this->md_section->getDate()));
    
                /* Description */
                $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
                $this->tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
                $this->tpl->setVariable("VAL_DESCRIPTION", ilUtil::prepareFormOutput($this->md_section->getDescription()));
                $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
                $this->tpl->setVariable(
                    "VAL_DESCRIPTION_LANGUAGE",
                    $this->__showLanguageSelect(
                        'annotation[' . $anno_id . '][Language]',
                        $this->md_section->getDescriptionLanguageCode()
                    )
                );
                
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->setCurrentBlock("annotation");
            $this->tpl->setVariable("EDIT_ACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function updateAnnotation()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // relation
        foreach ($ids = $this->md_obj->getAnnotationIds() as $id) {
            // entity
            $annotation = $this->md_obj->getAnnotation($id);
            $annotation->setEntity(ilUtil::stripSlashes($_POST['annotation'][$id]['Entity']));
            $annotation->setDate(ilUtil::stripSlashes($_POST['annotation'][$id]['Date']));
            $annotation->setDescription(ilUtil::stripSlashes($_POST['annotation'][$id]['Description']));
            $annotation->setDescriptionLanguage(
                new ilMDLanguageItem($_POST['annotation'][$id]['Language'])
            );

            $annotation->update();
        }
        
        $this->callListeners('Annotation');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
    }
    
    /*
     * list classification section
     */
    public function listClassification()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_classification');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_classification.html', 'Services/MetaData');

        $class_ids = $this->md_obj->getClassificationIds();
        if (!is_array($class_ids) || count($class_ids) == 0) {
            $this->tpl->setCurrentBlock("no_classification");
            $this->tpl->setVariable("TXT_NO_CLASSIFICATION", $this->lng->txt("meta_no_classification"));
            $this->tpl->setVariable("TXT_ADD_CLASSIFICATION", $this->lng->txt("meta_add"));
            $this->ctrl->setParameter($this, "section", "meta_classification");
            $this->tpl->setVariable(
                "ACTION_ADD_CLASSIFICATION",
                $this->ctrl->getLinkTarget($this, "addSection")
            );
            $this->tpl->parseCurrentBlock();
        } else {
            foreach ($class_ids as $class_id) {
                $this->md_section = $this->md_obj->getClassification($class_id);
                $this->ctrl->setParameter($this, "section", "meta_classification");
                
                /* TaxonPath */
                $tp_ids = $this->md_section->getTaxonPathIds();
                foreach ($tp_ids as $tp_id) {
                    $tax_path = $this->md_section->getTaxonPath($tp_id);
                    
                    $tax_ids = $tax_path->getTaxonIds();
                    
                    foreach ($tax_ids as $tax_id) {
                        $taxon = $tax_path->getTaxon($tax_id);
                        
                        if (count($tax_ids) > 1) {
                            $this->tpl->setCurrentBlock("taxon_delete");
                            $this->ctrl->setParameter($this, "meta_index", $tax_id);
                            $this->ctrl->setParameter($this, "meta_path", "classification_taxon");
                            $this->tpl->setVariable(
                                "TAXONPATH_TAXON_LOOP_ACTION_DELETE",
                                $this->ctrl->getLinkTarget($this, "deleteElement")
                            );
                            $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                            $this->tpl->parseCurrentBlock();
                        }

                        $this->tpl->setCurrentBlock("taxonpath_taxon_loop");
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_NO", $tax_id);
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TAXONPATH_NO", $tp_id);
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_TAXON", $this->lng->txt("meta_taxon"));
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_VAL_TAXON", ilUtil::prepareFormOutput($taxon->getTaxon()));
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_ID", $this->lng->txt("meta_id"));
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_VAL_ID", ilUtil::prepareFormOutput($taxon->getTaxonId()));
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                        $this->tpl->setVariable(
                            "TAXONPATH_TAXON_LOOP_VAL_TAXON_LANGUAGE",
                            $this->__showLanguageSelect(
                                'classification[TaxonPath][Taxon][' . $tax_id . '][Language]',
                                $taxon->getTaxonLanguageCode()
                            )
                        );

                        $this->ctrl->setParameter($this, "section_element", "Taxon_" . $class_id);
                        $this->ctrl->setParameter($this, "meta_index", $tp_id);
                        $this->tpl->setVariable(
                            "TAXONPATH_TAXON_LOOP_ACTION_ADD",
                            $this->ctrl->getLinkTarget($this, "addSectionElement")
                        );
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                        $this->tpl->parseCurrentBlock();
                    }

                    if (count($tp_ids) > 1) {
                        $this->tpl->setCurrentBlock("taxonpath_delete");
                        $this->ctrl->setParameter($this, "meta_index", $tp_id);
                        $this->ctrl->setParameter($this, "meta_path", "classification_taxon_path");
                        $this->tpl->setVariable(
                            "TAXONPATH_LOOP_ACTION_DELETE",
                            $this->ctrl->getLinkTarget($this, "deleteElement")
                        );
                        $this->tpl->setVariable("TAXONPATH_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                        $this->tpl->parseCurrentBlock();
                    }

                    $this->tpl->setCurrentBlock("taxonpath_loop");
                    $this->tpl->setVariable("TAXONPATH_LOOP_NO", $tp_id);
                    $this->tpl->setVariable("TAXONPATH_LOOP_ROWSPAN", (3 * count($tax_ids)) + 2);
                    $this->tpl->setVariable("TAXONPATH_LOOP_TXT_TAXONPATH", $this->lng->txt("meta_taxon_path"));
                    $this->tpl->setVariable("TAXONPATH_LOOP_TXT_SOURCE", $this->lng->txt("meta_source"));
                    $this->tpl->setVariable("TAXONPATH_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
                    $this->tpl->setVariable("TAXONPATH_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                    $this->tpl->setVariable("TAXONPATH_LOOP_VAL_SOURCE", ilUtil::prepareFormOutput($tax_path->getSource()));
                    $this->tpl->setVariable(
                        "TAXONPATH_LOOP_VAL_SOURCE_LANGUAGE",
                        $this->__showLanguageSelect(
                            'classification[TaxonPath][' . $tp_id . '][Source][Language]',
                            $tax_path->getSourceLanguageCode()
                        )
                    );
                    $this->ctrl->setParameter($this, "section_element", "TaxonPath_" . $class_id);
                    $this->ctrl->setParameter($this, "meta_index", $class_id);
                    $this->tpl->setVariable(
                        "TAXONPATH_LOOP_ACTION_ADD",
                        $this->ctrl->getLinkTarget($this, "addSectionElement")
                    );
                    $this->tpl->setVariable("TAXONPATH_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                    $this->tpl->parseCurrentBlock();
                }

                /* Description */
                $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
                $this->tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
                $this->tpl->setVariable(
                    "VAL_DESCRIPTION",
                    ilUtil::prepareFormOutput($this->md_section->getDescription())
                );
                $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
                $this->tpl->setVariable(
                    "VAL_DESCRIPTION_LANGUAGE",
                    $this->__showLanguageSelect(
                        'classification[' . $class_id . '][Language]',
                        $this->md_section->getDescriptionLanguageCode()
                    )
                );

                /* Keyword */
                $key_ids = $this->md_section->getKeywordIds();
                foreach ($key_ids as $key_id) {
                    if (count($key_ids) > 1) {
                        $this->ctrl->setParameter($this, "meta_index", $key_id);
                        $this->ctrl->setParameter($this, "meta_path", "classification_keyword");
                        $this->tpl->setCurrentBlock("keyword_delete");
                        $this->tpl->setVariable(
                            "KEYWORD_LOOP_ACTION_DELETE",
                            $this->ctrl->getLinkTarget($this, "deleteElement")
                        );
                        $this->tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                        $this->tpl->parseCurrentBlock();
                    }
                    
                    $keyword = $this->md_section->getKeyword($key_id);
                    $this->tpl->setCurrentBlock("keyword_loop");
                    $this->tpl->setVariable("KEYWORD_LOOP_NO", $key_id);
                    $this->tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("meta_keyword"));
                    $this->tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
                    $this->tpl->setVariable(
                        "KEYWORD_LOOP_VAL",
                        ilUtil::prepareFormOutput($keyword->getKeyword())
                    );
                    $this->tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
                    $this->tpl->setVariable(
                        "KEYWORD_LOOP_VAL_LANGUAGE",
                        $this->__showLanguageSelect(
                            'classification[Keyword][' . $key_id . '][Language]',
                            $keyword->getKeywordLanguageCode()
                        )
                    );
                    $this->ctrl->setParameter($this, "meta_index", $class_id);
                    $this->ctrl->setParameter($this, "section_element", "Keyword_" . $class_id);
                    $this->tpl->setVariable(
                        "KEYWORD_LOOP_ACTION_ADD",
                        $this->ctrl->getLinkTarget($this, "addSectionElement")
                    );
                    $this->tpl->setVariable("KEYWORD_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
                    $this->tpl->parseCurrentBlock();
                }
                
                $this->tpl->setCurrentBlock("classification_loop");
                $this->tpl->setVariable("TXT_CLASSIFICATION", $this->lng->txt("meta_classification"));
                $this->ctrl->setParameter($this, "meta_index", $class_id);
                $this->tpl->setVariable(
                    "ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, "deleteSection")
                );
                $this->tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->setVariable(
                    "ACTION_ADD",
                    $this->ctrl->getLinkTarget($this, "addSection")
                );
                $this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
    
                $this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
                $this->tpl->setVariable("TXT_TAXONPATH", $this->lng->txt("meta_taxon_path"));
                $this->tpl->setVariable("TXT_KEYWORD", $this->lng->txt("meta_keyword"));
                $this->tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
                
                $this->tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
                $this->tpl->setVariable("CLASS_ID", $class_id);
                $this->tpl->setVariable("TXT_PURPOSE", $this->lng->txt("meta_purpose"));
                $this->tpl->setVariable("TXT_DISCIPLINE", $this->lng->txt("meta_learning_resource_type"));
                $this->tpl->setVariable("TXT_IDEA", $this->lng->txt("meta_idea"));
                $this->tpl->setVariable("TXT_PREREQUISITE", $this->lng->txt("meta_prerequisite"));
                $this->tpl->setVariable("TXT_EDUCATIONALOBJECTIVE", $this->lng->txt("meta_educational_objective"));
                $this->tpl->setVariable("TXT_ACCESSIBILITYRESTRICTIONS", $this->lng->txt("meta_accessibility_restrictions"));
                $this->tpl->setVariable("TXT_EDUCATIONALLEVEL", $this->lng->txt("meta_educational_level"));
                $this->tpl->setVariable("TXT_SKILLLEVEL", $this->lng->txt("meta_skill_level"));
                $this->tpl->setVariable("TXT_SECURITYLEVEL", $this->lng->txt("meta_security_level"));
                $this->tpl->setVariable("TXT_COMPETENCY", $this->lng->txt("meta_competency"));
                $this->tpl->setVariable("VAL_PURPOSE_" . strtoupper($this->md_section->getPurpose()), " selected");
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->setCurrentBlock("classification");
            $this->tpl->setVariable(
                "EDIT_ACTION",
                $this->ctrl->getFormAction($this)
            );
            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function updateClassification()
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        // relation
        foreach ($ids = $this->md_obj->getClassificationIds() as $id) {
            // entity
            $classification = $this->md_obj->getClassification($id);
            $classification->setPurpose($_POST['classification'][$id]['Purpose']);
            
            $classification->setDescription(ilUtil::stripSlashes($_POST['classification'][$id]['Description']));
            $classification->setDescriptionLanguage(
                new ilMDLanguageItem($_POST['classification'][$id]['Language'])
            );

            $classification->update();
            
            $key_ids = $classification->getKeywordIds();
            foreach ($key_ids as $key_id) {
                $keyword = $classification->getKeyword($key_id);
                $keyword->setKeyword(ilUtil::stripSlashes($_POST['classification']['Keyword'][$key_id]['Value']));
                $keyword->setKeywordLanguage(
                    new ilMDLanguageItem($_POST['classification']['Keyword'][$key_id]['Language'])
                );
                $keyword->update();
            }
            
            $tp_ids = $classification->getTaxonPathIds();
            foreach ($tp_ids as $tp_id) {
                $tax_path = $classification->getTaxonPath($tp_id);
                $tax_path->setSource(ilUtil::stripSlashes($_POST['classification']['TaxonPath'][$tp_id]['Source']['Value']));
                $tax_path->setSourceLanguage(
                    new ilMDLanguageItem($_POST['classification']['TaxonPath'][$tp_id]['Source']['Language'])
                );
                $tax_path->update();

                $tax_ids = $tax_path->getTaxonIds();
                    
                foreach ($tax_ids as $tax_id) {
                    $taxon = $tax_path->getTaxon($tax_id);
                    $taxon->setTaxon(ilUtil::stripSlashes($_POST['classification']['TaxonPath']['Taxon'][$tax_id]['Value']));
                    $taxon->setTaxonLanguage(
                        new ilMDLanguageItem($_POST['classification']['TaxonPath']['Taxon'][$tax_id]['Language'])
                    );
                    $taxon->setTaxonId(ilUtil::stripSlashes($_POST['classification']['TaxonPath']['Taxon'][$tax_id]['Id']));
                    $taxon->update();
                }
            }
        }
        
        $this->callListeners('Classification');
        ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    public function deleteElement()
    {
        include_once 'Services/MetaData/classes/class.ilMDFactory.php';

        $md_element = ilMDFactory::_getInstance($_GET['meta_path'], $_GET['meta_index'], $_GET['meta_technical']);
        $md_element->delete();
        
        $this->listSection();

        return true;
    }
    
    public function deleteSection()
    {
        include_once 'Services/MetaData/classes/class.ilMDFactory.php';

        $md_element = ilMDFactory::_getInstance($_GET['section'], $_GET['meta_index']);
        $md_element->delete();
        
        $this->listSection();

        return true;
    }
    
    public function addSection()
    {
        // Switch section
        switch ($_GET['section']) {
            case 'meta_technical':
                $this->md_section =&$this->md_obj->addTechnical();
                $this->md_section->save();
                break;


            case 'meta_lifecycle':
                $this->md_section =&$this->md_obj->addLifecycle();
                $this->md_section->save();
                $con =&$this->md_section->addContribute();
                $con->save();

                $ent =&$con->addEntity();
                $ent->save();
                break;

            case 'meta_meta_metadata':
                $this->md_section = $this->md_obj->addMetaMetadata();
                $this->md_section->save();

                $ide =&$this->md_section->addIdentifier();
                $ide->save();

                $con =&$this->md_section->addContribute();
                $con->save();

                $ent =&$con->addEntity();
                $ent->save();
                break;

            case 'meta_rights':
                $this->md_section = $this->md_obj->addRights();
                $this->md_section->save();
                break;
                
            case 'meta_educational':
                $this->md_section = $this->md_obj->addEducational();
                $this->md_section->save();
                break;
                
            case 'meta_relation':
                $this->md_section = $this->md_obj->addRelation();
                $this->md_section->save();
                $ident = $this->md_section->addIdentifier_();
                $ident->save();
                $des = $this->md_section->addDescription();
                $des->save();
                break;
                
            case 'meta_annotation':
                $this->md_section = $this->md_obj->addAnnotation();
                $this->md_section->save();
                break;

            case 'meta_classification':
                $this->md_section = $this->md_obj->addClassification();
                $this->md_section->save();

                $taxon_path =&$this->md_section->addTaxonPath();
                $taxon_path->save();

                $taxon =&$taxon_path->addTaxon();
                $taxon->save();

                $key =&$this->md_section->addKeyword();
                $key->save();
                break;

        }
        
        $this->listSection();
        return true;
    }

    public function addSectionElement()
    {
        $section_element = (empty($_POST['section_element']))
            ? $_GET['section_element']
            : $_POST['section_element'];
            

        // Switch section
        switch ($_GET['section']) {
            case 'meta_technical':
                $this->md_section =&$this->md_obj->getTechnical();
                break;

            case 'meta_lifecycle':
                $this->md_section =&$this->md_obj->getLifecycle();
                break;

            case 'meta_meta_metadata':
                $this->md_section =&$this->md_obj->getMetaMetadata();
                break;

            case 'meta_general':
                $this->md_section = $this->md_obj->getGeneral();
                break;
                
            case 'meta_educational':
                $this->md_section = $this->md_obj->getEducational();
                break;

            case 'meta_classification':
                $arr = explode("_", $section_element);
                $section_element = $arr[0];
                $this->md_section = $this->md_obj->getClassification($arr[1]);
                break;
        }

        // Switch new element
        switch ($section_element) {
            case 'meta_or_composite':
                $md_new =&$this->md_section->addOrComposite();
                $md_new = $md_new->addRequirement();
                break;

            case 'meta_requirement':
                $md_new =&$this->md_section->addRequirement();
                break;

            case 'meta_location':
                $md_new =&$this->md_section->addLocation();
                break;

            case 'meta_format':
                $md_new = $this->md_section->addFormat();
                break;

            case 'meta_entity':
                $md_new = $this->md_section->getContribute((int) $_GET['meta_index']);
                $md_new = $md_new->addEntity();
                break;

            case 'meta_identifier':
                $md_new = $this->md_section->addIdentifier();
                break;

            case 'meta_contribute':
                $md_new =&$this->md_section->addContribute();
                $md_new->save();
                $md_new = $md_new->addEntity();
                break;

            case 'educational_language':
            case 'meta_language':
                $md_new = $this->md_section->addLanguage();
                break;

            case 'educational_description':
            case 'meta_description':
                $md_new = $this->md_section->addDescription();
                break;

            case 'Keyword':
            case 'meta_keyword':
                $md_new = $this->md_section->addKeyword();
                break;

            case 'educational_typical_age_range':
                $md_new = $this->md_section->addTypicalAgeRange();
                break;
                
            case 'relation_resource_identifier':
                $rel = $this->md_obj->getRelation($_GET['meta_index']);
                $md_new = $rel->addIdentifier_();
                break;
                
            case 'relation_resource_description':
                $rel = $this->md_obj->getRelation($_GET['meta_index']);
                $md_new = $rel->addDescription();
                break;
                
            case 'TaxonPath':
                $md_new = $this->md_section->addTaxonPath();
                $md_new->save();
                $md_new = $md_new->addTaxon();
                break;
                
            case 'Taxon':
                $tax_path = $this->md_section->getTaxonPath($_GET['meta_index']);
                $md_new = $tax_path->addTaxon();
                break;
        }

        $md_new->save();

        $this->listSection();

        return true;
    }

    public function listSection()
    {
        switch ($_REQUEST['section']) {
            case 'meta_general':
                return $this->listGeneral();

            case 'meta_lifecycle':
                return $this->listLifecycle();

            case 'meta_technical':
                return $this->listTechnical();

            case 'meta_meta_metadata':
                return $this->listMetaMetadata();
                
            case 'debug':
                return $this->debug();
                
            case 'meta_rights':
                return $this->listRights();
                
            case 'meta_educational':
                return $this->listEducational();

            case 'meta_relation':
                return $this->listRelation();

            case 'meta_annotation':
                return $this->listAnnotation();

            case 'meta_classification':
                return $this->listClassification();

            default:
                if ($this->md_obj->obj_type=='sahs'||$this->md_obj->obj_type=='sco') {
                    return $this->listQuickEdit_scorm();
                } else {
                    return $this->listQuickEdit();
                }
        }
    }


    // PRIVATE
    public function __fillSubelements()
    {
        if (count($subs = $this->md_section->getPossibleSubelements())) {
            //$subs = array_merge(array('' => 'meta_please_select'),$subs);

            $this->tpl->setCurrentBlock("subelements");
            $this->tpl->setVariable("SEL_SUBELEMENTS", ilUtil::formSelect('', 'section_element', $subs));
            $this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setVariable("TXT_ADD", $this->lng->txt('meta_add'));
        }
        return true;
    }



    public function __setTabs($a_active)
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        
        $tabs = array('meta_quickedit' => 'listQuickEdit',
                      'meta_general' => 'listGeneral',
                      'meta_lifecycle' => 'listLifecycle',
                      'meta_meta_metadata'	=> 'listMetaMetadata',
                      'meta_technical' => 'listTechnical',
                      'meta_educational' => 'listEducational',
                      'meta_rights' => 'listRights',
                      'meta_relation' => 'listRelation',
                      'meta_annotation' => 'listAnnotation',
                      'meta_classification' => 'listClassification');

        if (DEVMODE) {
            $tabs['debug'] = 'debug';
        }

        include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
        $section = new ilSelectInputGUI($this->lng->txt("meta_section"), "section");

        $options = array();
        foreach (array_keys($tabs) as $key) {
            $options[$key]= $this->lng->txt($key);
        }
        $section->setOptions($options);
        $section->setValue($a_active);

        $ilToolbar->addStickyItem($section, true);
        
        include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
        $button = ilSubmitButton::getInstance();
        $button->setCaption("show");
        $button->setCommand("listSection");
        $ilToolbar->addStickyItem($button);
                
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "listSection"));

        return true;
    }


    /**
    * shows language select box
    */
    public function __showLanguageSelect($a_name, $a_value = "")
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        $tpl = new ilTemplate(
            "tpl.lang_selection.html",
            true,
            true,
            "Services/MetaData"
        );

        foreach (ilMDLanguageItem::_getLanguages() as $code => $text) {
            $tpl->setCurrentBlock("lg_option");
            $tpl->setVariable("VAL_LG", $code);
            $tpl->setVariable("TXT_LG", $text);

            if ($a_value != "" &&
                $a_value == $code) {
                $tpl->setVariable("SELECTED", "selected");
            }

            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
        $tpl->setVariable("SEL_NAME", $a_name);

        $return = $tpl->get();
        unset($tpl);

        return $return;
    }

    public function __buildMonthsSelect($sel_month)
    {
        for ($i = 0;$i <= 24;$i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilUtil::formSelect($sel_month, 'tlt[mo]', $options, false, true);
    }


    public function __buildDaysSelect($sel_day)
    {
        for ($i = 0;$i <= 31;$i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilUtil::formSelect($sel_day, 'tlt[d]', $options, false, true);
    }
                
        

    // Observer methods
    public function addObserver(&$a_class, $a_method, $a_element)
    {
        $this->observers[$a_element]['class'] =&$a_class;
        $this->observers[$a_element]['method'] =&$a_method;

        return true;
    }
    public function callListeners($a_element)
    {
        if (isset($this->observers[$a_element])) {
            $class =&$this->observers[$a_element]['class'];
            $method = $this->observers[$a_element]['method'];

            return $class->$method($a_element);
        }
        return false;
    }

    /**
     * Get cnange copyright modal
     *
     * @return \ILIAS\UI\Component\Modal\Interruptive
     */
    protected function getChangeCopyrightModal()
    {
        $md_settings = ilMDSettings::_getInstance();
        if (!$md_settings->isCopyrightSelectionActive()) {
            return null;
        }

        $link = $this->ctrl->getLinkTarget($this, 'updateQuickEdit');
        return $this->ui_factory
            ->modal()
            ->interruptive(
                $this->lng->txt("meta_copyright_change_warning_title"),
                $this->lng->txt("meta_copyright_change_info"),
                $link
            );
    }
}
