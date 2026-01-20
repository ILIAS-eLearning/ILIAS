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

declare(strict_types=1);

use ILIAS\Questions\Question\QuestionImplementation;

class ilAssQuestionPage extends ilPageObject
{
    /**
     * Get parent type
     * @return string parent type
     */
    public function getParentType(): string
    {
        return "qpl";
    }

    public function copyToAnswerForm(
        int $new_id,
        QuestionImplementation $question
    ): void {
        $new_page_object = new QstsQuestionPage();
        $new_page_object->setParentId($this->getParentId());
        $new_page_object->setId($new_id);
        $new_page_object->setXMLContent($this->copyXMLContent(false, $this->getParentId()));
        $new_page_object->setActive($this->getActive());
        $new_page_object->setActivationStart($this->getActivationStart());
        $new_page_object->setActivationEnd($this->getActivationEnd());
        $new_page_object->setQuestion($question);
        $new_page_object->create(false);
    }
}
