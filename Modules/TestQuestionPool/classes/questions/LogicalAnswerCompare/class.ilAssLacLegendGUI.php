<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssLacLegendGUI extends ilOverlayGUI
{
	protected $lng;
	
	protected $tpl;
	
	private $initialVisibilityEnabled;
	
	public function __construct(ilLanguage $lng, ilTemplate $tpl)
	{
		$this->lng = $lng;
		$this->tpl = $tpl;
		
		$this->initialVisibilityEnabled = false;

		parent::__construct('qpl_lac_legend');
	}

	public function isInitialVisibilityEnabled()
	{
		return $this->initialVisibilityEnabled;
	}

	public function setInitialVisibilityEnabled($initialVisibilityEnabled)
	{
		$this->initialVisibilityEnabled = $initialVisibilityEnabled;
	}
	
	public function getHTML()
	{
		$this->initOverlay();
		
		$tpl = $this->getTemplate();
		
		$this->renderCommonLegendPart($tpl);
		$this->renderQuestSpecificLegendPart($tpl);
		
		$this->populateVisibilityCss($tpl);
		$this->populateTriggerDepencies($tpl);
		
		return $tpl->get();
	}
	
	protected function initOverlay()
	{
		include_once 'Services/YUI/classes/class.ilYuiUtil.php';
		ilYuiUtil::initOverlay();

		$this->tpl->addCss('Modules/TestQuestionPool/templates/default/lac_legend.css');
		
		//$this->setAnchor('fixed_content', 'tr', 'tr');
		// we use css instead, does not hoppel over screen for initially visible overlays

		//$this->setTrigger('lac_legend_toggle_btn', 'click');
		// is done by own listener that also changes the toggle label
		
		$this->setVisible($this->isInitialVisibilityEnabled());
		$this->setAutoHide(false);

		$this->add();
	}
	
	protected function getTemplate()
	{
		return new ilTemplate(
			'tpl.qpl_logical_answer_compare_legend.html', true, true, 'Modules/TestQuestionPool'
		);
	}
	
	protected function renderCommonLegendPart(ilTemplate $tpl)
	{
		$tpl->setVariable(
			'COMMON_ELEMENTS_HEADER', $this->lng->txt('qpl_lac_legend_header_common')
		);
		
		foreach($this->getCommonElements() as $element => $description)
		{
			$tpl->setCurrentBlock('common_elements');
			$tpl->setVariable('CE_ELEMENT', $element);
			$tpl->setVariable('CE_DESCRIPTION', $description);
			$tpl->parseCurrentBlock();
		}
	}
	
	protected function getCommonElements()
	{
		return array(
			'&' => $this->lng->txt('qpl_lac_desc_logical_and'),
			'|' => $this->lng->txt('qpl_lac_desc_logical_or'),
			'!' => $this->lng->txt('qpl_lac_desc_negation'),
			'()' => $this->lng->txt('qpl_lac_desc_brackets'),
			//'Qn' => $this->lng->txt('qpl_lac_desc_res_of_quest_n'),
			//'Qn[m]' => $this->lng->txt('qpl_lac_desc_res_of_answ_m_of_quest_n'),
			'R' => $this->lng->txt('qpl_lac_desc_res_of_cur_quest'),
			'R[m]' => $this->lng->txt('qpl_lac_desc_res_of_answ_m_of_cur_quest')
		);
	}

	protected function renderQuestSpecificLegendPart(ilTemplate $tpl)
	{
		$tpl->setVariable(
			'QUEST_SPECIFIC_ELEMENTS_HEADER', $this->lng->txt('qpl_lac_legend_header_quest_specific')
		);
		
		foreach($this->getQuestionTypeSpecificElements() as $element => $info)
		{
			$tpl->setCurrentBlock('quest_specific_elements');
			$tpl->setVariable('QSE_ELEMENT', $element);
			$tpl->setVariable('QSE_DESCRIPTION', $info['description']);
			$tpl->setVariable('QSE_OPERATORS_TXT', $this->lng->txt('qpl_lac_legend_label_operators'));
			$tpl->setVariable('QSE_OPERATORS', implode(', ', $info['operators']));
			$tpl->parseCurrentBlock();
		}
	}

	protected function getQuestionTypeSpecificElements()
	{
		return array(
			'%n%' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_quest_res'),
				'operators' => array('<', '<=', '=', '>=', '>', '<>')
			),
			'#n#' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_number'),
				'operators' => array('<', '<=', '=', '>=', '>', '<>')
			),
			'~TEXT~' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_text'),
				'operators' => array('=', '<>')
			),
			';n:m;' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_assignment'),
				'operators' => array('=', '<>')
			),
			'$n,m,o,p$' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_sequence'),
				'operators' => array('=', '<>')
			),
			'+n+' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_answer_n'),
				'operators' => array('=', '<>')
			),
			'*n,m,o,p*' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_with_exact_sequence'),
				'operators' => array('=', '<>')
			),
			'?' => array(
				'description'=> $this->lng->txt('qpl_lac_desc_compare_answer_exist'),
				'operators' => array('=', '<>')
			)
		);
	}
	
	protected function populateVisibilityCss(ilTemplate $tpl)
	{
		if( !$this->isInitialVisibilityEnabled() )
		{
			$tpl->setVariable('CSS_DISPLAY_NONE', 'display:none;');
		}
	}
	
	protected function populateTriggerDepencies(ilTemplate $tpl)
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';

		$tpl->setVariable(
			'TOGGLE_BTN_SHOW_LABEL', $this->lng->txt('ass_lac_show_legend_btn')
		);
		
		$tpl->setVariable(
			'TOGGLE_BTN_HIDE_LABEL', $this->lng->txt('ass_lac_hide_legend_btn')
		);
		
		$tpl->setVariable(
			'SKILL_POINT_EVAL_MODE_BY_RESULT', ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT
		);
		
		$tpl->setVariable(
			'SKILL_POINT_EVAL_MODE_BY_SOLUTION', ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION
		);
	}
	
	public function getTriggerElement()
	{
		return "<div id=\"qpl_lac_legend_trigger\"><a href=\"#\">".$this->lng->txt("qpl_lac_legend_link")."</a></div>";
	}
}