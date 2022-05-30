<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function __construct(int $a_mode, int $a_ref_id)
    {
        parent::__construct($a_mode, $a_ref_id);

        $this->obj_settings = new ilLPObjSettings($this->getObjId());
        $this->obj_lp = ilObjectLP::getInstance($this->getObjId());
    }

    /**
     * execute command
     */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();

        }
    }

    protected function initItemIdsFromPost() : array
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
    protected function show() : void
    {
        $this->help->setSubScreenId("trac_settings");
        $info = $this->obj_lp->getSettingsInfo();
        if ($info) {
            $this->tpl->setOnScreenMessage('info', $info);
        }

        $form = $this->initFormSettings();
        $this->tpl->setContent(
            $this->handleLPUsageInfo() .
            $form->getHTML() .
            $this->getTableByMode()
        );
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('tracking_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        // Mode
        $mod = new ilRadioGroupInputGUI($this->lng->txt('trac_mode'), 'modus');
        $mod->setRequired(true);
        $mod->setValue((string) $this->obj_lp->getCurrentMode());
        $form->addItem($mod);

        if ($this->obj_lp->hasIndividualModeOptions()) {
            $this->obj_lp->initInvidualModeOptions($mod);
        } else {
            foreach ($this->obj_lp->getValidModes() as $mode_key) {
                $opt = new ilRadioOption(
                    $this->obj_lp->getModeText($mode_key),
                    (string) $mode_key,
                    $this->obj_lp->getModeInfoText($mode_key)
                );
                $opt->setValue((string) $mode_key);
                $mod->addOption($opt);

                // :TODO: Subitem for visits ?!
                if ($mode_key == ilLPObjSettings::LP_MODE_VISITS) {
                    $vis = new ilNumberInputGUI(
                        $this->lng->txt('trac_visits'),
                        'visits'
                    );
                    $vis->setSize(3);
                    $vis->setMaxLength(4);
                    $vis->setInfo(
                        sprintf(
                            $this->lng->txt('trac_visits_info'),
                            (string) ilObjUserTracking::_getValidTimeSpan()
                        )
                    );
                    $vis->setRequired(true);
                    $vis->setValue((string) $this->obj_settings->getVisits());
                    $opt->addSubItem($vis);
                }
                $this->obj_lp->appendModeConfiguration((int) $mode_key, $opt);
            }
        }
        $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        return $form;
    }

    protected function saveSettings() : void
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            // anything changed?

            // mode
            if ($this->obj_lp->shouldFetchIndividualModeFromFormSubmission()) {
                $new_mode = $this->obj_lp->fetchIndividualModeFromFormSubmission(
                    $form
                );
            } else {
                $new_mode = (int) $form->getInput('modus');
            }
            $old_mode = $this->obj_lp->getCurrentMode();
            $mode_changed = ($old_mode != $new_mode);

            // visits
            $new_visits = null;
            $visits_changed = null;
            if ($new_mode == ilLPObjSettings::LP_MODE_VISITS) {
                $new_visits = (int) $form->getInput('visits');
                $old_visits = $this->obj_settings->getVisits();
                $visits_changed = ($old_visits != $new_visits);
            }

            $this->obj_lp->saveModeConfiguration($form, $mode_changed);

            if ($mode_changed) {
                // delete existing collection
                $collection = $this->obj_lp->getCollectionInstance();
                if ($collection) {
                    $collection->delete();
                }
            }

            $refresh_lp = ($mode_changed || $visits_changed);

            // has to be done before LP refresh!
            $this->obj_lp->resetCaches();

            $this->obj_settings->setMode($new_mode);
            $this->obj_settings->setVisits((int) $new_visits);
            $this->obj_settings->update($refresh_lp);

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

        $form->setValuesByPost();

        $this->tpl->setContent(
            $this->handleLPUsageInfo() .
            $form->getHTML() .
            $this->getTableByMode()
        );
    }

    /**
     * Get tables by mode
     */
    protected function getTableByMode() : string
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

    protected function assign() : void
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

    protected function deassign() : void
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
    protected function groupMaterials() : void
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
    protected function releaseMaterials() : void
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
    protected function saveObligatoryMaterials() : void
    {
        $groups = [];
        if ($this->http->wrapper()->post()->has('grp')) {
            $groups = $this->http->wrapper()->post()->retrieve(
                'grp',
                $this->refinery->kindlyTo()->listOf(
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
    protected function updateTLT() : void
    {
        $tlt = (array) ($this->http->request()->getParsedBody()['tlt'] ?? []);
        foreach ($tlt as $item_id => $item) {
            $md_obj = new ilMD($this->getObjId(), $item_id, 'st');
            if (!is_object($md_section = $md_obj->getEducational())) {
                $md_section = $md_obj->addEducational();
                $md_section->save();
            }
            $md_section->setPhysicalTypicalLearningTime(
                (int) $item['mo'],
                (int) $item['d'],
                (int) $item['h'],
                (int) $item['m'],
                0
            );
            $md_section->update();
        }

        // refresh learning progress
        ilLPStatusWrapper::_refreshStatus($this->getObjId());

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'show');
    }

    protected function getLPPathInfo(int $a_ref_id, array &$a_res) : bool
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

    protected function handleLPUsageInfo() : string
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
                "Services/Tracking"
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

            $panel = ilPanelGUI::getInstance();
            $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
            $panel->setHeading(
                $this->lng->txt("trac_lp_settings_info_parent_container")
            );
            $panel->setBody($tpl->get());

            return $panel->getHTML();
        }
        return '';
    }
}
