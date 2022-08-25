<?php

declare(strict_types=1);

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


use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\UI\Component\Signal;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\GlobalHttpState;

/**
 * Meta Data class (element general)
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @package      ilias-core
 * @version      $Id$
 * @ilCtrl_Calls ilMDEditorGUI: ilFormPropertyDispatchGUI
 */
class ilMDEditorGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs_gui;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilRbacSystem $rbac_system;
    protected ilTree $tree;
    protected ilToolbarGUI $toolbarGUI;
    protected ilMDSettings $md_settings;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    /**
     * @var ilMDTechnical|ilMDGeneral|ilMDLifecycle|ilMDEducational|ilMDRights|ilMDMetaMetadata|ilMDRelation|ilMDAnnotation|ilMDClassification $md_section
     */
    protected ?object $md_section = null;
    protected ?ilPropertyFormGUI $form = null;

    protected ilMD $md_obj;

    protected array $observers = [];

    protected int $rbac_id;
    protected int $obj_id;
    protected string $obj_type;

    public function __construct(int $a_rbac_id, int $a_obj_id, string $a_obj_type)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->rbac_system = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->toolbarGUI = $DIC->toolbar();

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->md_obj = new ilMD($a_rbac_id, $a_obj_id, $a_obj_type);

        $this->lng->loadLanguageModule('meta');

        $this->md_settings = ilMDSettings::_getInstance();
    }

    protected function initMetaIndexFromQuery(): int
    {
        $meta_index = 0;
        if ($this->http->wrapper()->query()->has('meta_index')) {
            $meta_index = $this->http->wrapper()->query()->retrieve(
                'meta_index',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $meta_index;
    }

    protected function initSectionFromQuery(): string
    {
        $section = '';
        if ($this->http->wrapper()->query()->has('section')) {
            $section = $this->http->wrapper()->query()->retrieve(
                'section',
                $this->refinery->kindlyTo()->string()
            );
        }

        return $section;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "listSection";
                }
                $this->$cmd();
                break;
        }
    }

    public function debug(): bool
    {
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
    public function listQuickEdit_scorm(): void
    {
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
                $this->tpl->setVariable(
                    "LANGUAGE_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
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
        $this->tpl->setVariable(
            "VAL_TITLE",
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getTitle())
        );
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
                $this->tpl->setVariable(
                    "DESCRIPTION_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("description_loop");
            $this->tpl->setVariable("DESCRIPTION_LOOP_NO", $id);
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable(
                "DESCRIPTION_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput($md_des->getDescription())
            );
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

        $lang = '';
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
            $this->tpl->setVariable(
                "KEYWORD_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput(
                    implode(", ", $keyword_set)
                )
            );
            $this->tpl->setVariable("LANG", $lang);
            $this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "keyword[language][$lang]",
                $lang
            ));
            $this->tpl->parseCurrentBlock();
        }

        if ($keywords === []) {
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
        $this->tpl->setVariable("TXT_EXPERTS", $this->lng->txt('meta_subjectmatterexpert'));
        $this->tpl->setVariable("TXT_COMMA_SEP", $this->lng->txt('comma_separated'));
        $this->tpl->setVariable("TXT_SCOPROP_EXPERT", $this->lng->txt('sco_propagate'));
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "SubjectMatterExpert") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str .= $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            $this->tpl->setVariable("EXPERTS_VAL", ilLegacyFormElementsUtil::prepareFormOutput($ent_str));
        }
        // InstructionalDesigner
        $this->tpl->setVariable("TXT_DESIGNERS", $this->lng->txt('meta_instructionaldesigner'));
        $this->tpl->setVariable("TXT_SCOPROP_DESIGNERS", $this->lng->txt('sco_propagate'));
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "InstructionalDesigner") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str .= $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            $this->tpl->setVariable("DESIGNERS_VAL", ilLegacyFormElementsUtil::prepareFormOutput($ent_str));
        }
        // Point of Contact
        $this->tpl->setVariable("TXT_POC", $this->lng->txt('meta_pointofcontact'));
        $this->tpl->setVariable("TXT_SCOPROP_POC", $this->lng->txt('sco_propagate'));
        if (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "PointOfContact") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str .= $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            $this->tpl->setVariable("POC_VAL", ilLegacyFormElementsUtil::prepareFormOutput($ent_str));
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

        $tlt = array(0, 0, 0, 0, 0);
        $valid = true;
        if (is_object($this->md_section = $this->md_obj->getEducational())) {
            if (!$tlt = ilMDUtils::_LOMDurationToArray($this->md_section->getTypicalLearningTime())) {
                if ($this->md_section->getTypicalLearningTime() !== '') {
                    $tlt = array(0, 0, 0, 0, 0);
                    $valid = false;
                }
            }
        }
        $this->tpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
        $this->tpl->setVariable("SEL_MONTHS", $this->__buildMonthsSelect((string) ($tlt[0] ?? '')));
        $this->tpl->setVariable("SEL_DAYS", $this->__buildDaysSelect((string) ($tlt[1] ?? '')));

        $this->tpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
        $this->tpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));

        $this->tpl->setVariable("TXT_TYPICAL_LEARN_TIME", $this->lng->txt('meta_typical_learning_time'));
        $this->tpl->setVariable(
            "SEL_TLT",
            ilLegacyFormElementsUtil::makeTimeSelect(
                'tlt',
                !$tlt[4],
                $tlt[2],
                $tlt[3],
                $tlt[4],
                false
            )
        );
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

    public function listQuickEdit(ilPropertyFormGUI $form = null): void
    {
        if (!is_object($this->md_section = $this->md_obj->getGeneral())) {
            $this->md_section = $this->md_obj->addGeneral();
            $this->md_section->save();
        }

        $this->__setTabs('meta_quickedit');

        $interruptive_modal = $this->getChangeCopyrightModal();
        $interruptive_signal = null;
        $modal_content = '';
        if ($interruptive_modal !== null) {
            $interruptive_signal = $interruptive_modal->getShowSignal();
            $modal_content = $this->ui_renderer->render($interruptive_modal);
        }
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initQuickEditForm($interruptive_signal);
        }

        $this->tpl->setContent(
            $modal_content . $form->getHTML()
        );
    }

    public function initQuickEditForm(?Signal $a_signal_id): ilPropertyFormGUI
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setId('ilquickeditform');
        $this->form->setShowTopButtons(false);

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "gen_title");
        $ti->setMaxLength(200);
        $ti->setSize(50);
        if ($this->md_obj->getObjType() !== 'sess') {
            $ti->setRequired(true);
        }
        $ti->setValue($this->md_section->getTitle());
        $this->form->addItem($ti);

        // description(s)
        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);

            $ta = new ilTextAreaInputGUI(
                $this->lng->txt("meta_description"),
                "gen_description[" . $id . "][description]"
            );
            $ta->setCols(50);
            $ta->setRows(4);
            $ta->setValue($md_des->getDescription());
            if (count($ids) > 1) {
                $ta->setInfo($this->lng->txt("meta_l_" . $md_des->getDescriptionLanguageCode()));
            }

            $this->form->addItem($ta);
        }

        // language(s)
        $first = true;
        $options = ilMDLanguageItem::_getLanguages();
        $first_lang = '';
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lan = $this->md_section->getLanguage($id);
            $first_lang = $md_lan->getLanguageCode();
            $si = new ilSelectInputGUI($this->lng->txt("meta_language"), 'gen_language_' . $id . '_language');
            $si->setOptions($options);
            $si->setValue($md_lan->getLanguageCode());
            $this->form->addItem($si);
            $first = false;
        }
        if ($first) {
            $si = new ilSelectInputGUI($this->lng->txt("meta_language"), "gen_language_language");
            $si->setOptions($options);
            $this->form->addItem($si);
        }

        // keyword(s)
        $first = true;
        $keywords = array();
        foreach ($ids = $this->md_section->getKeywordIds() as $id) {
            $md_key = $this->md_section->getKeyword($id);
            if (trim($md_key->getKeyword()) !== '') {
                $keywords[$md_key->getKeywordLanguageCode()][] = $md_key->getKeyword();
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
        if ($keywords === []) {
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
        if ($this->md_obj->getLifecycle() instanceof ilMDLifecycle) {
            $sep = $ent_str = "";
            foreach (($ids = $this->md_obj->getLifecycle()->getContributeIds()) as $con_id) {
                $md_con = $this->md_obj->getLifecycle()->getContribute($con_id);
                if ($md_con->getRole() === "Author") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $ent_str .= $sep . $md_ent->getEntity();
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

        $tlt = new ilTypicalLearningTimeInputGUI($this->lng->txt("meta_typical_learning_time"), "tlt");
        $edu = $this->md_obj->getEducational();
        if (is_object($edu)) {
            $tlt->setValueByLOMDuration($edu->getTypicalLearningTime());
        }
        $this->form->addItem($tlt);

        $this->form->addCommandButton("updateQuickEdit", $this->lng->txt("save"), 'button_ilquickeditform');
        $this->form->setTitle($this->lng->txt("meta_quickedit"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        if (ilMDSettings::_getInstance()->isCopyrightSelectionActive()) {
            $this->tpl->addJavaScript(
                'Services/MetaData/js/ilMetaCopyrightListener.js'
            );
            $this->tpl->addOnLoadCode(
                'il.MetaDataCopyrightListener.init("' .
                $a_signal_id . '","copyright","form_ilquickeditform","button_ilquickeditform");'
            );
        }

        return $this->form;
    }

    protected function listQuickEditCopyright(ilPropertyFormGUI $form): bool
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
        $copyright->setValue((string) $current_id);

        foreach ($cp_entries as $copyright_entry) {
            $radio_entry = new ilRadioOption(
                $copyright_entry->getTitle(),
                (string) $copyright_entry->getEntryId(),
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
                $blocked->setValue('1');
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
        $own_selection->setValue('0');

        // copyright text
        $own_copyright = new ilTextAreaInputGUI(
            '',
            'copyright_text'
        );
        if ($current_id === 0) {
            $own_copyright->setValue($description);
        }
        $own_selection->addSubItem($own_copyright);
        $copyright->addOption($own_selection);
        $form->addItem($copyright);
        return true;
    }

    public function keywordAutocomplete(): void
    {
        $term = '';
        if ($this->http->wrapper()->query()->has('term')) {
            $term = $this->http->wrapper()->query()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }

        $res = ilMDKeyword::_getMatchingKeywords(
            ilUtil::stripSlashes($term),
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

        echo json_encode($result, JSON_THROW_ON_ERROR);
        exit;
    }

    public function updateQuickEdit(): bool
    {
        $this->md_section = $this->md_obj->getGeneral();

        $form = $this->initQuickEditForm(null);
        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('title_required'));
            $form->setValuesByPost();
            $this->listQuickEdit($form);
            return false;
        }
        $this->md_section->setTitle($form->getInput('gen_title'));
        $this->md_section->update();

        $has_language = false;
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lan = $this->md_section->getLanguage($id);
            $md_lan->setLanguage(
                new ilMDLanguageItem(
                    $form->getInput('gen_language_' . $id . '_language')
                )
            );
            $md_lan->update();
            $has_language = true;
        }
        if (!$has_language) {
            $md_lan = $this->md_section->addLanguage();
            $md_lan->setLanguage(
                new ilMDLanguageItem(
                    $form->getInput('gen_language_language')
                )
            );
            $md_lan->save();
        }

        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);
            $md_des->setDescription($form->getInput('gen_description[' . $id . '][description]'));
            $md_des->update();
        }


        // Keyword

        $keywords = [];
        if ($this->http->wrapper()->post()->has('keywords')) {
            $keywords = (array) $this->http->wrapper()->post()->retrieve(
                'keywords',
                $this->refinery->identity()
            );
        }
        $keyword_values = $keywords['value'] ?? null;
        if (is_array($keyword_values)) {
            ilMDKeyword::updateKeywords($this->md_section, $keyword_values);
        }
        $this->callListeners('General');

        // Copyright
        $copyright = 0;
        if ($this->http->wrapper()->post()->has('copyright')) {
            $copyright = $this->http->wrapper()->post()->retrieve(
                'copyright',
                $this->refinery->kindlyTo()->int()
            );
        }
        $copyright_text = 0;
        if ($this->http->wrapper()->post()->has('copyright_text')) {
            $copyright_text = $this->http->wrapper()->post()->retrieve(
                'copyright_text',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (
            $copyright > 0 ||
            $copyright_text !== ''
        ) {
            if (!is_object($this->md_section = $this->md_obj->getRights())) {
                $this->md_section = $this->md_obj->addRights();
                $this->md_section->save();
            }
            if ($copyright > 0) {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription('il_copyright_entry__' . IL_INST_ID . '__' . (int) $copyright);
            } else {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription($copyright_text);
            }
            $this->md_section->update();

            // update oer status
            $oer_settings = ilOerHarvesterSettings::getInstance();
            if ($oer_settings->supportsHarvesting($this->md_obj->getObjType())) {
                $chosen_copyright = $copyright;
                $status = new ilOerHarvesterObjectStatus($this->md_obj->getRBACId());

                $copyright_blocked = false;
                if ($this->http->wrapper()->post()->has('copyright_oer_blocked_' . $chosen_copyright)) {
                    $copyright_blocked = $this->http->wrapper()->post()->retrieve(
                        'copyright_oer_blocked_' . $chosen_copyright,
                        $this->refinery->kindlyTo()->bool()
                    );
                }
                $status->setBlocked($copyright_blocked);
                $status->save();
            }
        } elseif (is_object($this->md_section = $this->md_obj->getRights())) {
            $this->md_section->setCopyrightAndOtherRestrictions("No");
            $this->md_section->setDescription("");
            $this->md_section->update();
        }
        $this->callListeners('Rights');

        //Educational...
        $tlt = $form->getInput('tlt');
        $tlt_set = false;
        for ($i = 0; $i < 5; $i++) {
            $tlt_section = (int) ($tlt[$i] ?? 0);
            if ($tlt_section > 0) {
                $tlt_set = true;
                break;
            }
        }
        if ($tlt_set) {
            if (!is_object($this->md_section = $this->md_obj->getEducational())) {
                $this->md_section = $this->md_obj->addEducational();
                $this->md_section->save();
            }
            $this->md_section->setPhysicalTypicalLearningTime(
                (int) ($tlt[0] ?? 0),
                (int) ($tlt[1] ?? 0),
                (int) ($tlt[2] ?? 0),
                (int) ($tlt[3] ?? 0),
                (int) ($tlt[4] ?? 0)
            );
            $this->md_section->update();
        } elseif (is_object($this->md_section = $this->md_obj->getEducational())) {
            $this->md_section->setPhysicalTypicalLearningTime(0, 0, 0, 0, 0);
            $this->md_section->update();
        }
        $this->callListeners('Educational');
        //Lifecycle...
        // Authors
        if ($form->getInput('life_authors') !== '') {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }

            // determine all entered authors
            $life_authors = $form->getInput('life_authors');
            $auth_arr = explode($this->md_settings->getDelimiter(), $life_authors);
            for ($i = 0, $iMax = count($auth_arr); $i < $iMax; $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }

            $md_con_author = "";

            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "Author") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);

                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr, true)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr, true)]);
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
        } elseif (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "Author") {
                    $md_con->delete();
                }
            }
        }
        $this->callListeners('Lifecycle');

        // Redirect here to read new title and description
        // Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, 'listSection');
        return true;
    }

    public function updateQuickEdit_scorm_propagate(string $request, string $type): void
    {
        $module_id = $this->md_obj->getObjId();
        if ($this->md_obj->getObjType() === 'sco') {
            $module_id = $this->md_obj->getRBACId();
        }
        $tree = new ilTree($module_id);
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");

        $post = $this->http->request()->getParsedBody();
        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, ['sco']) as $sco) {
            $sco_md = new ilMD($module_id, $sco['obj_id'], 'sco');
            if ($post[$request] != "") {
                if (!is_object($sco_md_section = $sco_md->getLifecycle())) {
                    $sco_md_section = $sco_md->addLifecycle();
                    $sco_md_section->save();
                }
                // determine all entered authors
                $auth_arr = explode(",", $post[$request]);
                for ($i = 0, $iMax = count($auth_arr); $i < $iMax; $i++) {
                    $auth_arr[$i] = trim($auth_arr[$i]);
                }

                $md_con_author = "";

                // update existing author entries (delete if not entered)
                foreach (($ids = $sco_md_section->getContributeIds()) as $con_id) {
                    $md_con = $sco_md_section->getContribute($con_id);
                    if ($md_con->getRole() === $type) {
                        foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                            $md_ent = $md_con->getEntity($ent_id);

                            // entered author already exists
                            if (in_array($md_ent->getEntity(), $auth_arr, true)) {
                                unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr, true)]);
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
            } elseif (is_object($sco_md_section = $sco_md->getLifecycle())) {
                foreach (($ids = $sco_md_section->getContributeIds()) as $con_id) {
                    $md_con = $sco_md_section->getContribute($con_id);
                    if ($md_con->getRole() === $type) {
                        $md_con->delete();
                    }
                }
            }
            $sco_md->update();
        }
        $this->updateQuickEdit_scorm();
    }

    public function updateQuickEdit_scorm_prop_expert(): void
    {
        $this->updateQuickEdit_scorm_propagate("life_experts", "SubjectMatterExpert");
    }

    public function updateQuickEdit_scorm_prop_designer(): void
    {
        $this->updateQuickEdit_scorm_propagate("life_designers", "InstructionalDesigner");
    }

    public function updateQuickEdit_scorm_prop_poc(): void
    {
        $this->updateQuickEdit_scorm_propagate("life_poc", "PointOfContact");
    }

    /**
     * @todo discuss with scorm maintainer how to proceed with this quick edit implementation
     */
    public function updateQuickEdit_scorm(): void
    {
        $post = $this->http->request()->getParsedBody();

        // General values
        $this->md_section = $this->md_obj->getGeneral();
        $this->md_section->setTitle(ilUtil::stripSlashes($post['gen_title'] ?? ''));
        $this->md_section->setTitleLanguage(new ilMDLanguageItem($post['gen_title_language'] ?? ''));
        $this->md_section->update();


        // Language
        if (is_array($post['gen_language'])) {
            foreach ($post['gen_language'] as $id => $data) {
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
        if (is_array($post['gen_description'])) {
            foreach ($post['gen_description'] as $id => $data) {
                $md_des = $this->md_section->getDescription($id);
                $md_des->setDescription(ilUtil::stripSlashes($data['description']));
                $md_des->setDescriptionLanguage(new ilMDLanguageItem($data['language']));
                $md_des->update();
            }
        }

        // Keyword
        if (is_array($post["keywords"]["value"])) {
            $new_keywords = array();
            foreach ($post["keywords"]["value"] as $lang => $keywords) {
                $language = $post["keyword"]["language"][$lang];
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
                    in_array($md_key->getKeyword(), $new_keywords[$lang], true)) {
                    unset($new_keywords[$lang][array_search($md_key->getKeyword(), $new_keywords[$lang], true)]);
                } else {  // existing keyword has not been entered again -> delete
                    $md_key->delete();
                }
            }

            // insert entered, but not existing keywords
            foreach ($new_keywords as $lang => $key_arr) {
                foreach ($key_arr as $keyword) {
                    if ($keyword !== "") {
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
        if ($post['copyright_id'] or $post['rights_copyright']) {
            if (!is_object($this->md_section = $this->md_obj->getRights())) {
                $this->md_section = $this->md_obj->addRights();
                $this->md_section->save();
            }
            if ($post['copyright_id']) {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription('il_copyright_entry__' . IL_INST_ID . '__' . (int) $post['copyright_id']);
            } else {
                $this->md_section->setCopyrightAndOtherRestrictions("Yes");
                $this->md_section->setDescription(ilUtil::stripSlashes($post["rights_copyright"]));
            }
            $this->md_section->update();
        } elseif (is_object($this->md_section = $this->md_obj->getRights())) {
            $this->md_section->setCopyrightAndOtherRestrictions("No");
            $this->md_section->setDescription("");
            $this->md_section->update();
        }
        $this->callListeners('Rights');

        //Educational...
        // Typical Learning Time
        if ($post['tlt']['mo'] or $post['tlt']['d'] or
            $post["tlt"]['h'] or $post['tlt']['m'] or $post['tlt']['s']) {
            if (!is_object($this->md_section = $this->md_obj->getEducational())) {
                $this->md_section = $this->md_obj->addEducational();
                $this->md_section->save();
            }
            $this->md_section->setPhysicalTypicalLearningTime(
                (int) $post['tlt']['mo'],
                (int) $post['tlt']['d'],
                (int) $post['tlt']['h'],
                (int) $post['tlt']['m'],
                (int) $post['tlt']['s']
            );
            $this->md_section->update();
        } elseif (is_object($this->md_section = $this->md_obj->getEducational())) {
            $this->md_section->setPhysicalTypicalLearningTime(0, 0, 0, 0, 0);
            $this->md_section->update();
        }
        $this->callListeners('Educational');
        //Lifecycle...
        // experts
        if ($post["life_experts"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }

            // determine all entered authors
            $auth_arr = explode(",", $post["life_experts"]);
            for ($i = 0, $iMax = count($auth_arr); $i < $iMax; $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }

            $md_con_author = "";

            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "SubjectMatterExpert") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);

                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr, true)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr, true)]);
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
        } elseif (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "SubjectMatterExpert") {
                    $md_con->delete();
                }
            }
        }

        // InstructionalDesigner
        if ($post["life_designers"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }

            // determine all entered authors
            $auth_arr = explode(",", $post["life_designers"]);
            for ($i = 0, $iMax = count($auth_arr); $i < $iMax; $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }

            $md_con_author = "";

            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "InstructionalDesigner") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);

                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr, true)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr, true)]);
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
        } elseif (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "InstructionalDesigner") {
                    $md_con->delete();
                }
            }
        }

        // Point of Contact
        if ($post["life_poc"] != "") {
            if (!is_object($this->md_section = $this->md_obj->getLifecycle())) {
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
            }

            // determine all entered authors
            $auth_arr = explode(",", $post["life_poc"]);
            for ($i = 0, $iMax = count($auth_arr); $i < $iMax; $i++) {
                $auth_arr[$i] = trim($auth_arr[$i]);
            }

            $md_con_author = "";

            // update existing author entries (delete if not entered)
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "PointOfContact") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);

                        // entered author already exists
                        if (in_array($md_ent->getEntity(), $auth_arr, true)) {
                            unset($auth_arr[array_search($md_ent->getEntity(), $auth_arr, true)]);
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
        } elseif (is_object($this->md_section = $this->md_obj->getLifecycle())) {
            foreach (($ids = $this->md_section->getContributeIds()) as $con_id) {
                $md_con = $this->md_section->getContribute($con_id);
                if ($md_con->getRole() === "PointOfContact") {
                    $md_con->delete();
                }
            }
        }

        $this->md_section = $this->md_obj->getLifecycle();
        $this->md_section->setVersionLanguage(new ilMDLanguageItem($post['lif_language']));
        $this->md_section->setVersion(ilUtil::stripSlashes($post['lif_version']));
        $this->md_section->setStatus($post['lif_status']);
        $this->md_section->update();

        $this->callListeners('Lifecycle');

        // Redirect here to read new title and description
        // Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, 'listSection');
    }

    public function listGeneral(): void
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

                if ($md_ide->getCatalog() !== "ILIAS") {
                    $this->tpl->setCurrentBlock("identifier_delete");
                    $this->tpl->setVariable(
                        "IDENTIFIER_LOOP_ACTION_DELETE",
                        $this->ctrl->getLinkTarget($this, 'deleteElement')
                    );
                    $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                    $this->tpl->parseCurrentBlock();
                }
            }

            $this->tpl->setCurrentBlock("identifier_loop");
            if ($md_ide->getCatalog() === "ILIAS") {
                $this->tpl->setVariable("DISABLE_IDENT", ' disabled="disabled" ');
            }
            $this->tpl->setVariable("IDENTIFIER_LOOP_NO", $id);
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG",
                ilLegacyFormElementsUtil::prepareFormOutput($md_ide->getCatalog())
            );
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY",
                ilLegacyFormElementsUtil::prepareFormOutput($md_ide->getEntry())
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
                $this->tpl->setVariable(
                    "LANGUAGE_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
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
        $this->tpl->setVariable(
            "VAL_TITLE",
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getTitle())
        );
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
                $this->tpl->setVariable(
                    "DESCRIPTION_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
                $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("description_loop");
            $this->tpl->setVariable("DESCRIPTION_LOOP_NO", $id);
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
            $this->tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable(
                "DESCRIPTION_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput($md_des->getDescription())
            );
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
                $this->tpl->setVariable(
                    "KEYWORD_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
                $this->tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("keyword_loop");
            $this->tpl->setVariable("KEYWORD_LOOP_NO", $id);
            $this->tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
            $this->tpl->setVariable(
                "KEYWORD_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput($md_key->getKeyword())
            );
            $this->tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
            $this->tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
                "gen_keyword[" . $id . '][language]',
                $md_key->getKeywordLanguageCode()
            ));

            $this->tpl->parseCurrentBlock();
        }

        // Coverage
        $this->tpl->setVariable("COVERAGE_LOOP_TXT_COVERAGE", $this->lng->txt('meta_coverage'));
        $this->tpl->setVariable(
            "COVERAGE_LOOP_VAL",
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getCoverage())
        );
        $this->tpl->setVariable("COVERAGE_LOOP_TXT_LANGUAGE", $this->lng->txt('meta_language'));
        $this->tpl->setVariable("COVERAGE_LOOP_VAL_LANGUAGE", $this->__showLanguageSelect(
            'gen_coverage_language',
            $this->md_section->getCoverageLanguageCode()
        ));

        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
    }

    public function updateGeneral(): bool
    {
        $gen_title = '';
        if ($this->http->wrapper()->post()->has('gen_title')) {
            $gen_title = $this->http->wrapper()->post()->retrieve(
                'gen_title',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (trim($gen_title) === '') {
            if ($this->md_obj->getObjType() !== 'sess') {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('title_required'));
                $this->listGeneral();
                return false;
            }
        }

        $gen_structure = '';
        if ($this->http->wrapper()->post()->has('gen_structure')) {
            $gen_structure = $this->http->wrapper()->post()->retrieve(
                'gen_structure',
                $this->refinery->kindlyTo()->string()
            );
        }
        $gen_title_language = '';
        if ($this->http->wrapper()->post()->has('gen_title_language')) {
            $gen_title_language = $this->http->wrapper()->post()->retrieve(
                'gen_title_language',
                $this->refinery->kindlyTo()->string()
            );
        }
        $gen_coverage = '';
        if ($this->http->wrapper()->post()->has('gen_coverage')) {
            $gen_coverage = $this->http->wrapper()->post()->retrieve(
                'gen_coverage',
                $this->refinery->kindlyTo()->string()
            );
        }
        $gen_coverage_language = '';
        if ($this->http->wrapper()->post()->has('gen_coverage_language')) {
            $gen_coverage_language = $this->http->wrapper()->post()->retrieve(
                'gen_coverage_language',
                $this->refinery->kindlyTo()->string()
            );
        }
        // General values
        $this->md_section = $this->md_obj->getGeneral();
        $this->md_section->setStructure($gen_structure);
        $this->md_section->setTitle($gen_title);
        $this->md_section->setTitleLanguage(new ilMDLanguageItem($gen_title_language));
        $this->md_section->setCoverage(ilUtil::stripSlashes($gen_coverage));
        $this->md_section->setCoverageLanguage(new ilMDLanguageItem($gen_coverage_language));
        $this->md_section->update();

        // Identifier
        $gen_identifier = [];
        if ($this->http->wrapper()->post()->has('gen_identifier')) {
            $gen_identifier = $this->http->wrapper()->post()->retrieve(
                'gen_identifier',
                $this->refinery->identity()
            );
        }
        foreach ($gen_identifier as $id => $data) {
            $md_ide = $this->md_section->getIdentifier($id);
            $md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog']));
            $md_ide->setEntry(ilUtil::stripSlashes($data['Entry']));
            $md_ide->update();
        }

        // Language
        $gen_language = [];
        if ($this->http->wrapper()->post()->has('gen_language')) {
            $gen_language = $this->http->wrapper()->post()->retrieve(
                'gen_language',
                $this->refinery->identity()
            );
        }
        foreach ($gen_language as $id => $data) {
            $md_lan = $this->md_section->getLanguage($id);
            $md_lan->setLanguage(new ilMDLanguageItem($data['language']));
            $md_lan->update();
        }
        // Description
        $gen_description = [];
        if ($this->http->wrapper()->post()->has('gen_description')) {
            $gen_description = $this->http->wrapper()->post()->retrieve(
                'gen_description',
                $this->refinery->identity()
            );
        }
        foreach ($gen_description as $id => $data) {
            $md_des = $this->md_section->getDescription($id);
            $md_des->setDescription(ilUtil::stripSlashes($data['description']));
            $md_des->setDescriptionLanguage(new ilMDLanguageItem($data['language']));
            $md_des->update();
        }
        // Keyword
        $gen_keyword = [];
        if ($this->http->wrapper()->post()->has('gen_keyword')) {
            $gen_keyword = $this->http->wrapper()->post()->retrieve(
                'gen_keyword',
                $this->refinery->identity()
            );
        }
        foreach ($gen_keyword as $id => $data) {
            $md_key = $this->md_section->getKeyword($id);

            $md_key->setKeyword(ilUtil::stripSlashes($data['keyword']));
            $md_key->setKeywordLanguage(new ilMDLanguageItem($data['language']));
            $md_key->update();
        }
        $this->callListeners('General');

        // Redirect here to read new title and description
        // Otherwise ('Lifecycle' 'technical' ...) simply call listSection()
        $this->ctrl->setParameter($this, "section", "meta_general");
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, 'listSection');
        return true;
    }

    public function updateTechnical(): bool
    {
        // update technical section
        $met_size = '';
        if ($this->http->wrapper()->post()->has('met_size')) {
            $met_size = $this->http->wrapper()->post()->retrieve(
                'met_size',
                $this->refinery->kindlyTo()->string()
            );
        }
        $met_inst = '';
        if ($this->http->wrapper()->post()->has('met_inst')) {
            $met_inst = $this->http->wrapper()->post()->retrieve(
                'met_inst',
                $this->refinery->kindlyTo()->string()
            );
        }
        $inst_language = '';
        if ($this->http->wrapper()->post()->has('inst_language')) {
            $inst_language = $this->http->wrapper()->post()->retrieve(
                'inst_language',
                $this->refinery->kindlyTo()->string()
            );
        }
        $met_opr = '';
        if ($this->http->wrapper()->post()->has('met_opr')) {
            $met_opr = $this->http->wrapper()->post()->retrieve(
                'met_opr',
                $this->refinery->kindlyTo()->string()
            );
        }
        $duration = '';
        if ($this->http->wrapper()->post()->has('duration')) {
            $duration = $this->http->wrapper()->post()->retrieve(
                'duration',
                $this->refinery->kindlyTo()->string()
            );
        }
        $opr_language = '';
        if ($this->http->wrapper()->post()->has('opr_language')) {
            $opr_language = $this->http->wrapper()->post()->retrieve(
                'opr_language',
                $this->refinery->kindlyTo()->string()
            );
        }

        $this->md_section = $this->md_obj->getTechnical();
        $this->md_section->setSize($met_size);
        $this->md_section->setInstallationRemarks($met_inst);
        $this->md_section->setInstallationRemarksLanguage(new ilMDLanguageItem($inst_language));
        $this->md_section->setOtherPlatformRequirements($met_opr);
        $this->md_section->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($opr_language));
        $this->md_section->setDuration($duration);
        $this->md_section->update();

        // Format
        $met_format = [];
        if ($this->http->wrapper()->post()->has('met_format')) {
            $met_format = (array) $this->http->wrapper()->post()->retrieve(
                'met_format',
                $this->refinery->identity()
            );
        }
        foreach ($met_format as $id => $data) {
            $md_for = $this->md_section->getFormat($id);
            $md_for->setFormat(ilUtil::stripSlashes($data['Format']));
            $md_for->update();
        }
        // Location
        $met_location = [];
        if ($this->http->wrapper()->post()->has('met_location')) {
            $met_location = (array) $this->http->wrapper()->post()->retrieve(
                'met_location',
                $this->refinery->identity()
            );
        }
        foreach ($met_location as $id => $data) {
            $md_loc = $this->md_section->getLocation($id);
            $md_loc->setLocation(ilUtil::stripSlashes($data['Location']));
            $md_loc->setLocationType(ilUtil::stripSlashes($data['Type']));
            $md_loc->update();
        }
        $met_re = [];
        if ($this->http->wrapper()->post()->has('met_re')) {
            $met_re = (array) $this->http->wrapper()->post()->retrieve(
                'met_re',
                $this->refinery->identity()
            );
        }
        foreach ($met_re as $id => $data) {
            $md_re = $this->md_section->getRequirement($id);
            $md_re->setOperatingSystemName(ilUtil::stripSlashes($data['os']['name']));
            $md_re->setOperatingSystemMinimumVersion(ilUtil::stripSlashes($data['os']['MinimumVersion']));
            $md_re->setOperatingSystemMaximumVersion(ilUtil::stripSlashes($data['os']['MaximumVersion']));
            $md_re->setBrowserName(ilUtil::stripSlashes($data['browser']['name']));
            $md_re->setBrowserMinimumVersion(ilUtil::stripSlashes($data['browser']['MinimumVersion']));
            $md_re->setBrowserMaximumVersion(ilUtil::stripSlashes($data['browser']['MaximumVersion']));
            $md_re->update();
        }
        $this->callListeners('Technical');

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
        return true;
    }

    public function listTechnical(): bool
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
            $md_for = $this->md_section->getFormat($id);

            $this->tpl->setCurrentBlock("format_loop");

            $this->ctrl->setParameter($this, 'meta_index', $id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_format');
            $this->tpl->setVariable("FORMAT_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
            $this->tpl->setVariable("FORMAT_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("FORMAT_LOOP_NO", $id);
            $this->tpl->setVariable("FORMAT_LOOP_TXT_FORMAT", $this->lng->txt('meta_format'));
            $this->tpl->setVariable(
                "FORMAT_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput($md_for->getFormat())
            );

            $this->tpl->parseCurrentBlock();
        }
        // Size
        $this->tpl->setVariable("SIZE_TXT_SIZE", $this->lng->txt('meta_size'));
        $this->tpl->setVariable("SIZE_VAL", ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getSize()));

        // Location
        foreach ($ids = $this->md_section->getLocationIds() as $id) {
            $md_loc = $this->md_section->getLocation($id);

            $this->tpl->setCurrentBlock("location_loop");

            $this->ctrl->setParameter($this, 'meta_index', $id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_location');
            $this->tpl->setVariable("LOCATION_LOOP_ACTION_DELETE", $this->ctrl->getLinkTarget($this, 'deleteElement'));
            $this->tpl->setVariable("LOCATION_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("LOCATION_LOOP_TXT_LOCATION", $this->lng->txt('meta_location'));
            $this->tpl->setVariable("LOCATION_LOOP_NO", $id);
            $this->tpl->setVariable("LOCATION_LOOP_TXT_TYPE", $this->lng->txt('meta_type'));
            $this->tpl->setVariable(
                "LOCATION_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput($md_loc->getLocation())
            );

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
            $md_re = $this->md_section->getRequirement($id);

            $this->tpl->setCurrentBlock("requirement_loop");

            $this->ctrl->setParameter($this, 'meta_index', $id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_requirement');
            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_ACTION_DELETE",
                $this->ctrl->getLinkTarget($this, 'deleteElement')
            );
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
                ilLegacyFormElementsUtil::prepareFormOutput($md_re->getOperatingSystemMinimumVersion())
            );

            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MAXIMUMVERSION",
                ilLegacyFormElementsUtil::prepareFormOutput($md_re->getOperatingSystemMaximumVersion())
            );

            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_BROWSER_MINIMUMVERSION",
                ilLegacyFormElementsUtil::prepareFormOutput($md_re->getBrowserMinimumVersion())
            );

            $this->tpl->setVariable(
                "REQUIREMENT_LOOP_VAL_BROWSER_MAXIMUMVERSION",
                ilLegacyFormElementsUtil::prepareFormOutput($md_re->getBrowserMaximumVersion())
            );
            $this->tpl->parseCurrentBlock();
        }
        // OrComposite
        foreach ($ids = $this->md_section->getOrCompositeIds() as $or_id) {
            $md_or = $this->md_section->getOrComposite($or_id);
            foreach ($ids = $md_or->getRequirementIds() as $id) {
                $md_re = $this->md_section->getRequirement($id);

                $this->tpl->setCurrentBlock("orrequirement_loop");

                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_requirement');
                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_DELETE", $this->lng->txt('delete'));

                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_REQUIREMENT", $this->lng->txt('meta_requirement'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_TYPE", $this->lng->txt('meta_type'));
                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_TXT_OPERATINGSYSTEM",
                    $this->lng->txt('meta_operating_system')
                );
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_BROWSER", $this->lng->txt('meta_browser'));
                $this->tpl->setVariable("ORREQUIREMENT_LOOP_TXT_NAME", $this->lng->txt('meta_name'));
                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_TXT_MINIMUMVERSION",
                    $this->lng->txt('meta_minimum_version')
                );
                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_TXT_MAXIMUMVERSION",
                    $this->lng->txt('meta_maximum_version')
                );

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
                    ilLegacyFormElementsUtil::prepareFormOutput($md_re->getOperatingSystemMinimumVersion())
                );

                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MAXIMUMVERSION",
                    ilLegacyFormElementsUtil::prepareFormOutput($md_re->getOperatingSystemMaximumVersion())
                );

                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_BROWSER_MINIMUMVERSION",
                    ilLegacyFormElementsUtil::prepareFormOutput($md_re->getBrowserMinimumVersion())
                );

                $this->tpl->setVariable(
                    "ORREQUIREMENT_LOOP_VAL_BROWSER_MAXIMUMVERSION",
                    ilLegacyFormElementsUtil::prepareFormOutput($md_re->getBrowserMaximumVersion())
                );
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("orcomposite_loop");

            $this->ctrl->setParameter($this, 'meta_index', $or_id);
            $this->ctrl->setParameter($this, 'meta_path', 'meta_or_composite');
            $this->ctrl->setParameter($this, 'meta_technical', $this->md_section->getMetaId());
            $this->tpl->setVariable(
                "ORCOMPOSITE_LOOP_ACTION_DELETE",
                $this->ctrl->getLinkTarget($this, 'deleteElement')
            );
            $this->tpl->setVariable("ORCOMPOSITE_LOOP_TXT_DELETE", $this->lng->txt('delete'));

            $this->tpl->setVariable("ORCOMPOSITE_LOOP_TXT_ORCOMPOSITE", $this->lng->txt('meta_or_composite'));
            $this->tpl->parseCurrentBlock();
        }

        // InstallationRemarks
        $this->tpl->setVariable(
            "INSTALLATIONREMARKS_TXT_INSTALLATIONREMARKS",
            $this->lng->txt('meta_installation_remarks')
        );
        $this->tpl->setVariable("INSTALLATIONREMARKS_TXT_LANGUAGE", $this->lng->txt('meta_language'));

        $this->tpl->setVariable(
            "INSTALLATIONREMARKS_VAL",
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getInstallationRemarks())
        );
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
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getOtherPlatformRequirements())
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
        $this->tpl->setVariable(
            "DURATION_VAL",
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getDuration())
        );

        $this->tpl->setCurrentBlock("technical");
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
        $this->tpl->parseCurrentBlock();
        return true;
    }

    public function listLifecycle(): bool
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
        $this->tpl->setVariable(
            "VAL_VERSION",
            ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getVersion())
        );

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
                $this->tpl->setVariable(
                    "CONTRIBUTE_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
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
                    $this->tpl->setVariable(
                        "CONTRIBUTE_ENTITY_LOOP_ACTION_DELETE",
                        $this->ctrl->getLinkTarget($this, 'deleteElement')
                    );
                    $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("contribute_entity_loop");

                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_CONTRIBUTE_NO", $con_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_NO", $ent_id);
                $this->tpl->setVariable(
                    "CONTRIBUTE_ENTITY_LOOP_VAL_ENTITY",
                    ilLegacyFormElementsUtil::prepareFormOutput($md_ent->getEntity())
                );
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ENTITY", $this->lng->txt('meta_entity'));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("contribute_loop");

            $this->ctrl->setParameter($this, 'section_element', 'meta_entity');
            $this->ctrl->setParameter($this, 'meta_index', $con_id);
            $this->tpl->setVariable(
                "CONTRIBUTE_ENTITY_LOOP_ACTION_ADD",
                $this->ctrl->getLinkTarget($this, 'addSectionElement')
            );
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
            $this->tpl->setVariable(
                "CONTRIBUTE_LOOP_VAL_DATE",
                ilLegacyFormElementsUtil::prepareFormOutput($md_con->getDate())
            );

            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
        return true;
    }

    public function updateLifecycle(): bool
    {
        $lif_language = '';
        if ($this->http->wrapper()->post()->has('lif_language')) {
            $lif_language = $this->http->wrapper()->post()->retrieve(
                'lif_language',
                $this->refinery->kindlyTo()->string()
            );
        }
        $lif_version = '';
        if ($this->http->wrapper()->post()->has('lif_version')) {
            $lif_version = $this->http->wrapper()->post()->retrieve(
                'lif_version',
                $this->refinery->kindlyTo()->string()
            );
        }
        $lif_status = '';
        if ($this->http->wrapper()->post()->has('lif_status')) {
            $lif_status = $this->http->wrapper()->post()->retrieve(
                'lif_status',
                $this->refinery->kindlyTo()->string()
            );
        }

        // update metametadata section
        $this->md_section = $this->md_obj->getLifecycle();
        $this->md_section->setVersionLanguage(new ilMDLanguageItem($lif_language));
        $this->md_section->setVersion(ilUtil::stripSlashes($lif_version));
        $this->md_section->setStatus($lif_status);
        $this->md_section->update();

        // Identifier
        $ide_post = [];
        if ($this->http->wrapper()->post()->has('met_identifier')) {
            $ide_post = (array) $this->http->wrapper()->post()->retrieve(
                'met_identifier',
                $this->refinery->identity()
            );
        }
        foreach ($ide_post as $id => $data) {
            $md_ide = $this->md_section->getIdentifier($id);
            $md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog'] ?? ''));
            $md_ide->setEntry(ilUtil::stripSlashes($data['Entry'] ?? ''));
            $md_ide->update();
        }
        // Contribute
        $contribute_post = [];
        if ($this->http->wrapper()->post()->has('met_contribute')) {
            $contribute_post = (array) $this->http->wrapper()->post()->retrieve(
                'met_contribute',
                $this->refinery->identity()
            );
        }
        foreach ($contribute_post as $id => $cont_data) {
            $md_con = $this->md_section->getContribute($id);
            $md_con->setRole(ilUtil::stripSlashes($cont_data['Role'] ?? ''));
            $md_con->setDate(ilUtil::stripSlashes($cont_data['Date'] ?? ''));
            $md_con->update();

            $entity_post = [];
            if ($this->http->wrapper()->post()->has('met_entity')) {
                $entity_post = (array) $this->http->wrapper()->post()->retrieve(
                    'met_entity',
                    $this->refinery->identity()
                );
            }
            foreach (($entity_post[$id] ?? []) as $ent_id => $ent_data) {
                $md_ent = $md_con->getEntity($ent_id);
                $md_ent->setEntity(ilUtil::stripSlashes($ent_data['Entity']));
                $md_ent->update();
            }
        }
        $this->callListeners('Lifecycle');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
        return true;
    }

    public function listMetaMetaData(): bool
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

        $this->tpl->setVariable(
            "VAL_LANGUAGE",
            $this->__showLanguageSelect('met_language', $this->md_section->getLanguageCode())
        );
        $this->tpl->setVariable("TXT_METADATASCHEME", $this->lng->txt('meta_metadatascheme'));
        $this->tpl->setVariable("VAL_METADATASCHEME", $this->md_section->getMetaDataScheme());

        // Identifier
        foreach ($ids = $this->md_section->getIdentifierIds() as $id) {
            $md_ide = $this->md_section->getIdentifier($id);

            if (count($ids) > 1) {
                $this->ctrl->setParameter($this, 'meta_index', $id);
                $this->ctrl->setParameter($this, 'meta_path', 'meta_identifier');

                $this->tpl->setCurrentBlock("identifier_delete");
                $this->tpl->setVariable(
                    "IDENTIFIER_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
                $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("identifier_loop");
            $this->tpl->setVariable("IDENTIFIER_LOOP_NO", $id);
            $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
            $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG",
                ilLegacyFormElementsUtil::prepareFormOutput($md_ide->getCatalog())
            );
            $this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
            $this->tpl->setVariable(
                "IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY",
                ilLegacyFormElementsUtil::prepareFormOutput($md_ide->getEntry())
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
                $this->tpl->setVariable(
                    "CONTRIBUTE_LOOP_ACTION_DELETE",
                    $this->ctrl->getLinkTarget($this, 'deleteElement')
                );
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
                    $this->tpl->setVariable(
                        "CONTRIBUTE_ENTITY_LOOP_ACTION_DELETE",
                        $this->ctrl->getLinkTarget($this, 'deleteElement')
                    );
                    $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_DELETE", $this->lng->txt('delete'));
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("contribute_entity_loop");

                $this->ctrl->setParameter($this, 'section_element', 'meta_entity');
                $this->ctrl->setParameter($this, 'meta_index', $con_id);
                $this->tpl->setVariable(
                    "CONTRIBUTE_ENTITY_LOOP_ACTION_ADD",
                    $this->ctrl->getLinkTarget($this, 'addSectionElement')
                );
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ADD", $this->lng->txt('add'));

                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_CONTRIBUTE_NO", $con_id);
                $this->tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_NO", $ent_id);
                $this->tpl->setVariable(
                    "CONTRIBUTE_ENTITY_LOOP_VAL_ENTITY",
                    ilLegacyFormElementsUtil::prepareFormOutput($md_ent->getEntity())
                );
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
            $this->tpl->setVariable(
                "CONTRIBUTE_LOOP_VAL_DATE",
                ilLegacyFormElementsUtil::prepareFormOutput($md_con->getDate())
            );

            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt('save'));
        return true;
    }

    public function updateMetaMetaData(): bool
    {
        // update metametadata section
        $met_language = '';
        if ($this->http->wrapper()->post()->has('met_language')) {
            $met_language = (array) $this->http->wrapper()->post()->retrieve(
                'met_language',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->md_section = $this->md_obj->getMetaMetadata();
        $this->md_section->setLanguage(new ilMDLanguageItem($met_language));
        $this->md_section->update();

        // Identifier
        $met_identifier = [];
        if ($this->http->wrapper()->post()->has('met_identifier')) {
            $met_identifier = (array) $this->http->wrapper()->post()->retrieve(
                'met_identifier',
                $this->refinery->identity()
            );
        }
        foreach ($met_identifier as $id => $data) {
            $md_ide = $this->md_section->getIdentifier($id);
            $md_ide->setCatalog(ilUtil::stripSlashes($data['Catalog'] ?? ''));
            $md_ide->setEntry(ilUtil::stripSlashes($data['Entry'] ?? ''));
            $md_ide->update();
        }
        // Contribute
        $met_contribute = [];
        if ($this->http->wrapper()->post()->has('met_contribute')) {
            $met_contribute = (array) $this->http->wrapper()->post()->retrieve(
                'met_contribute',
                $this->refinery->identity()
            );
        }
        foreach ($met_contribute as $id => $cont_data) {
            $md_con = $this->md_section->getContribute($id);
            $md_con->setRole(ilUtil::stripSlashes($cont_data['Role'] ?? ''));
            $md_con->setDate(ilUtil::stripSlashes($cont_data['Date'] ?? ''));
            $md_con->update();

            $met_entity = [];
            if ($this->http->wrapper()->post()->has('met_entity')) {
                $met_entity = (array) $this->http->wrapper()->post()->retrieve(
                    'met_entity',
                    $this->refinery->identity()
                );
            }
            foreach ($met_entity[$id] as $ent_id => $ent_data) {
                $md_ent = $md_con->getEntity($ent_id);
                $md_ent->setEntity(ilUtil::stripSlashes($ent_data['Entity'] ?? ''));
                $md_ent->update();
            }
        }
        $this->callListeners('MetaMetaData');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
        return true;
    }

    public function listRights(): void
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
            $this->tpl->setVariable(
                "TXT_COPYRIGHTANDOTHERRESTRICTIONS",
                $this->lng->txt("meta_copyright_and_other_restrictions")
            );
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
            $this->tpl->setVariable(
                "DESCRIPTION_LOOP_VAL",
                ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getDescription())
            );
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

    public function updateRights(): void
    {
        // update rights section
        $rights_post = [];
        if ($this->http->wrapper()->post()->has('rights')) {
            $rights_post = $this->http->wrapper()->post()->retrieve(
                'rights',
                $this->refinery->identity()
            );
        }

        $this->md_section = $this->md_obj->getRights();
        $this->md_section->setCosts($rights_post['Cost'] ?? '');
        $this->md_section->setCopyrightAndOtherRestrictions($rights_post['CopyrightAndOtherRestrictions'] ?? '');
        $this->md_section->setDescriptionLanguage(new ilMDLanguageItem($rights_post['DescriptionLanguage'] ?? ''));
        $this->md_section->setDescription(ilUtil::stripSlashes($rights_post['Description'] ?? ''));
        $this->md_section->update();

        $this->callListeners('Rights');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    public function listEducational(): void
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

            $this->tpl->setVariable(
                "VAL_INTERACTIVITYTYPE_" . strtoupper($this->md_section->getInteractivityType()),
                " selected"
            );
            $this->tpl->setVariable(
                "VAL_LEARNINGRESOURCETYPE_" . strtoupper($this->md_section->getLearningResourceType()),
                " selected"
            );
            $this->tpl->setVariable(
                "VAL_INTERACTIVITYLEVEL_" . strtoupper($this->md_section->getInteractivityLevel()),
                " selected"
            );
            $this->tpl->setVariable(
                "VAL_SEMANTICDENSITY_" . strtoupper($this->md_section->getSemanticDensity()),
                " selected"
            );
            $this->tpl->setVariable(
                "VAL_INTENDEDENDUSERROLE_" . strtoupper($this->md_section->getIntendedEndUserRole()),
                " selected"
            );
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
            $tlt = array(0, 0, 0, 0, 0);
            $valid = true;

            if (!$tlt = ilMDUtils::_LOMDurationToArray($this->md_section->getTypicalLearningTime())) {
                if ($this->md_section->getTypicalLearningTime() !== '') {
                    $tlt = array(0, 0, 0, 0, 0);
                    $valid = false;
                }
            }

            $this->tpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
            $this->tpl->setVariable("SEL_MONTHS", $this->__buildMonthsSelect((string) ($tlt[0] ?? '')));
            $this->tpl->setVariable("SEL_DAYS", $this->__buildDaysSelect((string) ($tlt[1] ?? '')));

            $this->tpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
            $this->tpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));

            $this->tpl->setVariable("TXT_TYPICAL_LEARN_TIME", $this->lng->txt('meta_typical_learning_time'));
            $this->tpl->setVariable(
                "SEL_TLT",
                ilLegacyFormElementsUtil::makeTimeSelect(
                    'tlt',
                    $tlt[4] ? false : true,
                    $tlt[2],
                    $tlt[3],
                    $tlt[4],
                    false
                )
            );
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
                    $this->tpl->setVariable(
                        "TYPICALAGERANGE_LOOP_VAL",
                        ilLegacyFormElementsUtil::prepareFormOutput($md_age->getTypicalAgeRange())
                    );
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
                $this->tpl->setVariable(
                    "DESCRIPTION_LOOP_VAL",
                    ilLegacyFormElementsUtil::prepareFormOutput($md_des->getDescription())
                );
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

    public function updateEducational(): void
    {
        $educational_post = [];
        if ($this->http->wrapper()->post()->has('educational')) {
            $educational_post = $this->http->wrapper()->post()->retrieve(
                'educational',
                $this->refinery->identity()
            );
        }

        // update rights section
        $this->md_section = $this->md_obj->getEducational();
        $this->md_section->setInteractivityType($educational_post['InteractivityType'] ?? '');
        $this->md_section->setLearningResourceType($educational_post['LearningResourceType'] ?? '');
        $this->md_section->setInteractivityLevel($educational_post['InteractivityLevel'] ?? '');
        $this->md_section->setSemanticDensity($educational_post['SemanticDensity'] ?? '');
        $this->md_section->setIntendedEndUserRole($educational_post['IntendedEndUserRole'] ?? '');
        $this->md_section->setContext($educational_post['Context'] ?? '');
        $this->md_section->setDifficulty($educational_post['Difficulty'] ?? '');

        $tlt_post = [];
        if ($this->http->wrapper()->post()->has('tlt')) {
            $tlt_post = $this->http->wrapper()->post()->retrieve(
                'tlt',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $this->md_section->setPhysicalTypicalLearningTime(
            $tlt_post['mo'] ?? 0,
            $tlt_post['d'] ?? 0,
            $tlt_post['h'] ?? 0,
            $tlt_post['m'] ?? 0,
            $tlt_post['s'] ?? 0
        );
        $this->callListeners('Educational');

        /* TypicalAgeRange */
        foreach ($ids = $this->md_section->getTypicalAgeRangeIds() as $id) {
            $md_age = $this->md_section->getTypicalAgeRange($id);
            $md_age->setTypicalAgeRange(ilUtil::stripSlashes($educational_post['TypicalAgeRange'][$id]['Value'] ?? ''));
            $md_age->setTypicalAgeRangeLanguage(
                new ilMDLanguageItem($educational_post['TypicalAgeRange'][$id]['Language'] ?? '')
            );
            $md_age->update();
        }

        /* Description */
        foreach ($ids = $this->md_section->getDescriptionIds() as $id) {
            $md_des = $this->md_section->getDescription($id);
            $md_des->setDescription(ilUtil::stripSlashes($educational_post['Description'][$id]['Value'] ?? ''));
            $md_des->setDescriptionLanguage(
                new ilMDLanguageItem($educational_post['Description'][$id]['Language'] ?? '')
            );
            $md_des->update();
        }

        /* Language */
        foreach ($ids = $this->md_section->getLanguageIds() as $id) {
            $md_lang = $this->md_section->getLanguage($id);
            $md_lang->setLanguage(
                new ilMDLanguageItem($educational_post['Language'][$id] ?? '')
            );
            $md_lang->update();
        }

        $this->md_section->update();

        $this->callListeners('Educational');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    public function listRelation(): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_relation');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_relation.html', 'Services/MetaData');

        $rel_ids = $this->md_obj->getRelationIds();
        if ($rel_ids === []) {
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
                        ilLegacyFormElementsUtil::prepareFormOutput($ident->getCatalog())
                    );
                    $this->tpl->setVariable(
                        "IDENTIFIER_LOOP_VAL_ENTRY",
                        ilLegacyFormElementsUtil::prepareFormOutput($ident->getEntry())
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
                        ilLegacyFormElementsUtil::prepareFormOutput($des->getDescription())
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

    public function updateRelation(): void
    {
        $relation_post = [];
        if ($this->http->wrapper()->post()->has('relation')) {
            $relation_post = $this->http->wrapper()->post()->retrieve(
                'relation',
                $this->refinery->identity()
            );
        }
        // relation
        foreach ($ids = $this->md_obj->getRelationIds() as $id) {
            // kind
            $relation = $this->md_obj->getRelation($id);
            $relation->setKind((string) ($relation_post[$id]['Kind'] ?? ''));
            $relation->update();

            // identifiers
            $res_idents = $relation->getIdentifier_Ids();
            foreach ($res_idents as $res_id) {
                $ident = $relation->getIdentifier_($res_id);
                $ident->setCatalog(ilUtil::stripSlashes($relation_post['Resource']['Identifier'][$res_id]['Catalog'] ?? ''));
                $ident->setEntry(ilUtil::stripSlashes($relation_post['Resource']['Identifier'][$res_id]['Entry'] ?? ''));
                $ident->update();
            }

            // descriptions
            $res_dess = $relation->getDescriptionIds();
            foreach ($res_dess as $res_des) {
                $des = $relation->getDescription($res_des);
                $des->setDescription(ilUtil::stripSlashes($relation_post['Resource']['Description'][$res_des]['Value'] ?? ''));
                $des->setDescriptionLanguage(
                    new ilMDLanguageItem($relation_post['Resource']['Description'][$res_des]['Language'] ?? '')
                );
                $des->update();
            }
        }

        $this->callListeners('Relation');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    public function listAnnotation(): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_annotation');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_annotation.html', 'Services/MetaData');

        $anno_ids = $this->md_obj->getAnnotationIds();
        if ($anno_ids === []) {
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
                $this->tpl->setVariable(
                    "VAL_ENTITY",
                    ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getEntity())
                );
                $this->tpl->setVariable("TXT_DATE", $this->lng->txt("meta_date"));
                $this->tpl->setVariable(
                    "VAL_DATE",
                    ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getDate())
                );

                /* Description */
                $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
                $this->tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
                $this->tpl->setVariable(
                    "VAL_DESCRIPTION",
                    ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getDescription())
                );
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

    public function updateAnnotation(): void
    {
        $annotation_post = [];
        if ($this->http->wrapper()->post()->has('annotation')) {
            $annotation_post = $this->http->wrapper()->post()->retrieve(
                'annotation',
                $this->refinery->identity()
            );
        }

        // relation
        foreach ($ids = $this->md_obj->getAnnotationIds() as $id) {
            // entity
            $annotation = $this->md_obj->getAnnotation($id);
            $annotation->setEntity(ilUtil::stripSlashes($annotation_post[$id]['Entity'] ?? ''));
            $annotation->setDate(ilUtil::stripSlashes($annotation_post[$id]['Date'] ?? ''));
            $annotation->setDescription(ilUtil::stripSlashes($annotation_post[$id]['Description'] ?? ''));
            $annotation->setDescriptionLanguage(
                new ilMDLanguageItem($annotation_post[$id]['Language'])
            );
            $annotation->update();
        }

        $this->callListeners('Annotation');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    public function listClassification(): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.md_editor.html', 'Services/MetaData');
        $this->__setTabs('meta_classification');
        $this->tpl->addBlockFile('MD_CONTENT', 'md_content', 'tpl.md_classification.html', 'Services/MetaData');

        $class_ids = $this->md_obj->getClassificationIds();
        if ($class_ids === []) {
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
                        $this->tpl->setVariable(
                            "TAXONPATH_TAXON_LOOP_VAL_TAXON",
                            ilLegacyFormElementsUtil::prepareFormOutput($taxon->getTaxon())
                        );
                        $this->tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_ID", $this->lng->txt("meta_id"));
                        $this->tpl->setVariable(
                            "TAXONPATH_TAXON_LOOP_VAL_ID",
                            ilLegacyFormElementsUtil::prepareFormOutput($taxon->getTaxonId())
                        );
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
                    $this->tpl->setVariable(
                        "TAXONPATH_LOOP_VAL_SOURCE",
                        ilLegacyFormElementsUtil::prepareFormOutput($tax_path->getSource())
                    );
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
                    ilLegacyFormElementsUtil::prepareFormOutput($this->md_section->getDescription())
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
                        ilLegacyFormElementsUtil::prepareFormOutput($keyword->getKeyword())
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
                $this->tpl->setVariable(
                    "TXT_ACCESSIBILITYRESTRICTIONS",
                    $this->lng->txt("meta_accessibility_restrictions")
                );
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

    public function updateClassification(): void
    {
        $classification_post = [];
        if ($this->http->wrapper()->post()->has('classification')) {
            $classification_post = $this->http->wrapper()->post()->retrieve(
                'classification',
                $this->refinery->identity()
            );
        }

        // relation
        foreach ($ids = $this->md_obj->getClassificationIds() as $id) {
            // entity
            $classification = $this->md_obj->getClassification($id);
            $classification->setPurpose($classification_post[$id]['Purpose'] ?? '');

            $classification->setDescription(ilUtil::stripSlashes($classification_post[$id]['Description'] ?? ''));
            $classification->setDescriptionLanguage(
                new ilMDLanguageItem($classification_post[$id]['Language'] ?? '')
            );

            $classification->update();

            $key_ids = $classification->getKeywordIds();
            foreach ($key_ids as $key_id) {
                $keyword = $classification->getKeyword($key_id);
                $keyword->setKeyword(ilUtil::stripSlashes($classification_post['Keyword'][$key_id]['Value'] ?? ''));
                $keyword->setKeywordLanguage(
                    new ilMDLanguageItem($classification_post['Keyword'][$key_id]['Language'] ?? '')
                );
                $keyword->update();
            }

            $tp_ids = $classification->getTaxonPathIds();
            foreach ($tp_ids as $tp_id) {
                $tax_path = $classification->getTaxonPath($tp_id);
                $tax_path->setSource(ilUtil::stripSlashes($classification_post['TaxonPath'][$tp_id]['Source']['Value'] ?? ''));
                $tax_path->setSourceLanguage(
                    new ilMDLanguageItem((string) ($classification_post['TaxonPath'][$tp_id]['Source']['Language'] ?? ''))
                );
                $tax_path->update();

                $tax_ids = $tax_path->getTaxonIds();

                foreach ($tax_ids as $tax_id) {
                    $taxon = $tax_path->getTaxon($tax_id);
                    $taxon->setTaxon(ilUtil::stripSlashes($classification_post['TaxonPath']['Taxon'][$tax_id]['Value'] ?? ''));
                    $taxon->setTaxonLanguage(
                        new ilMDLanguageItem((string) ($classification_post['TaxonPath']['Taxon'][$tax_id]['Language'] ?? ''))
                    );
                    $taxon->setTaxonId(ilUtil::stripSlashes($classification_post['TaxonPath']['Taxon'][$tax_id]['Id'] ?? ''));
                    $taxon->update();
                }
            }
        }

        $this->callListeners('Classification');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->listSection();
    }

    public function deleteElement(): bool
    {
        $meta_path = '';
        if ($this->http->wrapper()->query()->has('meta_path')) {
            $meta_path = $this->http->wrapper()->query()->retrieve(
                'meta_path',
                $this->refinery->kindlyTo()->string()
            );
        }

        $meta_technical = 0;
        if ($this->http->wrapper()->query()->has('meta_technical')) {
            $meta_technical = $this->http->wrapper()->query()->retrieve(
                'meta_technical',
                $this->refinery->kindlyTo()->int()
            );
        }

        $md_element = ilMDFactory::_getInstance($meta_path, $this->initMetaIndexFromQuery(), $meta_technical);
        $md_element->delete();

        $this->listSection();

        return true;
    }

    public function deleteSection(): bool
    {
        $md_element = ilMDFactory::_getInstance($this->initSectionFromQuery(), $this->initMetaIndexFromQuery());
        $md_element->delete();

        $this->listSection();

        return true;
    }

    public function addSection(): bool
    {
        // Switch section
        switch ($this->initSectionFromQuery()) {
            case 'meta_technical':
                $this->md_section = $this->md_obj->addTechnical();
                $this->md_section->save();
                break;

            case 'meta_lifecycle':
                $this->md_section = $this->md_obj->addLifecycle();
                $this->md_section->save();
                $con = $this->md_section->addContribute();
                $con->save();

                $ent = $con->addEntity();
                $ent->save();
                break;

            case 'meta_meta_metadata':
                $this->md_section = $this->md_obj->addMetaMetadata();
                $this->md_section->save();

                $ide = $this->md_section->addIdentifier();
                $ide->save();

                $con = $this->md_section->addContribute();
                $con->save();

                $ent = $con->addEntity();
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

                $taxon_path = $this->md_section->addTaxonPath();
                $taxon_path->save();

                $taxon = $taxon_path->addTaxon();
                $taxon->save();

                $key = $this->md_section->addKeyword();
                $key->save();
                break;
        }

        $this->listSection();
        return true;
    }

    public function addSectionElement(): bool
    {
        $section_element = '';
        if ($this->http->wrapper()->query()->has('section_element')) {
            $section_element = $this->http->wrapper()->query()->retrieve(
                'section_element',
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($this->http->wrapper()->post()->has('section_element')) {
            $section_element = $this->http->wrapper()->query()->retrieve(
                'section_element',
                $this->refinery->kindlyTo()->string()
            );
        }

        // Switch section
        switch ($this->initSectionFromQuery()) {
            case 'meta_technical':
                $this->md_section = $this->md_obj->getTechnical();
                break;

            case 'meta_lifecycle':
                $this->md_section = $this->md_obj->getLifecycle();
                break;

            case 'meta_meta_metadata':
                $this->md_section = $this->md_obj->getMetaMetadata();
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
                $this->md_section = $this->md_obj->getClassification((int) ($arr[1] ?? 0));
                break;
        }

        // Switch new element
        $md_new = null;
        switch ($section_element) {
            case 'meta_or_composite':
                $md_new = $this->md_section->addOrComposite();
                $md_new = $md_new->addRequirement();
                break;

            case 'meta_requirement':
                $md_new = $this->md_section->addRequirement();
                break;

            case 'meta_location':
                $md_new = $this->md_section->addLocation();
                break;

            case 'meta_format':
                $md_new = $this->md_section->addFormat();
                break;

            case 'meta_entity':
                $md_new = $this->md_section->getContribute($this->initMetaIndexFromQuery());
                $md_new = $md_new->addEntity();
                break;

            case 'meta_identifier':
                $md_new = $this->md_section->addIdentifier();
                break;

            case 'meta_contribute':
                $md_new = $this->md_section->addContribute();
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
                $rel = $this->md_obj->getRelation($this->initMetaIndexFromQuery());
                $md_new = $rel->addIdentifier_();
                break;

            case 'relation_resource_description':
                $rel = $this->md_obj->getRelation($this->initMetaIndexFromQuery());
                $md_new = $rel->addDescription();
                break;

            case 'TaxonPath':
                $md_new = $this->md_section->addTaxonPath();
                $md_new->save();
                $md_new = $md_new->addTaxon();
                break;

            case 'Taxon':
                $tax_path = $this->md_section->getTaxonPath($this->initMetaIndexFromQuery());
                $md_new = $tax_path->addTaxon();
                break;
        }

        $md_new->save();

        $this->listSection();

        return true;
    }

    public function listSection(): void
    {
        switch ($_REQUEST['section']) {
            case 'meta_general':
                $this->listGeneral();
                break;

            case 'meta_lifecycle':
                $this->listLifecycle();
                break;

            case 'meta_technical':
                $this->listTechnical();
                break;

            case 'meta_meta_metadata':
                $this->listMetaMetaData();
                break;

            case 'debug':
                $this->debug();
                break;

            case 'meta_rights':
                $this->listRights();
                break;

            case 'meta_educational':
                $this->listEducational();
                break;

            case 'meta_relation':
                $this->listRelation();
                break;

            case 'meta_annotation':
                $this->listAnnotation();
                break;

            case 'meta_classification':
                $this->listClassification();
                break;

            default:
                if ($this->md_obj->getObjType() === 'sahs' || $this->md_obj->getObjType() === 'sco') {
                    $this->listQuickEdit_scorm();
                    break;
                } else {
                    $this->listQuickEdit();
                    break;
                }
        }
    }

    // PRIVATE
    public function __fillSubelements(): void
    {
        if (count($subs = $this->md_section->getPossibleSubelements())) {
            //$subs = array_merge(array('' => 'meta_please_select'),$subs);

            $this->tpl->setCurrentBlock("subelements");
            $this->tpl->setVariable(
                "SEL_SUBELEMENTS",
                ilLegacyFormElementsUtil::formSelect('', 'section_element', $subs)
            );
            $this->tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setVariable("TXT_ADD", $this->lng->txt('meta_add'));
        }
    }

    public function __setTabs(string $a_active): void
    {
        $tabs = array(
            'meta_quickedit' => 'listQuickEdit',
            'meta_general' => 'listGeneral',
            'meta_lifecycle' => 'listLifecycle',
            'meta_meta_metadata' => 'listMetaMetadata',
            'meta_technical' => 'listTechnical',
            'meta_educational' => 'listEducational',
            'meta_rights' => 'listRights',
            'meta_relation' => 'listRelation',
            'meta_annotation' => 'listAnnotation',
            'meta_classification' => 'listClassification'
        );

        if (DEVMODE) {
            $tabs['debug'] = 'debug';
        }

        $section = new ilSelectInputGUI($this->lng->txt("meta_section"), "section");

        $options = array();
        foreach (array_keys($tabs) as $key) {
            $options[$key] = $this->lng->txt($key);
        }
        $section->setOptions($options);
        $section->setValue($a_active);

        $this->toolbarGUI->addStickyItem($section, true);

        $button = ilSubmitButton::getInstance();
        $button->setCaption("show");
        $button->setCommand("listSection");
        $this->toolbarGUI->addStickyItem($button);

        $this->toolbarGUI->setFormAction($this->ctrl->getFormAction($this, "listSection"));
    }

    public function __showLanguageSelect(string $a_name, string $a_value = ""): string
    {
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

            if ($a_value !== "" && $a_value === $code) {
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

    public function __buildMonthsSelect(string $sel_month): string
    {
        $options = [];
        for ($i = 0; $i <= 24; $i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilLegacyFormElementsUtil::formSelect($sel_month, 'tlt[mo]', $options, false, true);
    }

    public function __buildDaysSelect(string $sel_day): string
    {
        $options = [];
        for ($i = 0; $i <= 31; $i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilLegacyFormElementsUtil::formSelect($sel_day, 'tlt[d]', $options, false, true);
    }

    // Observer methods
    public function addObserver(object $a_class, string $a_method, string $a_element): bool
    {
        $this->observers[$a_element]['class'] = $a_class;
        $this->observers[$a_element]['method'] = $a_method;

        return true;
    }

    /**
     * @return mixed
     */
    public function callListeners(string $a_element)
    {
        if (isset($this->observers[$a_element])) {
            $class = &$this->observers[$a_element]['class'];
            $method = $this->observers[$a_element]['method'];

            return $class->$method($a_element);
        }
        return '';
    }

    protected function getChangeCopyrightModal(): ?Interruptive
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
