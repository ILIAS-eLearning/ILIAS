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

use ILIAS\FileUpload\MimeType;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

class ilDclFileFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(): FormInput
    {
        $map = MimeType::getExt2MimeMap();
        $mime_types = [];
        foreach ($this->getField()->getSupportedExtensions() as $extension) {
            if (isset($map['.' . $extension])) {
                $mime_types[] = $map['.' . $extension];
            }
        }

        return $this->factory->input()->field()->file(
            new ilDataCollectionUploadHandlerGUI(),
            $this->getField()->getTitle(),
            $this->getField()->getDescription()
        )->withAcceptedMimeTypes($mime_types);
    }

    protected function buildFieldCreationInput(
        ilObjDataCollection $dcl,
        string $mode = 'create'
    ): ilRadioOption {
        $opt = parent::buildFieldCreationInput($dcl, $mode);

        $prop_filetype = new ilTextInputGUI(
            $this->lng->txt('dcl_supported_filetypes'),
            'prop_' . ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES
        );
        $prop_filetype->setInfo($this->lng->txt('dcl_supported_filetypes_desc'));

        $opt->addSubItem($prop_filetype);

        return $opt;
    }
}
