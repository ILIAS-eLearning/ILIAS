<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/SplitButton/interfaces/interface.ilSplitButtonMenuItem.php';

/**
 * Class ilButtonToSplitButtonMenuItemAdapter
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesUIComponent
 */
class ilButtonToSplitButtonMenuItemAdapter implements ilSplitButtonMenuItem
{
    /**
     * @var ilButtonBase
     */
    protected $button;

    /**
     * @param ilButtonBase $button
     */
    public function __construct(ilButtonBase $button)
    {
        $this->button = $button;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->button->applyDefaultCss(false);
        return $this->button->render();
    }
}
