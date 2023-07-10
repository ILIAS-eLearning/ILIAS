<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Input\Container;

/**
 * This is what a factory for input containers looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      Forms are used to let the user enter or modify data, check her inputs
     *      and submit them to the system.
     *      Forms arrange their contents (i.e. fields) in an explanatory rather
     *      than space-saving way.
     *   composition: >
     *      Forms are composed of input fields, displaying their labels and bylines.
     *   rivals:
     *      filter: >
     *          Filters are used to limit search results; they never modify data in
     *          the system.
     *
     *
     * ---
     * @return  \ILIAS\UI\Component\Input\Container\Form\Factory
     */
    public function form(): Form\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *      Filters are used to let the user limit content within a table, list or any other collection of items
     *      presented on the screen.
     *   composition: >
     *      Filters are composed of two visually separated areas:
     *      First, there is the Filter Bar at the top. It contains an Expand/Collapse Bulky Button with the label
     *      "Filter" on the left side. On the right, a Toggle Button for activating/deactivating the Filter is placed.
     *      Second, there is an area which is called the Filter Content Area. It is placed below the Filter Bar. This
     *      Filter Content Area is available in two versions depending on the view (expanded = editable,
     *      collapsed = non-editable) of the Filter:
     *      If the Filter is expanded, editable Input Fields are shown in this Filter Content Area, starting from the
     *      top left. Input Fields within a Filter are rendered with its label on the left and a "Remove" Glyph on the
     *      right. Values can be entered in the fields. After the last Input Field, an "Add" Bulky Button is shown if
     *      currently hidden Input Fields can be added. To hide Input Fields, the "Remove" Glyph of the corresponding
     *      field can be used. Two Bulky Buttons, one called "Apply" and another called "Reset", are placed on the left
     *      side below the Input Fields.
     *      If the Filter is collapsed, the labels and values of the applied Input Fields are visible as non-editable
     *      text in the Filter Content Area. The texts of the Input Fields have a reduced size and are rendered close
     *      together to save space in the Filter Content Area when the Filter is collapsed. If the Filter is deactivated
     *      at the same time, the applied Input Fields are grayed out. If there is not applied a value for at least one
     *      Input Field, the Filter Content Area is not displayed at all and the user only sees the Filter Bar.
     *   effect: >
     *      The Filter has two statuses: deactivated and activated. The status of the Filter indicates whether the
     *      item collection is filtered or not. The status of the Filter is signalized by the Toggle Button. When the
     *      Toggle Button is ON, the Filter is activated and the corresponding item collection is filtered according to
     *      the predefinded Input Field values. When the Toggle Button is OFF, the Filter is deactivated and the item
     *      collection is not filtered. Clicking on the Toggle Button activates/deactivates the Filter and reloads the
     *      content of the item collection immediately. Clicking on the Toggle Button always applies the entered values
     *      and the visibility of the Input Fields.
     *      There are different behaviours when switching the Toggle Button from OFF to ON: If the Filter is collapsed
     *      and no values have been entered in the Input Fields, the Filter is activated and expanded at the same time
     *      so that the Input Fields can be edited directly. If the Filter is collapsed and already applied values exist
     *      in the Input Fields, the Filter will stay collapsed and the applied Input Fields are no longer grayed out
     *      after activation, so that it is clear that filtering is currently being executed according to these values.
     *      If the Filter is expanded, it will stay expanded.
     *      When switching the Toggle Button from ON to OFF, there are also different behaviours: If the Filter is
     *      collapsed and contains already applied values in the Input Fields, the applied Input Fields will be grayed
     *      out. If the Filter is collapsed and the values for all Input Fields are removed, the Filter Content Area
     *      will disappear. If the Filter is expanded, the Input Fields stay editable in the deactivated state of the
     *      Filter.
     *      The Expand/Collapse Bulky Button in the Filter Bar can be used to show/hide the Filter Content Area if the
     *      Filter is empty. If there are applied values, it can be used to change the Filter Content Area between the
     *      editable and not-editable version. The varying rendering of this area is described in "composition".
     *      In the Filter Content Area, the Apply Button and Reset Button can be used. Clicking on the Apply Button
     *      has multiple effects: The appearance of the Input Fields, i.e. if an Input Field is shown or hidden in the
     *      Filter Content Area, will be saved. The values, which are entered into the Input Fields, are applied. The
     *      content of the item collection is reloaded immediately according to the Filter settings. The status of the
     *      Filter changes to activated if it was deactivated before. Clicking on the Reset Button brings the Filter
     *      back to its default settings, which were definded by the consuming developer: The appearance of the Input
     *      Fields, i.e. if an Input Field is shown or hidden in the Filter Content Area, will be reset to its default.
     *      The values of the Input Fields are reset to its default values. The Filter gets its initial status
     *      (activated/deactivated). The content of the item collection is reloaded immediately according to the status
     *      of the Filter and, if activated, the default values of the Input Fields. Both Buttons, Apply and Reset, are
     *      always clickable, regardless of whether it has an effect on the Filter or the item collection.
     *      In the Filter Content Area, clicking on the Remove Glyph next to an Input Field hides this Input Field
     *      from the field of view. If an Input Field contains a value, the value will be deleted when removing the
     *      Input Field.
     *      Clicking on the Add Bulky Button shows up a list with labels of all currently hidden Input Fields in a
     *      Popover, which were either removed manually by the user or initially by the developer. Clicking on one label
     *      in this list makes the selected Input Field visible on its predefined position and puts the focus on it.
     *   rivals:
     *      forms: >
     *          Unlike Filters, Forms are used to enter or modify data in the system.
     *
     * rules:
     *   usage:
     *     1: Filters MUST be used on the same page as tables or other collections of items.
     *     2: Input Fields with default values MUST NOT be rendered initially hidden.
     *   interaction:
     *     1: Input Fields SHOULD be editable directly when clicking on it (e.g. Text Input, Select Input).
     *     2: >
     *        For more complex Input Fields (e.g. Duration Input, Multi Select Input), a Popover MUST be used, which
     *        shows up the whole Input when clicking on the Input Field in the Filter. The Input Field in the Filter
     *        MUST show the entered values of the Input Field in the Popover in real time.
     *     3: Input Fields MUST be editable, regardless of whether the Filter is deactivated or activated.
     *     4: Input Fields MUST only be editable when the Filter is expanded.
     *     5: >
     *        Buttons MUST always be clickable, regardless of whether the Filter is deactivated or activated and
     *        regardless of whether it has an effect on the Filter or not.
     *   wording:
     *     1: The labels of the Input Fields MUST be shown shortened (with three dots at the end) when space is scarce.
     *     2: The values of the Input Fields MUST be shown shortened (with three dots at the end) when space is scarce.
     *   style:
     *     1: >
     *        The Filter Bar and the Input Fields Area SHOULD be separated visually, e.g. with a border-line or
     *        different background colors.
     *     2: The word "Filter" MUST be used as label for the Expand/Collapse Bulky Button.
     *     3: The Toggle Button MUST contain a label representing its current status (ON/OFF).
     *     4: The Apply Button and the Reset Button MUST use "Apply" respectively "Reset" as its Button label.
     *     5: The Popovers SHOULD be shown below the elements which trigger them.
     *   ordering:
     *     1: A Filter MUST be placed above the item collection it acts upon.
     *   responsiveness:
     *     1: >
     *        On screens larger than medium size, there MUST be three Input Fields per row. On medium-sized screens
     *        or below, only one Input Field MUST be shown per row.
     *     2: On small-sized screens, the "Apply" and "Reset" Buttons MAY be shown one below the other.
     *   accessibility:
     *     1: >
     *        The Expand/Collapse Bulky Button MUST be accessible by keyboard by using Tab and clickable by using
     *        Return or Space.
     *     2: The Toggle Button MUST be accessible by keyboard by using Tab and clickable by using Return or Space.
     *     3: >
     *        The Apply Button and Reset Button MUST be accessible by keyboard by using Tab and clickable by using
     *        Return or Space.
     *     4: >
     *        The Remove Glyph next to the Input Field MUST be accessible by keyboard by using Tab and clickable by
     *        using Return.
     *     5: >
     *        Input Fields MUST be accessible by keyboard by using Tab. If they are rendered without a Popover, they
     *        MUST be directly editable when getting focus. If they are more complex and rendered with a Popover, they
     *        MUST be clickable by using Return or Space to open the Popover.
     *     6: If a click event for an complex Input Field is triggered, the focus MUST change to its Popover.
     *     7: Using Return while the focus is on an Input Field MUST imitate a click on the Apply Button.
     *     8: The Add Button MUST be accessible by keyboard by using Tab and clickable by using Return or Space.
     *     9: >
     *        If a click event for the Add Button is triggered, the Popover with the list of hidden Input Fields MUST
     *        be accessible by keyboard by using Tab. Every single list entry MUST be accessible by keyboard by using
     *        Tab and clickable by using Return or Space.
     *
     *
     * ---
     * @return  \ILIAS\UI\Component\Input\Container\Filter\Factory
     */
    public function filter(): Filter\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *      The View Control Container orchestrates a collection of View Control Inputs
     *      for exactly one visualization of data (e.g. a table or diagram) and defines
     *      the way how input from those controls is being relayed to the system.
     *   composition: >
     *      The View Control Container encapsulates View Control Inputs.
     *   rivals:
     *      filter: >
     *          Filters are used to limit presented data, i.e. to modify the dataset.
     *          View Controls will alter the presentation.
     *      form: >
     *          View Controls will not change persistent data.
     * rules:
     *   usage:
     *     1: >
     *        View Control MUST be used on the same page as the visualization they have effect
     *        upon.
     *   interaction:
     *     1: >
     *        View Control Containers SHOULD NOT be applied by (manual) submission; operating
     *        a View Control SHOULD apply all View Controls in this container to the targeted
     *        visualization.
     *
     * ---
     * @return \ILIAS\UI\Component\Input\Container\ViewControl\Factory
     */
    public function viewControl(): ViewControl\Factory;
}
