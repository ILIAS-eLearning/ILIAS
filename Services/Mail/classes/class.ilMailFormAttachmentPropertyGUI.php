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

class ilMailFormAttachmentPropertyGUI extends ilFormPropertyGUI
{
    /** @var list<string> */
    private array $attachment_titles = [];

    public function __construct(
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \ILIAS\UI\Renderer $ui_renderer,
        private readonly string $button_label,
        private readonly string $button_cmd,
        string $http_post_param_name
    ) {
        parent::__construct('', $http_post_param_name);
        $this->setTitle($this->lng->txt('attachments'));
    }

    public function addItem(string $attachment_title): void
    {
        $this->attachment_titles[] = $attachment_title;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tpl.mail_new_attachments.html', true, true, 'Services/Mail');

        $tpl->setVariable('ATTACHMENT_BUTTON_LABEL', $this->button_label);
        $tpl->setVariable('ATTACHMENT_BUTTON_COMMAND', $this->button_cmd);

        if ($this->attachment_titles !== []) {
            $tpl->setVariable('ATTACHMENT_LIST', $this->ui_renderer->render(
                $this->ui_factory->listing()->unordered($this->attachment_titles)
            ));
        }

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
