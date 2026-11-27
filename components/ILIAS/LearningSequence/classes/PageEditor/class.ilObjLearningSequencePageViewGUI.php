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

/**
 * Shows the intro or extro page to learners and serves the commands the
 * rendered page links to (file downloads, media). It never forwards to the
 * page editor; editing stays with the Edit*GUI classes behind write permission.
 */
class ilObjLearningSequencePageViewGUI extends ilLearningSequencePageObjectGUI
{
    public const PARAM_PAGE_TYPE = 'lso_page';

    private const ALLOWED_COMMANDS = [
        'downloadFile',
        'displayMedia',
        'displayMediaFullscreen',
        'download_paragraph',
    ];

    public function __construct(
        protected LSOPageType $lso_page_type,
        int $page_id
    ) {
        parent::__construct($lso_page_type->value, $page_id);
        $this->setEnableEditing(false);
    }

    public function showPage(): string
    {
        $this->ctrl->setParameter($this, self::PARAM_PAGE_TYPE, $this->lso_page_type->value);
        $html = parent::showPage();
        $this->ctrl->clearParameterByClass(self::class, self::PARAM_PAGE_TYPE);
        return $html;
    }

    public function executeCommand(): string
    {
        $next_class = (string) $this->ctrl->getNextClass($this);
        $cmd = (string) $this->ctrl->getCmd();

        if ($next_class !== '' || !in_array($cmd, self::ALLOWED_COMMANDS, true)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->$cmd();
        return '';
    }
}
