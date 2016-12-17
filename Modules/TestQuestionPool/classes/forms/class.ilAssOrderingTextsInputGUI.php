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
	public function __construct($a_title, $a_postvar)
	{
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
		$manipulator = new ilAssOrderingDefaultElementFallback();
		$this->addFormValuesManipulator($manipulator);
		
		parent::__construct($a_title, $a_postvar);
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
		$manipulator = new ilAssOrderingFormValuesObjectsConverter();
		$manipulator->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_TEXT);
		$manipulator->setPostVar($this->getPostVar());
		$this->addFormValuesManipulator($manipulator);
	}
	
	protected function fetchContentFromValue($value)
	{
		return $value->getContent();
	}
}