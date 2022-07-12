<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestPassDeletionConfirmationGUI extends ilConfirmationGUI
{
    const CONTEXT_PASS_OVERVIEW = 'contPassOverview';
    const CONTEXT_INFO_SCREEN = 'contInfoScreen';
    const CONTEXT_DYN_TEST_PLAYER = 'contDynTestPlayer';

    protected ilCtrl $ctrl;
    
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, object $parentGUI)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;

        $this->setFormAction($this->ctrl->getFormAction($parentGUI));
    }
    
    public function build(int $activeId, int $pass, string $context) : void
    {
        $this->addHiddenItem('active_id', (string) $activeId);
        $this->addHiddenItem('pass', (string) $pass);
        
        switch ($context) {
            case self::CONTEXT_PASS_OVERVIEW:
            case self::CONTEXT_INFO_SCREEN:
            case self::CONTEXT_DYN_TEST_PLAYER:

                $this->addHiddenItem('context', $context);
                break;
                
            default: throw new ilTestException('invalid context given!');
        }

        $this->setCancel($this->lng->txt('cancel'), 'cancelDeletePass');
        $this->setConfirm($this->lng->txt('delete'), 'performDeletePass');

        if ($context == self::CONTEXT_DYN_TEST_PLAYER) {
            $this->setHeaderText($this->lng->txt('conf_delete_pass_ctm'));
        } else {
            $this->setHeaderText($this->lng->txt('conf_delete_pass'));
        }
    }
}
