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
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Show active rules
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSCategoryMappingTableGUI extends ilTable2GUI
{
    private ilLogger $logger;

    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->logger = $DIC->logger()->wsrv();

        $this->addColumn('', 'f', '1px');
        $this->addColumn($this->lng->txt('obj_cat'), 'category', '40%');
        $this->addColumn($this->lng->txt('ecs_cat_mapping_type'), 'kind', '50%');
        $this->addColumn('', 'edit', '10%');
        $this->setRowTemplate('tpl.rule_row.html', 'Services/WebServices/ECS');
        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox('rules');
        $this->setTitle($this->lng->txt('ecs_tbl_active_rules'));
        $this->addMultiCommand('deleteCategoryMappings', $this->lng->txt('delete'));
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('TXT_ID', $this->lng->txt('ecs_import_id'));
        $this->tpl->setVariable('VAL_CAT_ID', $a_set['category_id']);
        $this->tpl->setVariable('TXT_TITLE', $this->lng->txt('title'));
        $this->tpl->setVariable('VAL_CAT_TITLE', $a_set['category']);
        $this->tpl->setVariable('VAL_CONDITION', $a_set['kind']);
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
        $this->tpl->setVariable('PATH', $this->buildPath($a_set['category_id']));
        if ($this->getParentObject()) {
            $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'rule_id', $a_set['id']);
            $this->tpl->setVariable(
                'EDIT_LINK',
                $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()), 'editCategoryMapping')
            );
            $this->ctrl->clearParametersByClass(get_class($this->getParentObject()));
        } else {
            $this->logger->error("Cannot fill Category Mapping Table due to parent object being null");
        }
    }

    /**
     * Parse
     * @param	array	$a_rules	Array of mapping rules
     */
    public function parse(array $a_rules) : void
    {
        $content = [];
        foreach ($a_rules as $rule) {
            $tmp_arr['id'] = $rule->getMappingId();
            $tmp_arr['category_id'] = $rule->getContainerId();
            $tmp_arr['category'] = ilObject::_lookupTitle(ilObject::_lookupObjId($rule->getContainerId()));
            $tmp_arr['kind'] = $rule->conditionToString();
            
            $content[] = $tmp_arr;
        }
        $this->setData($content);
    }
    
    private function buildPath(int $a_ref_id) : string
    {
        $loc = new ilLocatorGUI();
        $loc->setTextOnly(false);
        $loc->addContextItems($a_ref_id);
        
        return $loc->getHTML();
    }
}
