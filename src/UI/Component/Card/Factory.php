<?php

/* Copyright (c) 2016 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;

/**
 * This is how the factory for UI elements looks.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      A card is a flexible content container for small chunks of structured data.
	 *      Cards are often used in so-called Decks which are a gallery of Cards.
	 *   composition: >
	 *      Standard cards contain a header, which often includes an Image or Icon and a Title as well as possible actions as
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
	 *      1: Standard Cards MUST contain a title.
	 *      2: Standard Cards SHOULD contain an Image or Icon in the header section.
	 *      3: Standard Cards MAY contain Interaction Triggers.
	 *   style:
	 *      1: Sections of Cards MUST be separated by Dividers.
	 *   accessibility:
	 *      1: If multiple Cards are used, they MUST be contained in a Deck.
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Image\Image $image
	 * @return \ILIAS\UI\Component\Card\Standard
	 */
	public function standard($labelled_actions, $aria_label);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      A Repository Object card is a flexible content container for small chunks of structured data based on
	 *      repository objects and display specific icons to identify about what object type is the card related.
	 *      Cards are often used in so-called Decks which are a gallery of Cards.
	 *   composition: >
	 *      Repository Object cards add icons on a darkened layer over the image. This Darkened layer is divided into
	 *      4 horizontal cells where the icons can be located.
	 *      Starting from the left, the icons have the following order:
	 *          Cell 1: Object type (UI Icon)
	 *          Cell 2: Learning Progress (UI ProgressMeter) or Certificate (UI Icon)
	 *          Cell 3: Empty
	 *          Cell 4: Actions (UI Dropdown)
	 *      Cells and its content are responsively adapted if the size of the screen is changed.
	 *   rivals:
	 *      Heading Panel: Heading Panels fill up the complete available width in the Center Content Section. Multiple Heading Panels are stacked vertically.
	 *      Block Panels: Block Panels are used in Sidebars
	 *      Item: Items are used in lists or similar contexts.
	 * rules:
	 *   usage:
	 *       1: Repository Object Cards MAY contain a UI Icon displaying the object type.
	 *       2: Repository Object Cards MAY contain a UI ProgressMeter displaying the learning progress of the user.
	 *       3: Repository Object Cards MAY contain a UI Icon displaying a certificate icon if the user finished the task.
	 *       4: Repository Object Cards MAY contain a UI ProgressMeter OR UI Icon certificate, NOT both.
	 * featurewiki:
	 *       - https://docu.ilias.de/goto_docu_wiki_wpage_4921_1357.html
	 *
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Image\Image $image
	 * @return \ILIAS\UI\Component\Card\RepositoryObject
	 */
	public function repositoryObject($title, $image);

}
