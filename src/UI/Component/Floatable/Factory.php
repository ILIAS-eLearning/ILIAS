<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Floatable;

use ILIAS\UI\Component\Component;
/**
 * Floatable factory
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Instructional Overlays keep didactical instructions available to users
	 *       while they carry out workflows presented by this instruction that spread
	 *       over several screens. Instructional information and actions are made
	 *       available at the very screen the user is working on.
	 *   composition: >
	 *       The Instructional Overlay is comprised of a title and a glyphed
	 *       close-button, an assignment description (as shown in the info-tab of e.g. the
	 *       exercise) namely textual work instructions, accompanying files as links or
	 *       inline media, schedule information if applicable and some
	 *       submission-Button to carry out action the single most important action of
	 *       the workflow.
	 *   effect: >
	 *       Initially the Instructional Overlay is positioned upon the screen on the
	 *       right-hand side of the screen. The Instructional Overlay is called upon by
	 *       clicking a Button eliciting the respective hand-in workflow in the
	 *       Submission section of an Assignment. Instructional Overlays will persist
	 *       while users navigate through the system. It is closed by clicking the
	 *       Close glyph in the Instructional Overlay or completing the hand-in action.
	 *
	 * rules:
	 *   composition:
	 *       1: >
	 *          There MUST be only one button in an Instructional Overlay.
	 *   interaction:
	 *       1: >
	 *          The Instructional Overlay MUST only be closed by handing-in or
	 *          clicking the close-button and not by navigating ILIAS.
	 *       2: >
	 *          Completing the workflow by clicking the button MUST take the user back
	 *          to the location that the Instructional Overlay was called upon.
	 * ---
	 * @param string $title
	 * @param Component|Component[] $content
	 *
	 * @return  \ILIAS\UI\Component\Floatable\Floatable
	 */
	public function instructional($title, $content);
}
