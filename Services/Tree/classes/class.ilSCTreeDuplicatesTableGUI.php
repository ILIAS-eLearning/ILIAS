<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Defines a system check task
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeDuplicatesTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        $this->setId('sysc_tree_duplicates');
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }
    
    /**
     * Init Table
     */
    public function init()
    {
        $this->setExternalSorting(true);
        $this->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this->getParentObject()));
        
        $this->setDisableFilterHiding(true);
        $this->setRowTemplate('tpl.sc_tree_duplicates_row.html', 'Services/Tree');

        $this->addColumn($this->lng->txt('sysc_duplicates_repository'), '');
        $this->addColumn($this->lng->txt('sysc_duplicates_trash'), '');
        
        $this->addCommandButton('deleteDuplicatesFromRepository', $this->lng->txt('sysc_delete_duplicates_from_repository'));
        $this->addCommandButton('deleteDuplicatesFromTrash', $this->lng->txt('sysc_delete_duplicates_from_trash'));
    }
    
    /**
     * parse data
     */
    public function parse($a_duplicate_id)
    {
        $this->setData(array(array('id' => $a_duplicate_id)));
    }
    
    
    /**
     *
     * @param type $a_set
     */
    public function fillRow($a_set)
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $duplicates = ilSCTreeTasks::findDuplicates($a_set['id']);
        
        $this->tpl->setVariable('DUP_ID', $a_set['id']);
        
        
        $GLOBALS['DIC']['ilLog']->write('---------------------------- ' . print_r($duplicates, true));
        
        foreach ($duplicates as $a_id => $node) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': DUPPSSSSSSSSSSSSSSSSSSS ' . print_r($node, true));
            if ($node['tree'] == 1) {
                include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
                $childs = ilSCTreeTasks::getChilds($node['tree'], $node['child']);
                
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': DUPPSSSSSSSSSSSSSSSSSSS CHILDSSSSSSSSSSS' . print_r($childs, true));
                $start_depth = $node['depth'];

                $this->fillObjectRow($node['tree'], $node['child'], $start_depth, '');
                
                include_once './Services/Tree/classes/class.ilPathGUI.php';
                $path = new ilPathGUI();
                $path->enableHideLeaf(true);
                $path->enableTextOnly(false);
                $this->tpl->setVariable('PATH', $path->getPath(ROOT_FOLDER_ID, $node['child']));
                
                
                foreach ($childs as $child) {
                    $this->fillObjectRow($node['tree'], $child, $start_depth, '');
                }
            }
            if ($node['tree'] < 1) {
                include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
                $childs = ilSCTreeTasks::getChilds($node['tree'], $node['child']);
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': TRASJH DUPPSSSSSSSSSSSSSSSSSSS CHILDSSSSSSSSSSS' . print_r($childs, true));

                $start_depth = $node['depth'];

                $this->fillObjectRow($node['tree'], $node['child'], $start_depth, 'b');
                
                foreach ($childs as $child) {
                    $this->fillObjectRow($node['tree'], $child, $start_depth, 'b');
                }
            }
        }
    }
    
    /**
     *
     * @param type $a_ref_id
     * @param type $a_start_depth
     * @param type $a_prefix
     */
    protected function fillObjectRow($a_tree_id, $a_ref_id, $a_start_depth, $a_prefix)
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $child_data = ilSCTreeTasks::getNodeInfo($a_tree_id, $a_ref_id);
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($child_data, true));

        for ($i = $a_start_depth; $i <= $child_data['depth']; $i++) {
            $this->tpl->touchBlock($a_prefix . 'padding');
            $this->tpl->touchBlock($a_prefix . 'end_padding');
        }
        $this->tpl->setVariable($a_prefix . 'OBJ_TITLE', $child_data['title']);

        if ($child_data['description']) {
            $this->tpl->setVariable($a_prefix . 'VAL_DESC', $child_data['description']);
        }
        $this->tpl->setVariable($a_prefix . 'TYPE_IMG', ilUtil::getTypeIconPath($child_data['type'], $child_data['obj_id']));
        $this->tpl->setVariable($a_prefix . 'TYPE_STR', $this->lng->txt('obj_' . $child_data['type']));
    }
}
