<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/SplitButton/interfaces/interface.ilSplitButtonSeparatorMenuItem.php';

/**
 * Class ilSplitButtonItemDivider
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesUIComponent
 */
class ilSplitButtonItemDivider implements ilSplitButtonSeparatorMenuItem
{
    /**
     * @return string
     */
    public function getContent()
    {
        return '';
    }
}
