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

use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * @ilCtrl_isCalledBy ilPCLegacyAnswerFormTextGUI: ilPageEditorGUI
 */
class ilPCLegacyAnswerFormTextGUI extends ilPageContentGUI
{
    public function executeCommand()
    {
        $this->tpl->setOnScreenMessage(MessageBox::FAILURE, $this->lng->txt('legacy_text_cannot_be_edited'), true);
        $this->ctrl->redirectByClass(\QstsQuestionPageGUI::class, 'edit');
    }
}
