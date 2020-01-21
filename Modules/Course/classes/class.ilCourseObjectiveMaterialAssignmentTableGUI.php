<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* TableGUI for material assignments of course objectives
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilCourseObjectiveMaterialAssignmentTableGUI extends ilTable2GUI
{
    private $objective = null;
    private $objective_lm = null;
    
    private $objective_id = 0;
    private $course_obj;

    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_course_obj, $a_objective_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->objective_id = $a_objective_id;
        $this->course_obj = $a_course_obj;
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule('crs');
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, 'materialAssignment');
        $this->setFormName('assignments');
        $this->addColumn('', 'f', "1");
        $this->addColumn($this->lng->txt('type'), 'type', "1px");
        $this->addColumn($this->lng->txt('title'), 'title', '99%');
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.crs_objective_list_materials_row.html", "Modules/Course");
        #$this->disable('sort');
        #$this->disable('header');
        #$this->disable('numinfo');
        #$this->disable('select_all');
        
        $this->setDefaultOrderField('title');
        $this->setLimit(200);
        
        $this->setNoEntriesText($this->lng->txt('crs_no_objective_lms_found'));
        
        
        $this->addCommandButton('updateMaterialAssignment', $this->lng->txt('crs_wiz_next'));
        #$this->addMultiCommand('updateMaterialAssignment', $this->lng->txt('crs_wiz_next'));
        $this->addCommandButton('create', $this->lng->txt('crs_wiz_back'));
        
        $this->initObjectiveAssignments();
    }
    
    /**
     * fill row
     *
     * @access protected
     * @param array row data
     * @return
     */
    protected function fillRow($a_set)
    {
        foreach ($a_set['sub'] as $sub_data) {
            // Indentation
            for ($i = $sub_data['depth'];$i > 1;$i--) {
                $this->tpl->touchBlock('begin_depth');
                $this->tpl->touchBlock('end_depth');
            }

            $this->tpl->setCurrentBlock('chapter');
            include_once('Modules/LearningModule/classes/class.ilLMObject.php');
            
            if ($this->objective_lm->isChapterAssigned($a_set['id'], $sub_data['id'])) {
                $this->tpl->setVariable('CHAP_CHECKED', 'checked="checked"');
            }
            
            $this->tpl->setVariable('CHAPTER_TITLE', ilLMObject::_lookupTitle($sub_data['id']));
            $this->tpl->setVariable('CHAP_ID', $a_set['id'] . '_' . $sub_data['id']);
            $this->tpl->setVariable('CHAP_TYPE_IMG', ilObject::_getIcon($sub_data['id'], "tiny", $sub_data['type']));
            $this->tpl->setVariable('CHAP_TYPE_ALT', $this->lng->txt('obj_' . $sub_data['type']));
            $this->tpl->parseCurrentBlock();
        }
        if (count($a_set['sub'])) {
            $this->tpl->setVariable('TXT_CHAPTER', $this->lng->txt('objs_st'));
        }

        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        
        if ($this->objective_lm->isAssigned($a_set['id'])) {
            $this->tpl->setVariable('VAL_CHECKED', 'checked="checked"');
        }
        
        $this->tpl->setVariable('ROW_TYPE_IMG', ilObject::_getIcon($a_set['obj_id'], "tiny", $a_set['type']));
        $this->tpl->setVariable('ROW_TYPE_ALT', $this->lng->txt('obj_' . $a_set['type']));
        
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
    }
    
    /**
     * parse
     *
     * @access public
     * @param array array of assignable nodes (tree node data)
     * @return
     */
    public function parse($a_assignable)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        $materials = array();
        foreach ($a_assignable as $node) {
            // no side blocks here
            if ($objDefinition->isSideBlock($node['type'])) {
                continue;
            }
            
            $tmp_data = array();
            $subobjects = array();

            if ($node['type'] == 'lm') {
                include_once('./Modules/LearningModule/classes/class.ilLMObject.php');


                // Chapters and pages
                foreach ($chapters = $this->getAllSubObjects($node['child']) as $chapter => $chapter_data) {
                    $sub['title'] = ilLMObject::_lookupTitle($chapter);
                    $sub['id'] = $chapter;
                    $sub['depth'] = $chapter_data['depth'];
                    $sub['type'] = $chapter_data['type'];
                    
                    $subobjects[] = $sub;
                }
            }
            
            $tmp_data['sub'] = $subobjects;
            $tmp_data['title'] = $node['title'];
            $tmp_data['description'] = $node['description'];
            $tmp_data['type'] = $node['type'];
            $tmp_data['id'] = $node['child'];
            $tmp_data['obj_id'] = $node['obj_id'];
            
            $materials[] = $tmp_data;
        }
        
        $this->setData($materials);
    }
    
    /**
     * get all subobject (structure and page objects) of a lm
     *
     * @access protected
     * @return
     */
    protected function getAllSubObjects($a_ref_id)
    {
        $tree = new ilTree(ilObject::_lookupObjId($a_ref_id));
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");

        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId())) as $node) {
            if ($node['type'] == 'st' or $node['type'] == 'pg') {
                $depth = $node['depth'] - 1;
                $child = $node['child'];
                $chapter[$child]['depth'] = $depth;
                $chapter[$child]['type'] = $node['type'];
            }
        }
        return $chapter ? $chapter : array();
    }

    
    /**
     * init objective assignments
     *
     * @access protected
     * @return
     */
    protected function initObjectiveAssignments()
    {
        include_once('./Modules/Course/classes/class.ilCourseObjective.php');
        $this->objective = new ilCourseObjective($this->course_obj, $this->objective_id);
        
        include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
        $this->objective_lm = new ilCourseObjectiveMaterials($this->objective_id);
    }
}
