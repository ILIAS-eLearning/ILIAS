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
class ilTestPassDeletionConfirmationGUI extends ilConfirmationGUI
{
    public const CONTEXT_PASS_OVERVIEW = 'contPassOverview';
    public const CONTEXT_INFO_SCREEN = 'contInfoScreen';
    public const CONTEXT_DYN_TEST_PLAYER = 'contDynTestPlayer';

    protected ilCtrl $ctrl;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng, object $parentGUI)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;

        $this->setFormAction($this->ctrl->getFormAction($parentGUI));
    }

    public function build(int $activeId, int $pass, string $context): void
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
