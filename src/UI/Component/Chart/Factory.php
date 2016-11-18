<?php
namespace ILIAS\UI\Component\Chart;
/**
 * This is how a factory for glyphs looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Pick Charts are used to display a set of items some of which especially highlighted. E.g. they can
	 *     be used to inform about a score or target on a rank ordered scale.
	 *   composition: >
	 *     Pick Charts are composed of of a set of bars of equal size. Each bar contains a title. The highlighted elements
	 *     differ from the others through their darkened background.
	 *
	 * context: >
	 *   Pick Charts are are used in the Competence Management on the Personal Desktop.
	 *
	 * rules:
	 *   composition:
	 *     1: Each Bar of the Pick Charts MUST bear a title.
	 *     2: The title of Pick Charts MUST NOT contain any other content than text and links.
	 * ----
	 * @param array string => boolean Set of elements to be rendered, boolean should be true if highlighted
	 * @return  \ILIAS\UI\Component\Chart\Pick
	 */
	public function pick(array $items);
}