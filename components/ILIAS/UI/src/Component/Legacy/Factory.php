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


namespace ILIAS\UI\Component\Legacy;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     This component is used to wrap an existing ILIAS UI element into a
     *     UI component.
     *     This is useful if a container of the UI components needs to contain
     *     content that is not yet implement in the centralized UI components.
     *   composition: >
     *     The legacy component contains html or any other content as string.
     *
     * rules:
     *   usage:
     *     1: >
     *       This component MUST only be used to ensure backwards compatibility
     *       with existing UI elements in ILIAS,
     *       therefore it SHOULD only contain Elements which cannot be generated
     *       using other UI Components.
     * ---
     * @param string $content the content of the legacy component
     * @return \ILIAS\UI\Component\Legacy\Content
     */
    public function content(string $content): Content;

}
