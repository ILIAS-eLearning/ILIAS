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

/**
 * Class ilButtonToSplitButtonMenuItemAdapter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilButtonToSplitButtonMenuItemAdapter implements ilSplitButtonMenuItem
{
    protected ilButtonBase $button;

    public function __construct(ilButtonBase $button)
    {
        $this->button = $button;
    }

    public function getContent() : string
    {
        $this->button->applyDefaultCss(false);
        return $this->button->render();
    }
}
