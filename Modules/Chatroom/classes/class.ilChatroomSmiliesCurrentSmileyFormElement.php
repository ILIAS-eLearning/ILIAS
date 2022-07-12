<?php declare(strict_types=1);

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
