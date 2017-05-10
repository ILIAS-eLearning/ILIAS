<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test
 */
class ilTestRandomQuestionsQuantitiesDistribution
{
	/**
	 * @var ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
	 */
	protected $questionCollectionProvider;
	
	/**
	 * ilTestRandomQuestionsQuantitiesDistribution constructor.
	 *
	 * @param ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider
	 */
	public function __construct(ilTestRandomSourcePoolDefinitionQuestionCollectionProvider $questionCollectionProvider)
	{
		$this->questionCollectionProvider = $questionCollectionProvider;
	}
	
	/**
	 * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
	 */
	public function initialise($sourcePoolDefinitionList)
	{
		// build register for quantities distribution
	}
}