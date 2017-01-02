<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilMultipleImagesInputGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingImagesInputGUI extends ilMultipleImagesInputGUI
{
	const POST_VARIABLE_NAME = 'ordering';
	
	/**
	 * @var assOrderingQuestion
	 */
	protected $questionOBJ = null;
	
	/**
	 * ilAssOrderingImagesInputGUI constructor.
	 */
	public function __construct($postVar)
	{
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
		$manipulator = new ilAssOrderingDefaultElementFallback();
		$this->addFormValuesManipulator($manipulator);
		
		parent::__construct('', $postVar);
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
		$manipulator = new ilAssOrderingFormValuesObjectsConverter();
		$manipulator->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_IMAGE);
		$manipulator->setPostVar($this->getPostVar());
		$manipulator->setImageRemovalCommand($this->getImageRemovalCommand());
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
	 * @param string $filenameInput
	 * @return bool
	 */
	protected function isValidFilenameInput($filenameInput)
	{
		/* @var ilAssOrderingElement $filenameInput */
		return (bool)strlen($filenameInput->getContent());
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