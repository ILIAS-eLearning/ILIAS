<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetStagingPool
{


	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}

	public function rebuild(ilTestRandomQuestionSetConfig $questionSetConfig, ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$this->reset();

		$this->build($questionSetConfig, $sourcePoolDefinitionList);
	}

	private function reset()
	{

	}

	private function build(ilTestRandomQuestionSetConfig $questionSetConfig, ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$this->mirrorSourcePoolsTaxonomies();
		$this->fetchQuestionsFromSourcePools();
	}
}