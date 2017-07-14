<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 *
 * Consumers of the UI-Service MUST program against this interface and not
 * use any concrete implementations from Internal.
 */

interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Counter inform users about the quantity of items indicated
	 *       by a glyph.
	 *   composition: >
	 *       Counters consist of a number and some background color and are
	 *       placed one the 'end of the line' in reading direction of the item
	 *       they state the count for.
	 *   effect: >
	 *       Counters convey information, they are not interactive.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_3854_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: A counter MUST only be used in combination with a glyph.
	 *   composition:
	 *       1: >
	 *          A counter MUST contain exactly one number greater than zero and no
	 *          other characters.
	 * ---
	 * @return  \ILIAS\UI\Component\Counter\Factory
	 */
	public function counter();
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Glyphs map a generally known concept or symbol to a specific concept in ILIAS.
	 *       Glyphs are used when space is scarce.
	 *   composition: >
	 *       A glyph is a typographical character that represents
	 *       something else. As any other typographical character, they can be
	 *       manipulated by regular CSS. If hovered they change their background
	 *       to indicate possible interactions.
	 *   effect: >
	 *       Glyphs act as trigger for some action such as opening a certain
	 *       Overlay type or as shortcut.
	 *   rivals:
	 *       icon: >
	 *           Standalone Icons are not interactive. Icons can be in an interactive container however.
	 *           Icons merely serve as additional hint of the functionality described by some title.
	 *           Glyphs are visually distinguished from object icons: they are monochrome.
	 * background: >
	 *     "In typography, a glyph is an elemental symbol within an agreed set of
	 *     symbols, intended to represent a readable character for the purposes
	 *     of writing and thereby expressing thoughts, ideas and concepts."
	 *     (https://en.wikipedia.org/wiki/Glyph)
	 *
	 *     Lidwell states that such symbols are used "to improve the recognition
	 *     and recall of signs and controls".
	 *
	 * rules:
	 *   usage:
	 *       1: Glyphs MUST NOT be used in content titles.
	 *       2: >
	 *          Glyphs MUST be used for cross-sectional functionality such as mail for
	 *          example and NOT for representing objects.
	 *       3: >
	 *          Glyphs SHOULD be used for very simple tasks that are repeated at
	 *          many places throughout the system.
	 *       4: >
	 *          Services such as mail MAY be represented by a glyph AND an icon.
	 *   style:
	 *       1: >
	 *          All Glyphs MUST be taken from the Bootstrap Glyphicon Halflings
	 *          set. Exceptions MUST be approved by the JF.
	 *   accessibility:
	 *       1: >
	 *          The functionality triggered by the Glyph must be indicated to
	 *          screen readers with by the attribute aria-label or aria-labelledby attribute.
	 * ---
	 * @return  \ILIAS\UI\Component\Glyph\Factory
	 */
	public function glyph();
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Buttons trigger interactions that change the system’s status. Usually
	 *      Buttons are contained in an Input Collection. The Toolbar is the main
	 *      exception to this rule, since buttons in the Toolbar might also perform
	 *      view changes.
	 *   composition: >
	 *      Button is a clickable, graphically obtrusive control element. It can
	 *      bear text.
	 *   effect: >
	 *      On-click, the action indicated by the button is carried out.
	 *   rivals:
	 *      glyph: >
	 *          Glyphs are used if the enclosing Container Collection can not provide
	 *          enough space for textual information or if such an information would
	 *          clutter the screen.
	 *      links: >
	 *          Links are used to trigger Interactions that do not change the systems
	 *          status. They are usually contained inside a Navigational Collection.
	 *
	 * background: >
	 *      Wording rules have been inspired by the iOS Human Interface Guidelines
	 *      (UI-Elements->Controls->System Button)
	 *
	 *      Style rules have been inspired from the GNOME Human Interface Guidelines->Buttons.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *           Buttons MUST NOT be used inside a Textual Paragraph.
	 *   interaction:
	 *      1: >
	 *           A Button SHOULD trigger an action. Only in Toolbars, Buttons MAY also
	 *           change the view.
	 *      2: >
	 *           If an action is temporarily not available, Buttons MUST be disabled by
	 *           setting as type 'disabled'.
	 *   style:
	 *      1: >
	 *           If Text is used inside a Button, the Button MUST be at least six characters
	 *           wide.
	 *   wording:
	 *      1: >
	 *           The caption of a Button SHOULD contain no more than two words.
	 *      2: >
	 *           The wording of the button SHOULD describe the action the button performs
	 *           by using a verb or a verb phrase.
	 *      3: >
	 *           Every word except articles, coordinating conjunctions and prepositions
	 *           of four or fewer letters MUST be capitalized.
	 *      4: >
	 *           For standard events such as saving or canceling the existing standard
	 *           terms MUST be used if possible: Save, Cancel, Delete, Cut, Copy.
	 *      5: >
	 *           There are cases where a non-standard label such as “Send Mail” for saving
	 *           and sending the input of a specific form might deviate from the standard.
	 *           These cases MUST however specifically justified.
	 *   accessibility:
	 *      1: >
	 *           DOM elements of type "button" MUST be used to properly identify an
	 *           element as a Button if there is no good reason to do otherwise.
	 *      2: >
	 *           Button DOM elements MUST either be of type "button", of type "a"
	 *           accompanied with the aria-role “Button” or input along with the type
	 *           attribute “button” or "submit".
	 * ---
	 * @return  \ILIAS\UI\Component\Button\Factory
	 */
	public function button();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      A card is a flexible content container for small chunks of structured data.
	 *      Cards are often used in so-called Decks which are a gallery of Cards.
	 *   composition: >
	 *      Cards contain a header, which often includes an Image or Icon and a Title as well as possible actions as
	 *      Default Buttons and 0 to n sections that may contain further textual descriptions, links and buttons.
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
	public function card($title, \ILIAS\UI\Component\Image\Image $image = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Decks are used to display multiple Cards in a grid.
	 *      They should be used if a  page contains many content items that have similar style and importance.
	 *      A Deck gives each item equal horizontal space indicating that they are of equal importance.
	 *   composition: >
	 *      Decks are composed only of Cards arranged in a grid. The cards displayed by decks are all of equal size. This
	 *      Size ranges very small (XS) to very large (XL).
	 *   effect: >
	 *      The Deck is a mere scaffolding element, is has no effect.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_3992_1357.html
	 *
	 * rules:
	 *   usage:
	 *      1: Decks MUST only be used to display multiple Cards.
	 *   style:
	 *      1: The number of cards displayed per row MUST adapt to the screen size.
	 * ---
	 * @param \ILIAS\UI\Component\Card\Card[] $cards
	 * @return \ILIAS\UI\Component\Deck\Deck
	 */
	public function deck(array $cards);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Listings are used to structure itemised textual information.
	 *   composition: >
	 *     Listings may contain ordered, unordered, or
	 *     labeled items.
	 *   effect: >
	 *     Listings hold only textual information. They may contain Links but no Buttons.
	 * rules:
	 *   composition:
	 *     1: Listings MUST NOT contain Buttons.
	 * ---
	 * @return \ILIAS\UI\Component\Listing\Factory
	 */
	public function listing();

	/**
	 * ---
	 * description:
	 *   purpose: The Image component is used to display images of various sources.
	 *   composition: An Image is composed of the image and an alternative text for screen readers.
	 *   effect: Images may be included in interacted components but not interactive on their own.
	 *
	 * rules:
	 *   accessibility:
	 *     1: >
	 *        Images MUST contain the alt attribute. This attribute MAY be left empty (alt="") if the image is of
	 *        decorative nature. According to the WAI, decorative images don’t add information to the content of a page. For example, the information provided by the image
	 *        might already be given using adjacent text, or the image might be included to make the website more visually attractive
	 *        (see <a href="https://www.w3.org/WAI/tutorials/images/decorative/">https://www.w3.org/WAI/tutorials/images/decorative/</a>).
	 * ---
	 * @return \ILIAS\UI\Component\Image\Factory
	 */
	public function image();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     This component is used to wrap an existing ILIAS UI element into a UI component. This is useful if a container
	 *     of the UI components needs to contain content that is not yet implement in the centralized UI components.
	 *   composition: >
	 *     The legacy component contains html or any other content as string.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *          This component MUST only be used to ensure backwards compatibility with existing UI elements in ILIAS,
	 *          therefore it SHOULD only contain Elements which cannot be generated using other UI Components from the UI Service.
	 * ---
	 *
	 * @param   string $content
	 * @return  \ILIAS\UI\Component\Legacy\Legacy
	 */
	public function legacy($content);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Panels are used to group titled content.
	 *   composition: >
	 *      Panels consist of a header and content section. They form one Gestalt and so build a perceivable
	 *      cluster of information.
	 *   effect: The effect of interaction with panels heavily depends on their content.
	 *
	 * rules:
	 *   wording:
	 *      1: Panels MUST contain a title.
	 * ---
	 * @return \ILIAS\UI\Component\Panel\Factory
	 */
	public function panel();

	/**
	 * ---
	 * description:
	 *   purpose: The Modal forces users to focus on the task at hand.
	 *   composition: >
	 *     A Modal is a full-screen dialog on top of the greyed-out ILIAS screen. The Modal consists
	 *     of a header with a close button and a typography modal title, a content
	 *     section and might have a footer.
	 *   effect: >
	 *     All controls of the original context are inaccessible until the Modal is completed.
	 *     Upon completion the user returns to the original context.
	 *   rivals:
	 *     1: >
	 *       Modals have some relations to popovers. The main difference between the two is the disruptive
	 *       nature of the Modal and the larger amount of data that might be displayed inside a modal.
	 *       Also popovers perform mostly action to add or consult metadata of an item while
	 *       Modals manipulate or focus items or their sub-items directly.
	 *
	 * background: http://quince.infragistics.com/Patterns/Modal%20Panel.aspx
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       The main purpose of the Modals MUST NOT be navigational. But Modals MAY be dialogue of one
	 *       or two steps and thus encompass "next"-buttons  or the like.
	 *     2: Modals MUST NOT contain other modals (Modal in Modal).
	 *     3: Modals SHOULD not be used to perform complex workflows.
	 *     4: Modals MUST be closable by a little “x”-button on the right side of the header.
	 *     5: Modals MUST contain a title in the header.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Modal\Factory
	 **/
	public function modal();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Popovers can be used when space is scarce i.e. within List GUI items, table cells or
	 *     menus in the Header section. They offer either secondary information on object like a
	 *     preview or rating to be displayed or entered. They display information about ongoing
	 *     processes
	 *   composition: >
	 *     Popovers consist of a layer displayed above all other content.
	 *     The content of the Popover depends on the functionality it performs.
	 *     A Popover MAY display a title above its content.
	 *     All Popovers contain a pointer pointing from the Popover to the Triggerer of the Popover.
	 *   effect: >
	 *     Popovers are shown by clicking a Triggerer component such as a Button or Glyph.
	 *     The position of the Popover is calculated automatically be default. However, it is possible to
	 *     specify if the popover appears horizontal (left, right) or vertical (top, bottom) relative to
	 *     its Triggerer component. Popovers disappear by clicking anywhere outside the Popover or by pressing
	 *     the ESC key.
	 * rivals: >
	 *   Modals: >
	 *     Modals hide all other content while Popovers do not prevent interaction with other parts
	 *     of the current context.
	 * rules:
	 *   usage:
	 *     1: >
	 *        Popovers MUST NOT contain horizontal scrollbars.
	 *     2: >
	 *        Popovers MAY contain vertical scrollbars. The content component is responsible to
	 *        define its own height and show vertical scrollbars.
	 *     3: >
	 *        If Popovers are used to present secondary information of an object, they SHOULD
	 *        display a title
	 *        representing the object.
	 *   interaction:
	 *     1: >
	 *        A Popover MUST only be displayed if the Trigger component is clicked.
	 *        This behaviour is different from Tooltips that appear on hovering.
	 *        Popovers disappear by clicking anywhere outside the Popover or by pressing
	 *        the ESC key.
	 *   style:
	 *     1: Popovers MUST always relate to the Trigger component by a little pointer.
	 *   accessibility:
	 *     1: >
	 *        There MUST be a way to open the Popover by only using the keyboard.
	 *     2: >
	 *        The focus MUST be inside the Popover, once it is open if it contains at least one
	 *        interactive item.
	 *        Otherwise the focus MUST remain on the Triggerer component.
	 *     3: >
	 *        The focus MUST NOT leave the Popover for as long as it is open.
	 *     4: >
	 *        There MUST be a way to reach every control in the Popover by only using the keyboard.
	 *     5: >
	 *        The Popover MUST be closable by pressing the ESC key.
	 *     6: >
	 *        Once the Popover is closed, the focus MUST return to the element triggering the
	 *        opening of the Popover or the element being clicked if the Popover was
	 *        closed on click.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Popover\Factory
	 */
	public function popover();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A divider marks a thematic change in a sequence of other components. A Horizontal Divider
	 *     is used to mark a thematic change in sequence of elements that are stacked from top to bottom,
	 *     e.g. in a Dropdown. A Vertical Divider is used to mark a thematic change in a sequence of elements
	 *     that are lined up from left to right, e.g. a Toolbar.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       Dividers MUST only be used in container components that explicitly state
	 *       and define the usage of Dividers within the container.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Divider\Factory
	 **/
	public function divider();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Links are used navigate to other resources or views of the system. Clicking
	 *      on a link does not change the systems status.
	 *   composition: >
	 *      Link is a clickable, graphically minimal obtrusive control element. It can
	 *      bear text or other content. Links always contain a valid href tag which
	 *      should not not just contain a hash sign.
	 *   effect: >
	 *      On-click, the resource or view indicated by the link is requested and
	 *      presented. Links are not used to trigger Javascript events.
	 *   rivals:
	 *      buttons: >
	 *          Buttons are used to trigger Interactions that usually change the systems
	 *          status. Buttons are much more obtrusive than links and may fire JS events.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *           Links MAY be used inline in a Textual Paragraphs.
	 *   interaction:
	 *      1: >
	 *           Hovering an active link should indicate a possible interaction.
	 *      2: >
	 *           Links MUST not be used to fire Javascript events.
	 *   style:
	 *      1: >
	 *           Links SHOULD not be presented with a separate background color.
	 *   wording:
	 *      1: >
	 *           The wording of the link SHOULD name the target view or resource.
	 *   accessibility:
	 *      1: >
	 *           DOM elements of type "a" MUST be used to properly identify an
	 *           element.
	 * ---
	 * @return  \ILIAS\UI\Component\Link\Factory
	 */
	public function link();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Dropdowns reveal a list of interactions that change the system’s status or navigate to
	 *      a different view.
	 *   composition: >
	 *      Dropdown is a clickable, graphically obtrusive control element. It can
	 *      bear text. On-click a list of Shy Buttons and optional Dividers is shown.
	 *   effect: >
	 *      On-click, a list of actions is revealed. Clicking an item will trigger the action indicated.
	 *      Clicking outside of an opened Dropdown will close the list of items.
	 *   rivals:
	 *      button: >
	 *          Buttons are used, if single actions should be presented directly in the user interface.
	 *      links: >
	 *          Links are used to trigger actions that do not change the systems
	 *          status. They are usually contained inside a Navigational Collection.
	 *      popovers: >
	 *          Dropdowns only provide a list of possible actions. Popovers can include more diverse
	 *          and flexible content.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *           Dropdowns MUST NOT be used standalone. They are only parts of more complex UI elements.
	 *           These elements MUST define their use of Dropdown. E.g. a List or a Table MAY define that a certain
	 *           kind of Dropdown is used as part of the UI element.
	 *   interaction:
	 *      1: >
	 *           Only Dropdown Items MUST trigger an action or change a view. The Dropdown trigger element
	 *           is only used to show and hide the list of Dropdown Items.
	 *   style:
	 *      1: >
	 *           If Text is used inside a Dropdown label, the Dropdown MUST be at least six characters
	 *           wide.
	 *   wording:
	 *      1: >
	 *           The label of a Dropdown SHOULD contain no more than two words.
	 *      2: >
	 *           Every word except articles, coordinating conjunctions and prepositions
	 *           of four or fewer letters MUST be capitalized.
	 *      3: >
	 *           For standard events such as saving or canceling the existing standard
	 *           terms MUST be used if possible: Delete, Cut, Copy.
	 *      4: >
	 *           There are cases where a non-standard label such as “Send Mail” for saving
	 *           and sending the input of a specific form might deviate from the standard.
	 *           These cases MUST however specifically justified.
	 *   accessibility:
	 *      1: >
	 *           DOM elements of type "button" MUST be used to properly identify an
	 *           element as a Dropdown.
	 *      2: >
	 *           Dropdown items MUST be implemented as "ul" list with a set of "li" elements and
	 *           nested Shy Button elements for the actions.
	 *      3: >
	 *           Triggers of Dropdowns MUST indicate their effect by the aria-haspopup attribute
	 *           set to true.
	 *      4: >
	 *           Triggers of Dropdowns MUST indicate the current state of the Dropdown by the
	 *           aria-expanded label.
	 *      5: >
	 *           Dropdowns MUST be accessible by keyboard by focusing the trigger element and
	 *           clicking the return key.
	 *      6: >
	 *           Entries in a Dropdown MUST be accessible by the tab-key if opened.
	 *      7: >
	 *           The focus MAY leave the Dropdown if tab is pressed while focusing the last
	 *           element. This differs from the behaviour in Popovers and Modals.
	 * ---
	 * @return  \ILIAS\UI\Component\Dropdown\Factory
	 */
	public function dropdown();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      An item displays a unique entity within the system. It shows information
	 *      about that entity in a structured way.
	 *   composition: >
	 *      Items contain the name of the entity as a title. The item contains three
	 *      sections, where one section contains important information about the item,
	 *      the second section shows the content of the item and another section shows
	 *      metadata about the entity.
	 *   effect: >
	 *      Items may contain Interaction Triggers such as Glyphs, Buttons or Tags.
	 *   rivals:
	 *      Card: >
	 *         Cards define the look of items in a deck. Todo: We need to refactor cards.
	 *
	 * rules:
	 *   composition:
	 *      1: Items MUST contain the name of the displayed entity as a title.
	 *      2: Items SHOULD contain a section with it's content.
	 *      3: Items MAY contain Interaction Triggers.
	 *      4: Items MAY contain a section with metadata.
	 * ---
	 * @return \ILIAS\UI\Component\Item\Factory
	 */
	public function item();

 	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Icons are quickly comprehensible and recognizable graphics.
	 *     They indicate the functionality or nature of a text-element or context:
	 *     Icons will mainly be used in front of object-titles, e.g. in the
	 *     header, the tree and in repository listing.
	 *   composition: >
	 *     Icons come in three fixed sizes: small, medium and large.
	 *     They can be configured with an additional "abbreviation",
	 *     a text of a few characters that will be rendered on top of the image.
	 *   effect: >
	 *     Icons themselves are not interactive; however they are allowed
	 *     within interactive containers.
	 *   rivals:
	 *     1: >
	 *       Glyphs are typographical characters that act as a trigger for
	 *       some action.
	 *     2: >
	 *       Images belong to the content and can be purely decorative.
	 * rules:
	 *   usage:
	 *     1: Icons MUST be used to represent objects or context.
	 *     2: Icons MUST be used in combination with a title or label.
	 *     3: An unique Icon MUST always refer to the same thing.
	 *   style:
	 *     1: Icons MUST have a class indicating their usage.
	 *     2: Icons MUST be tagged with a CSS-class indicating their size.
	 *   accessibility:
	 *     1: Icons MUST use aria-label.
	 *   wording:
	 *     1: The aria-label MUST state the represented object-type.
	 *     2: The abbreviation SHOULD consist of one or two letters.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Icon\Factory
	 **/
	public function icon();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     View Controls switch between different visualisation of data.
	 *   composition: >
	 *      View Controls are composed mainly of buttons, they are often found in toolbars.
	 *   effect: Interacting with a view control changes to display in some content area.
	 * ---
	 * @return \ILIAS\UI\Component\ViewControl\Factory
	 */
	public function viewControl();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Breadcrumbs is a supplemental navigation scheme. It eases the
	 *     user's navigation to higher items in hierarchical structures.
	 *     Breadcrumbs also serve as an effective visual aid indicating the
	 *     user's location on a website.
	 *   composition: >
	 *     Breadcrumbs-entries are rendered as horizontally arranged UI Links
	 *     with a seperator in-between.
	 *   effect: >
	 *     Clicking on an entry will get the user to the respective location.
	 *
	 * context: >
	 *   1. Suplemental navigation under the main menu
	 *   2. Location hint in search results
	 *   3. Path to current location on info page
	 *
	 * rules:
	 *   usage:
	 *     1: Crumbs MUST trigger navigation to other resources of the system.
	 * ---
	 * @param 	\ILIAS\UI\Component\Link\Standard[] 	$crumbs 	a list of Links
	 * @return 	\ILIAS\UI\Component\Breadcrumbs\Breadcrumbs
	 **/
	public function breadcrumbs(array $crumbs);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Charts are used to graphically represent data in various forms such as maps, graphs or diagrams.
	 *   composition: >
	 *      Charts are composed of various graphical and textual elements representing the raw data.
	 *
	 * rules:
	 *   style:
	 *      1: Charts SHOULD not rely on colors to convey information.
	 * ---
	 * @return \ILIAS\UI\Component\Chart\Factory
	 */
	public function chart();

}
