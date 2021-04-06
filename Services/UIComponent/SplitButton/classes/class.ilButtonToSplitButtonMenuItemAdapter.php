<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilButtonToSplitButtonMenuItemAdapter
 * @author Michael Jansen <mjansen@databay.de>
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
