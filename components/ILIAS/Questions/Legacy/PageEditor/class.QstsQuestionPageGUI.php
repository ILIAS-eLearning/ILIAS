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

use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Question\Question;
use ILIAS\Data\URI;

/**
 *
 * @ilCtrl_Calls QstsQuestionPageGUI: ilPageEditorGUI, ilEditClipboardGUI
 * @ilCtrl_Calls QstsQuestionPageGUI: ilPublicUserProfileGUI, ilCommentGUI
 * @ilCtrl_Calls QstsQuestionPageGUI: ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent\UploadHandlerGUI
 */
class QstsQuestionPageGUI extends ilPageObjectGUI
{
    private URI $return_uri;

    public function __construct(
        Question $question,
        int $obj_id,
        ?Edit $edit_view = null
    ) {
        parent::__construct('qsts', $question->getPageId());

        $this->setEnabledPageFocus(false);

        $this->obj->setQuestion($question);
        $this->obj->setParentId($obj_id);

        if ($edit_view !== null) {
            $this->obj->setEditView($edit_view);
        }
    }

    #[\Override]
    public function finishEditing(): void
    {
        $this->ctrl->redirectToURL($this->return_uri->__toString());
    }

    public function withReturnUri(
        URI $return_uri
    ): self {
        $clone = clone $this;
        $clone->return_uri = $return_uri;
        return $clone;
    }
}
