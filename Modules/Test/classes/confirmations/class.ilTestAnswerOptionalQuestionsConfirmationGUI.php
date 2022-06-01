<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    }

    public function getCancelCmd() : ?string
    {
        return $this->cancelCmd;
    }

    public function setCancelCmd(string $cancelCmd) : void
    {
        $this->cancelCmd = $cancelCmd;
    }

    public function getConfirmCmd() : ?string
    {
        return $this->confirmCmd;
    }

    public function setConfirmCmd(string $confirmCmd) : void
    {
        $this->confirmCmd = $confirmCmd;
    }
    
    public function build(bool $isFixedTest) : void
    {
        $this->setHeaderText($this->buildHeaderText($isFixedTest));
        $this->setCancel($this->lng->txt('back'), $this->getCancelCmd());
        $this->setConfirm($this->lng->txt('proceed'), $this->getConfirmCmd());
    }
    
    private function buildHeaderText(bool $isFixedTest) : string
    {
        if ($isFixedTest) {
            return $this->lng->txt('tst_optional_questions_confirmation_fixed_test');
        }

        return $this->lng->txt('tst_optional_questions_confirmation_non_fixed_test');
    }
}
