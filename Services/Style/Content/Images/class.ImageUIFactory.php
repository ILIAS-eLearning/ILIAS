<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access\StyleAccessManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageUIFactory
{
    /**
     * @var UIFactory
     */
    protected $ui_factory;

    /**
     * Constructor
     */
    public function __construct(UIFactory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
    }
    // images editing
    public function ilContentStyleImageGUI(
        StyleAccessManager $access_manager,
        ImageManager $image_manager
    ) : \ilContentStyleImageGUI {
        return new \ilContentStyleImageGUI(
            $this->ui_factory,
            $access_manager,
            $image_manager
        );
    }
}
