<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access\StyleAccessManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicUIFactory
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

    // characteristics editing
    public function ilStyleCharacteristicGUI(
        \ilObjStyleSheet $style_sheet_obj,
        string $super_type,
        StyleAccessManager $access_manager,
        CharacteristicManager $characteristic_manager,
        ImageManager $image_manager
    ) : \ilStyleCharacteristicGUI {
        return new \ilStyleCharacteristicGUI(
            $this->ui_factory,
            $style_sheet_obj,
            $super_type,
            $access_manager,
            $characteristic_manager,
            $image_manager
        );
    }

    // characteristics table
    public function CharacteristicTableGUI(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_super_type,
        \ilObjStyleSheet $a_style,
        CharacteristicManager $manager,
        Access\StyleAccessManager $access_manager
    ) : CharacteristicTableGUI {
        return new CharacteristicTableGUI(
            $this->ui_factory,
            $a_parent_obj,
            $a_parent_cmd,
            $a_super_type,
            $a_style,
            $manager,
            $access_manager
        );
    }
}
