<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getSrcPoolDefListRelatedQuestCombinationCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList): ilTestRandomQuestionSetQuestionCollection;

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefListRelatedQuestUniqueCollection(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList): ilTestRandomQuestionSetQuestionCollection;

    /**
     * @param ilTestRandomQuestionSetSourcePoolDefinition $definition
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getSrcPoolDefRelatedQuestCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition): ilTestRandomQuestionSetQuestionCollection;
}
