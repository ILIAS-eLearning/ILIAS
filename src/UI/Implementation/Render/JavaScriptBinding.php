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

/**
 * Provides methods to interface with javascript.
 */
interface JavaScriptBinding
{
    /**
     * Create a fresh unique id.
     *
     * This MUST return a new id on every call.
     */
    public function createId() : string;

    /**
     * Add some JavaScript-statements to the on-load handler of the page.
     */
    public function addOnLoadCode(string $code) : void;

    /**
     * Get all the registered on-load javascript code for the async context, e.g. return all code
     * inside <script> tags
     */
    public function getOnLoadCodeAsync() : string;
}
