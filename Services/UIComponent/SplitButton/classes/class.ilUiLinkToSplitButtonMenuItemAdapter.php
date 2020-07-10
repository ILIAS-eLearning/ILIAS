<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/SplitButton/interfaces/interface.ilSplitButtonMenuItem.php';

/**
 * Class ilUiLinkToSplitButtonMenuItemAdapter
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesUIComponent
 */
class ilUiLinkToSplitButtonMenuItemAdapter implements ilSplitButtonMenuItem
{
    /**
     * @var \ILIAS\UI\Component\Button\Button
     */
    protected $link;

    /**
     * @param ilButtonBase $button
     */
    public function __construct(\ILIAS\UI\Component\Button\Button $link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        global $DIC;
        
        return $DIC->ui()->renderer()->render([$this->link]);
    }
}
