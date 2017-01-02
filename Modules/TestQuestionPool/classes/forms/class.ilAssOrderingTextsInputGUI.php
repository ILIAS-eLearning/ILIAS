<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilMultipleTextsInputGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingTextsInputGUI extends ilMultipleTextsInputGUI
{
	/**
	 * @var assOrderingQuestion
	 */
	protected $questionOBJ = null;
	
	/**
	 * ilAssOrderingTextsInputGUI constructor.
	 */
	public function __construct($postVar)
	{
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
		$manipulator = new ilAssOrderingDefaultElementFallback();
		$this->addFormValuesManipulator($manipulator);
		
		parent::__construct('', $postVar);
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
		$manipulator = new ilAssOrderingFormValuesObjectsConverter();
		$manipulator->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_TEXT);
		$manipulator->setPostVar($this->getPostVar());
		$this->addFormValuesManipulator($manipulator);
	}
	
	/**
	 * @param ilAssOrderingElementList $elementList
	 */
	public function setElementList(ilAssOrderingElementList $elementList)
	{
		$this->setMultiValues( $elementList->getRandomIdentifierIndexedElements() );
	}
	
	/**
	 * @return ilAssOrderingElementList
	 */
	public function getElementList()
	{
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
		return ilAssOrderingElementList::buildInstance($this->getQuestionOBJ()->getId(), $this->getMultiValues());
	}
	
	/**
	 * @return assOrderingQuestion
	 */
	public function getQuestionOBJ()
	{
		return $this->questionOBJ;
	}
	
	/**
	 * @param assOrderingQuestion $questionOBJ
	 */
	public function setQuestionOBJ(assOrderingQuestion $questionOBJ)
	{
		$this->questionOBJ = $questionOBJ;
	}
}