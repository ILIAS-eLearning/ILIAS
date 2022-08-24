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

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * Describes a factory for file dropzones.
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      The standard dropzone is used to drop files dragged from outside
     *      the browser window. The dropped files are presented to the user and
     *      can be uploaded to the server.
     *   composition: >
     *      Standard dropzones consist of a visible area where files can
     *      be dropped. They MUST contain a message explaining that it is possible to
     *      drop files inside. The dropped files are presented to the user, optionally
     *      with some button to start the upload process.
     *   effect: >
     *      A standard dropzone is highlighted when the user is dragging files
     *      over the dropzone. After dropping, the dropped files are presented
     *      to the user with some meta information of the files such the file name
     *      and file size.
     *   rivals:
     *      Rival 1: >
     *          A wrapper dropzone can hold other ILIAS UI components instead of
     *          a message.
     *      Rival 2: >
     *          A file-input can be used instead of this component if other values
     *          have to be submitted at the same time.
     * rules:
     *   usage:
     *     1: Standard dropzones MUST contain a message.
     *   accessibility:
     *     1: >
     *        Standard dropzones MUST offer the possibility to select files
     *        manually from the computer.
     * ---
     * @param UploadHandler $upload_handler for async file upload
     * @param string        $post_url       for submitting the file data
     * @param Input|null    $metadata_input optional template for metadata inputs
     * @return \ILIAS\UI\Component\Dropzone\File\Standard
     */
    public function standard(
        UploadHandler $upload_handler,
        string $post_url,
        ?Input $metadata_input = null
    ): Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *      A wrapper dropzone is used to display other ILIAS UI components
     *      inside it. In contrast to the standard dropzone, the wrapper
     *      dropzone is not visible by default. Only the wrapped components are
     *      visible. Any wrapper dropzone gets highlighted once the user is dragging
     *      files over the browser window. Thus, a user needs to have the knowledge
     *      that there are wrapper dropzones present. They can be introduced to offer
     *      additional approaches to complete some workflow more conveniently.
     *      Especially in situation where space is scarce such as appointments
     *      in the calendar.
     *   composition: >
     *      A wrapper dropzone contains one or multiple ILIAS UI components.
     *      A roundtrip modal is used to present the dropped files and to initialize
     *      the upload process.
     *   effect: >
     *      All wrapper dropzones on the page are highlighted when the user
     *      dragging files over the browser window. After dropping the files, the
     *      roundtrip modal is opened showing all files. The modal contains a button
     *      to start the upload process.
     *   rivals:
     *      Rival 1: >
     *          A standard dropzone displays a message instead of other
     *          ILIAS UI components.
     * rules:
     *   usage:
     *     1: >
     *        Most pages SHOULD NOT contain a wrapper dropzone. Whenever you want to introduce a
     *        new usage of the Wrapper-Dropzone, propose it to the Jour Fixe.
     *     2: Wrapper dropzones MUST contain one or more ILIAS UI components.
     *     3: Wrapper dropzones MUST NOT contain any other file dropzones.
     *     4: Wrapper dropzones MUST NOT be used in modals.
     * ---
     * @param UploadHandler         $upload_handler for async file upload
     * @param string                $post_url       for submitting the file data
     * @param Component[]|Component $content        Component(s) wrapped by the dropzone
     * @param Input|null            $metadata_input optional template for metadata inputs
     * @return \ILIAS\UI\Component\Dropzone\File\Wrapper
     */
    public function wrapper(
        UploadHandler $upload_handler,
        string $post_url,
        $content,
        ?Input $metadata_input = null
    ): Wrapper;
}
