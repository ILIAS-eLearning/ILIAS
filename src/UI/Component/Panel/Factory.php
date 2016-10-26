<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use \ILIAS\UI\Component\Component as Component;
/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Standard Panels are used in the Center Content section to group content.
	 *   composition: >
	 *      Standard consist of a title and a content section. The structure of this content might be varying from Standard
	 *      Panel to Standard Panel. Standard Panels may contain Sub Panels.
	 *   rivals:
	 *      Cards: >
	 *        Often Cards are used in Decks to display multiple uniformly structured chunks of Data horizontally and vertically.
	 *
	 * rules:
	 *   usage:
	 *      1: In Forms Standard Panels MUST be used to group different sections into Form Parts.
	 *      2: Standard Panels SHOULD be used in the Center Content as primary Container for grouping content of varying content.
	 * ---
	 * @param string $title
	 * @param Component[]|Component
	 * @return \ILIAS\UI\Component\Panel\Standard
	 */
	public function standard($title,$content);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Sub Panels are used to structure the content of Standard panels further into titled sections.
	 *   composition: >
	 *       Sub Panels consist of a title and a content section. They may contain a Card on their right side to display
	 *       meta information about the content displayed.
	 *   rivals:
	 *      Standard Panel: >
	 *        The Standard Panel might contain a Sub Panel.
	 *      Card: >
	 *        The Sub Panels may contain one card.
	 *
	 * rules:
	 *   usage:
	 *      1: Sub Panels MUST only be inside Standard Panels
	 *   composition:
	 *      1: Sub Panels MUST NOT contain Sub Panels or Standard Panels as content.
	 * ---
	 * @param string $title
	 * @param Component[]|Component
	 * @return \ILIAS\UI\Component\Panel\Sub
	 */
	public function sub($title,$content);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Report Panels display user-generated data combining text in lists, tables and sometimes  charts.
	 *       Report Panels always draw from two distinct sources: the structure / scaffolding of the Report Panels
	 *       stems from user-generated content (i.e a question of a survey, a competence with levels) and is
	 *       filled with user-generated content harvested by that very structure (i.e. participants’ answers to
	 *       the question, self-evaluation of competence).
	 *   composition: >
	 *       They are composed of a Standard Panel which contains several Block Panels. They might also contain
	 *       a card to display information meta information in their first block.
	 *   effect: >
	 *       Report Panels are predominantly used for displaying data. They may however comprise links or buttons.
	 *   rivals:
	 *      Standard Panels: >
	 *        The Report Panels contains sub panels used to structure information.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *         Report Panels SHOULD be used when user generated content of two sources (i.e results, guidelines in a template)
	 *         is to be displayed alongside each other.
	 *   interaction:
	 *      1: Links MAY open new views.
	 *      2: Buttons MAY trigger actions or inline editing.
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Panel\Sub[] $sub_panels
	 * @return \ILIAS\UI\Component\Panel\Report
	 */
	public function report($title,$sub_panels);
}
