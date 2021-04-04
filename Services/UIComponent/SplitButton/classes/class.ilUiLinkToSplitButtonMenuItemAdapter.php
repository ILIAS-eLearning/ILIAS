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
     * @var \ILIAS\UI\Renderer
     */
    protected $renderer;

    /**
     * @param ilButtonBase $button
     *
     */
    public function __construct(\ILIAS\UI\Component\Button\Button $link, \ILIAS\UI\Renderer $renderer)
    {
        $this->link = $link;
        $this->renderer = $renderer;
    }

    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->renderer->render([$this->link]);
    }
}
