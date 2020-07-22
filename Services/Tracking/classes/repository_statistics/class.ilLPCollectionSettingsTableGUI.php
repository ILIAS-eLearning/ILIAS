<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPCollectionSettingsTableGUI extends ilTable2GUI
{
    private $node_id;
    private $mode;

    /**
     * Constructor
     * @param ilObject $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_node_id, $a_mode)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setId('lpobjs_' . $this->getNode());
        
        $this->setShowRowsSelector(false);
        
        $this->node_id = $a_node_id;
        $this->mode = $a_mode;
    }

    /**
     * Get node id of current learning progress item
     * @return int $node_id
     */
    public function getNode()
    {
        return $this->node_id;
    }
    
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
     * Read and parse items
     */
    public function parse(ilLPCollection $a_collection)
    {
        $this->setData($a_collection->getTableGUIData($this->getNode()));
        $this->initTable();
        
        // grouping actions
        if ($this->getMode() == ilLPObjSettings::LP_MODE_COLLECTION &&
            ilLPCollectionOfRepositoryObjects::hasGroupedItems(ilObject::_lookupObjId($this->getNode()))) {
            $this->addMultiCommand('releaseMaterials', $this->lng->txt('trac_release_materials'));
            
            foreach ($this->row_data as $item) {
                if ($item["grouped"]) {
                    $this->addCommandButton('saveObligatoryMaterials', $this->lng->txt('trac_group_materials_save'));
                    break;
                }
            }
        }
    }

    /**
     * Fill template row
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $ilAccess = $DIC['ilAccess'];
        
        include_once './Services/Link/classes/class.ilLink.php';

        $this->tpl->setCurrentBlock('item_row');
        $this->tpl->setVariable('ITEM_ID', $a_set['id']);
        $this->tpl->setVariable('COLL_TITLE', $a_set['title']);
        $this->tpl->setVariable('COLL_DESC', $a_set['description']);
            
        if ($objDefinition->isPluginTypeName($a_set["type"])) {
            $alt = ilObjectPlugin::lookupTxtById($a_set['type'], "obj_" . $a_set['type']);
        } else {
            $alt = $this->lng->txt('obj_' . $a_set['type']);
        }
        $this->tpl->setVariable('ALT_IMG', $alt);
        $this->tpl->setVariable(
            'TYPE_IMG',
            ilObject::_getIcon(
                $a_set['obj_id'],
                'tiny',
                $a_set['type']
            )
        );

        if ($this->getMode() != ilLPObjSettings::LP_MODE_COLLECTION_MANUAL &&
            $this->getMode() != ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
            if ($a_set['ref_id']) {
                $this->tpl->setVariable('COLL_LINK', ilLink::_getLink($a_set['ref_id'], $a_set['type']));
                $this->tpl->setVariable('COLL_FRAME', ilFrameTargetInfo::_getFrame('MainContent', $a_set['type']));

                include_once './Services/Tree/classes/class.ilPathGUI.php';
                $path = new ilPathGUI();
                $this->tpl->setVariable('COLL_PATH', $this->lng->txt('path') . ': ' . $path->getPath($this->getNode(), $a_set['ref_id']));

                $mode_suffix = '';
                if ($a_set['grouped'] ||
                    $a_set['group_item']) {
                    // #14941
                    $mode_suffix = '_INLINE';

                    if (!$a_set['group_item']) {
                        $this->tpl->setVariable("COLL_MODE", "");
                    }
                }
                include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
                if (ilLearningProgressAccess::checkPermission('edit_learning_progress', $a_set['ref_id'])) {
                    $lp_settings_link = ilLink::_getLink($a_set['ref_id'], $a_set['type'], array('gotolp' => 1));
                    $a_set["mode"] = '<a href="' . $lp_settings_link . '">' . $a_set['mode'] . '</a>'; // :TODO: il_ItemAlertProperty?
                }

                $mode = $a_set['mode_id'];
                if ($mode != ilLPObjSettings::LP_MODE_DEACTIVATED && $mode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    $this->tpl->setVariable("COLL_MODE" . $mode_suffix, $a_set['mode']);
                } else {
                    $this->tpl->setVariable("COLL_MODE" . $mode_suffix, "");
                    $this->tpl->setVariable("COLL_MODE_DEACTIVATED" . $mode_suffix, $a_set['mode']);
                }
                if ($a_set["anonymized"]) {
                    $this->tpl->setVariable("ANONYMIZED" . $mode_suffix, $this->lng->txt('trac_anonymized_info_short'));
                }

                if ($mode_suffix) {
                    $this->tpl->setVariable("COLL_MODE_LABEL", $this->lng->txt("trac_mode"));
                }
            }
        } else {
            $this->tpl->setVariable('COLL_LINK', $a_set['url']);

            if ($this->getMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
                // handle tlt settings
                $this->tpl->setCurrentBlock("tlt");
                $this->tpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
                $this->tpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
                $this->tpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));
                $this->tpl->setVariable("TLT_HINT", '(hh:mm)');

                // seconds to units
                $mon = floor($a_set["tlt"] / (60 * 60 * 24 * 30));
                $tlt = $a_set["tlt"] % (60 * 60 * 24 * 30);
                $day = floor($tlt / (60 * 60 * 24));
                $tlt = $tlt % (60 * 60 * 24);
                $hr = floor($tlt / (60 * 60));
                $tlt = $tlt % (60 * 60);
                $min = floor($tlt / 60);

                $options = array();
                for ($i = 0;$i <= 24;$i++) {
                    $options[$i] = sprintf('%02d', $i);
                }
                $this->tpl->setVariable(
                    "SEL_MONTHS",
                    ilUtil::formSelect($mon, 'tlt[' . $a_set['id'] . '][mo]', $options, false, true)
                );

                for ($i = 0;$i <= 31;$i++) {
                    $options[$i] = sprintf('%02d', $i);
                }
                $this->tpl->setVariable(
                    "SEL_DAYS",
                    ilUtil::formSelect($day, 'tlt[' . $a_set['id'] . '][d]', $options, false, true)
                );

                $this->tpl->setVariable("SEL_TLT", ilUtil::makeTimeSelect(
                    'tlt[' . $a_set['id'] . ']',
                    true,
                    $hr,
                    $min,
                    null,
                    false
                ));

                $this->tpl->parseCurrentBlock();
            }
        }

        // Assigned ?
        $this->tpl->setVariable(
            "ASSIGNED_IMG_OK",
            $a_set['status']
                ? ilUtil::getImagePath('icon_ok.svg')
                : ilUtil::getImagePath('icon_not_ok.svg')
        );
        $this->tpl->setVariable(
            "ASSIGNED_STATUS",
            $a_set['status']
                ? $this->lng->txt('trac_assigned')
                : $this->lng->txt('trac_not_assigned')
        );
        $this->tpl->parseCurrentBlock();


        // Parse grouped items
        foreach ((array) $a_set['grouped'] as $item) {
            $item['group_item'] = true;
            $this->fillRow($item);
        }

        // show num obligatory info
        if (
            is_array($a_set) &&
            array_key_exists('grouped', $a_set) &&
            count($a_set['grouped'])
        ) {
            $this->tpl->setCurrentBlock('num_passed_items');
            $this->tpl->setVariable('MIN_PASSED_TXT', $this->lng->txt('trac_min_passed'));
            $this->tpl->setVariable('NUM_OBLIGATORY', $a_set['num_obligatory']);
            $this->tpl->setVariable('GRP_ID', $a_set['grouping_id']);
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function initTable()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        switch ($this->getMode()) {
            case ilLPObjSettings::LP_MODE_COLLECTION:
            case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
                $this->setRowTemplate('tpl.lp_collection_row.html', 'Services/Tracking');
                $this->setTitle($this->lng->txt('trac_lp_determination'));
                $this->setDescription($this->lng->txt('trac_lp_determination_info_crs'));
                break;

            case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
                $this->setRowTemplate('tpl.lp_collection_row.html', 'Services/Tracking');
                $this->setTitle($this->lng->txt('trac_lp_determination_tutor'));
                $this->setDescription($this->lng->txt('trac_lp_determination_info_crs_tutor'));
                break;

            case ilLPObjSettings::LP_MODE_SCORM:
                $this->setRowTemplate('tpl.lp_collection_scorm_row.html', 'Services/Tracking');
                $this->setTitle($this->lng->txt('trac_lp_determination'));
                $this->setDescription($this->lng->txt('trac_lp_determination_info_sco'));
                break;
            
            case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
                $this->setRowTemplate('tpl.lp_collection_subitem_row.html', 'Services/Tracking');
                $this->setTitle($this->lng->txt('trac_lp_determination'));
                $this->setDescription($this->lng->txt('trac_lp_determination_info_crs'));
                $this->lng->loadLanguageModule("meta");
                
                $this->addCommandButton('updateTLT', $this->lng->txt('save'));
                break;
            
            case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                $this->setRowTemplate('tpl.lp_collection_subitem_row.html', 'Services/Tracking');
                $this->setTitle($this->lng->txt('trac_lp_determination'));
                $this->setDescription($this->lng->txt('trac_lp_determination_info_mob'));
                break;
        }

        $this->addColumn('', '', '1px');
        $this->addColumn($this->lng->txt('item'), 'title', '50%');
        
        if ($this->getMode() != ilLPObjSettings::LP_MODE_SCORM &&
            $this->getMode() != ilLPObjSettings::LP_MODE_COLLECTION_MOBS &&
            $this->getMode() != ilLPObjSettings::LP_MODE_COLLECTION_MANUAL &&
            $this->getMode() != ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
            $this->addColumn($this->lng->txt('trac_mode'), 'mode');
        } elseif ($this->getMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
            $this->addColumn($this->lng->txt('meta_typical_learning_time'), 'tlt');
        }
        
        if ($this->getMode() != ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) {
            $this->addMultiCommand('assign', $this->lng->txt('trac_collection_assign'));
            $this->addMultiCommand('deassign', $this->lng->txt('trac_collection_deassign'));
            $this->addColumn($this->lng->txt('trac_determines_learning_progress'), 'status');
        } else {
            $this->addMultiCommand('assign', $this->lng->txt('trac_manual_display'));
            $this->addMultiCommand('deassign', $this->lng->txt('trac_manual_no_display'));
            $this->addColumn($this->lng->txt('trac_manual_is_displayed'), 'status');
        }

        $this->enable('select_all');
        $this->setSelectAllCheckbox('item_ids');

        if ($this->getMode() == ilLPObjSettings::LP_MODE_COLLECTION) {
            $this->addMultiCommand('groupMaterials', $this->lng->txt('trac_group_materials'));
        }
    }
}
