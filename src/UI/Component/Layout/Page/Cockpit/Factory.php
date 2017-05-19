<?php
namespace ILIAS\UI\Component\Layout\Page\Cockpit;
/**
 * This is the factory for cockpit-components
 */
interface Factory {
    /**
     * ---
     * description:
     *   purpose: >
     *     The bar is the primary visual manifestation of the cockpit.
     *
     *   composition: >
     *     The bar is the cockpit's contanier for entries.
     *
     *   effect: >
     *     In a desktop environment, a vertical bar is rendered on the left side
     *     of the screen covering the full height minus the header area.
     *     Entries are aligned vertically.
     *
     *     For mobile devices, the bar is rendered horizontally on the bottom
     *     of the screen with the entries aligned horizontally.
     *
     *     The cockpit(-bar) is always visible and available (except in exam/kiosk mode).
     *
     * rules:
     *   usage:
     *     1: There MUST be but one cockpit bar on the page.
     *
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
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Cockpit\Bar
     */
    public function bar();


    /**
     * ---
     * description:
     *   purpose: >
     *     An entry in this context describes an item in the cockpit-bar.
     *     Usually, it is a labeled, clickable icon which triggers a slate.
     *     However, a clickable linking to a content-element is feasible as well
     *     as a glyph with a counter.
     *
     *   composition: >
     *     Entries consist of a caption, an image or glyph and, optionally, a slate.
     *
     * rules:
     *   usage:
     *     1: Entries MUST only be inside a cockpit-bar.
     *     1: Entries CAN be connected with a slate
     *
     *   interaction:
     *     1: Entries MUST have states.
     *     2: Entries CAN trigger a slate.
     *     3: Entries CAN trigger navigation to a content element.
     *     4: Entries CAN be unclickable.
     *
     *   composition:
     *     1: Entries MUST have a caption.
     *     2: Entries MUST have a graphical representation like an image or glyph.
     *
     *   style:
     *     1: Graphical elements of entries SHOULD be of the same size.
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Cockpit\Entry
     */
    public function entry();


    /**
     * ---
     * description:
     *   purpose: >
     *     The slate is a page-area within the cockpit; it acts like an enhanced
     *     fly-out menu for cockpit-entries.
     *
     *   composition: >
     *     The slate can hold a large variety of components. These can be (further)
     *     navigational entries, forms, text and images or combinations of those.
     *     For example, a submenu could be expressed by (accordion-)panels with
     *     underlying buttons.
     *
     *   effect: >
     *     When triggered, the slate opens on the right hand of the cockpit-bar,
     *     between bar and content, thus "pushing" the content to the right.
     *     The slate will allways have a "close"-button at its bottom.
     *
     *     The contents of a slate can vary heavily:
     *     A search form, the repository tree, contextual help,
     *     further navigation via buttons, etc.
     *
     *     When content-length exceeds the slate's height, the area above the
     *     close button will start scrolling.
     *
     *   rivals: >
     *
     * rules:
     *   usage:
     *     1: There MUST be only one slate visible on the page.
     *     2: >
     *        The contents of a slate CAN navigate immanently within the slate
     *        without changing the context.
     *
     *   accessibility:
     *     1: The slate MUST be triggered by an entry.
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Cockpit\Slate
     */
    public function slate();
}
