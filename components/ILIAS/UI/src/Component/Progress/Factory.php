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

namespace ILIAS\UI\Component\Progress;

use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Progress Bar is designed to represent the state of a single or bundled task
     *     or process, which can be processed in a single step without further user interaction
     *     and takes a while to finish.
     *   composition: >
     *     The Progress Bar is composed out of one horizontal track, the area of which is
     *     filled according to the current progress (value). It is also accompanied by a label,
     *     describing the process/task at hand, and a Glyph to indicate a finished status
     *     (success or failure). An optional message can be displayed, to inform about a
     *     concrete status.
     *   effect: >
     *     When the Progress Bar value is updated, the filled area of the track changes
     *     accordingly.
     *     When the Progress Bar is finished, the Glyph changes to one indicating success or
     *     failure, and an according message will be shown.
     *   rivals:
     *     ProgressMeter: >
     *       use a ProgressMeter if the quality of the progress is evaluated and/or the progress is compared.
     *     Workflow: >
     *       use a Workflow component if the underlying process/task is completed in multiple steps that require
     *       further user interaction.
     *
     * background: >
     *   https://developer.mozilla.org/en-US/docs/Web/HTML/Element/progress,
     *   https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Roles/progressbar_role
     *
     * rules:
     *   usage:
     *     1: >
     *       The Progress Bar (value) SHOULD NOT be decreased. It should only be reset to 0, if
     *       the underlying process/task is restarted.
     *     2: >
     *       The Progress Bar SHOULD NOT be used, if the underlying process/task can only be
     *       0% or 100% processed. A loading animation and/or Glyph COULD be used instead.
     *   accessibility:
     *     1: >
     *       The Progress Bar must be fully understandable without any graphical UI.
     * ---
     * @param \ILIAS\Data\URI $async_url
     * @param string          $label
     * @return \ILIAS\UI\Component\Progress\Bar
     */
    public function bar(string $label, ?URI $async_url = null): Bar;

    /**
     * ---
     * description:
     *   purpose: >
     *     A State represents a particular version of a component at a specific time, which will be used
     *     to communicate with our client during asynchronous requests. The State conveys information in
     *     a manner that is understood by our clientside component, and provides the nesessary information
     *     to perform a desired change. We have been referring to this concept as "HTML over the wire" in
     *     the past, and are now implementing it for certain components in iterations.
     *   composition: >
     *     A State consist of HTML structures, typically other (or the same) UI component(s).
     *   effect: >
     *     When a State is rendered, it will be used by the clientside component to update the
     *     existing HTML structure according to the new State.
     *
     * rules:
     *   usage:
     *     1: You MUST NOT use a State outside of asynchronous requests.
     *     2: You MUST use @see Renderer::renderAsync() to render a State.
     * ---
     * @return \ILIAS\UI\Component\Progress\State\Factory
     */
    public function state(): State\Factory;
}
