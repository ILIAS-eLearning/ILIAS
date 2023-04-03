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
namespace ILIAS\GlobalScreen\Scope\Tool\Provider;

use ILIAS\GlobalScreen\Provider\DynamicProvider;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\GlobalScreen\ScreenContext\ScreenContextAwareProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

/**
 * Interface DynamicToolProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicToolProvider extends Provider, ScreenContextAwareProvider
{
    /**
     * @param CalledContexts $called_contexts
     * @return Tool[] These Slates
     * can be passed to the MainMenu dynamic for a specific location/context.
     * @see DynamicProvider
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array;
}
