<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Utilities/classes/class.ilConfirmationGUI.php';

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

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    
    /**
     * @var ilLanguage
     */
    protected $lng;
    
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;

        $this->setFormAction($this->ctrl->getFormAction($parentGUI));
    }
    
    public function build($activeId, $pass, $context)
    {
        $this->addHiddenItem('active_id', $activeId);
        $this->addHiddenItem('pass', $pass);
        
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
