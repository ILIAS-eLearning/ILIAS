<?php

namespace ILIAS\GlobalScreen\Client;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ModeToggle
 * This is just for testing!!! And will be removed after
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModeToggle
{
    const GS_MODE = 'gs_mode';
    const MODE1 = "all";
    const MODE2 = "none";
    
    public function getMode() : string
    {
        return $_COOKIE[self::GS_MODE] ?? self::MODE1;
    }
    
    public function saveStateOfAll() : bool
    {
        return $this->getMode() == ItemState::LEVEL_OF_TOOL;
    }
    
    public function toggle() : void
    {
        $current_mode = $this->getMode();
        $new_mode     = $current_mode == self::MODE1 ? self::MODE2 : self::MODE1;
        setcookie(self::GS_MODE, $new_mode, 0, "/");
        $_COOKIE[ItemState::COOKIE_NS_GS] = "";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}
