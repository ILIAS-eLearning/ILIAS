<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This is how a factory for buttons looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The standard button is the default button to be used in ILIAS. If
	 *       there is no good reason using another button instance in ILIAS, this
	 *       is the one that should be used.
	 *   composition: >
	 *       The standard button uses the primary bolor as background.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Standard buttons MUST be used if there is no good reason using
	 *          another instance.
	 *   ordering:
	 *       1: >
	 *          The most important standard button SHOULD be first in reading
     *          direction if there are several buttons.
	 *       2: >
	 *          In the toolbar and in forms special regulations for the ordering
	 *          of the buttons MAY apply.
	 *   responsiveness:
	 *       1: >
	 *          The most important standard button in multi-action bars MUST be
	 *          sticky (stay visible on small screens).
	 * ---
	 * @param	string		$label
	 * @param	string		$action
	 * @return  \ILIAS\UI\Component\Button\Standard
	 */
	public function standard($label, $action);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The primary button indicates the most important action on a screen.
	 *       By definition there can only be one single “most important” action
	 *       on any given screen and thus only one single primary button per screen.
	 *   composition: >
	 *       The background color is the btn-primary-color. This screen-unique
	 *       button-color ensures that it stands out and attracts the user’s
	 *       attention while there are several buttons competing for attention.
	 *   effect: >
	 *      In toolbars the primary button are required to be sticky, meaning
	 *      they stay in view in the responsive view.
	 *
	 * background: >
	 *      Tiddwell refers to the primary button as “prominent done button” and
	 *      describes that “the button that finishes a transaction should be
	 *      placed at the end of the visual flow; and is to be made big and well
	 *      labeled.” She explains that “A well-understood, obvious last step
	 *      gives your users a sense of closure. There’s no doubt that the
	 *      transaction will be done when that button is clicked; don’t leave
	 *      them hanging, wondering whether their work took effect”.
	 *
	 *      The GNOME Human Interface Guidelines -> Buttons also describes a
	 *      button indicated as most important for dialogs.
	 *
	 * context:
	 *      - “Start test” in Module “Test”
	 *      - “Hand In” in exercise
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *           Most pages SHOULD NOT have any Primary Button at all.
	 *       2: >
	 *           There MUST no more than one Primary Button per page in ILIAS.
	 *       3: >
	 *           The decision to make a Button a Primary Button MUST be confirmed
	 *           by the JF.
	 * ---
	 * @param	string		$label
	 * @param	string		$action
	 * @return  \ILIAS\UI\Component\Button\Primary
	 */
	public function primary($label, $action);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The close button triggers the closing of some collection displayed
	 *       temporarily such as an overlay.
	 *   composition: >
	 *       The close button is displayed without border.
	 *   effect: >
	 *       Clicking the close button closes the enclosing collection.
	 *
	 * rules:
	 *   ordering:
	 *       1: >
	 *           The Close Button MUST always be positioned in the top right of a
	 *           collection.
	 *   accessibility:
	 *       1: >
	 *           The functionality of the close button MUST be indicated for screen
	 *           readers by an aria-label.
	 * ---
	 * @return  \ILIAS\UI\Component\Button\Close
	 */
	public function close();
}
