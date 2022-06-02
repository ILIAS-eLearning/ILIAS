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
 
namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Helpers for loaders of component renderers.
 */
trait LoaderHelper
{
    /**
     * Get and collapse the names of the passes components.
     *
     * @param	Component[]	$contexts
     * @return	string[]
     */
    protected function getContextNames(array $contexts) : array
    {
        $names = [];
        foreach ($contexts as $context) {
            $names[] = str_replace(" ", "", $context->getCanonicalName());
        }
        return $names;
    }
}
