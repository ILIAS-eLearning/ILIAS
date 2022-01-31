<?php /** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Scope\Tool;

use ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\GlobalScreen\SingletonTrait;

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
 * Class ToolServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolServices
{
    use SingletonTrait;
    
    /**
     * @return ToolFactory
     */
    public function factory() : ToolFactory
    {
        return $this->get(ToolFactory::class);
    }
    
    /**
     * @return ContextServices
     */
    public function context() : ContextServices
    {
        return $this->get(ContextServices::class);
    }
}
