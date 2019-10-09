<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\Modal\LightboxImagePage;
use ILIAS\UI\Implementation\Component\Modal\LightboxTextPage;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *      An Interruptive modal disrupts the user in critical situation,
     *      forcing him or her to focus on the task at hand.
     *   composition: >
     *      The modal states why this situation needs attention and may point out consequences.
     *   effect: >
     *      All controls of the original context are inaccessible until the modal is completed.
     *      Upon completion the user returns to the original context.
     *
     * rules:
     *   usage:
     *     1: >
     *        Due to the heavily disruptive nature of this type of modal it MUST be
     *        restricted to critical situations (e.g. loss of data).
     *     2: >
     *        All actions where data is deleted from the system are considered to be critical situations and
     *        SHOULD be implemented as an Interruptive modal. Exceptions are possible if items from lists in
     *        forms are to be deleted or if the modal would heavily disrupt the workflow.
     *     3: >
     *        Interruptive modals MUST contain a primary button continuing the action that initiated the modal
     *        (e.g. Delete the item) on the left side of the footer of the modal and a default button canceling
     *        the action on the right side of the footer.
     *     4: >
     *        The cancel button in the footer and the close button in the header MUST NOT perform
     *        any additional action than closing the Interruptive modal.
     * ---
     *
     * @param string $title
     * @param string $message A plain string informing the user about the critical situation
     * @param string $form_action The URL where the modal posts its form data
     *
     * @return \ILIAS\UI\Component\Modal\Interruptive
     */
    public function interruptive($title, $message, $form_action);


    /**
     * ---
     * description:
     *   purpose: >
     *     Interruptive items are displayed in an Interruptive modal and represent the object(s) being affected
     *     by the critical action, e.g. deleting.
     *   composition:
     *     An Interruptive item is composed of an Id, title, description and an icon.
     *   effect:
     * rules:
     *   usage:
     *     1: >
     *       An interruptive item MUST have an ID and title.
     *     2: >
     *       An interruptive item SHOULD have an icon representing the affected object.
     *     3: >
     *       An interruptive item MAY have a description which helps to further identify the object.
     *       If an Interruptive modal displays multiple items having the the same title,
     *       the description MUST be used in order to distinct these objects from each other.
     *     4: >
     *       If an interruptive item represents an ILIAS object, e.g. a course, then the Id, title, description
     *       and icon of the item MUST correspond to the Id, title, description and icon from the ILIAS object.
     * ---
     *
     * @param string $id
     * @param string $title
     * @param Image $icon
     * @param string $description
     *
     * @return InterruptiveItem
     */
    public function interruptiveItem($id, $title, Image $icon = null, $description = '');


    /**
     * ---
     * description:
     *   purpose: >
     *     Round-Trip modals are to be used if the context would be lost by performing this action otherwise.
     *     Round-Trip modals accommodate sub-workflows within an overriding workflow.
     *     The Round-Trip modal ensures that the user does not leave the trajectory of the
     *     overriding workflow. This is typically the case if an ILIAS service is being called
     *     while working in an object.
     *   composition: >
     *     Round-Trip modals are completed by a well defined sequence of only a few steps that might be
     *     displayed on a sequence of different modals connected through some "next" button.
     *   effect: >
     *     Round-Trip modals perform sub-workflow involving some kind of user input. Sub-workflow is completed
     *     and user is returned to starting point allowing for continuing the overriding workflow.
     *
     * rules:
     *   usage:
     *     1: >
     *       Round-Trip modals MUST contain at least two buttons at the bottom of the modals: a button
     *       to cancel (right) the workflow and a button to finish or reach the next step in the workflow (left).
     *     2: >
     *       Round-Trip modals SHOULD be used, if the user would lose the context otherwise. If the action
     *       can be performed within the same context (e.g. add a post in a forum, edit a wiki page),
     *       a Round-Trip modal MUST NOT be used.
     *     3: >
     *       When the workflow is completed, Round-Trip modals SHOULD show the same view that
     *       was displayed when initiating the modal.
     *     4: >
     *       Round-Trip modals SHOULD NOT be used to add new items of any kind since adding item is a
     *       linear workflow redirecting to the newly added item setting- or content-tab.
     *     5: >
     *       Round-Trip modals SHOULD NOT be used to perform complex workflows.
     *   wording:
     *     1: >
     *       The label of the Button used to close the Round-Trip-Modal MAY be adapted, if the default label (cancel)
     *       does not fit the workflow presented on the screen.
     * ---
     * @param string $title
     * @param Component\Component|Component\Component[] $content
     *
     * @return \ILIAS\UI\Component\Modal\RoundTrip
     */
    public function roundtrip($title, $content);


    /**
     * ---
     * description:
     *   purpose: >
     *     The Lightbox modal displays media data such as images or videos. It may also display text
     *     that has a purely descriptive nature and does not offer interaction.
     *   composition: >
     *     A Lightbox modal consists of one or multiple lightbox pages representing the text or media together
     *     with a title.
     *   effect: >
     *     Lightbox modals are activated by clicking the full view glyphicon,
     *     the title of the object or it's thumbnail.
     *     If multiple pages are to be displayed, they can flipped through.
     *
     * rules:
     *   usage:
     *     1: >
     *       Lightbox modals MUST contain a title above the presented item.
     *     2: >
     *       Lightbox modals SHOULD contain a descriptional text below the presented items.
     *     3: >
     *       Multiple items inside a Lightbox modal MUST be presented in carousel
     *       like manner allowing to flickr through items.
     *
     * ---
     * @param LightboxPage|LightboxPage[] $pages
     *
     * @return \ILIAS\UI\Component\Modal\Lightbox
     */
    public function lightbox($pages);


    /**
     * ---
     * description:
     *   purpose: >
     *     A Lightbox image page represents an image inside a Lightbox modal.
     *   composition: >
     *     The page consists of the image, a title and optional description.
     *   effect: >
     *     The image is displayed in the content section of the Lightbox modal and the title is used
     *     as modal title. If a description is present, it will be displayed below the image.
     * rules:
     *   usage:
     *     2: >
     *       A Lighbox image page MUST have an image and a short title.
     *     1: >
     *       A Lightbox image page SHOULD have short a description, describing the presented image.
     *       If the description is omitted, the Lightbox image page falls back to the alt tag of the image.
     * ---
     *
     * @param Image $image
     * @param string $title
     * @param string $description
     *
     * @return LightboxImagePage
     */
    public function lightboxImagePage(Image $image, $title, $description = '');

    /**
     * ---
     * description:
     *   purpose: >
     *     A Lightbox text page represents a document like content/text inside a Lightbox modal.
     *   composition: >
     *     The page consists of text and a title
     *   effect: >
     *     The text is displayed in the content section of the Lightbox modal and the title is used
     *     as modal title.
     * rules:
     *   usage:
     *     1: >
     *       A Lighbox text page MUST have text content and a short title.
     *     2: >
     *       A Lighbox text page MUST NOT have a description.
     * ---
     *
     * @param string $text
     * @param string $title
     *
     * @return LightboxTextPage
     */
    public function lightboxTextPage(string $text, string $title);
}
