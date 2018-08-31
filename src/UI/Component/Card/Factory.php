<?php

/* Copyright (c) 2016 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Component;

/**
 * This is how the factory for UI elements looks.
 */
interface Factory {

	/**
	 * TODO ::: Rename this card to standard and change this description if necessary.
	 * ---
	 * description:
	 *   purpose: >
	 *      A card is a flexible content container for small chunks of structured data.
	 *      Cards are often used in so-called Decks which are a gallery of Cards.
	 *   composition: >
	 *      Cards contain a header, which often includes an Image or Icon and a Title as well as possible actions as
	 *      Default Buttons and 0 to n sections that may contain further textual descriptions, links and buttons.
	 *      The size of the cards in decks may be set to extra small (12 cards per row),
	 *      small (6 cards per row, default), medium (4 cards per row), large (3 cards per row),
	 *      extra large (2 cards per row) and full (1 card per row). The number of cards
	 *      per row is responsively adapted, if the size of the screen is changed.
	 *   effect: >
	 *      Cards may contain Interaction Triggers.
	 *   rivals:
	 *      Heading Panel: Heading Panels fill up the complete available width in the Center Content Section. Multiple Heading Panels are stacked vertically.
	 *      Block Panels: Block Panels are used in Sidebars
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_3208_1357.html
	 *
	 * rules:
	 *   composition:
	 *      1: Cards MUST contain a title.
	 *      2: Cards SHOULD contain an Image or Icon in the header section.
	 *      3: Cards MAY contain Interaction Triggers.
	 *   style:
	 *      1: Sections of  Cards MUST be separated by Dividers.
	 *   accessibility:
	 *      1: If multiple Cards are used, they MUST be contained in a Deck.
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Image\Image $image
	 * @return \ILIAS\UI\Component\Card\Card
	 */
	public function card($labelled_actions, $aria_label);


	/**
	 * TODO ::: Create full description for the custom card.
	 * TODO ::: Rename this custom to something more relevant.
	 * TODO ::: Type the parameters properly.
	 * ---
	 *
	 * TODO ::: What outlined icons should we use?
	 * TODO ::: To discuss:
	 * Are the object type icons mandatory?
	 * 		(yes) UI Outlined Icon in the constructor.
	 * 		(no) New method withObjectIcon(UI Outlined Icon) will be required.
	 * 		IMO should be yes. Since we are placing the icons in specific position.
	 * Should we consider the possibility to allow other icons depending on the object type? e.g.Survey finished or not, sessions attending or no etc.
	 * 		(yes) Should we create different cards for object type?
	 * 			e.g. courseCard(?)->withProgress(icon)
	 * 			e.g. sessionCard(?)->withAttending(icon)

	 *
	 *
	 * featurewiki:
	 *       - https://docu.ilias.de/goto_docu_wiki_wpage_4921_1357.html
	 *
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Image\Image $image
	 * @param \ILIAS\UI\Component\Icon\????
	 * @return \ILIAS\UI\Component\Card\Custom
	 */
	public function custom($title, $image, $object_icon);

}
