<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\ContentEditingMode;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ilPropertyFormGUI;
use ilSelectInputGUI;

/**
 * Class QuestionTypeSelectForm
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionTypeSelectForm extends ilPropertyFormGUI {
	const VAR_QUESTION_TYPE = "question_type";
	const VAR_CONTENT_EDIT_MODE = "content_edit_mode";

    /**
     * QuestionTypeSelectForm constructor.
     */
	public function __construct( ) {
		$this->initForm();

		parent::__construct();
	}

	/**
	 * Init settings property form
	 *
	 * @access private
	 */
	private function initForm() {

	    global $DIC; /* @var \ILIAS\DI\Container $DIC */

		$select = new ilSelectInputGUI(
		    $DIC->language()->txt('asq_input_question_type'), self::VAR_QUESTION_TYPE
        );
		$select->setOptions(QuestionLegacyData::getQuestionTypes());
		$this->addItem($select);

        if( \ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
        {
            $radio = new \ilRadioGroupInputGUI(
                $DIC->language()->txt("asq_input_cont_edit_mode"), self::VAR_CONTENT_EDIT_MODE
            );

            $radio->addOption(new \ilRadioOption(
                $DIC->language()->txt('asq_input_cont_edit_mode_rte_textarea'),
                ContentEditingMode::RTE_TEXTAREA
            ));

            $radio->addOption(new \ilRadioOption(
                $DIC->language()->txt('asq_input_cont_edit_mode_page_object'),
                ContentEditingMode::PAGE_OBJECT
            ));

            $radio->setValue(ContentEditingMode::RTE_TEXTAREA);

            $this->addItem($radio);
        }
	}

    /**
     * @return int|null
     */
	public function getQuestionType() : ?int {
		$val =  intval($_POST[self::VAR_QUESTION_TYPE]);
		//return null for type new so that no legacy object will be generated
		return $val === 0 ? null : $val;
	}


    /**
     * @return bool
     */
	public function hasContentEditingMode() : bool
    {
        $input = $this->getItemByPostVar(self::VAR_CONTENT_EDIT_MODE);
        return $input instanceof \ilFormPropertyGUI;
    }


    /**
     * @return bool
     */
    public function getContentEditingMode() : string
    {
        return $this->getInput(self::VAR_CONTENT_EDIT_MODE);
    }
}
