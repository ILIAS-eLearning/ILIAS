<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
interface ilTestRandomQuestionSequence
{
    public function getResponsibleSourcePoolDefinitionId($questionId);
}
