<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssFileUploadFileTableCommandButton.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/TestQuestionPool
 */
class ilAssFileUploadFileTableDeleteButton extends ilAssFileUploadFileTableCommandButton
{
	const ACTION = 'delete';
	
	public function __construct($type)
	{
		parent::__construct($type);
		$this->setAction(self::ACTION);
		$this->setCaption($this->lng()->txt('delete'), false);
	}
	
	public static function getInstance()
	{
		return new self(self::TYPE_SUBMIT);
	}
}