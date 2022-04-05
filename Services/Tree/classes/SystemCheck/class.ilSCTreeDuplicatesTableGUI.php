<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check task
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeDuplicatesTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd = '')
    {
        $this->setId('sysc_tree_duplicates');
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function init() : void
    {
        $this->setExternalSorting(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->setDisableFilterHiding(true);
        $this->setRowTemplate('tpl.sc_tree_duplicates_row.html', 'Services/Tree');

        $this->addColumn($this->lng->txt('sysc_duplicates_repository'), '');
        $this->addColumn($this->lng->txt('sysc_duplicates_trash'), '');

        $this->addCommandButton(
            'deleteDuplicatesFromRepository',
            $this->lng->txt('sysc_delete_duplicates_from_repository')
        );
        $this->addCommandButton('deleteDuplicatesFromTrash', $this->lng->txt('sysc_delete_duplicates_from_trash'));
    }

    public function parse(int $a_duplicate_id) : void
    {
        $this->setData(array(array('id' => $a_duplicate_id)));
    }

    protected function fillRow(array $a_set) : void
    {
        $id = (int) ($a_set['id'] ?? 0);
        $duplicates = ilSCTreeTasks::findDuplicates($id);

        $this->tpl->setVariable('DUP_ID', $id);

        foreach ($duplicates as $a_id => $node) {
            $child_id = (int) ($node['child'] ?? 0);
            if (isset($node['tree']) && $node['tree'] == 1) {
                $childs = ilSCTreeTasks::getChilds($node['tree'], $child_id);

                $start_depth = (int) ($node['depth'] ?? 0);

                $this->fillObjectRow($node['tree'], $child_id, $start_depth, '');

                $path = new ilPathGUI();
                $path->enableHideLeaf(true);
                $path->enableTextOnly(false);
                $this->tpl->setVariable('PATH', $path->getPath(ROOT_FOLDER_ID, $child_id));

                foreach ($childs as $child) {
                    $this->fillObjectRow($node['tree'], $child, $start_depth, '');
                }
            }
            if (isset($node['tree']) && $node['tree'] < 1) {
                $childs = ilSCTreeTasks::getChilds($node['tree'], $child_id);

                $start_depth = (int) ($node['depth'] ?? 0);

                $this->fillObjectRow($node['tree'], $child_id, $start_depth, 'b');

                foreach ($childs as $child) {
                    $this->fillObjectRow($node['tree'], $child, $start_depth, 'b');
                }
            }
        }
    }

    protected function fillObjectRow(int $a_tree_id, int $a_ref_id, int $a_start_depth, string $a_prefix) : void
    {
        $child_data = ilSCTreeTasks::getNodeInfo($a_tree_id, $a_ref_id);

        $depth = (int) ($child_data['depth'] ?? 0);
        for ($i = $a_start_depth; $i <= $depth; $i++) {
            $this->tpl->touchBlock($a_prefix . 'padding');
            $this->tpl->touchBlock($a_prefix . 'end_padding');
        }
        $this->tpl->setVariable($a_prefix . 'OBJ_TITLE', (string) ($child_data['title'] ?? ''));

        if (isset($child_data['description']) && $child_data['description']) {
            $this->tpl->setVariable($a_prefix . 'VAL_DESC', $child_data['description']);
        }
        $this->tpl->setVariable(
            $a_prefix . 'TYPE_IMG',
            ilObject::_getIcon((int) ($child_data['obj_id'] ?? 0), "small", (string) ($child_data['type'] ?? ''))
        );
        $this->tpl->setVariable($a_prefix . 'TYPE_STR', $this->lng->txt('obj_' . ($child_data['type'] ?? '')));
    }
}
