<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Form;

use ILIAS\AssessmentQuestion\DomainModel\Hint\Hint;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;

/**
 * Class QuestionHintFormGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\AssessmentQuestion\UserInterface\Web\Form
 */
class HintFormGUI extends \ilPropertyFormGUI
{
    /**
     * @var Page
     */
    protected $page;
    /**
     * @var QuestionDto
     */
    protected $question_dto;
    /**
     * @var Hint
     */
    protected $hint;


    /**
     * QuestionHintFormGUI constructor.
     *
     * @param Page        $page
     * @param QuestionDto $questionDto
     */
    public function __construct(
        QuestionDto $question_dto,
        Hint $hint
    ) {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();

        $this->question_dto = $question_dto;
        $this->hint = $hint;

        $this->setTitle($DIC->language()->txt('asq_feedback_form_title'));

        $this->initForm();
    }


    protected function initForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $this->setTitle(sprintf($DIC->language()->txt('asq_question_hints_form_header'), $this->question_dto->getData()->getTitle()));

        //Hint Order Number
        $order_number = new HintFieldOrderNumber($this->hint->getOrderNumber());
        $this->addItem($order_number->getField());

        //RTE or PageEditor?
        $content_rte = new HintFieldContentRte($this->hint->getContent(), $this->question_dto->getContainerObjId(), $this->question_dto->getLegacyData()->getContainerObjType());
        $this->addItem($content_rte->getField());

        $points_deduction = new HintFieldPointsDeduction($this->hint->getPointDeduction());
        $this->addItem($points_deduction->getField());
    }

    public static function getHintFromPost() {

        $hint_order_number =  HintFieldOrderNumber::getValueFromPost();
        $content_rte = HintFieldContentRte::getValueFromPost();
        $points_deduction = HintFieldPointsDeduction::getValueFromPost();

        return new Hint($hint_order_number, $content_rte,$points_deduction);
    }
}