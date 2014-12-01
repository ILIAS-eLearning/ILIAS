<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @author Björn Heyser <bheyser@databay.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestQuestionsTableGUI extends ilTable2GUI
{
	protected $writeAccess 		= false;
	protected $totalPoints 		= 0;
	protected $totalWorkingTime = '00:00:00';
	protected $checked_move 	= false;
	protected $total 			= 0;
	protected $position			= 0;

	/**
	 * @var array
	 */
	protected $visibleOptionalColumns = array();

	/**
	 * @var array
	 */
	protected $optionalColumns = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false, $a_checked_move = false, $a_total = 0)
	{
		$this->setId('tst_qst_lst_'.$a_parent_obj->object->getRefId());
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->total = $a_total;
	
		$this->setWriteAccess($a_write_access);
		$this->setCheckedMove($a_checked_move);
		
		$this->setFormName('questionbrowser');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn('','f','1%', true);
		$this->addColumn('','f','1%');
		$this->visibleOptionalColumns = (array)$this->getSelectedColumns();
		$this->optionalColumns        = (array)$this->getSelectableColumns();
		if(isset($this->visibleOptionalColumns['qid']))
		{
			$this->addColumn($this->optionalColumns['qid']['txt'],'qid', '');
		}
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		//$this->addColumn($this->lng->txt("tst_sequence"),'sequence', '');
		if( $a_parent_obj->object->areObligationsEnabled() )
		{
			$this->addColumn($this->lng->txt("obligatory"),'obligatory', '');
		}
		if(isset($this->visibleOptionalColumns['description']))
		{
			$this->addColumn($this->optionalColumns['description']['txt'],'description', '');
		}
		$this->addColumn($this->lng->txt("tst_question_type"),'type', '');
		$this->addColumn($this->lng->txt("points"),'', '');
		if(isset($this->visibleOptionalColumns['author']))
		{
			$this->addColumn($this->optionalColumns['author']['txt'],'author', '');
		}
		if(isset($this->visibleOptionalColumns['working_time']))
		{
			$this->addColumn($this->optionalColumns['working_time']['txt'],'working_time', '');
		}
		$this->addColumn($this->lng->txt("qpl"),'qpl', '');

		$this->setSelectAllCheckbox('q_id');

		$this->setExternalSegmentation(true);

		if ($this->getWriteAccess() && !$this->getTotal())
		{
			$this->addMultiCommand('removeQuestions', $this->lng->txt('remove_question'));
			$this->addMultiCommand('moveQuestions', $this->lng->txt('move'));
			if ($this->checked_move)
			{
				$this->addMultiCommand('insertQuestionsBefore', $this->lng->txt('insert_before'));
				$this->addMultiCommand('insertQuestionsAfter', $this->lng->txt('insert_after'));
			}
                        //$this->addMultiCommand('copyToQuestionpool', $this->lng->txt('copy_to_questionpool'));
                        $this->addMultiCommand('copyQuestion', $this->lng->txt('copy'));
			$this->addMultiCommand('copyAndLinkToQuestionpool', $this->lng->txt('copy_and_link_to_questionpool'));
                        
		}


		$this->setRowTemplate("tpl.il_as_tst_questions_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		if( $a_parent_obj->object->areObligationsEnabled() )
		{
			$this->addCommandButton('saveOrderAndObligations', $this->lng->txt('saveOrderAndObligations'));
		}
		else
		{
			$this->addCommandButton('saveOrderAndObligations', $this->lng->txt('saveOrder'));
		}

		$this->disable('sort');
		$this->enable('header');
		$this->enable('select_all');
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$cols = array(
			'qid'         => array('txt' => $this->lng->txt('question_id'), 'default' => true),
			'description' => array('txt' => $this->lng->txt('description'), 'default' => false),
			'author'      => array('txt' => $this->lng->txt('author'), 'default' => false),
			'working_time'=> array('txt' => $this->lng->txt('working_time'), 'default' => false)
		);

		return $cols;
	}

	function fillHeader()
	{
		foreach ($this->column as $key => $column)
		{
			if (strcmp($column['text'], $this->lng->txt("points")) == 0)
			{
				$this->column[$key]['text'] = $this->lng->txt("points") . "&nbsp;(" . $this->totalPoints . ")";
			}
			elseif (strcmp($column['text'], $this->lng->txt("working_time")) == 0)
			{
				$this->column[$key]['text'] = $this->lng->txt("working_time") . "&nbsp;(" . $this->totalWorkingTime . ")";
			}
		}
		parent::fillHeader();
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		global $ilUser,$ilAccess;
		
		$q_id = $data["question_id"];

		$this->tpl->setVariable("QUESTION_ID", $q_id);
		if(isset($this->visibleOptionalColumns['qid']))
		{
			$this->tpl->setVariable("QUESTION_ID_PRESENTATION", $q_id);
		}
		if ($this->getWriteAccess() && !$this->getTotal() && $data["obj_fi"] > 0) 
		{
                        if (!$data['complete']) {
                            $this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("icon_alert.svg"));
                            $this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
                            $this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
                        }
                        
			
			$qpl_ref_id = current(ilObject::_getAllReferences($data["obj_fi"]));
			$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTarget($this->getParentObject(), "questions") . "&eqid=$q_id&eqpl=$qpl_ref_id" . "\">" . $data["title"] . "</a>");

			// obligatory checkbox (when obligation is possible)
			if( $data["obligationPossible"] )
			{
				$CHECKED = $data["obligatory"] ? "checked=\"checked\" " : "";
				$OBLIGATORY = "<input type=\"checkbox\" name=\"obligatory[$q_id]\" value=\"1\" $CHECKED/>";
			}
			else
			{
				$OBLIGATORY = "";
			}
		} 
		else 
		{
			global $lng;
			
			$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
			
			// obligatory icon
			if( $data["obligatory"] )
			{
				$OBLIGATORY = "<img src=\"".ilUtil::getImagePath("obligatory.gif", "Modules/Test").
						"\" alt=\"".$lng->txt("question_obligatory").
						"\" title=\"".$lng->txt("question_obligatory")."\" />";
			}
			else $OBLIGATORY = '';
		}
		
		if( $this->parent_obj->object->areObligationsEnabled() )
		{
			$this->tpl->setVariable("QUESTION_OBLIGATORY", $OBLIGATORY);
		}
		
		$this->tpl->setVariable("QUESTION_SEQUENCE", $this->lng->txt("tst_sequence"));

		if ($this->getWriteAccess() && !$this->getTotal()) 
		{
			if ($data["sequence"] != 1)
			{
				$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->ctrl->getLinkTarget($this->getParentObject(), "questions") . "&up=".$data["question_id"]."\">" . ilGlyphGUI::get(ilGlyphGUI::UP, $this->lng->txt('up')) . "</a>");
			}
			if ($data["sequence"] != count($this->getData()))
			{
				$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->ctrl->getLinkTarget($this->getParentObject(), "questions") . "&down=".$data["question_id"]."\">" . ilGlyphGUI::get(ilGlyphGUI::DOWN, $this->lng->txt('down')) . "</a>");
			}
		}

		if(isset($this->visibleOptionalColumns['description']))
		{
			if($data["description"])
			{
				$this->tpl->setVariable("QUESTION_COMMENT", $data["description"] ? $data["description"] : '&nbsp;');
			}
			else
			{
				$this->tpl->touchBlock('question_comment_block');
			}
		}
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
		$this->tpl->setVariable("QUESTION_POINTS", $data["points"]);
		$this->totalPoints 		+= $data["points"];
		$this->totalWorkingTime = assQuestion::sumTimesInISO8601FormatH_i_s_Extended($this->totalWorkingTime,$data['working_time']);
		if(isset($this->visibleOptionalColumns['author']))
		{
			$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
		}
		if(isset($this->visibleOptionalColumns['working_time']))
		{
			$this->tpl->setVariable("QUESTION_WORKING_TIME", $data["working_time"]);
		}
		if (ilObject::_lookupType($data["orig_obj_fi"]) == 'qpl') {
		    $this->tpl->setVariable("QUESTION_POOL", ilObject::_lookupTitle($data["orig_obj_fi"]));
		}
		else {
		    $this->tpl->setVariable("QUESTION_POOL", $this->lng->txt('tst_question_not_from_pool_info'));
		}


		$this->position += 10;
		$field = "<input type=\"text\" name=\"order[q_".$data["question_id"].
			"]\" value=\"".$this->position."\" maxlength=\"3\" style=\"width:30px\" />";
		$this->tpl->setVariable("QUESTION_POSITION", $field);
	}
	
	public function setWriteAccess($value)
	{
		$this->writeAccess = $value;
	}
	
	public function getWriteAccess()
	{
		return $this->writeAccess;
	}

	public function setCheckedMove($value)
	{
		$this->checked_move = $value;
	}
	
	public function getCheckedMove()
	{
		return $this->checked_move;
	}

	public function setTotal($value)
	{
		$this->total = $value;
	}
	
	public function getTotal()
	{
		return $this->total;
	}
}
?>