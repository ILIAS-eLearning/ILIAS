<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Table;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hint;
use AsqQuestionHintEditorGUI;
use ilAdvancedSelectionListGUI;
use ilAssQuestionHintRequestGUI;
use ilAssQuestionHintsGUI;
use ilTable2GUI;


/**
 * Table GUI for managing list of hints for a question
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 */
class ilAsqHintsTableGUI extends ilTable2GUI
{

    const VAR_HINT_ORDER_NUMBER = 'hint_order_number';


	public function __construct($parent_gui, $parent_cmd, QuestionDto $qustion_dto)
	{
		global $DIC;

		$this->question_dto = $qustion_dto;

		$this->setPrefix('tsthints');
		$this->setId('tsthints');
		
		parent::__construct($parent_gui, $parent_cmd);
		
		$this->setTitle( sprintf($DIC->language()->txt('tst_question_hints_table_header'), $qustion_dto->getData()->getTitle()));
		$this->setNoEntriesText( $DIC->language()->txt('tst_question_hints_table_no_items') );
		

		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);


        $table_data = $qustion_dto->getQuestionHints()->getHints();



		/*if( $this->questionOBJ->isAdditionalContentEditingModePageObject() )
		{
			require_once 'Modules/TestQuestionPool/classes/class.ilAssHintPageGUI.php';
			
			foreach( $tableData as $key => $data )
			{
				$pageObjectGUI = new ilAssHintPageGUI($data['hint_id']);
				$pageObjectGUI->setOutputMode("presentation");
				$tableData[$key]['hint_text'] = $pageObjectGUI->presentation();
			}
		}*/


        $this->setData($table_data);

		$this->setRowTemplate('tpl.hints_administration_table_row.html', 'Services/AssessmentQuestion/src/UserInterface/Web/Component/Hint/Table');
		$this->setSelectAllCheckbox(HintTableFieldSelectHint::VAR_HINTS_BY_ORDER_NUMBER.'[]');
		$rowCount = count($table_data);
		$this->initColumns($rowCount);
		$this->initAdministrationCommands($rowCount);

	}
	

	private function initAdministrationCommands()
	{
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$lng = $DIC['lng'];
		
		$this->setFormAction( $ilCtrl->getFormAction($this->parent_obj) );

			$this->addMultiCommand(
                AsqQuestionHintEditorGUI::CMD_CONFIRM_DELETE_HINTS,
					$lng->txt('tst_questions_hints_table_multicmd_delete_hint')
			);

			$this->addCommandButton(
                AsqQuestionHintEditorGUI::CMD_SAVE_ORDER_NUMBERS,
					$lng->txt('tst_questions_hints_table_cmd_save_order')
			);
	}


    /**
     *
     */
	private function initTestoutputCommands()
	{
		if( $this->parent_obj instanceof ilAssQuestionHintsGUI )
		{
			return;
		}
		
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$lng = $DIC['lng'];
		
		$this->setFormAction( $ilCtrl->getFormAction($this->parent_obj) );
		
		$this->addCommandButton(
				ilAssQuestionHintRequestGUI::CMD_BACK_TO_QUESTION,
				$lng->txt('tst_question_hints_back_to_question')
		);
	}
	
	/**
	 * inits the required columns
	 * for administration table mode
	 * 
	 * @access	private
	 * @global	ilLanguage	$lng
	 * @param	integer		$rowCount
	 */
	private function initColumns($rowCount)
	{
		global $DIC;
		$lng = $DIC['lng'];
		
		$this->addColumn( '', '', '30', true );
		
		$this->addColumn( $lng->txt('tst_question_hints_table_column_hint_order'), '', '60');
		$this->addColumn( $lng->txt('tst_question_hints_table_column_hint_text'), '');
		$this->addColumn( $lng->txt('tst_question_hints_table_column_hint_points'), '', '250');
		
		$this->addColumn('', '', '100');
		
		$this->setDefaultOrderField("hint_index");
		$this->setDefaultOrderDirection("asc");
		
		if( $rowCount < 1 )
		{
			$this->disable('header');
		}
	}


    /**
     * @param array $row_data
     */
	public function fillRow($row_data)
	{
        global $DIC;
        /** @var Hint $hint */
       $hint =  $row_data;



			/*
			if( $this->questionOBJ->isAdditionalContentEditingModePageObject() )
			{
				$editPointsHref = $ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM);
				$editPointsHref = ilUtil::appendUrlParameterString($editPointsHref, "hint_id={$row_data->getOrderNumber()}", true);
				$list->addItem($lng->txt('tst_question_hints_table_link_edit_hint_points'), '', $editPointsHref);
				
				$editPageHref = $ilCtrl->getLinkTargetByClass('ilasshintpagegui', 'edit');
				$editPageHref = ilUtil::appendUrlParameterString($editPageHref, "hint_id={$$row_data->getOrderNumber()}", true);
				$list->addItem($lng->txt('tst_question_hints_table_link_edit_hint_page'), '', $editPageHref);
			}
			else
			{
				$editHref = $ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM);
				$editHref = ilUtil::appendUrlParameterString($editHref, "hint_id={$row_data->getOrderNumber()}", true);
				$list->addItem($lng->txt('tst_question_hints_table_link_edit_hint'), '', $editHref);
			}
			
			$deleteHref = $ilCtrl->getLinkTarget($this->parent_obj, ilAssQuestionHintsGUI::CMD_CONFIRM_DELETE);
			$deleteHref = ilUtil::appendUrlParameterString($deleteHref, "hint_id={$row_data->getOrderNumber()}", true);
			$list->addItem($lng->txt('tst_question_hints_table_link_delete_hint'), '', $deleteHref);

			$this->tpl->setVariable('ACTIONS', $list->getHTML());

			$this->tpl->setVariable('HINT_ID', $row_data->getOrderNumber());

		}*/

		$checkbox = new HintTableFieldSelectHint($hint->getOrderNumber());
        $this->tpl->setVariable('INPUT_TYPE_CHECKBOX_SELECT_HINT', $checkbox->getFieldAsHtml());

        $order_number = new HintTableFieldOrderNumber($hint->getOrderNumber(),$hint->getOrderNumber());
		$this->tpl->setVariable('INPUT_TYPE_TEXT_HINT_ORDER_NUMBER', $order_number->getFieldAsHtml());

		$this->tpl->setVariable('HINT_TEXT', $hint->getContent());

		$this->tpl->setVariable('HINT_POINTS', $hint->getPointDeduction());


        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($DIC->language()->txt('actions'));
        $actions->setId("advsl_hint_{$hint->getOrderNumber()}_actions");
        $DIC->ctrl()->setParameterByClass(AsqQuestionHintEditorGUI::class,self::VAR_HINT_ORDER_NUMBER, $hint->getOrderNumber());

            $item =  $DIC->ctrl()->getLinkTargetByClass(AsqQuestionHintEditorGUI::class, AsqQuestionHintEditorGUI::CMD_CONFIRM_DELETE_HINTS);
            $actions->addItem($DIC->language()->txt('tst_question_hints_table_link_delete_hint'), '', $item);

            $item = $DIC->ctrl()->getLinkTargetByClass(AsqQuestionHintEditorGUI::class, AsqQuestionHintEditorGUI::CMD_SHOW_HINT_FORM);
            $actions->addItem(  $DIC->language()->txt('tst_question_hints_table_link_edit_hint'), '', $item);

        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
	}


    /**
     * @return int[]
     * @throws \ilAsqException
     */
    public static function getSelectedHintOrderNumnbersFromPost(): array {
        return $hint_order_numbers =  HintTableFieldSelectHint::getValueFromPost();
    }

    /**
     * @return int[]
     * @throws \ilAsqException
     */
    public static function getHintOrderSuggestionFromPost(): array {
        return $hint_order_suggestion =  HintTableFieldOrderNumber::getValueFromPost();
    }
}

