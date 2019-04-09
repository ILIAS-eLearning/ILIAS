<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls;

use \ILIAS\UI\Component\Image\Image;

/**
 * This is what a factory for main controls looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Meta Bar is a unique page section to accomodate elements that
	 *     should permamently be in sight of the user.
	 *     The Meta Bar shall, first of all, host Prompts, i.e. notifications
	 *     from the System to the user, but may also accomodate components and
	 *     links deemed important, like help or search. The content of the bar
	 *     does not change when navigating the system, but may depend on a
	 *     configuration.
	 *
	 *   composition: >
	 *     The Meta Bar is rendered horizontally at the very top of the page. It
	 *     is always visible and available (except in some specialized view modes
	 *     like an kiosk mode) as a static screen element and is unaffected by
	 *     scrolling.
	 *
	 *     Elements in the Meta Bar are always placed on the right hand side.
	 *     Currently, these are "Search", "Help", "Notifications", "Awareness"
	 *     and "User".
	 *
	 *     Especially in mobile context, the total width of all entries may exceed
	 *     the availble width of the screen. In this case, all entries are
	 *     summarized under a "..."-Button.
	 *
	 *     Elements are rendered as Bulky Buttons. Prompts in the Meta Bar may be
	 *     marked with counters for new/existing notifications.
	 *
	 *   effect: >
	 *     Entries in the Meta Bar may open a Slate when clicked. They will be set
	 *     to "engaged" accordingly, and bear the aria-pressed attribute. There
	 *     will be only one engaged Button/Slate at a time. Also, Buttons in the
	 *     Meta Bar may trigger navigation or activate tools in the Main Bar, like
	 *     the Help. In this case, the buttons are not stateful.
	 *
	 *   rivals:
	 *     Main Bar: >
	 *       The Main Bar offers navigational strategies, while the Meta Bar foremost
	 *       provides notifications to the user and offers controls that are deemed
	 *       important.
	 *       The (general) direction of communication for the Meta Bar is "system to
	 *       user", while the direction is "user to system" for elements of the Main Bar.
	 *
	 * context:
	 *   - The Meta Bar is used in the Standard Page.
	 *
	 * rules:
	 *   usage:
	 *     1: The Meta Bar is unique for the page - there MUST be at most one.
	 *     2: Elements in the Meta Bar MUST NOT vary according to context.
	 *     3: New elements in the Meta Bar MUST be approved by JF.
	 *     4: >
	 *       Since mainly items that pitch the user are placed in the Meta Bar,
	 *       you SHOULD only propose items for this section that have the nature
	 *       of informing the user.
	 *
	 *   style:
	 *     1: The bar MUST have a fixed height.
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\MainControls\MetaBar
	 */
	public function metaBar(): MetaBar;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Main Bar is a unique page section that bundles access to content-
	 *     based navigational strategies (like the repository tree) as well as
	 *     navigation to services unrelated to the actual content, like the
	 *     administrative settings.
	 *
	 *     Since the controls necessary for theses purposes might be quite complex,
	 *     they are summed up in an easy to grasp Icon or Glyph in conjunction with
	 *     a short text. Theses reductions form the entries for the Main Bar, which
	 *     thus is the primary list of navigational options for the user and the
	 *     usual starting point for the user to explore the system.
	 *
	 *     There are entries in the bar that are never modified by changing context,
	 *     but may vary according to e.g. the current user's permissions or settings
	 *     of the installation. There also is the tools-section of entries in the bar
	 *     that is used to show tools that are opened on request of the user, e.g. the
	 *     help, or depending on requirements of the content, e.g. a local navigation.
	 *
	 *     However, content actions, like  "new item"-actions, the actions-menu (with
	 *     comments, notes and tags), moving, linking or deleting objects and the like
	 *     are NOT part of the Main Bar.
	 *
	 *     Also, there should be a differentiation between elements of the Main Bar
	 *     and elements of e.g., the Personal Dekstop: The Personal Desktop provides
	 *     access to services and tools and displays further information at first
	 *     glance (e.g. the calendar). The Main Bar may reference those tools as well,
	 *     but rather in form of a link than a widget.
	 *
	 *   composition: >
	 *     The Main Bar holds Slates and Bulky Buttons.
	 *
	 *     In a desktop environment, a vertical bar is rendered on the left side
	 *     of the screen covering the full height (minus header- and footer area).
	 *     Entries are aligned vertically.
	 *
	 *     In a mobile context, the bar will be rendered horizontally on the bottom.
	 *
	 *     When the entries of a Main Bar exceed the available height (mobile: width),
	 *     remaining buttons will be collected in a "..."-Button.
	 *
	 *     The Main Bar is always visible and available (except in specialized views
	 *     like the exam mode) as a static screen element unaffected by scrolling.
	 *
	 *   effect: >
	 *     Clicking an entry will carry out its configured action. For slates, this
	 *     is expanding the slate, while for Bulky Buttons this might be, e.g., just
	 *     changing the page.
	 *
	 *     Buttons in the Main Bar are stateful, i.e. they have a pressed-status
	 *     that can either be toggled by clicking the same button again or by
	 *     clicking a different button. This does not apply to Buttons directly
	 *     changing the context.
	 *
	 *     Opening a slate by clicking an entry will close all other slates in the
	 *     Main Bar. On desktop, slates open on the right hand of the Main Bar, between
	 *     bar and content, thus "pushing" the content to the right, if there is not
	 *     enough room.
	 *
	 *     If the content's width would fall below its defined minimum, the expanded
	 *     slate is opened above (like in overlay, not "on top of") the content.
	 *
	 *     The slates height equals that of the Main Bar. Also, their position will
	 *     remain fixed when the page is scrolled. A button to close a slate is
	 *     rendered underneath the slate. It will close all visible Slates and reset
	 *     the states of all Main Bar-entries.
	 *
	 *     When a tool (such as the help), whose contents are displayed in a slate,
	 *     is being triggered, a special entry is rendered as first element of the
	 *     Main Bar, making the available/invoked tool(s) accessible. Tools can be
	 *     closed, i.e. removed from the Main Bar, via a Close Button. When the last
	 *     Tool is closed, the tools-section is removed as well.
	 *
	 *   rivals:
	 *     Tab Bar: >
	 *       The Main Bar (and its components) shall not be used to substitute
	 *       functionality available at objects, such as settings, members or
	 *       learning progress. Those remain in the Tab Bar.
	 *
	 *     Meta Bar: >
	 *       Notifications from the system to the user, e.g. new Mail, are placed
	 *       in Elements of the Meta Bar. The general direction of communication for
	 *       the Main Bar is "user to system", while the direction is "system to user"
	 *       with elements of the Meta Bar. However, navigation from both components
	 *       can lead to the same page.
	 *
	 * context:
	 *   - The Main Bar is used in the Standard Page.
	 *
	 * rules:
	 *   usage:
	 *     1: There SHOULD be a Main Bar on the page.
	 *     2: If there is a Main Bar, it MUST be unique for the page.
	 *     3: >
	 *       Entries and Tools in the Main Bar, or for that matter, their respective
	 *       slate-contents, MUST NOT be used to reflect the outcome of a user's
	 *       action, e.g., display a success-message.
	 *     4: >
	 *       Contents of the slates, both in Entries and Tools, MUST NOT be used
	 *       to provide information of a content object if that information
	 *       cannot be found in the content itself. They MUST NOT be used as
	 *       a "second screen" to the content-part of the Page.
	 *
	 *   composition:
	 *     1: The bar MUST NOT contain items other than Bulky Buttons or Slates.
	 *     2: The bar MUST contain at least one Entry.
	 *     3: The bar SHOULD NOT contain more than five Entries.
	 *     4: The bar SHOULD NOT contain more than five Tool-Entries.
	 *     5: >
	 *       Entries and Tools in the Main Bar MUST NOT be enhanced with counters
	 *       or other notifications drawing the user's attention.
	 *
	 *   style:
	 *     1: The bar MUST have a fixed width (desktop).
	 *
	 *   interaction:
	 *     1: >
	 *        Operating elements in the bar MUST either lead to further navigational
	 *        options within the bar (open a slate) OR actually invoke navigation, i.e.
	 *        change the location/content of the current page.
	 *     2: Elements in the bar MUST NOT open a modal or new Viewport.
	 *
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\MainControls\MainBar
	 */
	public function mainBar(): MainBar;


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A Slate is a collection of Components that serve a specific and singular
	 *     purpose in their entirety. The purpose can be subsummed in one Icon/Glyph
	 *     and a very short label, for Slates will act as elaboration on one specific
	 *     concept in ILIAS.
	 *
	 *     Slates are not part of the content and will reside next to or over it. They
	 *     will open and close without changing the current context.
	 *     Accordingly, Slates depend on a component that toggles their visibility.
	 *
	 *     In contrast to purely receptive components, Slates usually provide a form
	 *     of interaction, whereas this interaction may trigger a navigation or alter
	 *     the contents of the slate itself. However, slates are not meant to modify
	 *     states of entities in the system in any way.
	 *
	 *     E.g.: A Help-Screen, where the user can read a certain text and also search
	 *     available topics via a text-input, or a drill-down navigation, where all
	 *     siblings of the current level are shown next to a "back"-button.
	 *
	 *     A special case of Slate is the Prompt: while in a common Slate the general
	 *     direction of communiction is user to system, a Prompt is used for communication
	 *     from the system to the user. These can be, e.g, alerts concerning new mails
	 *     or a change in the online status of another learner.
	 *
	 *   composition: >
	 *     Slates may hold a variety of components. These can be navigational entries,
	 *     text and images or even other slates. When content-length exceeds the Slate's
	 *     height, the contents will start scrolling vertically with a scrollbar on the
	 *     right.
	 *
	 *   rivals:
	 *     Panel: >
	 *       Panels are used for content.
	 *
	 *     Modal: >
	 *       The Modal forces users to focus on a task, the slate offers possibilities.
	 *
	 *     Popover: >
	 *       Popovers provide additional information or actions in direct context
	 *       to specific elements. Popovers do not have a fixed position on the page.
	 *
	 *
	 * rules:
	 *   wording:
	 *     1: It MUST be possible to subsume a slates purpose in one Icon/Glyph and one word.
	 *
	 *   usage:
	 *     1: Slates MUST NOT be used standalone, i.e. without a controlling Component.
	 *     2: There MUST be only one Slate visible at the same time per triggering Component.
	 *     3: Elements in the Slate MUST NOT modify entities in the system.
	 *     4: Slates MUST be closeable/expandable without changing context.
	 *     5: >
	 *        Slates MUST NOT be used to provide additional information of content-objects
	 *        that cannot be found anywhere else.
	 *
	 *   style:
	 *     1: Slates MUST have a fixed width.
	 *     2: Slates MUST NOT use horizontal scrollbars.
	 *     3: Slates SHOULD NOT use vertical scrollbars.
	 *     4: Slates MUST visually relate to their triggering Component.
	 *     5: Slates SHOULD NOT be affected by scrolling the page.
	 *
	 *   accessibility:
	 *     1: The Slate MUST be closeable by only using the keyboard
	 *     2: >
	 *        Actions or navigational elements offered inside a Slate MUST be accessible
	 *        by only using the keyboard
	 *     3: A Slate MUST set the aria-hidden attribute.
	 *
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\MainControls\Slate\Factory
	 */
	public function slate(): Slate\Factory;

}
