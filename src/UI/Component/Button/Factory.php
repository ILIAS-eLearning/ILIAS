<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Signal;

/**
 * This is how a factory for buttons looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The standard button is the default button to be used in ILIAS. If
     *       there is no good reason using another button instance in ILIAS, this
     *       is the one that should be used.
     *   composition: >
     *       The standard button uses the primary color as background.
     *   effect: >
     *       If the loading animation is activated, the button shows a spinner wheel
     *       on-click and automatically switches to a deactivated state.
     *
     * rules:
     *   usage:
     *       1: >
     *          Standard buttons MUST be used if there is no good reason using
     *          another instance.
     *       2: >
     *          The loading animation SHOULD be activated if the Buttons starts
     *          any background process (e.g. ajax calls) without any other immediate
     *          feedback for the user. After the process finished, the button MUST be
     *          removed from/replaced in the DOM.
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
     *   accessibility:
     *       1: >
     *          Standard buttons MAY define aria-label attribute. Use it in cases
     *          where a text label is not visible on the screen or when the label does not provide enough information
     *          about the action.
     *       2: >
     *          Standard buttons MAY define aria-checked attribute. Use it to inform which is the currently active button.
     * ---
     * @param	string				$label
     * @param	string|Signal		$action		will be triggered on click
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
     *      they stay in view in the responsive view. If the loading animation
     *      is activated, the button shows a spinner wheel on-click and automatically
     *      switches to a deactivated state.
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
     *           There MUST be no more than one Primary Button per page in ILIAS.
     *       3: >
     *           The decision to make a Button a Primary Button MUST be confirmed
     *           by the JF.
     *       4: >
     *           The loading animation rules of the Standard Button MUST be respected.
     * ---
     * @param	string		$label
     * @param	string|Signal		$action		will be triggered on click
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

    /**
     * ---
     * description:
     *   purpose: >
     *       Shy buttons are used in contexts that need a less obtrusive presentation
     *       than usual buttons have, e.g. in UI collections like Dropdowns.
     *   composition: >
     *       Shy buttons do not come with a separte background color.
     *
     * rules:
     *   usage:
     *       1: >
     *           Shy buttons MUST only be used, if a standard button presentation
     *           is not appropriate. E.g. if usual buttons destroy the presentation
     *           of an outer UI component or if there is not enough space for a
     *           standard button presentation.
     * ---
     * @param	string		$label
     * @param	string|Signal		$action		will be triggered on click
     * @return  \ILIAS\UI\Component\Button\Shy
     */
    public function shy($label, $action);

    /**
     * ---
     * description:
     *   purpose: >
     *       The Month Button enables to select a specific month to fire some action (probably a change of view).
     *   composition: >
     *       The Month Button is composed of a Button showing the default month directly (probably the
     *       month currently rendered by some view). A dropdown contains an interface enabling the selection of a month from
     *       the future or the past.
     *   effect: >
     *      Selecting a month from the dropdown directly fires the according action (e.g. switching the view to the
     *      selected month). Technically this is currently a Javascript event being fired.
     *
     * context:
     *      - Marginal Grid Calendar
     *
     * rules:
     *   interaction:
     *       1: >
     *          Selecting a month from the dropdown MUST directly fire the according action.
     *
     * ---
     * @param string $default Initial value, use format "mm-yyyy".
     * @return  \ILIAS\UI\Component\Button\Month
     */
    public function month($default);

    /**
     * ---
     * description:
     *   purpose: >
     *     Tags classify entities. Thus, their primary purpose is the visualization
     *     of those classifications for one entity. However, tags are usually
     *     clickable - either to edit associations or list related entities,
     *     i.e. objects with the same tag.
     *   composition: >
     *     Tags are a colored area with text on it.
     *     When used in a tag-cloud (a list of tags), tags can be visually "weighted"
     *     according to the number of their occurences, be it with different
     *     (font-)sizes, different colors or all of them.
     *   effect: >
     *     Tags may trigger an action or change the view when clicked.
     *     There is no visual difference (besides the cursor) between
     *     clickable tags and tags with unavailable action.
     *
     *
     * rules:
     *   style:
     *       1: Tags SHOULD be used with an additonal class to adjust colors.
     *       2: >
     *           The font-color SHOULD be set with high contrast to the chosen
     *           background color.
     *   accessibility:
     *       1: >
     *           The functionality of the tag button MUST be indicated for screen
     *           readers by an aria-label.
     * ---
     * @param	string		$label
     * @param	string|Signal		$action		will be triggered on click
     * @return  \ILIAS\UI\Component\Button\Tag
     */
    public function tag($label, $action);

    /**
     * ---
     * description:
     *   purpose: >
     *     The bulky button is highly obtrusive. It combines the recognisability
     *     of a graphical element with an explicit textual label on an unusually
     *     sized button. It is hard to overlook and indicates an important action
     *     on the screen.
     *
     *   composition: >
     *     The Bulky Button consists of an icon or glyph and a (very short) text.
     *
     *   effect: >
     *     The button has an "engaged"-state: When the button is used to toggle the
     *     visibility of a component, it stays engaged until the component is hidden again.
     *
     *   rivals:
     *     Primary Button: >
     *       Primary Buttons indicate the most important action among a collection
     *       of actions, e.g. in a tool bar, controls of a form or in a modal. Bulky
     *       Buttons make it hard to miss the indicated action by occupying space.
     *
     * rules:
     *   usage:
     *     1: >
     *       Since Bulky Buttons are so obtrusive they MUST only be used
     *       to indicate important actions on the screen.
     *   wording:
     *     1: The icon/glyph and the text on the Bulky Button MUST be corresponding.
     *   style:
     *     1: >
     *       Bulky Buttons MUST occupy as much space as their container
     *       leaves them.
     *     2: >
     *       When used to toggle the visibility of another component, the button
     *       MUST reflect the componentes state of visibility.
     *
     *   accessibility:
     *     1: >
     *       The functionality of the Bulky Button MUST be indicated for screen
     *       readers by an aria-label.
     *     2: Bulky Buttons MUST define aria-pressed attribute.
     *
     * ---
     * @param	\ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph		$icon_or_glyph
     * @param	string		$label
     * @param	string		$action
     * @return  \ILIAS\UI\Component\Button\Bulky
     */
    public function bulky($icon_or_glyph, $label, $action);

    /**
     * ---
     * description:
     *   purpose: >
     *       The Toggle Button triggers the activation/deactivation of some control already shown on the screen, i.e.
     *       a filter. The deactivation of a control means, that it is still shown and the user can still interact
     *       with it, but it has no effect on the system.
     *   composition: >
     *       The Toggle Button uses different background colors for the on and off state.
     *       The toggle of the Toggle Button is placed on the left side when it is off, and on the right side
     *       when it is on.
     *   effect: >
     *       Clicking the Toggle Button activates/deactivates the related control. The on/off state of the control
     *       is visually noticeable for the user, i.e. by greying out the control in the off state.
     *   rivals:
     *     Checkbox: >
     *       Checkboxes are established as controls for choosing a value for submission and are therefore handled as Inputs.
     *       Toggle Buttons are used for switching the activation of some control and are therefore handled as Buttons.
     *     Collapse/Expand Glyph: >
     *       Collapse and Expand Glyphs hide or trigger the display of some content. Toggle Buttons leave a control
     *       visible to the user, but activate or deactivate it.
     *     Mode View Control: >
     *       Mode View Controls enable the switching between different aspects of some data. Toggle Buttons
     *       activate/deactivate some control, but do not change or switch the control which the user see currently.
     *
     * rules:
     *   usage:
     *       1: >
     *           The Toggle Button MUST be placed next to the control it activates/deactivates.
     *   ordering:
     *       1: >
     *           The Toggle Button SHOULD be placed above the control it activates/deactivates.
     *   accessibility:
     *       1: >
     *           The functionality of the Toggle Button MUST be indicated for screen
     *           readers by an aria-label.
     *       2: >
     *           The state of the Toggle Button MUST be indicated for screen readers
     *           by using the aria-pressed attribute.
     * ---
     * @param	string			$label
     * @param	string|Signal	$toggle_on_action action performed when button changes from off to on
     * @param	string|Signal	$toggle_off_action action performed when button changes from on to off
     * @param	bool			$is_on
     * @param	Signal|null		$click_signal action performed when button is clicked
     * @return  \ILIAS\UI\Component\Button\Toggle
     */
    public function toggle(string $label, $on_action, $off_action, bool $is_on = false, Signal $click_signal = null) : \ILIAS\UI\Component\Button\Toggle;
}
