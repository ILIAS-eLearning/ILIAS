<?php

declare(strict_types=1);

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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestAnswerOptionalQuestionsConfirmationGUI extends ilConfirmationGUI
{
    protected ?string $cancelCmd;

    protected ?string $confirmCmd;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;

        $this->cancelCmd = null;
        $this->confirmCmd = null;

        parent::__construct();
    }

    public function getCancelCmd(): ?string
    {
        return $this->cancelCmd;
    }

    public function setCancelCmd(string $cancelCmd): void
    {
        $this->cancelCmd = $cancelCmd;
    }

    public function getConfirmCmd(): ?string
    {
        return $this->confirmCmd;
    }

    public function setConfirmCmd(string $confirmCmd): void
    {
        $this->confirmCmd = $confirmCmd;
    }

    public function build(bool $isFixedTest): void
    {
        $this->setHeaderText($this->buildHeaderText($isFixedTest));
        $this->setCancel($this->lng->txt('back'), $this->getCancelCmd());
        $this->setConfirm($this->lng->txt('proceed'), $this->getConfirmCmd());
    }

    private function buildHeaderText(bool $isFixedTest): string
    {
        if ($isFixedTest) {
            return $this->lng->txt('tst_optional_questions_confirmation_fixed_test');
        }

        return $this->lng->txt('tst_optional_questions_confirmation_non_fixed_test');
    }
}
