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

    /**
     * ---
     * description:
     *   purpose: >
     *     This component is used to wrap an existing ILIAS UI element into a
     *     UI component AND enable a rendering of LaTeX within.
     *
     *     This is useful if a container of the UI components needs to contain
     *     content that is not yet implement in the centralized UI components.
     *   composition: >
     *     The LatexContent component contains html or any other content as string.
     *     LaTeX content within must be wrapped by the delimiters [tex] and [/tex]
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
     * @return \ILIAS\UI\Component\Legacy\LatexContent
     */
    public function latexContent(string $content): LatexContent;

    /**
     * ---
     * description:
     *   purpose: >
     *     The legacy segment is used as container for visible content in
     *     the context of sequence navigations.
     *     We currently lack quite a lot of UI components for describing the
     *     actual contents of the page, e.g. questions, combinations of text/table/form, etc.
     *     Until these components exist an enable us to better describe the
     *     correlations to navigations, the legacy segment is used to contain
     *     rendered HTML.
     *   composition: >
     *     The legacy segment contains html (or any other content) as string;
     *     it also has a title.
     *
     * context:
     *   - A segment is the content affected by operating the sequence navigation.
     * ---
     * @param string $title the title of the legacy segment
     * @param string $content the content of the legacy segment
     * @return \ILIAS\UI\Component\Legacy\Segment
     */
    public function segment(string $title, string $content): Segment;

}
