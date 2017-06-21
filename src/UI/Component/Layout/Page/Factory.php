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
     *     The Notification Center concentrates the visualization of _any_
     *     event-notification into one expandable glyph.
     *     This unification removes the multitude of dedicated notification-icons
     *     in favor of visual cleanliness as well as providing a designated
     *     location for further extensions.
     *
     *   composition: >
     *      The Notification Center is visualized as a glyph with a counter.
     *      Clicked, a pop-over list with the notifying services and their
     *      respective counter-glyphs is expanded.
     *
     *   effect: >
     *      All notifications, regardless of their origin, are summed up in
     *      the counter of the Notification Center's glyph.
     *      When clicked, a list is shown with all notifying services.
     *      The entries each consist of the services' respective glyph, counter
     *      and title.
     *      Entries as well can be clicked; the user is then directed to the
     *      view of the service.
     *
     *   rivals:
     *     Awareness Tool: >
     *        As the Awareness Tool does not actually target potentially
     *        permanent notifications to the user and offers further functionality
     *        when an entry is expanded, it cannot be properly enclosed in the
     *        Notification Center and should reside next to it.
     *
     * rules:
     *   usage:
     *     1: There MUST be but one notification center on the page.
     *   interaction:
     *   composition:
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\NotificationCenter
     */
    public function notificationCenter();


}
