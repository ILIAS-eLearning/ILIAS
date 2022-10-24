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
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @ingroup ServicesMail
 */
class ilMailFormAttachmentPropertyGUI extends ilFormPropertyGUI
{
    public string $buttonLabel;
    /** @var string[] */
    public array $items = [];

    public function __construct(string $buttonLabel)
    {
        $this->buttonLabel = $buttonLabel;
        parent::__construct();
        $this->setTitle($this->lng->txt('attachments'));
    }

    public function addItem(string $label): void
    {
        $this->items[] = $label;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tpl.mail_new_attachments.html', true, true, 'Services/Mail');

        foreach ($this->items as $item) {
            $tpl->setCurrentBlock('attachment_list_item');
            $tpl->setVariable('ATTACHMENT_LABEL', $item);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable('ATTACHMENT_BUTTON_LABEL', $this->buttonLabel);

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
