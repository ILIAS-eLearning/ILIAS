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
include_once './Modules/Course/classes/class.ilCourseObjective.php';
include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
include_once('./Modules/Course/classes/class.ilCourseObjectiveQuestion.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse 
*/
class ilCourseObjectivesTableGUI extends ilTable2GUI
{
	protected $course_obj = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object parent gui object
	 * @return
	 */
	public function __construct($a_parent_obj,$a_course_obj)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->course_obj = $a_course_obj;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('crs');
	 	$this->ctrl = $ilCtrl;
	 	
		parent::__construct($a_parent_obj,'listObjectives');
		$this->setFormName('objectives');
	 	$this->addColumn('','f',"1px");
	 	$this->addColumn($this->lng->txt('position'),'1px');
	 	$this->addColumn($this->lng->txt('title'),'title','25%');
	 	$this->addColumn($this->lng->txt('crs_objective_assigned_materials'),'materials','25%');
	 	$this->addColumn($this->lng->txt('crs_objective_self_assessment'),'self','25%');
	 	$this->addColumn($this->lng->txt('crs_objective_final_test'),'final','25%');
	 	$this->addColumn($this->lng->txt(''),'5em');
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.crs_objectives_table_row.html","Modules/Course");
		$this->disable('sort');
		$this->enable('header');
		$this->disable('numinfo');
		$this->enable('select_all');
		$this->setLimit(200);
		
		$this->addMultiCommand('askDeleteObjective',$this->lng->txt('delete'));
		$this->addCommandButton('saveSorting',$this->lng->txt('sorting_save'));
		$this->addCommandButton('create',$this->lng->txt('crs_add_objective'));
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
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('VAL_POSITION',$a_set['position']);
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable('VAL_DESC',$a_set['description']);
		}
		
		// materials
		foreach($a_set['materials'] as $ref_id => $data)
		{
			if($data['items'])
			{
				$this->tpl->touchBlock('ul_begin');
				foreach($data['items'] as $pg_st)
				{
					$this->tpl->setCurrentBlock('st_pg');
					$this->tpl->setVariable('MAT_IMG',ilUtil::getImagePath('icon_'.$pg_st['type'].'_s.gif'));
					$this->tpl->setVariable('MAT_ALT',$this->lng->txt('obj_'.$pg_st['type']));
					include_once('Modules/LearningModule/classes/class.ilLMObject.php');
					$title = ilLMObject::_lookupTitle($pg_st['obj_id']);
					$this->tpl->setVariable('MAT_TITLE',$title);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->touchBlock('ul_end');
			}
			else
			{
				$this->tpl->touchBlock('new_line');
			}
			$this->tpl->setCurrentBlock('mat_row');
			$this->tpl->setVariable('LM_IMG',ilUtil::getImagePath('icon_'.$data['type'].'_s.gif'));
			$this->tpl->setVariable('LM_ALT',$this->lng->txt('obj_'.$data['type']));
			$this->tpl->setVariable('LM_TITLE',ilObject::_lookupTitle($data['obj_id']));
			$this->tpl->parseCurrentBlock();
		}
		
		// self assessment
		foreach($a_set['self'] as $test)
		{
			foreach($test['questions'] as $question)
			{
				$this->tpl->setCurrentBlock('self_qst_row');
				$this->tpl->setVariable('SELF_QST_TITLE',$question['title']);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock('self_test_row');
			$this->tpl->setVariable('SELF_TST_IMG',ilUtil::getImagePath('icon_tst_s.gif'));
			$this->tpl->setVariable('SELF_TST_ALT',$this->lng->txt('obj_tst'));
			$this->tpl->setVariable('SELF_TST_TITLE',ilObject::_lookupTitle($test['obj_id']));
			$this->tpl->parseCurrentBlock();	
		}				

		// final test questions
		foreach($a_set['final'] as $test)
		{
			foreach($test['questions'] as $question)
			{
				$this->tpl->setCurrentBlock('final_qst_row');
				$this->tpl->setVariable('FINAL_QST_TITLE',$question['title']);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock('final_test_row');
			$this->tpl->setVariable('FINAL_TST_IMG',ilUtil::getImagePath('icon_tst_s.gif'));
			$this->tpl->setVariable('FINAL_TST_ALT',$this->lng->txt('obj_tst'));
			$this->tpl->setVariable('FINAL_TST_TITLE',ilObject::_lookupTitle($test['obj_id']));
			$this->tpl->parseCurrentBlock();	
		}	
		
		// Edit Link
		$this->ctrl->setParameterByClass(get_class($this->getParentObject()),'objective_id',$a_set['id']);
		$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'edit'));
		$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));			
	}
		
	
	/**
	 * parse
	 *
	 * @access public
	 * @param array array of objective id's
	 */
	public function parse($a_objective_ids)
	{
		$position = 1;
		foreach($a_objective_ids as $objective_id)
		{
			$objective = new ilCourseObjective($this->course_obj,$objective_id);
			
			$objective_data['id'] = $objective_id;
			$objective_data['position'] = sprintf("%.1f",$position++);
			$objective_data['title'] = $objective->getTitle();
			$objective_data['description'] = $objective->getDescription();
			
			// assigned materials
			$materials = array();
			$ass_materials = new ilCourseObjectiveMaterials($objective_id);
			foreach($ass_materials->getMaterials() as $material)
			{
				$materials[$material['ref_id']]['obj_id'] = $obj_id = ilObject::_lookupObjId($material['ref_id']);
				$materials[$material['ref_id']]['type'] = ilObject::_lookupType($obj_id);  

				switch($material['type'])
				{
					case 'pg':
					case 'st':
						$materials[$material['ref_id']]['items'][] = $material;
						break;
					default:
						
				}


			}
			$objective_data['materials'] = $materials;
			$question_obj = new ilCourseObjectiveQuestion($objective_id);
			
			// self assessment questions
			$tests = array();
			foreach($question_obj->getSelfAssessmentTests() as $test)
			{
				$questions = array();
				foreach($question_obj->getQuestionsOfTest($test['obj_id']) as $qst)
				{
					$questions[] = $qst;
				}
				$tmp_test = $test;
				$tmp_test['questions'] = $questions;
				
				$tests[] = $tmp_test;
			}
			$objective_data['self'] = $tests;
			
			// final test questions
			$tests = array();
			foreach($question_obj->getFinalTests() as $test)
			{
				$questions = array();
				foreach($question_obj->getQuestionsOfTest($test['obj_id']) as $qst)
				{
					$questions[] = $qst;
				}
				$tmp_test = $test;
				$tmp_test['questions'] = $questions;
				
				$tests[] = $tmp_test;
			}
			$objective_data['final'] = $tests;

			$objectives[] = $objective_data;
		}
		
		$this->setData($objectives ? $objectives : array());
	}
	
}
?>