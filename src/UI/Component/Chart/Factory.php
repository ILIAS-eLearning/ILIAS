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
	 *     Scale Bars are used to display a set of items some of which
	 *     especially highlighted. E.g. they can be used to inform about a
	 *     score or target on a rank ordered scale.
	 *   composition: >
	 *     Scale Bars are composed of of a set of bars of equal size. Each
	 *     bar contains a title. The highlighted elements
	 *     differ from the others through their darkened background.
	 *
	 * context: >
	 *   Scale Bars are are used in the Competence Management on the Personal Desktop.
	 *
	 * rules:
	 *   composition:
	 *     1: Each Bar of the Scale Bars MUST bear a title.
	 *     2: The title of Scale Bars MUST NOT contain any other content than text.
	 * ----
	 * @param array string => boolean Set of elements to be rendered, boolean should be true if highlighted
	 * @return  \ILIAS\UI\Component\Chart\ScaleBar
	 */
	public function scaleBar(array $items);


    /**
     * ---
     * description:
     *   purpose: >
     *     Speedos are used to display a progress, in the form of an speedometer.
     *     E.g. they can be used to inform about a progress in a course or other
     *     learning objectives.
     *   composition: >
     *     Speedos are composed of one or two bars inside a speedometer-like container.
     *     The bars change between two colors, to identify a specific reached score. It
     *     additionally may show a percentage score.
     *
     * context: >
     *     Speedos are used inside courses on the content view.
     *
     * ---
     * @return \ILIAS\UI\Component\Chart\Speedo\Factory
     */
    public function speedo();

}