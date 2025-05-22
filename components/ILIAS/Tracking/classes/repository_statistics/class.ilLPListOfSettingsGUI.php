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

declare(strict_types=0);

use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Tracking\View\Factory as ViewFactory;
use ILIAS\Tracking\View\ProgressBlock\Settings\RepositoryInterface as ProgressBlockSettings;

/**
 * Class ilLPListOfSettingsGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilLPListOfSettingsGUI:
 * @ingroup      ServicesTracking
 */
class ilLPListOfSettingsGUI extends ilLearningProgressBaseGUI
{
    protected ilLPObjSettings $obj_settings;
    protected ilObjectLP $obj_lp;
    protected ProgressBlockSettings $progress_block_settings;

    public function __construct(int $a_mode, int $a_ref_id)
    {
        parent::__construct($a_mode, $a_ref_id);

        $this->obj_settings = new ilLPObjSettings($this->getObjId());
        $this->obj_lp = ilObjectLP::getInstance($this->getObjId());
        $this->progress_block_settings = (new ViewFactory())->progressBlock()->settings()->repository();
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();
        }
    }

    protected function initItemIdsFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('item_ids')) {
            return $this->http->wrapper()->post()->retrieve(
                'item_ids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    /**
     * Show settings tables
     */
    protected function show(): void
    {
        $this->help->setSubScreenId("trac_settings");
        $info = $this->obj_lp->getSettingsInfo();
        if ($info) {
            $this->tpl->setOnScreenMessage('info', $info);
        }

        $form = $this->initFormSettings();
        $this->tpl->setContent(
            $this->handleLPUsageInfo() .
            $this->ui_renderer->render($form) .
            $this->getTableByMode()
        );
    }

    protected function initFormSettings(): StandardForm
    {
        $mode_groups = [];
        if ($this->obj_lp->hasIndividualModeOptions()) {
            $mode_groups = $this->obj_lp->initInvidualModeOptions();
        } else {
            foreach ($this->obj_lp->getValidModes() as $mode_key) {
                $mode_config_inputs = [];

                if ($mode_key == ilLPObjSettings::LP_MODE_VISITS) {
                    $mode_config_inputs['visits'] = $this->ui_factory->input()->field()->numeric(
                        $this->lng->txt('trac_visits'),
                        sprintf(
                            $this->lng->txt('trac_visits_info'),
                            (string) ilObjUserTracking::_getValidTimeSpan()
                        )
                    )->withRequired(true)
                     ->withAdditionalTransformation(
                         $this->refinery->in()->series([
                             $this->refinery->int()->isGreaterThanOrEqual(1),
                             $this->refinery->int()->isLessThanOrEqual(99999)
                         ])
                     )->withValue($this->obj_settings->getVisits());
                }

                if ($mode_key == ilLPObjSettings::LP_MODE_COLLECTION) {
                    $mode_config_inputs['show_block'] = $this->ui_factory->input()->field()->checkbox(
                        $this->lng->txt('trac_show_progress_block'),
                    )->withValue(
                        $this->progress_block_settings->isBlockShownForObject($this->getObjId())
                    );
                }

                $mode_config_inputs = array_merge(
                    $mode_config_inputs,
                    $this->obj_lp->appendModeConfiguration($mode_key)
                );

                $mode_groups[$mode_key] = $this->ui_factory->input()->field()->group(
                    $mode_config_inputs,
                    $this->obj_lp->getModeText($mode_key),
                    $this->obj_lp->getModeInfoText($mode_key)
                );
            }
        }

        $mode = $this->ui_factory->input()->field()->switchableGroup(
            $mode_groups,
            $this->lng->txt('trac_mode')
        )->withRequired(true)
         ->withValue((string) $this->obj_lp->getCurrentMode());

        $section = $this->ui_factory->input()->field()->section(
            ['modus' => $mode],
            $this->lng->txt('tracking_settings')
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, 'saveSettings'),
            ['main' => $section]
        );
    }

    protected function saveSettings(): void
    {
        $form = $this->initFormSettings()
                     ->withRequest($this->http->request());
        if ($data = $form->getData()) {
            $selected_mode = (string) $data['main']['modus'][0];
            $mode_data = $data['main']['modus'][1];

            // mode
            if ($this->obj_lp->shouldFetchIndividualModeFromFormSubmission()) {
                $new_mode = $this->obj_lp->fetchIndividualModeFromFormSubmission(
                    $selected_mode,
                    $mode_data
                );
            } else {
                $new_mode = (int) $selected_mode;
            }
            $old_mode = $this->obj_lp->getCurrentMode();
            // anything changed?
            $mode_changed = ($old_mode != $new_mode);

            // visits
            $new_visits = null;
            $visits_changed = null;
            if ($new_mode == ilLPObjSettings::LP_MODE_VISITS) {
                $new_visits = (int) $mode_data['visits'];
                $old_visits = $this->obj_settings->getVisits();
                $visits_changed = ($old_visits != $new_visits);
            }

            // progress block
            if ($new_mode == ilLPObjSettings::LP_MODE_COLLECTION) {
                $this->progress_block_settings->setShowBlockForObject(
                    $this->getObjId(),
                    (bool) $mode_data['show_block']
                );
            }

            $this->obj_lp->saveModeConfiguration(
                $selected_mode,
                $mode_data,
                $mode_changed
            );

            if ($mode_changed) {
                // delete existing collection
                $collection = $this->obj_lp->getCollectionInstance();
                if ($collection) {
                    $collection->delete();
                }
            }


            // has to be done before LP refresh!
            $this->obj_lp->resetCaches();

            $this->obj_settings->setMode($new_mode);
            $this->obj_settings->setVisits((int) $new_visits);
            $this->obj_settings->update(true);

            if ($mode_changed &&
                $this->obj_lp->getCollectionInstance() &&
                $new_mode != ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) { // #14819
                $this->tpl->setOnScreenMessage(
                    'info',
                    $this->lng->txt(
                        'trac_edit_collection'
                    ),
                    true
                );
            }
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt(
                    'trac_settings_saved'
                ),
                true
            );
            $this->ctrl->redirect($this, 'show');
        }

        $this->tpl->setContent(
            $this->handleLPUsageInfo() .
            $this->ui_renderer->render($form) .
            $this->getTableByMode()
        );
    }

    /**
     * Get tables by mode
     */
    protected function getTableByMode(): string
    {
        $collection = $this->obj_lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            $table = new ilLPCollectionSettingsTableGUI(
                $this,
                'show',
                $this->getRefId(),
                $this->obj_lp->getCurrentMode()
            );
            $table->parse($collection);
            return $table->getHTML();
        }
        return '';
    }

    protected function assign(): void
    {
        if (!$this->initItemIdsFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'show');
        }
        if (count($this->initItemIdsFromPost())) {
            $collection = $this->obj_lp->getCollectionInstance();
            if ($collection && $collection->hasSelectableItems()) {
                $collection->activateEntries($this->initItemIdsFromPost());
            }
            // refresh learning progress
            $this->obj_lp->resetCaches();
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('trac_settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'show');
    }

    protected function deassign(): void
    {
        if (!$this->initItemIdsFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'show');
            return;
        }
        if (count($this->initItemIdsFromPost())) {
            $collection = $this->obj_lp->getCollectionInstance();
            if ($collection && $collection->hasSelectableItems()) {
                $collection->deactivateEntries($this->initItemIdsFromPost());
            }

            // #15045 - has to be done before LP refresh!
            $this->obj_lp->resetCaches();

            // refresh learning progress
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('trac_settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Group materials
     */
    protected function groupMaterials(): void
    {
        if (!count((array) $this->initItemIdsFromPost())) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'show');
        }

        $collection = $this->obj_lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            // Assign new grouping id
            $collection->createNewGrouping($this->initItemIdsFromPost());

            $this->obj_lp->resetCaches();

            // refresh learning progress
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('trac_settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'show');
    }

    /**
     *
     */
    protected function releaseMaterials(): void
    {
        if (!count((array) $this->initItemIdsFromPost())) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'show');
        }

        $collection = $this->obj_lp->getCollectionInstance();
        if ($collection && $collection->hasSelectableItems()) {
            $collection->releaseGrouping($this->initItemIdsFromPost());

            $this->obj_lp->resetCaches();

            // refresh learning progress
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('trac_settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Save obligatory state per grouped materials
     */
    protected function saveObligatoryMaterials(): void
    {
        $groups = [];
        if ($this->http->wrapper()->post()->has('grp')) {
            $groups = $this->http->wrapper()->post()->retrieve(
                'grp',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (!count($groups)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'show');
        }

        try {
            $collection = $this->obj_lp->getCollectionInstance();
            if ($collection && $collection->hasSelectableItems()) {
                $collection->saveObligatoryMaterials($groups);

                $this->obj_lp->resetCaches();

                // refresh learning progress
                ilLPStatusWrapper::_refreshStatus($this->getObjId());
            }

            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('settings_saved'),
                true
            );
            $this->ctrl->redirect($this, 'show');
        } catch (UnexpectedValueException $e) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt(
                    'trac_grouped_material_obligatory_err'
                ),
                true
            );
            $this->tpl->setOnScreenMessage(
                'info',
                $this->lng->txt('err_check_input'),
                true
            );
            $this->ctrl->redirect($this, 'show');
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function updateTLT(): void
    {
        $paths = $this->lom_services->paths();
        $data_helper = $this->lom_services->dataHelper();

        $tlt = (array) ($this->http->request()->getParsedBody()['tlt'] ?? []);
        foreach ($tlt as $item_id => $item) {
            $lom_duration = $data_helper->durationFromIntegers(
                null,
                (int) $item['mo'],
                (int) $item['d'],
                (int) $item['h'],
                (int) $item['m'],
                null
            );
            $this->lom_services->manipulate($this->getObjId(), $item_id, 'st')
                               ->prepareCreateOrUpdate(
                                   $paths->firstTypicalLearningTime(),
                                   $lom_duration
                               )->execute();
        }

        // refresh learning progress
        $this->obj_lp->resetCaches();
        ilLPStatusWrapper::_refreshStatus($this->getObjId());

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'show');
    }

    protected function getLPPathInfo(int $a_ref_id, array &$a_res): bool
    {
        $has_lp_parents = false;

        $path = $this->tree->getNodePath($a_ref_id);
        array_shift($path);     // root
        foreach ($path as $node) {
            $supports_lp = ilObjectLP::isSupportedObjectType($node["type"]);
            if ($supports_lp || $has_lp_parents) {
                $a_res[(int) $node["child"]]["node"] = array(
                    "type" => (string) $node["type"]
                    ,
                    "title" => (string) $node["title"]
                    ,
                    "obj_id" => (int) $node["obj_id"]
                    ,
                    "lp" => false
                    ,
                    "active" => false
                );
            }

            if (
                $supports_lp &&
                $node["child"] != $a_ref_id) {
                $a_res[(int) $node["child"]]["node"]["lp"] = true;
                $has_lp_parents = true;

                $parent_obj_id = (int) $node['obj_id'];
                $parent_obj_lp = \ilObjectLP::getInstance($parent_obj_id);
                $parent_collection = $parent_obj_lp->getCollectionInstance();
                if (
                    $parent_collection &&
                    $parent_collection->hasSelectableItems() &&
                    $parent_collection->isAssignedEntry($a_ref_id)
                ) {
                    $a_res[$node['child']]['node']['active'] = true;
                }
            }
        }
        return $has_lp_parents;
    }

    protected function handleLPUsageInfo(): string
    {
        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->http->wrapper()->post()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->post()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $coll = array();
        if ($ref_id &&
            $this->getLPPathInfo((int) $ref_id, $coll)) {
            $tpl = new ilTemplate(
                "tpl.lp_obj_settings_tree_info.html",
                true,
                true,
                "components/ILIAS/Tracking"
            );

            $margin = 0;
            $has_active = false;
            foreach ($coll as $parent_ref_id => $parts) {
                $node = $parts["node"];
                $params = array();
                if ($node["lp"]) {
                    if ($node["active"]) {
                        $tpl->touchBlock("parent_active_bl");
                        $has_active = true;
                    }

                    $params["gotolp"] = 1;
                }

                if ($this->access->checkAccess("read", "", $parent_ref_id) &&
                    $parent_ref_id != $ref_id) { // #17170
                    $tpl->setCurrentBlock("parent_link_bl");
                    $tpl->setVariable("PARENT_LINK_TITLE", $node["title"]);
                    $tpl->setVariable(
                        "PARENT_URL",
                        ilLink::_getLink(
                            $parent_ref_id,
                            $node["type"],
                            $params
                        )
                    );
                    $tpl->parseCurrentBlock();
                } else {
                    $tpl->setCurrentBlock("parent_nolink_bl");
                    $tpl->setVariable("PARENT_NOLINK_TITLE", $node["title"]);
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock("parent_usage_bl");
                $tpl->setVariable(
                    "PARENT_TYPE_URL",
                    ilObject::_getIcon(
                        $node["obj_id"],
                        "small",
                        $node["type"]
                    )
                );
                $tpl->setVariable(
                    "PARENT_TYPE_ALT",
                    $this->lng->txt("obj_" . $node["type"])
                );

                $tpl->setVariable(
                    "PARENT_STYLE",
                    $node["lp"]
                    ? ''
                    : ' class="ilLPParentInfoListLPUnsupported"'
                );
                $tpl->setVariable("MARGIN", $margin);
                $tpl->parseCurrentBlock();

                $margin += 25;
            }

            if ($has_active) {
                $tpl->setVariable(
                    "LEGEND",
                    sprintf(
                        $this->lng->txt("trac_lp_settings_info_parent_legend"),
                        ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
                    )
                );
            }

            $panel = $this->ui_factory->panel()->secondary()->legacy(
                $this->lng->txt("trac_lp_settings_info_parent_container"),
                $this->ui_factory->legacy()->content($tpl->get())
            );

            return $this->ui_renderer->render($panel);
        }
        return '';
    }
}
