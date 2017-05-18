<?php
namespace ILIAS\UI\Component\Layout\Page;
/**
 * This is the factory for page-layout components
 */
interface Factory {

    /**
     * ---
     * description:
     *   purpose: >
     *     The menubar is a unique page element that holds globally available
     *     navigational entries and functions.
     *     It shall condense accessibility options for content independent items,
     *     services and navigational strategies such as personal desktop and profile,
     *     search, help or the repository tree, and provide context-independent
     *     editing or search options.
     *
     *   composition: >
     *     The menubar consists of two parts: the main entries in form of a
     *     button-collection and a section ("slate") that opens upon the selection
     *     of one of them, very similar to a fly-out or mega menu.
     *
     *   effect: >
     *     The menubar is always visible and available (except in exam/kiosk mode).
     *     In a desktop environment, a vertical bar is rendered on the left side
     *     of the screen covering the full height. Entries are aligned vertically.
     *
     *     For mobile devices, the menubar is rendered horizontally on the bottom
     *     of the screen. Entries are aligned horizontally.
     *
     *   rivals: >
     *
     *
     * rules:
     *   usage:
     *     1: There MUST be but one menubar on the page.
     *
     *   interaction:

     *   composition:
     *     1: The menubar MUST contain an element "Entries"
     *     2: The menubar SHOULD contain an element "Slate"
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Menubar\Factory
     */
    public function menubar();


}
