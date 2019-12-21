<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
interface ilTestRandomSourcePoolDefinitionQuestionCollectionProvider
{
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefListRelatedQuestCombinationCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList);

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefListRelatedQuestUniqueCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList);
    
    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefRelatedQuestCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition);
}
