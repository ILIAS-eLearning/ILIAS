<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilUiLinkToSplitButtonMenuItemAdapter
 * @author Michael Jansen <mjansen@databay.de>
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
