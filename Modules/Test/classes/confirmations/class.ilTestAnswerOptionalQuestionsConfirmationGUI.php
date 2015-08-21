<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Utilities/classes/class.ilConfirmationGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestAnswerOptionalQuestionsConfirmationGUI extends ilConfirmationGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var string
	 */
	protected $cancelCmd;

	/**
	 * @var string
	 */
	protected $confirmCmd;

	/**
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->lng = $lng;
		
		$this->cancelCmd = null;
		$this->confirmCmd = null;
	}

	/**
	 * @return string
	 */
	public function getCancelCmd()
	{
		return $this->cancelCmd;
	}

	/**
	 * @param string $cancelCmd
	 */
	public function setCancelCmd($cancelCmd)
	{
		$this->cancelCmd = $cancelCmd;
	}

	/**
	 * @return string
	 */
	public function getConfirmCmd()
	{
		return $this->confirmCmd;
	}

	/**
	 * @param string $confirmCmd
	 */
	public function setConfirmCmd($confirmCmd)
	{
		$this->confirmCmd = $confirmCmd;
	}
	
	public function build($isFixedTest)
	{		
		$this->setHeaderText($this->buildHeaderText($isFixedTest));
		$this->setCancel($this->lng->txt('back'), $this->getCancelCmd());
		$this->setConfirm($this->lng->txt('proceed'), $this->getConfirmCmd());
	}
	
	private function buildHeaderText($isFixedTest)
	{
		$text = $this->lng->txt('tst_confirm_optional_questions');
		
		if($isFixedTest)
		{
			$text .= ' '.$this->lng->txt('tst_optional_questions_fixed_test');
		}
		else
		{
			$text .= ' '.$this->lng->txt('tst_optional_questions_non_fixed_test');
		}
		
		return $text;
	}
} 