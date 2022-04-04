<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function addItem(string $label) : void
    {
        $this->items[] = $label;
    }
    
    public function insert(ilTemplate $a_tpl) : void
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
