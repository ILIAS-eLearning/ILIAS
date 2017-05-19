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
     *     The cockpit is a unique page element that holds globally available
     *     navigational entries and tools.
     *     It condenses accessibility options for content independent items,
     *     services and navigational strategies such as personal desktop and profile,
     *     search, help or the repository tree, and provides context-independent
     *     editing or search options.
     *
     *   composition: >
     *     The cockpit is built of a bar holding one to five entries, which will
     *     usually open the "slate", a larger area next to the bar providing room
     *     for a large variety of components.
     *
     *
     *   effect: >
     *     The cockpit(-bar) is always visible and available (except in exam/kiosk mode).
     *     It is _the_ main navigational item apart from links/buttons in the content.
     *
     *   rivals: >
     *
     *
     * rules:
     *   usage:
     *     1: There MUST be but one cockpit on the page.
     *
     *   interaction:

     *   composition:
     *     1: The cockpit MUST contain a bar.
     *     2: The cockpit SHOULD contain a slate.
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Cockpit\Factory
     */
    public function cockpit();


}
