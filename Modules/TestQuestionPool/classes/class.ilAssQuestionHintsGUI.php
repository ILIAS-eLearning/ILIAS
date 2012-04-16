<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintAbstractGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsOrderingClipboard.php';

/**
 * @ilCtrl_Calls ilAssQuestionHintsGUI: ilAssQuestionHintGUI
 * 
 * GUI class for hints management of assessment questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintsGUI extends ilAssQuestionHintAbstractGUI
{
	/**
	 * command constants
	 */
	const CMD_SHOW_LIST								= 'showList';
	const CMD_CONFIRM_DELETE						= 'confirmDelete';
	const CMD_PERFORM_DELETE						= 'performDelete';
	const CMD_SAVE_LIST_ORDER						= 'saveListOrder';
	const CMD_CUT_TO_ORDERING_CLIPBOARD				= 'cutToOrderingClipboard';
	const CMD_PASTE_FROM_ORDERING_CLIPBOARD_BEFORE	= 'pasteFromOrderingClipboardBefore';
	const CMD_PASTE_FROM_ORDERING_CLIPBOARD_AFTER	= 'pasteFromOrderingClipboardAfter';
	const CMD_RESET_ORDERING_CLIPBOARD				= 'resetOrderingClipboard';
	
	/**
	 * @var ilAssQuestionHintOrderingClipboard
	 */
	private $hintOrderingClipboard = null;
	
	/**
	 * Constructor
	 * 
	 * @param	assQuestionGUI	$questionGUI 
	 */
	public function __construct(assQuestionGUI $questionGUI)
	{
		parent::__construct($questionGUI);
		
		$this->hintOrderingClipboard = new ilAssQuestionHintsOrderingClipboard($questionGUI->object);
	}

	/**
	 * Execute Command
	 * 
	 * @global	ilCtrl	$ilCtrl
	 * @return	mixed 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd(self::CMD_SHOW_LIST);
		$nextClass = $ilCtrl->getNextClass($this);

		switch($nextClass)
		{
			case 'ilassquestionhintgui':
				
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';
				$gui = new ilAssQuestionHintGUI($this->questionGUI);
				
				return $ilCtrl->forwardCommand($gui);
				
			default:
				
				$cmd .= 'Cmd';
				return $this->$cmd();
		}
	}
	
	/**
	 * shows a table with existing hints
	 * 
	 * @global	ilTemplate	$tpl
	 */
	private function showListCmd()
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->initHintOrderingClipboardNotification();
		
		require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbar = new ilToolbarGUI();
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsTableGUI.php';
		$table = new ilAssQuestionHintsTableGUI($this->questionOBJ, $this->hintOrderingClipboard, $this, self::CMD_SHOW_LIST);

		$questionHintList = ilAssQuestionHintList::getListByQuestionId( $this->questionOBJ->getId() );

		if( $this->hintOrderingClipboard->hasStored() )
		{
			$questionHintList = $this->getQuestionHintListWithoutHintStoredInOrderingClipboard($questionHintList);

			$toolbar->addButton(
				$lng->txt('tst_questions_hints_toolbar_cmd_reset_ordering_clipboard'),
				$ilCtrl->getLinkTarget($this, self::CMD_RESET_ORDERING_CLIPBOARD)
			);
		}
		else
		{
			$toolbar->addButton(
				$lng->txt('tst_questions_hints_toolbar_cmd_add_hint'),
				$ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM)
			);
		}
		
		$table->setData( $questionHintList->getTableData() );

		$tpl->setContent( $toolbar->getHTML() . $table->getHTML() );
	}

	/**
	 * shows a confirmation screen with selected hints for deletion
	 * 
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilTemplate	$tpl
	 * @global	ilLanguage	$lng
	 */
	private function confirmDeleteCmd()
	{
		global $ilCtrl, $tpl, $lng;
		
		$hintIds = self::fetchHintIdsParameter();

		if( !count($hintIds) )
		{
			ilUtil::sendFailure($lng->txt('tst_question_hints_delete_hints_missing_selection_msg'), true);
			$ilCtrl->redirect($this);
		}
		
		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmation = new ilConfirmationGUI();
		
		$confirmation->setHeaderText($lng->txt('tst_question_hints_delete_hints_confirm_header'));
		$confirmation->setFormAction($ilCtrl->getFormAction($this));
		$confirmation->setConfirm($lng->txt('tst_question_hints_delete_hints_confirm_cmd'), self::CMD_PERFORM_DELETE);
		$confirmation->setCancel($lng->txt('cancel'), self::CMD_SHOW_LIST);

		$questionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());
		
		foreach($questionHintList as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */
			
			if( in_array($questionHint->getId(), $hintIds) )
			{
				$confirmation->addItem('hint_ids[]', $questionHint->getId(), sprintf(
						$lng->txt('tst_question_hints_delete_hints_confirm_item'), $questionHint->getIndex(), $questionHint->getText()
				));
			}
		}
		
		$tpl->setContent( $confirmation->getHTML() );
	}

	/**
	 * performs confirmed deletion for selected hints
	 * 
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilLanguage	$lng
	 */
	private function performDeleteCmd()
	{
		global $ilCtrl, $tpl, $lng;
		
		$hintIds = self::fetchHintIdsParameter();
		
		if( !count($hintIds) )
		{
			ilUtil::sendFailure($lng->txt('tst_question_hints_delete_hints_missing_selection_msg'), true);
			$ilCtrl->redirect($this);
		}
		
		$questionCompleteHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());
		
		$questionRemainingHintList = new ilAssQuestionHintList();
		
		foreach($questionCompleteHintList as $listKey => $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */
			
			if( in_array($questionHint->getId(), $hintIds) )
			{
				$questionHint->delete();
			}
			else
			{
				$questionRemainingHintList->addHint($questionHint);
			}
		}
		
		$questionRemainingHintList->reIndexHints();
		
		ilUtil::sendSuccess($lng->txt('tst_question_hints_delete_success_msg'), true);
		$ilCtrl->redirect($this);
	}
	
	/**
	 * saves the order based on indexes passed from tables form
	 * (the table must not be paginated, because ALL hints indexes are required)
	 *
	 * @global ilCtrl		$ilCtrl
	 * @global ilLanguage	$lng
	 */
	private function saveListOrderCmd()
	{
		global $ilCtrl, $lng;
		
		$hintIndexes = self::fetchPreparedHintIndexesParameter();
		
		if( !count($hintIndexes) )
		{
			ilUtil::sendFailure($lng->txt('tst_question_hints_save_order_unkown_failure_msg'), true);
			$ilCtrl->redirect($this);
		}
		
		$curQuestionHintList = ilAssQuestionHintList::getListByQuestionId( $this->questionOBJ->getId() );
		
		$newQuestionHintList = new ilAssQuestionHintList();
		
		foreach($hintIndexes as $hintIndex => $hintId)
		{
			if( !$curQuestionHintList->hintExists($hintId) )
			{
				ilUtil::sendFailure($lng->txt('tst_question_hints_save_order_unkown_failure_msg'), true);
				$ilCtrl->redirect($this);
			}
			
			$questionHint = $curQuestionHintList->getHint($hintId);
			
			$newQuestionHintList->addHint($questionHint);
		}
		
		$newQuestionHintList->reIndexHints();
		
		ilUtil::sendSuccess($lng->txt('tst_question_hints_save_order_success_msg'), true);
		$ilCtrl->redirect($this);
	}
	
	/**
	 * cuts a hint from question hint list and stores it to ordering clipboard
	 *
	 * @global ilCtrl	$ilCtrl
	 */
	private function cutToOrderingClipboardCmd()
	{
		global $ilCtrl;
		
		$moveHintIds = self::fetchHintIdsParameter();
		$this->checkForSingleHintIdAndRedirectOnFailure($moveHintIds);
		
		$moveHintId = current($moveHintIds);
		
		$this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($moveHintId);
		
		$this->hintOrderingClipboard->setStored($moveHintId);
		
		$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
	}
	
	/**
	 * pastes a hint from ordering clipboard before the selected one
	 *
	 * @global ilCtrl		$ilCtrl
	 * @global ilLanguage	$lng
	 */
	private function pasteFromOrderingClipboardBeforeCmd()
	{
		global $ilCtrl, $lng;

		$targetHintIds = self::fetchHintIdsParameter();
		$this->checkForSingleHintIdAndRedirectOnFailure($targetHintIds);
		
		$targetHintId = current($targetHintIds);
		
		$this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($targetHintId);
		
		$curQuestionHintList = ilAssQuestionHintList::getListByQuestionId( $this->questionOBJ->getId() );
		$newQuestionHintList = new ilAssQuestionHintList( $this->questionOBJ->getId() );
		
		foreach($curQuestionHintList as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */

			if( $questionHint->getId() == $this->hintOrderingClipboard->getStored() )
			{
				continue;
			}
			
			if( $questionHint->getId() == $targetHintId )
			{
				$targetQuestionHint = $questionHint;

				$pasteQuestionHint = ilAssQuestionHint::getInstanceById( $this->hintOrderingClipboard->getStored() );
				
				$newQuestionHintList->addHint($pasteQuestionHint);
			}
			
			$newQuestionHintList->addHint($questionHint);
		}
		
		$newQuestionHintList->reIndex();
		
		$this->hintOrderingClipboard->resetStored();
		
		ilUtil::sendSuccess(sprintf(
				$lng->txt('tst_question_hints_paste_after_success_msg'),
				$pasteQuestionHint->getIndex(), $targetQuestionHint->getIndex()
		), true);

		$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
	}
	
	/**
	 * pastes a hint from ordering clipboard after the selected one
	 *
	 * @global ilCtrl		$ilCtrl
	 * @global ilLanguage	$lng
	 */
	private function pasteFromOrderingClipboardAfterCmd()
	{
		global $ilCtrl, $lng;

		$targetHintIds = self::fetchHintIdsParameter();
		$this->checkForSingleHintIdAndRedirectOnFailure($targetHintIds);
		
		$targetHintId = current($targetHintIds);
		
		$this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($targetHintId);
		
		$curQuestionHintList = ilAssQuestionHintList::getListByQuestionId( $this->questionOBJ->getId() );
		$newQuestionHintList = new ilAssQuestionHintList( $this->questionOBJ->getId() );
		
		foreach($curQuestionHintList as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */

			if( $questionHint->getId() == $this->hintOrderingClipboard->getStored() )
			{
				continue;
			}
			
			$newQuestionHintList->addHint($questionHint);
			
			if( $questionHint->getId() == $targetHintId )
			{
				$targetQuestionHint = $questionHint;
				
				$pasteQuestionHint = ilAssQuestionHint::getInstanceById( $this->hintOrderingClipboard->getStored() );
				
				$newQuestionHintList->addHint($pasteQuestionHint);
			}
		}
		
		$newQuestionHintList->reIndex();

		$this->hintOrderingClipboard->resetStored();

		ilUtil::sendSuccess(sprintf(
				$lng->txt('tst_question_hints_paste_after_success_msg'),
				$pasteQuestionHint->getIndex(), $targetQuestionHint->getIndex()
		), true);
		
		$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
	}
	
	/**
	 * resets the ordering clipboard
	 *
	 * @global ilCtrl		$ilCtrl
	 * @global ilLanguage	$lng
	 */
	private function resetOrderingClipboardCmd()
	{
		global $ilCtrl, $lng;
		
		$this->hintOrderingClipboard->resetStored();
		
		ilUtil::sendInfo($lng->txt('tst_question_hints_ordering_clipboard_resetted'), true);
		$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
	}
	
	/**
	 * inits the notification telling the user,
	 * that a hint is stored to hint ordering clipboard
	 * 
	 * @global	ilLanguage	$lng
	 */
	private function initHintOrderingClipboardNotification()
	{
		global $lng;
		
		if( !$this->hintOrderingClipboard->hasStored() )
		{
			return;
		}

		$questionHint = ilAssQuestionHint::getInstanceById( $this->hintOrderingClipboard->getStored() );

		ilUtil::sendInfo(sprintf(
				$lng->txt('tst_question_hints_item_stored_in_ordering_clipboard'), $questionHint->getIndex()
		));
	}
	
	/**
	 * checks for an existing hint relating to current question and redirects
	 * with corresponding failure message on failure
	 *
	 * @param	integer	$hintId 
	 */
	private function checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($hintId)
	{
		$questionHintList = ilAssQuestionHintList::getListByQuestionId( $this->questionOBJ->getId() );
		
		if( !$questionHintList->hintExists($hintId) )
		{
			ilUtil::sendFailure($lng->txt('tst_question_hints_invalid_hint_id'), true);
			$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
		}
	}
	
	/**
	 * returns a new quastion hint list that contains all question hints
	 * from the passed list except for the hint that is stored to ordering clipboard
	 *
	 * @param	ilAssQuestionHintList	$questionHintList
	 * @return	ilAssQuestionHintList	$filteredQuestionHintList
	 */
	private function getQuestionHintListWithoutHintStoredInOrderingClipboard(ilAssQuestionHintList $questionHintList)
	{
		$filteredQuestionHintList = new ilAssQuestionHintList();
		
		foreach($questionHintList as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */

			if( $questionHint->getId() != $this->hintOrderingClipboard->getStored() )
			{
				$filteredQuestionHintList->addHint($questionHint);
			}
		}
		
		return $filteredQuestionHintList;
	}
	
	/**
	 * checks for a hint id in the passed array and redirects
	 * with corresponding failure message if not exactly one id is given
	 *
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilLanguage	$lng
	 * @param	array		$hintIds
	 */
	private function checkForSingleHintIdAndRedirectOnFailure($hintIds)
	{
		global $ilCtrl, $lng;
		
		if( !count($hintIds) )
		{
			ilUtil::sendFailure($lng->txt('tst_question_hints_cut_hints_missing_selection_msg'), true);
			$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
		}
		elseif( count($hintIds) > 1 )
		{
			ilUtil::sendFailure($lng->txt('tst_question_hints_cut_hints_single_selection_msg'), true);
			$ilCtrl->redirect($this, self::CMD_SHOW_LIST);
		}
	}
	
	/**
	 * fetches either an array of hint ids from POST or a single hint id from GET
	 * and returns an array of (a single) hint id(s) casted to integer in both cases
	 *
	 * @access	private
	 * @static
	 * @return	array	$hintIds
	 */
	private static function fetchHintIdsParameter()
	{
		$hintIds = array();
		
		if( isset($_POST['hint_ids']) && is_array($_POST['hint_ids']) )
		{
			foreach($_POST['hint_ids'] as $hintId)
			{
				if( (int)$hintId ) $hintIds[] = (int)$hintId;
			}
		}
		elseif( isset($_GET['hint_id']) && (int)$_GET['hint_id'] )
		{
			$hintIds[] = (int)$_GET['hint_id'];
		}
		
		return $hintIds;
	}
	
	/**
	 * fetches an array of hint indexes from POST and prepares this array
	 * to be used for saving the hint lists order
	 * 
	 * flips and sorts the array so key is the index value is the hint id casted to integer
	 * and the elements have the new order to each other
	 *
	 * @access	private
	 * @static
	 * @return	array	$hintIndexes
	 */
	private static function fetchPreparedHintIndexesParameter()
	{
		$hintIndexes = array();
		
		if( isset($_POST['hint_indexes']) && is_array($_POST['hint_indexes']) )
		{
			foreach($_POST['hint_indexes'] as $hintId => $hintIndex)
			{
				if( (int)$hintId ) $hintIndexes[(int)$hintId] = $hintIndex;
			}
		}
		
		$hintIndexes = array_flip($hintIndexes);		
		
		ksort($hintIndexes);

		return $hintIndexes;
	}
}
