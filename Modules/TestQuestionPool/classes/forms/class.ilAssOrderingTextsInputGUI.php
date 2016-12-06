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
		parent::__construct($a_title, $a_postvar);
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingTextsValuesObjectsConverter.php';
		$this->addFormInputManipulator(new ilAssOrderingTextsValuesObjectsConverter());
		$this->addFormSubmitManipulator(new ilAssOrderingTextsValuesObjectsConverter());
	}
}