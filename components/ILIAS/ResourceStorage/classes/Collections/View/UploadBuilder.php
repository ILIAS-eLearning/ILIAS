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

namespace ILIAS\components\ResourceStorage\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Dropzone\File\File;
use ILIAS\UI\Component\Input\Field\UploadHandler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class UploadBuilder
{
    public function __construct(
        private Request $request,
        private \ilCtrlInterface $ctrl,
        private Factory $ui_factory,
        private \ilLanguage $language,
        private UploadHandler $upload_handler
    ) {
    }

    public function getDropZone(): \Generator
    {
        if ($this->request->canUserUplaod()) {
            yield $this->ui_factory->dropzone()->file()->standard(
                $this->language->txt('upload_modal_title'),
                $this->language->txt('msg_upload'),
                $this->ctrl->getLinkTargetByClass(
                    \ilResourceCollectionGUI::class,
                    \ilResourceCollectionGUI::CMD_POST_UPLOAD
                ),
                $this->ui_factory->input()->field()->file(
                    $this->upload_handler,
                    $this->language->txt('upload_field_title')
                )->withMaxFiles(100)
            )->withUploadButton(
                $this->ui_factory->button()->shy(
                    $this->language->txt('select_files_from_computer'),
                    '#'
                )
            )->withBulky(true);
        }
    }
}
