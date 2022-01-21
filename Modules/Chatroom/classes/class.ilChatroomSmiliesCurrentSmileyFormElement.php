<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmiliesCurrentSmileyFormElement
 * Simple form element that displays an image; does not add data to the containing form
 * but may be initialized by default methods, such as valuesByArray
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesCurrentSmileyFormElement extends ilCustomInputGUI
{
    private string $value = '';

    public function getHtml() : string
    {
        global $DIC;

        $tpl = new ilTemplate("tpl.chatroom_current_smiley_image.html", true, true, "Modules/Chatroom");
        $tpl->setVariable("IMAGE_ALT", $DIC->language()->txt("chatroom_current_smiley_image"));
        $tpl->setVariable("IMAGE_PATH", $this->value);

        return $tpl->get();
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function checkInput() : bool
    {
        return true;
    }
}
