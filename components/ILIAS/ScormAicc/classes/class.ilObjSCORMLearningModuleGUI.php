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

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Repository\Filter\FilterAdapterGUI;
use ILIAS\UI\Component\Dropdown\Standard as DropdownStandard;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id$
*
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI, ilContainerResourceGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilCertificateGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilSCORMTrackingItemsTableGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilLTIProviderObjectSettingGUI
*
* @ingroup components\ILIASScormAicc
*/
class ilObjSCORMLearningModuleGUI extends ilObjSAHSLearningModuleGUI
{
    private ilPropertyFormGUI $form;
    protected Container $dic;
    protected const EXPORT_BY_SCO = 'by_sco';
    protected const EXPORT_BY_USER = 'by_user';
    protected const TRACKING_FILTER_BY_SCO = 'sahs_tracking_by_sco_';
    protected const TRACKING_FILTER_BY_USER = 'sahs_tracking_by_user_';

    protected int $refId;
    protected ilCtrl $ctrl;

    protected const ADOPT_SOURCE_REF_ID = 'source_ref_id';
    protected const ADOPT_TREE_PARENT_REF_ID = 'sahs_adopt_tree_parent_ref_id';
    protected const ADOPT_TREE_MAX_SYNC_DEPTH = 2;

    /**
    * Constructor
    */
    public function __construct($data, int $id, bool $call_by_reference, bool $prepare_output = true) //missing typehint because mixed
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];

        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("search");

        $this->refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());

        $this->type = "sahs";
        parent::__construct($data, $id, $call_by_reference, false);
    }

    /**
     * assign scorm object to scorm gui object
     */
    protected function assignObject(): void
    {
        if ($this->id != 0) {
            if ($this->call_by_reference) {
                $this->object = new ilObjSCORMLearningModule((int) $this->id, true);
            } else {
                $this->object = new ilObjSCORMLearningModule((int) $this->id, false);
            }
        }
    }

    /**
    * scorm module properties
    */
    public function properties(): void
    {
        global $DIC;
        $ilToolbar = $DIC->toolbar();
        $ilTabs = $DIC->tabs();

        //        $lng->loadLanguageModule("style");

        $this->setSettingsSubTabs();
        $ilTabs->setSubTabActive('general');//cont_settings

        // view
        $ilToolbar->addComponent($this->object->getViewButton());

        // lm properties
        $this->initPropertiesForm();
        $this->getPropertiesFormValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Init properties form
     */
    public function initPropertiesForm(): void
    {
        $obj_service = $this->object_service;
        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle($this->lng->txt("cont_lm_properties"));

        //check/select only once
        $this->object->checkMasteryScoreValues();

        //title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "Fobject_title");
        $ti->setMaxLength(200);
        $this->form->addItem($ti);

        //description
        $ti = new ilTextAreaInputGUI($this->lng->txt("description"), "Fobject_description");
        $ti->setCols(40);
        $ti->setRows(2);
        $this->form->addItem($ti);

        // SCORM-type
        $ne = new ilNonEditableValueGUI($this->lng->txt("type"), "");
        $ne->setValue($this->lng->txt("lm_type_" . ilObjSAHSLearningModule::_lookupSubType($this->object->getID())));
        $this->form->addItem($ne);

        // version
        $ne = new ilNonEditableValueGUI($this->lng->txt("cont_sc_version"), "");
        $ne->setValue($this->object->getModuleVersion());
        $ne->setInfo($this->lng->txt("cont_sc_version_info"));
        $this->form->addItem($ne);

        //
        // activation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("activation"));
        $this->form->addItem($sh);

        // online
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
        $cb->setInfo($this->lng->txt("cont_online_info"));
        $this->form->addItem($cb);


        //
        // presentation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_presentation"));
        $this->form->addItem($sh);

        $radg = new ilRadioGroupInputGUI($this->lng->txt("cont_open"), "open_mode");
        $op0 = new ilRadioOption($this->lng->txt("cont_open_normal"), "0");
        $op0->setInfo($this->lng->txt("cont_open_normal_info"));
        $radg->addOption($op0);
        $op1 = new ilRadioOption($this->lng->txt("cont_open_iframe"), "1");
        $op1->setInfo($this->lng->txt("cont_open_iframe_info"));
        $radg->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("cont_open_window"), "5");
        $op2->setInfo($this->lng->txt("cont_open_window_info"));
        $radg->addOption($op2);

        // width
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $op2->addSubItem($ni);
        // height
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op2->addSubItem($ni);

        $this->form->addItem($radg);

        // auto navigation to last visited item
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_last_visited"), "cobj_auto_last_visited");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_auto_last_visited_info"));
        $this->form->addItem($cb);

        // auto continue
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_auto_continue"), "auto_continue");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_sc_auto_continue_info"));
        $this->form->addItem($cb);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

        //
        // scorm options
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_scorm_options"));
        $this->form->addItem($sh);

        // lesson mode
        $radg = new ilRadioGroupInputGUI($this->lng->txt("cont_def_lesson_mode"), "lesson_mode");
        $op0 = new ilRadioOption($this->lng->txt("cont_sc_less_mode_normal"), "normal");
        $op0->setInfo($this->lng->txt("cont_sc_less_mode_normal_info"));
        $radg->addOption($op0);
        $op1 = new ilRadioOption($this->lng->txt("cont_sc_less_mode_browse"), "browse");
        $op1->setInfo($this->lng->txt("cont_sc_less_mode_browse_info"));
        $radg->addOption($op1);


        // set lesson mode review when completed
        $options = array(
            "n" => $this->lng->txt("cont_sc_auto_review_no"),
            "y" => $this->lng->txt("cont_sc_auto_review_completed_or_passed"),
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_auto_review_2004"), "auto_review");
        $si->setOptions($options);
        // $si->setInfo($this->lng->txt("cont_sc_auto_review_info_12"));
        $op0->addSubItem($si);
        // end lesson mode
        $this->form->addItem($radg);

        // mastery_score
        if ($this->object->getMasteryScoreValues() != "") {
            $ni = new ilNumberInputGUI($this->lng->txt("cont_mastery_score_12"), "mastery_score");
            $ni->setMaxLength(3);
            $ni->setSize(3);
            $ni->setInfo($this->lng->txt("cont_mastery_score_12_info") . ' ' . $this->object->getMasteryScoreValues());
            $this->form->addItem($ni);
        }

        //
        // rte settings
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_rte_settings"));
        $this->form->addItem($sh);

        // unlimited session timeout
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_usession"), "cobj_session");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_sc_usession_info"));
        $this->form->addItem($cb);

        // storage of interactions
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_interactions"), "cobj_interactions");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_interactions_info_12"));
        $this->form->addItem($cb);

        // objectives
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_objectives"), "cobj_objectives");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_objectives_info"));
        $this->form->addItem($cb);

        // time from lms
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_time_from_lms"), "cobj_time_from_lms");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_time_from_lms_info"));
        $this->form->addItem($cb);

        // check values
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_check_values"), "cobj_check_values");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_check_values_info"));
        $this->form->addItem($cb);

        // auto cmi.core.exit to suspend
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_suspend"), "cobj_auto_suspend");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_auto_suspend_info"));
        $this->form->addItem($cb);

        // settings for student_id
        $options = array(
            0 => $this->lng->txt("cont_sc_id_setting_user_id"),
            1 => $this->lng->txt("cont_sc_id_setting_user_login"),
            2 => $this->lng->txt("cont_sc_id_setting_user_id_plus_ref_id"),
            3 => $this->lng->txt("cont_sc_id_setting_user_login_plus_ref_id"),
            4 => $this->lng->txt("cont_sc_id_setting_user_id_plus_obj_id"),
            5 => $this->lng->txt("cont_sc_id_setting_user_login_plus_obj_id")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_id_setting"), "id_setting");
        $si->setOptions($options);
        $si->setInfo($this->lng->txt("cont_sc_id_setting_info"));
        $this->form->addItem($si);

        // settings for student_name
        $options = array(
            0 => $this->lng->txt("cont_sc_name_setting_last_firstname"),
            1 => $this->lng->txt("cont_sc_name_setting_first_lastname"),
            2 => $this->lng->txt("cont_sc_name_setting_fullname"),
            3 => $this->lng->txt("cont_sc_name_setting_salutation_lastname"),
            4 => $this->lng->txt("cont_sc_name_setting_first_name"),
            9 => $this->lng->txt("cont_sc_name_setting_no_name")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_name_setting"), "name_setting");
        $si->setOptions($options);
        $si->setInfo($this->lng->txt("cont_sc_name_setting_info"));
        $this->form->addItem($si);

        //
        // debugging
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_debugging"));
        $this->form->addItem($sh);

        // test tool
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_debug"), "cobj_debug");
        $cb->setValue("y");
        if ($this->object->getDebugActivated() == false) {
            $cb->setDisabled(true);
            $cb->setInfo($this->lng->txt("cont_debug_deactivated"));
        } else {
            $cb->setInfo($this->lng->txt("cont_debug_deactivate12"));
        }
        $this->form->addItem($cb);
        $this->form->addCommandButton("saveProperties", $this->lng->txt("save"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }


    /**
     * Get values for properties form
     */
    public function getPropertiesFormValues(): void
    {
        $values = array();
        $values["Fobject_title"] = $this->object->getTitle();
        $values["Fobject_description"] = $this->object->getLongDescription();
        if (!$this->object->getOfflineStatus()) {
            $values["cobj_online"] = true;
        }
        //        $values["cobj_offline_mode"] = $this->object->getOfflineMode();
        $values["open_mode"] = $this->object->getOpenMode();
        $values["width_0"] = $this->object->getWidth();
        $values["width_1"] = $this->object->getWidth();
        $values["height_0"] = $this->object->getHeight();
        $values["height_1"] = $this->object->getHeight();
        $values["cobj_auto_last_visited"] = $this->object->getAuto_last_visited();
        $values["auto_continue"] = $this->object->getAutoContinue();
        $values["lesson_mode"] = $this->object->getDefaultLessonMode();
        $values["auto_review"] = $this->object->getAutoReviewChar();
        $values["mastery_score"] = $this->object->getMasteryScore();
        $values["cobj_session"] = $this->object->getSession();
        $values["cobj_interactions"] = $this->object->getInteractions();
        $values["cobj_objectives"] = $this->object->getObjectives();
        $values["cobj_time_from_lms"] = $this->object->getTime_from_lms();
        $values["cobj_check_values"] = $this->object->getCheck_values();
        $values["cobj_auto_suspend"] = $this->object->getAutoSuspend();
        $values["id_setting"] = $this->object->getIdSetting();
        $values["name_setting"] = $this->object->getNameSetting();
        $values["cobj_debug"] = $this->object->getDebug();
        $this->form->setValuesByArray($values);
    }

    public function adoptSettings(): void
    {

        if (!$this->dic->access()->checkAccess("write", "", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_write"), true);
            ilObjectGUI::_gotoRepositoryRoot();
            return;
        }

        $this->setSettingsSubTabs();
        $this->dic->tabs()->setSubTabActive('sahs_adopt_settings');
        $this->renderAdoptSettingsForm($this->buildAdoptSettingsForm());
    }

    public function applyAdoptSettings(): void
    {

        if (!$this->dic->access()->checkAccess("write", "", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_write"), true);
            ilObjectGUI::_gotoRepositoryRoot();
            return;
        }

        $this->setSettingsSubTabs();
        $this->dic->tabs()->setSubTabActive('sahs_adopt_settings');

        $form = $this->buildAdoptSettingsForm()->withRequest($this->dic->http()->request());
        $data = $form->getData();

        if (!is_array($data)) {
            $this->renderAdoptSettingsForm($form);
            return;
        }

        $source_ref_id = $this->extractSourceRefId($data[self::ADOPT_SOURCE_REF_ID] ?? null);
        if ($source_ref_id <= 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('sahs_adopt_settings_invalid_source'), true);
            $this->ctrl->redirect($this, 'adoptSettings');
        }

        $source_obj_id = ilObject::_lookupObjectId($source_ref_id);
        if ($source_obj_id === $this->object->getId()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('sahs_adopt_settings_invalid_source'), true);
            $this->ctrl->redirect($this, 'adoptSettings');
        }

        if (!$this->dic->access()->checkAccess("write", "", $source_ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('sahs_adopt_settings_source_no_write'), true);
            $this->ctrl->redirect($this, 'adoptSettings');
        }

        if ($source_obj_id <= 0 || ilObject::_lookupType($source_obj_id) !== "sahs") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('sahs_adopt_settings_invalid_source'), true);
            $this->ctrl->redirect($this, 'adoptSettings');
        }

        $target_subtype = $this->object->getSubType();
        $source_subtype = ilObjSAHSLearningModule::_lookupSubType($source_obj_id);
        if ($target_subtype !== $source_subtype) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('sahs_adopt_settings_subtype_mismatch'), true);
            $this->ctrl->redirect($this, 'adoptSettings');
        }

        $source = new ilObjSAHSLearningModule($source_ref_id, true);

        if (!$this->object->adoptSettingsFrom($source)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('sahs_adopt_settings_subtype_mismatch'), true);
            $this->ctrl->redirect($this, 'adoptSettings');
        }
        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('sahs_adopt_settings_success'), true);
        $this->ctrl->redirect($this, 'properties');
    }

    public function loadAdoptSettingsTreeNodes(): void
    {
        if (!$this->ctrl->isAsynch()) {
            throw new \RuntimeException(self::class . '::' . __FUNCTION__ . ' must be called asynchronously.');
        }

        if (!$this->dic->access()->checkAccess("write", "", $this->object->getRefId())) {
            $this->sendAdoptSettingsTreeResponse('');
            return;
        }

        [, $parent_id_parameter] = $this->buildAdoptSettingsTreeUrlBuilder();
        $parent_id_parameter_name = $parent_id_parameter->getName();

        $query = $this->dic->http()->wrapper()->query();
        if (!$query->has($parent_id_parameter_name)) {
            $this->sendAdoptSettingsTreeResponse('');
            return;
        }

        $parent_ref_id = (int) $query->retrieve(
            $parent_id_parameter_name,
            $this->dic->refinery()->kindlyTo()->int()
        );
        if ($parent_ref_id <= 0) {
            $this->sendAdoptSettingsTreeResponse('');
            return;
        }

        $factory = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();
        $node_retrieval = $this->buildAdoptSettingsNodeRetrieval();
        $node_generator = $node_retrieval->getNodes(
            $factory->input()->field()->node(),
            $factory->symbol()->icon(),
            [],
            (string) $parent_ref_id,
        );

        $html = '';
        foreach ($node_generator as $node) {
            $html .= $renderer->renderAsync($node);
        }

        $this->sendAdoptSettingsTreeResponse($html);
    }

    protected function buildAdoptSettingsForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $factory = $this->dic->ui()->factory();


        $source_input = $factory->input()->field()->treeSelect(
            $this->buildAdoptSettingsNodeRetrieval(),
            $this->lng->txt('sahs_adopt_settings_source'),
            $this->lng->txt('sahs_adopt_settings_source_info')
        )->withAdditionalTransformation(
            $this->dic->refinery()->custom()->constraint(
                static function ($value): bool {
                    if (is_array($value) && isset($value[0])) {
                        return (int) $value[0] > 0;
                    }
                    if (is_scalar($value)) {
                        return (int) $value > 0;
                    }
                    return false;
                },
                $this->lng->txt('sahs_adopt_settings_source_required')
            )
        )->withAdditionalOnLoadCode(
            static function (string $id): string {
                return <<<JS
const field_input = document.getElementById('$id');
if (field_input) {
    const tree_select = field_input.closest('.c-input-tree_select');
    if (tree_select) {
        const hide_non_leaf_selection = () => {
            tree_select.querySelectorAll('.c-input-node:not(.c-input-node__leaf) > .c-input-node__select')
                .forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                    const glyph = button.querySelector('.glyph');
                    if (glyph) {
                        glyph.classList.add('disabled');
                    }
                    const node = button.closest('.c-input-node');
                    const drilldown_trigger = node
                        ? node.querySelector('.c-drilldown__menulevel--trigger')
                        : null;
                    if (drilldown_trigger instanceof HTMLElement) {
                        drilldown_trigger.style.setProperty('width', '100%', 'important');
                    }
                    button.hidden = true;
                    button.style.display = 'none';
                    button.setAttribute('aria-hidden', 'true');
                    button.tabIndex = -1;
                    button.style.pointerEvents = 'none';
                });
        };
        if (typeof MutationObserver === 'function') {
            new MutationObserver(hide_non_leaf_selection).observe(tree_select, {childList: true, subtree: true});
        }

        hide_non_leaf_selection();
    }
}
JS;
            }
        );

        return $factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'applyAdoptSettings'),
            [self::ADOPT_SOURCE_REF_ID => $source_input],
        )->withSubmitLabel($this->lng->txt('sahs_adopt_settings_apply'));
    }

    protected function renderAdoptSettingsForm(\ILIAS\UI\Component\Input\Container\Form\Standard $form): void
    {
        $factory = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();

        $description = $factory->legacy()->content(
            '<p>' . htmlspecialchars($this->lng->txt('sahs_adopt_settings_description'), ENT_QUOTES, 'UTF-8') . '</p>'
        );
        $panel = $factory->panel()->standard(
            $this->lng->txt('sahs_adopt_settings_title'),
            [$description, $form],
        );
        $this->tpl->setContent($renderer->render($panel));
    }

    protected function buildAdoptSettingsNodeRetrieval(): ilSCORMAdoptSettingsNodeRetrieval
    {
        $data_factory = new DataFactory();
        [$url_builder, $parent_id_parameter] = $this->buildAdoptSettingsTreeUrlBuilder();

        $async_uri_provider = static function (int $parent_ref_id) use (
            $data_factory,
            $url_builder,
            $parent_id_parameter
        ): \ILIAS\Data\URI {
            return $data_factory->uri(
                (string) $url_builder
                    ->withParameter($parent_id_parameter, (string) $parent_ref_id)
                    ->buildURI()
            );
        };

        return new ilSCORMAdoptSettingsNodeRetrieval(
            $this->object->getRefId(),
            $this->object->getSubType(),
            $async_uri_provider,
            $this->dic->language(),
            $this->dic->access(),
            $this->dic->repositoryTree(),
            $this->dic['objDefinition']->getExplorerContainerTypes(),
            self::ADOPT_TREE_MAX_SYNC_DEPTH,
        );
    }

    /**
     * @return array{0: URLBuilder, 1: URLBuilderToken}
     */
    protected function buildAdoptSettingsTreeUrlBuilder(): array
    {
        $data_factory = new DataFactory();
        $url_builder = new URLBuilder(
            $data_factory->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget(
                    $this,
                    'loadAdoptSettingsTreeNodes',
                    null,
                    true
                )
            )
        );

        return $url_builder->acquireParameter(
            ['sahs', 'adopt', 'settings'],
            self::ADOPT_TREE_PARENT_REF_ID,
        );
    }

    protected function sendAdoptSettingsTreeResponse(string $html): void
    {
        $http = $this->dic->http();
        $http->saveResponse(
            $http->response()
                ->withHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody(Streams::ofString($html))
        );
        $http->sendResponse();
        $http->close();
    }

    protected function extractSourceRefId(mixed $value): int
    {
        if (is_array($value) && isset($value[0])) {
            return (int) $value[0];
        }
        if (is_scalar($value)) {
            return (int) $value;
        }
        return 0;
    }

    /**
    * upload new version of module
    */
    public function newModuleVersion(): void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $this->setSettingsSubTabs();
        $ilTabs->setSubTabActive('cont_sc_new_version');

        // a new module version replaces the content of the IRSS container, so
        // the module must have been migrated to the Resource Storage first
        if (!$this->object->hasContainerResource()) {
            $this->tpl->setContent(
                $DIC->ui()->renderer()->render([
                    $DIC->ui()->factory()->messageBox()->info($this->lng->txt('infobox_files_not_migrated'))
                ])
            );
            return;
        }

        $obj_id = ilObject::_lookupObjectId($this->refId);
        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
        $this->form = new ilPropertyFormGUI();
        //title
        $this->form->setTitle($this->lng->txt("import_sahs_new"));

        $this->form->setDescription($this->lng->txt("import_sahs_info"));

        // SCORM-type
        $ne = new ilNonEditableValueGUI($this->lng->txt("type"), "");
        $ne->setValue($this->lng->txt("lm_type_" . ilObjSAHSLearningModule::_lookupSubType($this->object->getID())));
        $this->form->addItem($ne);

        $options = array();
        if (ilUploadFiles::_getUploadDirectory()) {
            $options[""] = $this->lng->txt("cont_select_from_upload_dir");
            $files = ilUploadFiles::_getUploadFiles();
            foreach ($files as $file) {
                $file = htmlspecialchars($file, ENT_QUOTES, "utf-8");
                $options[$file] = $file;
            }
        }
        if (count($options) > 1) {
            // choose upload directory
            $radg = new ilRadioGroupInputGUI($this->lng->txt("cont_choose_file_source"), "file_source");
            $op0 = new ilRadioOption($this->lng->txt("cont_choose_local"), "local");
            $radg->addOption($op0);
            $op1 = new ilRadioOption($this->lng->txt("cont_choose_upload_dir"), "upload_dir");
            $radg->addOption($op1);
            $radg->setValue("local");

            $fi = new ilFileInputGUI($this->lng->txt("select_file"), "scormfile");
            $fi->setRequired(true);
            $op0->addSubItem($fi);

            $si = new ilSelectInputGUI($this->lng->txt("cont_uploaded_file"), "uploaded_file");
            $si->setOptions($options);
            $op1->addSubItem($si);

            $this->form->addItem($radg);
        } else {
            $fi = new ilFileInputGUI($this->lng->txt("select_file"), "scormfile");
            $fi->setRequired(true);
            $this->form->addItem($fi);
        }
        $this->form->addCommandButton("newModuleVersionUpload", $this->lng->txt("upload"));
        $this->form->addCommandButton("cancel", $this->lng->txt("cancel"));
        $this->form->setFormAction($DIC->ctrl()->getFormAction($this, "newModuleVersionUpload"));
        $DIC['tpl']->setContent($this->form->getHTML());
    }

    public function getMaxFileSize(): string
    {
        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = get_cfg_var("upload_max_filesize");
        // get the value for the maximal post data from the php.ini (if available)
        $pms = get_cfg_var("post_max_size");

        //convert from short-string representation to "real" bytes
        $multiplier_a = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);

        $umf_parts = preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pms_parts = preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($umf_parts) == 2) {
            $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if (count($pms_parts) == 2) {
            $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }

        // use the smaller one as limit
        $max_filesize = min($umf, $pms);

        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }

        //format for display in mega-bytes
        return $max_filesize = sprintf("%.1f MB", $max_filesize / 1024 / 1024);
    }

    /**
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function newModuleVersionUpload(): void
    {
        global $DIC;
        $rbacsystem = $DIC->access();
        $ilErr = $DIC["ilErr"];

        // a new module version replaces the IRSS container content, only
        // possible once the module has been migrated to the Resource Storage
        if (!$this->object->hasContainerResource()) {
            $this->newModuleVersion();
            return;
        }

        $unzip = PATH_TO_UNZIP;
        $tocheck = "imsmanifest.xml";

        // check create permission before because the uploaded file will be copied
        if (!$rbacsystem->checkAccess("write", "", $this->refId)) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"), $ilErr->WARNING);
        } elseif ($_FILES["scormfile"]["name"]) {
            // check if file was uploaded
            $source = $_FILES["scormfile"]["tmp_name"];
            if (($source === 'none') || (!$source)) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("upload_error_file_not_found"), true);
                $this->newModuleVersion();
                return;
            }
        } elseif ($DIC->http()->wrapper()->post()->has('uploaded_file')) {
            $uploadedFile = $DIC->http()->wrapper()->post()->retrieve('uploaded_file', $DIC->refinery()->kindlyTo()->string());
            // check if the file is in the ftp directory and readable
            if (!ilUploadFiles::_checkUploadFile($uploadedFile)) {
                $ilErr->raiseError($this->lng->txt("upload_error_file_not_found"), $ilErr->MESSAGE);
            }
            // copy the uploaded file to the client web dir to analyze the imsmanifest
            // the copy will be moved to the lm directory or deleted
            $source = CLIENT_WEB_DIR . "/" . $uploadedFile;
            ilUploadFiles::_copyUploadFile($uploadedFile, $source);
            $source_is_copy = true;
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("upload_error_file_not_found"), true);
            $this->newModuleVersion();
            return;
        }
        // fim.

        //unzip the imsmanifest-file from new uploaded file
        $pathinfo = pathinfo($source);
        $dir = $pathinfo["dirname"];
        $file = $pathinfo["basename"];
        $cdir = getcwd();
        chdir($dir);

        //we need more flexible unzip here than ILIAS standard classes allow
        $unzipcmd = $unzip . " -o " . ilShellUtil::escapeShellArg($source) . " " . $tocheck;
        exec($unzipcmd);
        chdir($cdir);
        $tmp_file = $dir . "/" . $this->refId . "." . $tocheck;

        ilFileUtils::rename($dir . "/" . $tocheck, $tmp_file);
        $new_manifest = file_get_contents($tmp_file);

        //remove temp file
        unlink($tmp_file);

        //get old manifest file from the container resource
        $old_manifest = (string) $this->object->getManifestFromContainer($tocheck);

        //reload fixed version of file
        $check = '/xmlns="http:\/\/www.imsglobal.org\/xsd\/imscp_v1p1"/';
        $replace = "xmlns=\"http://www.imsproject.org/xsd/imscp_rootv1p1p2\"";
        $reload_manifest = preg_replace($check, $replace, $new_manifest);

        //do testing for converted versions as well as earlier ILIAS version messed up utf8 conversion
        if (strcmp($new_manifest, $old_manifest) == 0 || strcmp(mb_convert_encoding($new_manifest, "UTF-8", mb_detect_encoding($new_manifest)), $old_manifest) == 0 ||
            strcmp($reload_manifest, $old_manifest) == 0 || strcmp(mb_convert_encoding($reload_manifest, "UTF-8", mb_detect_encoding($reload_manifest)), $old_manifest) == 0) {

            //get exisiting module version
            $module_version = $this->object->getModuleVersion() + 1;

            // store the uploaded package as a new revision of the container
            $revision_title = ($_FILES["scormfile"]["name"] ?? '') !== ''
                ? (string) $_FILES["scormfile"]["name"]
                : basename($source);
            $this->object->replaceContainerFromZipPath($source, $revision_title);

            if (isset($source_is_copy)) {
                @unlink($source);
            }

            //increase module version
            $this->object->setModuleVersion($module_version);
            $this->object->update();

            //redirect to properties and display success
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_new_module_added"), true);
            ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=" . $this->refId);
            exit;
        }

        if (isset($source_is_copy)) {
            unlink($source);
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_invalid_new_module"), true);
        $this->newModuleVersion();
    }

    /**
     * @throws ilCtrlException
     */
    public function saveProperties(): void
    {
        $obj_service = $this->object_service;
        $this->initPropertiesForm();
        if ($this->form->checkInput()) {
            $this->object->setTitle($this->dic->http()->wrapper()->post()->retrieve('Fobject_title', $this->dic->refinery()->kindlyTo()->string()));
            $this->object->setDescription($this->dic->http()->wrapper()->post()->retrieve('Fobject_description', $this->dic->refinery()->kindlyTo()->string()));

            if ($this->dic->http()->wrapper()->post()->has('mastery_score')) {
                $sMasteryScore = $this->dic->http()->wrapper()->post()->retrieve('mastery_score', $this->dic->refinery()->kindlyTo()->string());
                if ($sMasteryScore !== "") {
                    $this->object->setMasteryScore((int) $sMasteryScore);
                } else {
                    $this->object->setMasteryScore(null);
                }
            }

            $t_height = $this->object->getHeight();
            if ($this->dic->http()->wrapper()->post()->retrieve('height_0', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getHeight()) {
                $t_height = $this->dic->http()->wrapper()->post()->retrieve('height_0', $this->dic->refinery()->kindlyTo()->int());
            }
            if ($this->dic->http()->wrapper()->post()->retrieve('height_1', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getHeight()) {
                $t_height = $this->dic->http()->wrapper()->post()->retrieve('height_1', $this->dic->refinery()->kindlyTo()->int());
            }

            $t_width = $this->object->getWidth();
            if ($this->dic->http()->wrapper()->post()->retrieve('width_0', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getWidth()) {
                $t_width = $this->dic->http()->wrapper()->post()->retrieve('width_0', $this->dic->refinery()->kindlyTo()->int());
            }
            if ($this->dic->http()->wrapper()->post()->retrieve('width_1', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getWidth()) {
                $t_width = $this->dic->http()->wrapper()->post()->retrieve('width_1', $this->dic->refinery()->kindlyTo()->int());
            }

            $this->object->setOfflineStatus(!($this->dic->http()->wrapper()->post()->has('cobj_online')));
            //            $this->object->setOfflineMode($tmpOfflineMode);
            $this->object->setOpenMode($this->dic->http()->wrapper()->post()->retrieve('open_mode', $this->dic->refinery()->kindlyTo()->int()));
            $this->object->setWidth($t_width);
            $this->object->setHeight($t_height);
            $this->object->setAuto_last_visited($this->dic->http()->wrapper()->post()->has('cobj_auto_last_visited'));
            $this->object->setAutoContinue($this->dic->http()->wrapper()->post()->has('auto_continue'));
            //            $this->object->setMaxAttempt((int) $_POST["max_attempt"]);
            $this->object->setDefaultLessonMode($this->dic->http()->wrapper()->post()->retrieve('lesson_mode', $this->dic->refinery()->kindlyTo()->string()));
            $this->object->setAutoReview(ilUtil::yn2tf($this->dic->http()->wrapper()->post()->retrieve('auto_review', $this->dic->refinery()->kindlyTo()->string())));
            $this->object->setSession($this->dic->http()->wrapper()->post()->has('cobj_session'));
            $this->object->setInteractions($this->dic->http()->wrapper()->post()->has('cobj_interactions'));
            $this->object->setObjectives($this->dic->http()->wrapper()->post()->has('cobj_objectives'));
            $this->object->setTime_from_lms($this->dic->http()->wrapper()->post()->has('cobj_time_from_lms'));
            $this->object->setCheck_values($this->dic->http()->wrapper()->post()->has('cobj_check_values'));
            $this->object->setAutoSuspend($this->dic->http()->wrapper()->post()->has('cobj_auto_suspend'));
            $this->object->setDebug($this->dic->http()->wrapper()->post()->has('cobj_debug'));
            $this->object->setIdSetting($this->dic->http()->wrapper()->post()->retrieve('id_setting', $this->dic->refinery()->kindlyTo()->int()));
            $this->object->setNameSetting($this->dic->http()->wrapper()->post()->retrieve('name_setting', $this->dic->refinery()->kindlyTo()->int()));
            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
     * @throws ilCtrlException
     */
    protected function showTrackingItemsBySco(): bool
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $ilAccess = $DIC->access();

        $ilTabs->setTabActive("cont_tracking_data");

        if (!$ilAccess->checkAccess("read_learning_progress", "", $this->refId)) {
            if ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
                $this->modifyTrackingItems();
            }
            return true;
        }

        $this->setSubTabs();
        $ilTabs->setSubTabActive("cont_tracking_bysco");

        $reports = ['exportSelectedCore', 'exportSelectedInteractions', 'exportSelectedObjectives', 'exportSelectedRaw'];
        [$filter, $filter_data] = $this->buildTrackingFilter(
            self::TRACKING_FILTER_BY_SCO . $this->object->getId(),
            'showTrackingItemsBySco',
            'scoSelected',
            $this->lng->txt('chapter'),
            $this->getTrackingScoOptions(),
            $reports
        );

        $scoSelected = $filter_data['scoSelected'];
        $report = $filter_data['report'];
        $this->ctrl->setParameter($this, 'scoSelected', $scoSelected);
        $this->ctrl->setParameter($this, 'report', $report);

        $content = $filter->render();
        if ($report !== "choose") {
            $scosSelected = array();
            if ($scoSelected !== "all") {
                $scosSelected[] = $scoSelected;
            } else {
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scosSelected[] = (int) $row->getId();
                }
            }
            $a_users = ilTrQuery::getParticipantsForObject($this->ref_id);
            $tbl = new ilSCORMTrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItemsBySco', $a_users, $scosSelected, $report);
            $content .= $this->renderTrackingTableWithExportActions(
                $tbl->getHTML(),
                self::EXPORT_BY_SCO
            );
        }
        $this->tpl->setContent($content);
        return true;
    }

    /**
     * Show tracking table
     * @throws ilCtrlException
     */
    public function showTrackingItems(): bool
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $ilAccess = $DIC->access();

        $ilTabs->setTabActive('cont_tracking_data');

        if ($ilAccess->checkAccess("read_learning_progress", "", $this->refId)) {
            $this->setSubTabs();
            $ilTabs->setSubTabActive('cont_tracking_byuser');

            $reports = ['exportSelectedSuccess', 'exportSelectedCore', 'exportSelectedInteractions', 'exportSelectedObjectives', 'exportSelectedRaw'];
            [$filter, $filter_data] = $this->buildTrackingFilter(
                self::TRACKING_FILTER_BY_USER . $this->object->getId(),
                'showTrackingItems',
                'userSelected',
                $this->lng->txt('user'),
                $this->getTrackingUserOptions(),
                $reports
            );

            $userSelected = $filter_data['userSelected'];
            $report = $filter_data['report'];
            $this->ctrl->setParameter($this, 'userSelected', $userSelected);
            $this->ctrl->setParameter($this, 'report', $report);

            $content = $filter->render();
            if ($report !== "choose") {
                $usersSelected = array();
                if ($userSelected !== "all") {
                    $usersSelected[] = $userSelected;
                } else {
                    $users = ilTrQuery::getParticipantsForObject($this->ref_id);
                    foreach ($users as $user) {
                        if (ilObject::_exists((int) $user) && ilObject::_lookUpType((int) $user) === 'usr') {
                            $usersSelected[] = (int) $user;
                        }
                    }
                }
                $scosSelected = array();
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scosSelected[] = (int) $row->getId();
                }
                $tbl = new ilSCORMTrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItems', $usersSelected, $scosSelected, $report);
                $content .= $this->renderTrackingTableWithExportActions(
                    $tbl->getHTML(),
                    self::EXPORT_BY_USER
                );
            }
            $this->tpl->setContent($content);
        } elseif ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
            $this->modifyTrackingItems();
        }
        return true;
    }

    /**
     * @param array<int, string> $reports
     * @param array<string, string> $scope_options
     * @return array{0: FilterAdapterGUI, 1: array<string, string>}
     */
    protected function buildTrackingFilter(
        string $filter_id,
        string $cmd,
        string $scope_key,
        string $scope_label,
        array $scope_options,
        array $reports
    ): array {
        $this->lng->loadLanguageModule("scormtrac");

        $report_options = $this->getTrackingReportOptions($reports);
        $default_scope = array_key_exists('all', $scope_options)
            ? 'all'
            : (string) (array_key_first($scope_options) ?? 'all');

        $filter = new FilterAdapterGUI($filter_id, [get_class($this)], $cmd, true, true);
        $filter
            ->select($scope_key, $scope_label, $scope_options, true, $default_scope, true)
            ->select('report', $this->lng->txt('report'), $report_options, true, 'choose', true);

        $raw = $filter->getData() ?? [];

        $scope_value = (string) ($raw[$scope_key] ?? $default_scope);
        if (!array_key_exists($scope_value, $scope_options)) {
            $scope_value = $default_scope;
        }

        $report_value = (string) ($raw['report'] ?? 'choose');
        if (!array_key_exists($report_value, $report_options)) {
            $report_value = 'choose';
        }

        return [$filter, [$scope_key => $scope_value, 'report' => $report_value]];
    }

    /**
     * @param array<int, string> $reports
     * @return array<string, string>
     */
    protected function getTrackingReportOptions(array $reports): array
    {
        $options = ["choose" => $this->lng->txt("please_choose")];
        foreach ($reports as $value) {
            $options[$value] = $this->lng->txt(strtolower($value));
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    protected function getTrackingUserOptions(): array
    {
        $options = ["all" => $this->lng->txt("all")];
        $users = ilTrQuery::getParticipantsForObject($this->getRefId());
        $privacy = ilPrivacySettings::getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();

        if ($users && count($users) > 0) {
            foreach ($users as $usr) {
                $user = (int) $usr;
                if (ilObject::_exists($user) && ilObject::_lookUpType($user) === 'usr') {
                    if ($allowExportPrivacy) {
                        $e_user = new ilObjUser($user);
                        $options[(string) $user] = $e_user->getLastname() . ", " . $e_user->getFirstname();
                    } else {
                        $options[(string) $user] = 'User Id: ' . $user;
                    }
                }
            }
        } else {
            $options = ["-1" => $this->lng->txt("no_items")];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    protected function getTrackingScoOptions(): array
    {
        $options = ["all" => $this->lng->txt("all")];
        $scos = $this->object->getTrackedItems();

        foreach ($scos as $row) {
            $options[(string) $row->getId()] = $row->getTitle();
        }

        return $options;
    }

    protected function renderTrackingTableWithExportActions(string $table_html, string $export_mode): string
    {
        $factory = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();

        $panel = $factory->panel()->standard(
            $this->lng->txt('cont_tracking_items'),
            $factory->legacy()->content($table_html)
        )->withActions($this->buildTrackingItemsActionsDropdown($export_mode));

        return $renderer->render($panel);
    }

    protected function buildTrackingItemsActionsDropdown(string $export_mode): DropdownStandard
    {
        $factory = $this->dic->ui()->factory();

        switch ($export_mode) {
            case self::EXPORT_BY_SCO:
                $csv_cmd = 'exportTrackingItemsCsvBySco';
                $excel_cmd = 'exportTrackingItemsExcelBySco';
                break;

            case self::EXPORT_BY_USER:
                $csv_cmd = 'exportTrackingItemsCsvByUser';
                $excel_cmd = 'exportTrackingItemsExcelByUser';
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Unsupported export mode "%s".', $export_mode)
                );
        }

        $actions = [
            $factory->link()->standard(
                $this->lng->txt('export') . ' CSV',
                $this->ctrl->getLinkTarget($this, $csv_cmd)
            ),
            $factory->link()->standard(
                $this->lng->txt('export') . ' Excel',
                $this->ctrl->getLinkTarget($this, $excel_cmd)
            ),
        ];

        return $factory->dropdown()->standard($actions)->withLabel($this->lng->txt('export'));
    }

    protected function buildModifyTrackingDataActionsDropdown(): DropdownStandard
    {
        $factory = $this->dic->ui()->factory();

        $actions = [
            $factory->link()->standard(
                $this->lng->txt('import'),
                $this->ctrl->getLinkTarget($this, 'trackingImportForm')
            ),
            $factory->link()->standard(
                $this->lng->txt('cont_export_all'),
                $this->ctrl->getLinkTarget($this, 'exportAll')
            ),
        ];

        return $factory->dropdown()->standard($actions)->withLabel(
            $this->lng->txt('import') . ' / ' . $this->lng->txt('export')
        );
    }

    protected function exportTrackingItemsCsvByUser(): void
    {
        $this->exportTrackingItems(false, false);
    }

    protected function exportTrackingItemsExcelByUser(): void
    {
        $this->exportTrackingItems(false, true);
    }

    protected function exportTrackingItemsCsvBySco(): void
    {
        $this->exportTrackingItems(true, false);
    }

    protected function exportTrackingItemsExcelBySco(): void
    {
        $this->exportTrackingItems(true, true);
    }

    private function exportTrackingItems(bool $by_sco, bool $as_excel): void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];

        if (!$ilAccess->checkAccess("read_learning_progress", "", $this->refId)) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
        }

        $report = $this->readTrackingReport();
        $valid_reports = $by_sco
            ? [
                'exportSelectedCore',
                'exportSelectedInteractions',
                'exportSelectedObjectives',
                'exportSelectedRaw'
            ]
            : [
                'exportSelectedSuccess',
                'exportSelectedCore',
                'exportSelectedInteractions',
                'exportSelectedObjectives',
                'exportSelectedRaw'
            ];
        if (!in_array($report, $valid_reports, true)) {
            $report = "choose";
        }
        if ($report === "choose") {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("please_choose"), true);
            $this->ctrl->redirect($this, $by_sco ? 'showTrackingItemsBySco' : 'showTrackingItems');
            return;
        }

        if ($by_sco) {
            $sco_selected = $this->readTrackingScoSelection();
            $scos_selected = [];
            if ($sco_selected !== "all") {
                $scos_selected[] = $sco_selected;
            } else {
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scos_selected[] = (int) $row->getId();
                }
            }
            $users_selected = ilTrQuery::getParticipantsForObject($this->ref_id);

            $table = new ilSCORMTrackingItemsTableGUI(
                $this->object->getId(),
                $this,
                'showTrackingItemsBySco',
                $users_selected,
                $scos_selected,
                $report
            );
        } else {
            $user_selected = $this->readTrackingUserSelection();
            $users_selected = [];
            if ($user_selected !== "all") {
                $users_selected[] = $user_selected;
            } else {
                $users = ilTrQuery::getParticipantsForObject($this->ref_id);
                foreach ($users as $user) {
                    if (ilObject::_exists((int) $user) && ilObject::_lookUpType((int) $user) === 'usr') {
                        $users_selected[] = (int) $user;
                    }
                }
            }

            $scos_selected = [];
            $scos = $this->object->getTrackedItems();
            foreach ($scos as $row) {
                $scos_selected[] = (int) $row->getId();
            }

            $table = new ilSCORMTrackingItemsTableGUI(
                $this->object->getId(),
                $this,
                'showTrackingItems',
                $users_selected,
                $scos_selected,
                $report
            );
        }

        if ($as_excel) {
            $table->sendExportExcel();
            return;
        }
        $table->sendExportCSV();
    }

    private function readTrackingReport(): string
    {
        $report = "choose";
        if ($this->dic->http()->wrapper()->query()->has('report')) {
            $report = $this->dic->http()->wrapper()->query()->retrieve('report', $this->dic->refinery()->kindlyTo()->string());
        }
        if ($this->dic->http()->wrapper()->post()->has('report')) {
            $report = $this->dic->http()->wrapper()->post()->retrieve('report', $this->dic->refinery()->kindlyTo()->string());
        }
        return $report;
    }

    private function readTrackingUserSelection(): string
    {
        $user_selected = "all";
        if ($this->dic->http()->wrapper()->query()->has('userSelected')) {
            $user_selected = $this->dic->http()->wrapper()->query()->retrieve('userSelected', $this->dic->refinery()->kindlyTo()->string());
        }
        if ($this->dic->http()->wrapper()->post()->has('userSelected')) {
            $user_selected = $this->dic->http()->wrapper()->post()->retrieve('userSelected', $this->dic->refinery()->kindlyTo()->string());
        }
        return $user_selected;
    }

    private function readTrackingScoSelection(): string
    {
        $sco_selected = "all";
        if ($this->dic->http()->wrapper()->query()->has('scoSelected')) {
            $sco_selected = $this->dic->http()->wrapper()->query()->retrieve('scoSelected', $this->dic->refinery()->kindlyTo()->string());
        }
        if ($this->dic->http()->wrapper()->post()->has('scoSelected')) {
            $sco_selected = $this->dic->http()->wrapper()->post()->retrieve('scoSelected', $this->dic->refinery()->kindlyTo()->string());
        }
        return $sco_selected;
    }

    /**
     * @throws ilCtrlException
     */
    protected function modifyTrackingItems(): void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        if ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
            $privacy = ilPrivacySettings::getInstance();
            if (!$privacy->enabledSahsProtocolData()) {
                $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
            }

            global $DIC;
            $ilTabs = $DIC->tabs();

            $this->setSubTabs();
            $ilTabs->setTabActive('cont_tracking_data');
            $ilTabs->setSubTabActive('cont_tracking_modify');
            $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifyTrackingItems');
            $tbl->parse();
            if ($tbl->handleCommand()) {
                return;
            }

            $factory = $this->dic->ui()->factory();
            $renderer = $this->dic->ui()->renderer();
            $panel = $factory->panel()->standard(
                $this->lng->txt('cont_tracking_items'),
                $factory->legacy()->content($tbl->getTableHTML())
            )->withActions(
                $this->buildModifyTrackingDataActionsDropdown()
            );

            $this->tpl->setContent(
                $tbl->getFilterHTML()
                . $renderer->render($panel)
            );
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function applyUserTableFilter(): void
    {
        $this->ctrl->redirect($this, 'modifyTrackingItems');
    }

    /**
     * Reset table filter
     * @throws ilCtrlException
     */
    protected function resetUserTableFilter(): void
    {
        $this->ctrl->redirect($this, 'modifyTrackingItems');
    }

    /**
     * display deletion confirmation screen
     * @throws ilCtrlException
     */
    public function deleteTrackingForUser(?array $user_ids = null): void
    {
        global $DIC;
        $ilErr = $DIC["ilErr"];
        $DIC->tabs()->clearSubTabs();

        if ($user_ids === null) {
            if (!$DIC->http()->wrapper()->post()->has('user')) {
                $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
            }
            $user_ids = $DIC->http()->wrapper()->post()->retrieve(
                'user',
                $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int())
            );
        }

        if (($user_ids[0] ?? null) === 'ALL_OBJECTS') {
            $user_ids = $this->getTrackedUserIdsByCurrentModifyFilter();
        }

        if (count($user_ids) === 0) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteTracking");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDeleteTracking");
        foreach ($user_ids as $id) {
            if (ilObject::_exists((int) $id) && ilObject::_lookUpType((int) $id) === "usr") {
                $user = new ilObjUser((int) $id);

                //was ilUtil::getImageTagByType("sahs", (string) $this->tpl->getValue("TPLPATH")) .
                $caption = $this->lng->txt("cont_tracking_data") . ": " . $user->getLastname() . ", " . $user->getFirstname();

                $cgui->addItem("user[]", (string) $id, $caption);
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * cancel deletion of export files
     * @throws ilCtrlException
     */
    public function cancelDeleteTracking(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }

    /**
     * @throws ilCtrlException
     */
    public function confirmedDeleteTracking(): void
    {
        $this->object->deleteTrackingDataOfUsers($this->post_wrapper->retrieve('user', $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())));
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }

    /**
     * overwrite..jump back to trackingdata not parent
     * @throws ilCtrlException
     */
    public function cancel(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
     * gui functions for GUI export
     * @throws ilCtrlException
     */
    protected function importTracking(): void
    {
        $form = $this->initTrackingImportForm("");
        if ($form->checkInput()) {
            $source = $form->getInput('csv');
            $success = $this->object->importTrackingData($source['tmp_name']);
            switch ($success) {
                case true:
                    $this->tpl->setOnScreenMessage('info', 'Tracking data imported', true);
                    $this->ctrl->redirect($this, "showTrackingItems");
                    break;
                case false:
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
                    $this->trackingImportForm();
                    break;
            }
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->trackingImportForm();
    }

    /**
     * Show import form
     * @throws ilCtrlException
     */
    protected function trackingImportForm(): void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showTrackingItems'));

        $form = $this->initTrackingImportForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init import form
     * @throws ilCtrlException
     */
    protected function initTrackingImportForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('cont_import_tracking'));
        $form->addCommandButton('importTracking', $this->lng->txt('import'));
        $form->addCommandButton('showTrackingItems', $this->lng->txt('cancel'));

        $csv = new ilFileInputGUI($this->lng->txt('select_file'), 'csv');
        $csv->setRequired(true);
        $csv->setSuffixes(array('csv'));
        $form->addItem($csv);

        return $form;
    }

    /**
     * Show export section for all users
     */
    protected function exportAll(): void
    {
        $this->object->exportSelected(true);
    }

    /**
     * Export selection for selected users
     * @throws ilCtrlException
     */
    public function exportSelectionUsers(?array $user_ids = null): void
    {
        if ($user_ids === null) {
            if (!$this->post_wrapper->has('user')) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
                $this->ctrl->redirect($this, 'modifyTrackingItems');
            }
            $user_ids = $this->post_wrapper->retrieve(
                'user',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if (($user_ids[0] ?? null) === 'ALL_OBJECTS') {
            $user_ids = $this->getTrackedUserIdsByCurrentModifyFilter();
        }

        if (count($user_ids) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'modifyTrackingItems');
        }

        $this->object->exportSelected(false, array_map('intval', $user_ids));
    }

    /**
     * @return array<int, int>
     */
    private function getTrackedUserIdsByCurrentModifyFilter(): array
    {
        $filter = new FilterAdapterGUI(
            'sco_tr_usrs_' . $this->object->getId(),
            [get_class($this)],
            'modifyTrackingItems'
        );
        $filter->text('login_filter', $this->lng->txt('login'));
        $filter->text('firstname_filter', $this->lng->txt('firstname'));
        $filter->text('lastname_filter', $this->lng->txt('lastname'));
        $filter->text('email_filter', $this->lng->txt('email'));

        $filter_data = $filter->getData() ?? [];
        $users = $this->object->getTrackedUsers([
            'lastname_filter' => trim((string) ($filter_data['lastname_filter'] ?? '')),
            'firstname_filter' => trim((string) ($filter_data['firstname_filter'] ?? '')),
            'login_filter' => trim((string) ($filter_data['login_filter'] ?? '')),
            'email_filter' => trim((string) ($filter_data['email_filter'] ?? '')),
        ]);

        $user_ids = array_map(
            static fn(array $user): int => (int) ($user['user_id'] ?? 0),
            $users
        );

        return array_values(array_filter($user_ids, static fn(int $id): bool => $id > 0));
    }


    /**
     * @throws ilCtrlException
     */
    public function setSubTabs(): void
    {
        global $DIC;
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess("read_learning_progress", "", $this->refId)) {
            $ilTabs->addSubTabTarget(
                "cont_tracking_byuser",
                $this->ctrl->getLinkTarget($this, "showTrackingItems"),
                array("edit", ""),
                get_class($this)
            );

            $ilTabs->addSubTabTarget(
                "cont_tracking_bysco",
                $this->ctrl->getLinkTarget($this, "showTrackingItemsBySco"),
                array("edit", ""),
                get_class($this)
            );
        }
        if ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
            $ilTabs->addSubTabTarget(
                "cont_tracking_modify",
                $this->ctrl->getLinkTarget($this, "modifyTrackingItems"),
                array("edit", ""),
                get_class($this)
            );
        }
    }
}
// END class.ilObjSCORMLearningModule
