<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilListOfQuestionsTableGUI extends ilTable2GUI
{
	protected $showPointsEnabled = false;
	protected $showMarkerEnabled = false;

	protected $showObligationsEnabled = false;
	protected $obligationsFilterEnabled = false;
	
	protected $obligationsNotAnswered = false;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;

		$this->setFormName('listofquestions');
		$this->setStyle('table', 'fullwidth');

		$this->setRowTemplate("tpl.il_as_tst_list_of_questions_row.html", "Modules/Test");
		
		$this->setLimit(999);

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->enable('header');
		$this->disable('sort');
		$this->disable('select_all');
	}
	
	public function init()
	{
		// table title
		
		if( $this->isObligationsFilterEnabled() )
		{
			$this->setTitle($this->lng->txt('obligations_summary'));
		}
		else
		{
			$this->setTitle($this->lng->txt('question_summary'));
		}
		
		// columns

		$this->addColumn($this->lng->txt("tst_qst_order"),'order', '');
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		
		if( $this->isShowObligationsEnabled() )
		{
			$this->addColumn($this->lng->txt("obligatory"), 'obligatory', '');
		}
		
		$this->addColumn('' ,'postponed', '');
		
		if ($this->isShowPointsEnabled())
		{
			$this->addColumn($this->lng->txt("tst_maximum_points"),'points', '');
		}
		
		$this->addColumn($this->lng->txt("worked_through"),'worked_through', '');
		
		if( $this->isShowObligationsEnabled() )
		{
			$this->addColumn($this->lng->txt("answered"),'answered', '');
		}
		
		if ($this->isShowMarkerEnabled())
		{
			$this->addColumn($this->lng->txt("tst_question_marker"),'marked', '');
		}
		
		// command buttons
		
		$this->addCommandButton('backFromSummary', $this->lng->txt('back'));

		if( !$this->areObligationsNotAnswered() )
		{
			$button = ilSubmitButton::getInstance();
			$button->setCaption('save_finish');
			$button->setCommand('finishTest');
			$this->addCommandButtonInstance($button);
		}
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
		if ($this->isShowPointsEnabled())
		{
			$this->tpl->setCurrentBlock('points');
			$this->tpl->setVariable("POINTS", $data['points']);
			$this->tpl->parseCurrentBlock();
		}
		if (strlen($data['description']))
		{
			$this->tpl->setCurrentBlock('description');
			$this->tpl->setVariable("DESCRIPTION", ilUtil::prepareFormOutput($data['description']));
			$this->tpl->parseCurrentBlock();
		}
		if ($this->isShowMarkerEnabled())
		{
			if ($data['marked'])
			{
				$this->tpl->setCurrentBlock('marked_img');
				$this->tpl->setVariable("HREF_MARKED", ilUtil::img('./templates/default/images/marked.svg', $this->lng->txt("tst_question_marked"), '24px', '24px'));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->touchBlock('marker');
			}
		}
		if( $this->isShowObligationsEnabled() )
		{
			// obligatory answer status
			$value = '&nbsp;';
			if( $data['isAnswered'] )
			{
				$value = $this->lng->txt("yes");
			}
			$this->tpl->setCurrentBlock('answered_col');
			$this->tpl->setVariable('ANSWERED', $value);
			$this->tpl->parseCurrentBlock();
			
			// obligatory icon
			if( $data["obligatory"] )
			{
				$OBLIGATORY = "<img src=\"".ilUtil::getImagePath("obligatory.gif", "Modules/Test").
					"\" alt=\"".$this->lng->txt("question_obligatory").
					"\" title=\"".$this->lng->txt("question_obligatory")."\" />";
			}
			else $OBLIGATORY = '';
			$this->tpl->setVariable("QUESTION_OBLIGATORY", $OBLIGATORY);
		}
		
		$this->tpl->setVariable("ORDER", $data['order']);
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data['title']));
		$this->tpl->setVariable("HREF", $data['href']);
		$this->tpl->setVariable("POSTPONED", $data['postponed']);
		if ($data["worked_through"])
		{
			$this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("yes"));
		}
		else
		{
			$this->tpl->setVariable("WORKED_THROUGH", '&nbsp;');
		}
	}

	public function isShowPointsEnabled()
	{
		return $this->showPointsEnabled;
	}

	public function setShowPointsEnabled($showPointsEnabled)
	{
		$this->showPointsEnabled = $showPointsEnabled;
	}

	public function isShowMarkerEnabled()
	{
		return $this->showMarkerEnabled;
	}

	public function setShowMarkerEnabled($showMarkerEnabled)
	{
		$this->showMarkerEnabled = $showMarkerEnabled;
	}

	public function isShowObligationsEnabled()
	{
		return $this->showObligationsEnabled;
	}

	public function setShowObligationsEnabled($showObligationsEnabled)
	{
		$this->showObligationsEnabled = $showObligationsEnabled;
	}

	public function isObligationsFilterEnabled()
	{
		return $this->obligationsFilterEnabled;
	}

	public function setObligationsFilterEnabled($obligationsFilterEnabled)
	{
		$this->obligationsFilterEnabled = $obligationsFilterEnabled;
	}

	public function areObligationsNotAnswered()
	{
		return $this->obligationsNotAnswered;
	}

	public function setObligationsNotAnswered($obligationsNotAnswered)
	{
		$this->obligationsNotAnswered = $obligationsNotAnswered;
	}
	
	
}