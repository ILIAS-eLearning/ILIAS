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
     *     The cockpit is a unique page section that holds globally available
     *     tools and navigational elements.
     *     It bundles access to navigational strategies (search or the repository tree),
     *     tools parallel to the content like the help, and services unrelated to
     *     the actual content like the user's profile or desktop.
     *
     *   composition: >
     *     The cockpit is built of a bar holding one to five entries, which will
     *     usually open the "slate", a larger area next to the bar providing room
     *     for a large variety of components.
     *
     *   effect: >
     *     The cockpit(-bar) is always visible and available (except in exam/kiosk mode).
     *     It is _the_ main navigational item apart from links/buttons in the content.
     *
     *   rivals:
     *     tab bar: >
     *       The cockpit shall not be used to substitute object-actions;
     *       those remain in the tab-bar.
     *     Personal Desktop: >
     *       The Personal Desktop provides access to services and tools and
     *       displays further information at first glance (i.e. the calendar).
     *       The cockpit may reference those tools as well, but rather in form
     *       of a link than a widget.
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
