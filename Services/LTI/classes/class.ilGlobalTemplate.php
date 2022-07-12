<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace LTI;

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
 * special template class to simplify handling of ITX/PEAR
 * @author     Stefan Schneider <schneider@hrz.uni-marburg.de>
 * @version    $Id$
 */
// ToDo Stefan: Can be removed?
class ilGlobalTemplate extends \ilGlobalTemplate
{
    public function __construct(
        string $file,
        bool $flag1,
        bool $flag2,
        string $in_module = '',
        string $vars = "DEFAULT",
        bool $plugin = false,
        bool $a_use_cache = false
    ) {
        parent::__construct(
            $file,
            $flag1,
            $flag2,
            $in_module,
            $vars,
            $plugin,
            $a_use_cache
        );
    }

    // public function getMainMenu() : void
    // {
        // global $ilMainMenu;
        // //$ilMainMenu->setLoginTargetPar($this->getLoginTargetPar());
        // $this->main_menu = $ilMainMenu->getHTML();
        // $this->main_menu_spacer = $ilMainMenu->getSpacerClass();
    // }
}
