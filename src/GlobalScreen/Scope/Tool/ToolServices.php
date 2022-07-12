<?php declare(strict_types=1);
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
