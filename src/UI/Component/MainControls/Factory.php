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

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Link\Standard;

/**
 * This is what a factory for main controls looks like.
 */
interface Factory
{
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
     *     Elements in the Meta Bar are always placed on the right-hand side.
     *     Currently, these are "Search", "Help", "Notifications", "Awareness"
     *     and "User".
     *
     *     Especially in mobile context, the total width of all entries may exceed
     *     the available width of the screen. In this case, all entries are
     *     summarized under a "..."-Button.
     *
     *     Elements are rendered as Bulky Buttons. Prompts in the Meta Bar may be
     *     marked with counters for new/existing notifications.
     *
     *   effect: >
     *     Entries in the Meta Bar may open a Slate when clicked. They will be set
     *     to "engaged" accordingly. There will be only one engaged Button/Slate
     *     at a time. Also, Buttons in the Meta Bar may trigger navigation or
     *     activate tools in the Main Bar, like the Help. In this case, the buttons
     *     are not stateful.
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
     *
     *   accessibility:
     *     1: The Meta Bar MUST bear the ARIA role "menubar".
     *     2: >
     *       Bulky Buttons in the Meta Bar MUST bear the "aria-pressed" attribute to
     *       inform the user if the entry is engaged or disengaged at the moment.
     *     3: Bulky Buttons in the Meta Bar MUST bear the "aria-haspopup" attribute.
     *     4: Bulky Buttons in the Meta Bar MUST bear the ARIA role "menuitem".
     *     5: Slates in the Meta Bar MUST bear the ARIA role "menu".
     * ----
     * @return  \ILIAS\UI\Component\MainControls\MetaBar
     */
    public function metaBar(): MetaBar;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Main Bar allows exploring the content and features of the plattform.
     *     The Main Bar provides users their usual means to access to content, services and settings.
     *     The Main Bar may offer access to content, services and settings independent
     *     from what is presented in the content area.
     *     The creation and management of repository objects are not part of the Main bar.
     *     The Main Bar offers space for Tools to be displayed besides the actual content.
     *     Tools home functionality that could not be placed elsewhere, there is no sophisticated concept.
     *     We strive to keep the number of Tools low and hone the concept further.
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
     *     2: There MUST NOT be more than one Main Bar on the page.
     *     3: If there is a Main Bar, it MUST be unique for the page.
     *     4: >
     *       Entries and Tools in the Main Bar, or for that matter, their respective
     *       slate-contents, MUST NOT be used to reflect the outcome of a user's
     *       action, e.g., display a success-message.
     *     5: >
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
     *   accessibility:
     *     1: The HTML tag < nav > MUST be used for the Main Bar to be identified as
     *        the ARIA Landmark Role "Navigation".
     *     2: >
     *        The "aria-label" attribute MUST be set for the Main Bar, which MUST be
     *        language-dependant.
     *     3: >
     *        The area, where the entries of the Main Bar are placed, MUST bear the
     *        ARIA role "menubar".
     *     4: >
     *        Bulky Buttons in the Main Bar MUST bear the "aria-pressed" attribute to
     *        inform the user if the entry is engaged or disengaged at the moment.
     *     5: Bulky Buttons in the Main Bar MUST bear the "aria-haspopup" attribute.
     *     6: Bulky Buttons in the Main Bar MUST bear the ARIA role "menuitem".
     *     7: Slates in the Main Bar MUST bear the ARIA role "menu".
     *     8: Top-Level entries of the Main Bar MUST be rendered as a listitems.
     * ----
     * @return  \ILIAS\UI\Component\MainControls\MainBar
     */
    public function mainBar(): MainBar;


    /**
     * ---
     * description:
     *   purpose: >
     *     A Slate is a collection of Components that serve a specific and singular
     *     purpose in their entirety. The purpose can be subsumed in one Icon/Glyph
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
     *     direction of communication is user to system, a Prompt is used for communication
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
     *     3: A Slate MUST set the "aria-expanded" and the "aria-hidden" attributes.
     *
     * ----
     * @return  \ILIAS\UI\Component\MainControls\Slate\Factory
     */
    public function slate(): Slate\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Footer is a unique page section to accommodate links and shy buttons
     *     triggering Round Trip Modals that are not being used on a regular basis,
     *     such as links to the pages's imprint or a privacy policy document.
     *
     *   composition: >
     *     The Footer is composed of a list of Links or Shy Buttons triggering
     *     Round Trip Modals and an optional text-part.
     *
     * context:
     *   - The Footer is used with the Standard Page.
     *
     * rules:
     *   usage:
     *     1: The Footer is unique for the page - there MUST be not more than one.
     *     2: >
     *        Elements in the Footer SHOULD NOT vary according to context, but MAY
     *        vary according to the user's role or state (logged in/not logged in/...).
     *     3: >
     *        Although the footer is constructed only with its "static" parts,
     *        it SHOULD have attached a permanent URL for the current page/object.
     * ----
     * @param  \ILIAS\UI\Component\Link\Standard[] $links
     * @param  string $text
     * @return  \ILIAS\UI\Component\MainControls\Footer
     */
    public function footer(array $links, string $text = ''): Footer;


    /**
     * ---
     * description:
     *   purpose: >
     *     The Mode Info is a section on a page that informs the user that he is
     *     in a certain mode (e.g. in the preview as a member of a course).
     *   composition: >
     *     The Mode Info MUST contain a title explaining the mode.
     *     The Mode Info MUST contain a Close Button to leave the
     *     mode.
     *
     *   effect: >
     *      By clicking the Close Button, the user leaves the current
     *      (application wide) mode.
     *
     *   rivals:
     *      System Info: >
     *         use ModeInfo to indicate a certain state in a user context. The
     *         SystemInfo on the other hand informs about system-wide information.
     *
     * context:
     *   - The Mode Info is used with the Standard Page.
     * rules:
     *   usage:
     *     1: The Mode Info is unique for the page - there MUST be not more than one.
     *   interaction:
     *     1: The Mode Info MUST allow the user to leave the mode.
     *   accessibility:
     *     1: >
     *         The Mode Info informs about an important circumstance, which must be
     *         recognizable in particular also for persons with a handicap.
     * ----
     * @return \ILIAS\UI\Component\MainControls\ModeInfo
     */
    public function modeInfo(string $title, URI $close_action): ModeInfo;

    /**
     * ---
     * description:
     *   purpose: >
     *     The System Info is a section of the standard page that informs the user
     *     about the ILIAS system. This information can be of different relevance
     *     (denotation), from neutral to breaking (see rules).
     *
     *   composition: >
     *     A System Info is a horizontally arranged sequence of a headline, an
     *     information text and, if applicable, a Close Button.
     *     It can appear in three different colors, depending on its denotation:
     *     - neutral: indicates a System Info that has only a neutral relevance
     *       for the users, e.g. that the installation is a test installation.
     *     - important: indicates a System Info that should be seen by the users,
     *       but does not require immediate action by the user. For example
     *       "in 30 days your account will expire".
     *     - breaking: indicates a system info that should be seen by the user
     *       immediately and usually requires quick action or indicates upcoming
     *       events such as "ILIAS will not be available tomorrow due to
     *       maintenance" or "Your account expires in 3 days".
     *
     *   effect: >
     *     By clicking (if there is one) the Close Button, the user accepts the
     *     facts and does not wish to be informed further. The System Info
     *     containing the clicked Button should not appear anymore.
     *     If the information text is longer than the available space on the page
     *     allows, it will be hidden and a More Glyph will be displayed. Clicking
     *     the More Glyph displays the whole message, with the System Info
     *     automatically adjusting in height to match the content.
     *
     *   rivals:
     *     Mode Info: >
     *        use System Info to output system-wide information. The Mode Info
     *        only informs about a state the user is in.
     *
     * context:
     *   - The System Info is only used within the Standard Page.
     *
     * rules:
     *   usage:
     *     1: There MAY be multiple System Infos on the page.
     *     2: The System Info MUST contain a headline summarizing the information.
     *     3: >
     *         The System Info MUST contain an information text with additional
     *         information.
     *     4: >
     *         The System Info MAY contain a Close Button to dismiss and accept
     *         the notification.
     *     5: >
     *         If there is a Close Button in a System Info, clicking the Button
     *         MUST permanently close this System Info for the user.
     *   interaction:
     *     1: An interaction with the user is not mandatory, unless the System Info
     *        provides such an interaction. In this case the user MUST be able to
     *        close the info in its context by clicking on the Close Glyph.
     *   accessibility:
     *     1: Breaking System Infos MUST have a role="alert".
     *     2: Important and neutral System Infos MUST have an aria-live="polite".
     *     3: The headline MUST be referenced by aria-labelledby
     *     4: The information MUST be referenced by aria-describedby
     * ----
     * @return \ILIAS\UI\Component\MainControls\SystemInfo
     */
    public function systemInfo(string $headline, string $information_text): SystemInfo;
}
