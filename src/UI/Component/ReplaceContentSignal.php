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
 
namespace ILIAS\UI\Component;

/**
 * This signal replaces the content of a component by ajax
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Jesús Lópéz <lopez@leifos.com>
 * @author Alex Killing <killing@leifos.com>
 */
interface ReplaceContentSignal extends Signal
{
    /**
     * Get the same signal returning an element from the given url
     */
    public function withAsyncRenderUrl(string $url) : ReplaceContentSignal;

    /**
     * Get the url called to return the content.
     */
    public function getAsyncRenderUrl() : string;
}
