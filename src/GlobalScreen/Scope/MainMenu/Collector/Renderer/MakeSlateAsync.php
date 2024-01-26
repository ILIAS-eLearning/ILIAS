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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Trait MakeSlateAsync
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait MakeSlateAsync
{
    use Hasher;

    /**
     * @param Slate                       $slate
     * @param supportsAsynchronousLoading $item
     * @return Slate
     */
    protected function addAsyncLoadingCode(Slate $slate, supportsAsynchronousLoading $item) : Slate
    {
        if ($item->supportsAsynchronousLoading() === false) {
            return $slate;
        }

        $serialize = $item->getProviderIdentification()->serialize();
        $replace_signal = $slate->getReplaceSignal()->withAsyncRenderUrl("./gs_content.php?item={$this->hash($serialize)}");

        $slate = $slate->appendOnInView($replace_signal);

        return $slate;
    }
}
