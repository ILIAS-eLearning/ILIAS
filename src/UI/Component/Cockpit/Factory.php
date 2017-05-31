<?php
namespace ILIAS\UI\Component\Cockpit;
/**
 * This is the factory for cockpit-components
 */
interface Factory {
    /**
     * ---
     * description:
     *   purpose: >
     *     The bar is the part of the cockpit that remains visible and
     *     therefore accessible at any time.
     *
     *   composition: >
     *     The bar is the cockpit's container for entries.
     *     It holds one to five entries. The contents (entries) of the bar
     *     are never modified by changing context, but may vary according to
     *     e.g. the current user's roles.
     *
     *   effect: >
     *     The cockpit(-bar) is always visible and available (except in exam/kiosk mode).
     *
     *     In a desktop environment, a vertical bar is rendered on the left side
     *     of the screen covering the full height minus the header-area.
     *     Entries are aligned vertically.
     *
     *     Like the header, the bar is a static screen element unaffected by scrolling.
     *     Thus, entries will become inaccessible when the window is of smaller height
     *     than the height of all entries together.
     *
     *     The contents of the bar itself will not scroll.
     *
     *     Width of content- and footer-area is limited to a maximum of the
     *     overall available width minus that of the bar.
     *
     *     For mobile devices, the bar is rendered horizontally on the bottom
     *     of the screen with the entries aligned horizontally.
     *     Again, entries will become inacessible, if the window/screen is smaller
     *     than the width of all entries summed up.
     *
     *     -->TODO: what about the footer in mobile context?
     *
     *
     * rules:
     *   composition:
     *     1: The bar MUST NOT contain items other than entries.
     *     2: The bar MUST contain at least one entry.
     *     3: The bar MUST NOT contain more than five entries.
     *
     *   style:
     *     1: The bar MUST have a fixed witdth (desktop).
     *     2: The bar MUST have a fixed height (mobile).
     *
     * ----
     * @param  entry[] $entries
     * @return  \ILIAS\UI\Component\Cockpit\Bar
     */
    public function bar($entry_1, $entry_2=null, $entry_3=null, $entry_4=null, $entry_5=null);


    /**
     * ---
     * description:
     *   purpose: >
     *     An entry in this context describes an item in the cockpit-bar.
     *     Entries offer access to the fundamental functionality of ILIAS,
     *     such as navigation to some parts of the repository or
     *     links to the aspects of the personal desktop.
     *
     *   composition: >
     *     Entries consist of an icon, usually in combination with a caption.
     *     Alternatively to the icon, a short! string can be used to render
     *     a text-based element.
     *
     *   effect: >
     *     Upon click, the entry triggers a slate or links directly to some page.
     *     In the first case the entry will become "active", keeping that status
     *     until the slate is closed again or another entry is clicked.
     *     Only one entry can be active at the same time.
     *
     * rules:
     *   usage:
     *     1: Entries MUST only be inside a cockpit-bar.
     *     2: Entries MAY be connected with a slate
     *
     *   interaction:
     *     1: >
     *       Entries MUST have an active-state.
     *       Entries not triggering a slate MUST NOT become active at any time.
     *     2: Entries MAY trigger a slate.
     *     3: Entries MAY trigger navigation to a content element.
     *     4: >
     *        Entries MAY be unclickable in general
     *        and MUST be unclickable if in active-state.
     *
     *   composition:
     *     1: >
     *       Entries MUST have an icon
     *       OR MUST have an abbreviation-string as substitute.
     *     2: Entries MAY have a caption.
     *
     *   style:
     *     1: Graphical elements of entries MUST be of the same size.
     *
     *   accessibility:
     *     1: Every entry MUST be accessible by only using the keyboard
     *
     * ----
     * @param  \Slate|string|null  $action
     * @param  \Icon|string        $icon
     * @param  string              $caption
     * @param  boolean             $active
     * @return  \ILIAS\UI\Component\Cockpit\Entry
     */
    public function entry($action, $icon, $caption='', $active=false);


    /**
     * ---
     * description:
     *   purpose: >
     *     The slate is a page-area within the cockpit; it acts like an enhanced
     *     fly-out menu for cockpit-entries.
     *     The contents of a slate can vary heavily: A search form, the repository tree,
     *     contextual help, further navigation via buttons, etc.
     *     However, tools within the slate should not modify the systems data
     *     in any way - it is for navigation only.
     *
     *   composition: >
     *     The slate can hold a large variety of components. These can be (further)
     *     navigational entries, text and images or combinations of those.
     *     For example, a submenu could be expressed by (accordion-)panels with
     *     underlying buttons.
     *
     *   effect: >
     *     When triggered, the slate opens on the right hand of the cockpit-bar,
     *     between bar and content, thus "pushing" the content to the right.
     *     The slate's height equals that of the Cockpit Bar; also, its position
     *     will remain static when the page is scrolled.
     *
     *     The slate will allways have a "close"-button at its bottom.
     *
     *
     *     When content-length exceeds the slate's height, the area above the
     *     close button will start scrolling vertically with a scrollbar on the right.
     *
     * rules:
     *   usage:
     *     1: There MUST be only one slate visible on the page.
     *     2: The slate MUST be triggered by an entry.
     *     3: Elements in the slate MUST NOT modify content.
     *
     *   accessibility:
     *     1: The slate MUST be closeable by only using the keyboard
     *     2: >
     *        Actions or navigational elements offered inside a slate
     *        MUST be accessible by only using the keyboard
     *
     * ----
     * @param \Component $content
     * @return \ILIAS\UI\Component\Cockpit\Slate
     */
    public function slate($content);
}
