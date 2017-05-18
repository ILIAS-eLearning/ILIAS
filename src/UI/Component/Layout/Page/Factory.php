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
     *     The Notification Center concentrates the visualization of any
     *     push-notification into one expandable glyph.
     *     This unification removes the multitude of dedicated notification-icons
     *     in favor of visual cleanliness as well as providing a designated
     *     location for further extensions.
     *
     *   composition: >
     *      The Notification Center is visualized as a glyph with a counter.
     *      Clicked, a list with the notifying services and their respective
     *      counter-glyphs is expanded.
     *
     *   effect: >
     *      All notifications, regardless of their origin, are summed up in
     *      the counter of the Notification Center's glyph.
     *      When clicked, a list is shown with all notifying services.
     *      The entries each consist of the services' respective glyph, counter
     *      and title.
     *      Entries as well can be clicked; the user is then redirected to the
     *      view of the service.
     *
     *   rivals: >
     *
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
    public function notificationcenter();


}
