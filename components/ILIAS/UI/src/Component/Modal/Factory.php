<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Modal\InterruptiveItem;
use ILIAS\UI\Component\Card\Card;
use ILIAS\Data\URI;

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
     * @param string $title
     * @param string $message A plain string informing the user about the critical situation
     * @param string $form_action The URL where the modal posts its form data
     * @return \ILIAS\UI\Component\Modal\Interruptive
     */
    public function interruptive(string $title, string $message, string $form_action): Interruptive;


    /**
     * ---
     * description:
     *   purpose: >
     *     Interruptive items are displayed in an Interruptive modal and represent the object(s) being affected
     *     by the critical action, e.g. deleting.
     *   composition: >
     *     In a single interruptive modal, only one type of interruptive item SHOULD be used. If there are
     *     interruptive items of multiple types in an interruptive modal, they MUST be rendered grouped
     *     by type.
     *
     * rules:
     *   usage:
     *     1: >
     *       An interruptive item MUST have an ID.
     * ---
     * @return \ILIAS\UI\Component\Modal\InterruptiveItem\Factory
     */
    public function interruptiveItem(): InterruptiveItem\Factory;


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
     *     Round-Trip modals are completed by a well-defined sequence of only a few steps that might be
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
     * @param Component\Component|Component\Component[]|null $content
     * @param Component\Input\Container\Form\FormInput[] $inputs
     * @param string|null $post_url
     * @return \ILIAS\UI\Component\Modal\RoundTrip
     */
    public function roundtrip(string $title, array $content, array $inputs = [], string $post_url = null): RoundTrip;


    /**
     * ---
     * description:
     *   purpose: >
     *     The Lightbox modal displays media data such as images or videos. It may also display text
     *     that has a purely descriptive nature and does not offer interaction.
     *   composition: >
     *     A Lightbox modal consists of one or multiple lightbox pages representing the text or media together
     *     with a title. The Lightbox uses a dark scheme if there is one or more image pages and a bright scheme if
     *     there are only text pages.
     *   effect: >
     *     Lightbox modals are activated by clicking the full view glyphicon,
     *     the title of the object, or it's thumbnail.
     *     If multiple pages are to be displayed, they can flip through.
     *
     * rules:
     *   usage:
     *     1: >
     *       Lightbox modals MUST contain a title above the presented item.
     *     2: >
     *       Lightbox modals SHOULD contain a description text below the presented items.
     *     3: >
     *       Multiple items inside a Lightbox modal MUST be presented in carousel
     *       like manner allowing to flickr through items.
     *
     * ---
     * @param LightboxPage|LightboxPage[] $pages
     * @return \ILIAS\UI\Component\Modal\Lightbox
     */
    public function lightbox($pages): Lightbox;


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
     * @param Image $image
     * @param string $title
     * @param string $description
     * @return \ILIAS\UI\Component\Modal\LightboxImagePage
     */
    public function lightboxImagePage(Image $image, string $title, string $description = ''): LightboxImagePage;

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
     *       A Lightbox text page MUST have text content and a short title.
     *     2: >
     *       A Lightbox text page MUST NOT have a description.
     * ---
     * @param string $text
     * @param string $title
     * @return \ILIAS\UI\Component\Modal\LightboxTextPage
     */
    public function lightboxTextPage(string $text, string $title): LightboxTextPage;

    /**
     * ---
     * description:
     *   purpose: >
     *       A lightbox card page shows a card as a Lightbox modal.
     *   composition: >
     *       The page shows a card with it's hidden sections.
     *   effect: >
     *       The card title is used as the modal title and the sections and hidden sections are
     *       displayed in the content section of the lightbox modal.
     * rules:
     *   usage:
     *     1: >
     *       A Lightbox card page MUST show a card.
     *     2: >
     *       A Lightbox card page SHOULD be used to show further information.
     * ---
     * @param Card $card
     * @return \ILIAS\UI\Component\Modal\LightboxCardPage
     */
    public function lightboxCardPage(Card $card): LightboxCardPage;

    /**
     * ---
     * description:
     *   purpose: >
     *      A Modal interrupts a user to focus on a certain task or/and
     *      prompts for information without the user losing the current context.
     *      The Dialog Modal is async by default and merely provides a wrapper,
     *      its contents are defined by the Modal Response.
     *   composition: >
     *      The Dialog Modal uses the HTML dialog tag.
     *   effect: >
     *      The contents of Dialog Modal are loaded asynchronously by default;
     *      actions of Forms and targets of Links are "wrapped" to RPCs and thus
     *      stay in context of the Dialog, i.e. you may take roundtrips to the
     *      server and modify the Dialog's content without closing it.
     * context:
     *   - The Dialog Modal requires a Dialog Response.
     *
     * rules:
     *   usage:
     *     1: >
     *      The server MUST answer with a DialogResponse Component
     *      to a request to the url provided to the Dialog.
     * ---
     * @return \ILIAS\UI\Component\Modal\Dialog
     */
    public function dialog(URI $async_url): Dialog;

    /**
     * ---
     * description:
     *   purpose: >
     *      A Dialog Response serves as a formalized wrapper around output of
     *      asynchrounous requests in order to provide contents for a Dialog.
     *      It allows for dedicated changes to recurring parts of Dialogs,
     *      such as Title, Content or Buttons.
     *   composition: >
     *      The Dialog Response accepts Dialog Content to be handled by
     *      the Dialog Modal.
     *   effect: >
     *       The sections of the Dialog Response are rendered to their respective
     *       parts of the Dialog Modal.
     *       Forms and Links are automatically turned into async requests to
     *       stay in context of the Dialog.
     *       You may also tell the Dialog to close - after the request has been processed.
     * context:
     *   - The Dialog Response is used for Dialog Modals.
     *
     * ---
     * @return \ILIAS\UI\Component\Modal\DialogResponse
     */
    public function dialogResponse(
        \ILIAS\UI\Component\Modal\DialogContent $content
    ): DialogResponse;
}
