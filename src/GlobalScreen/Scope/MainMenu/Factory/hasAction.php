<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface hasAction
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasAction
{
    /**
     * @param string $action
     * @return hasAction
     */
    public function withAction(string $action) : hasAction;

    /**
     * @return string
     */
    public function getAction() : string;

    /**
     * @param bool $is_external
     * @return hasAction
     */
    public function withIsLinkToExternalAction(bool $is_external) : hasAction;

    /**
     * @return bool
     */
    public function isLinkWithExternalAction() : bool;
}
