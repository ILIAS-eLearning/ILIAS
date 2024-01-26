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
/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Scope\Tool;

use ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\GlobalScreen\SingletonTrait;

/**
 * Class ToolServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolServices
{
    /**
     * @var \ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory
     */
    private $tool_factory;
    /**
     * @var \ILIAS\GlobalScreen\ScreenContext\ContextServices
     */
    private $context_services;

    public function __construct()
    {
        $this->tool_factory = new ToolFactory();
        $this->context_services = new ContextServices();
    }


    /**
     * @return ToolFactory
     */
    public function factory() : ToolFactory
    {
        return $this->tool_factory;
    }

    /**
     * @return ContextServices
     */
    public function context() : ContextServices
    {
        return $this->context_services;
    }
}
