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
 ********************************************************************
 */

use ILIAS\FileUpload\MimeType;

/**
 * Class ilDclBaseFieldModel
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 */
class ilDclFileuploadRecordFieldModel extends ilDclBaseRecordFieldModel
{
    /**
     * @param array|int $value
     */
    public function parseValue($value): ?array
    {
        global $DIC;
        if ($value == -1) { //marked for deletion.
            return null;
        }

        $file = $value;

        $hasRecordId = $this->http->wrapper()->query()->has('record_id');
        $is_confirmed = $this->http->wrapper()->query()->has('save_confirmed');
        $has_save_confirmation = ($this->getRecord()->getTable()->getSaveConfirmation() && $hasRecordId);

        if (is_array($file) && $file['tmp_name'] != "" && (!$has_save_confirmation || $is_confirmed)) {
            $file_obj = new ilObjFile();
            $file_obj->setType("file");
            $file_obj->setTitle($file["name"]);
            $file_obj->setFileName($file["name"]);
            $file_obj->setFileType(MimeType::getMimeType("", $file["name"], $file["type"]));
            $file_obj->setMode(ilObjFile::MODE_OBJECT);
            $file_obj->create();

            if ($has_save_confirmation) {
                $ilfilehash = $this->http->wrapper()->query()->retrieve(
                    'ilfilehash',
                    $this->refinery->kindlyTo()->string()
                );

                $move_file = ilDclPropertyFormGUI::getTempFilename(
                    $ilfilehash,
                    'field_' . $this->getField()->getId(),
                    $file["name"],
                    $file["type"]
                );

                $file_obj->appendStream(
                    ILIAS\Filesystem\Stream\Streams::ofResource(fopen($move_file, 'rb')),
                    $file_obj->getTitle()
                );

                $file_obj->setFileName($file["name"]);
            } else {
                $move_file = $file['tmp_name'];
                /**
                 * @var \ILIAS\FileUpload\FileUpload $upload
                 */
                $upload = $DIC->upload();

                if (false === $upload->hasBeenProcessed()) {
                    $upload->process();
                }

                if (false === $upload->hasUploads()) {
                    throw new ilException($this->lng->txt('upload_error_file_not_found'));
                }
                $file_obj->getUploadFile($move_file, $file["name"]);
            }

            $file_obj->update();

            $file_id = $file_obj->getId();
            $return = $file_id;
        // handover for save-confirmation
        } else {
            if (is_array($file) && isset($file['tmp_name']) && $file['tmp_name'] != "") {
                $return = $file;
            } else {
                $return = $this->getValue();
            }
        }

        return $return;
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation): void
    {
        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $key => $value) {
                $confirmation->addHiddenItem('field_' . $this->field->getId() . '[' . $key . ']', $value);
            }
        }
    }

    /**
     * Set value for record field
     * @param string|int $value
     * @param bool       $omit_parsing If true, does not parse the value and stores it in the given format
     */
    public function setValue($value, bool $omit_parsing = false): void
    {
        $this->loadValue();

        if (!$omit_parsing) {
            $tmp = $this->parseValue($value);
            $old = $this->value;
            //if parse value fails keep the old value
            if ($tmp !== false) {
                $this->value = $tmp;
                //delete old file from filesystem
                if ($old && $old != $tmp) {
                    $this->getRecord()->deleteFile($old);
                }
            }
        } else {
            $this->value = $value;
        }
    }

    /**
     * @param string $value
     */
    public function parseExportValue($value): ?string
    {
        if (!ilObject2::_exists($value) || ilObject2::_lookupType($value) != "file") {
            return null;
        }

        $file = $value;
        if ($file != "-") {
            $file_obj = new ilObjFile($file, false);
            $file_name = $file_obj->getFileName();

            return $file_name;
        }

        return $file;
    }

    /**
     * Returns sortable value for the specific field-types
     * @param int $value
     */
    public function parseSortingValue($value, bool $link = true): string
    {
        if (!ilObject2::_exists($value) || ilObject2::_lookupType($value) != "file") {
            return '';
        }
        $file_obj = new ilObjFile($value, false);

        return $file_obj->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function setValueFromForm(ilPropertyFormGUI $form): void
    {
        $value = $form->getInput("field_" . $this->getField()->getId());
        if ($form->getItemByPostVar("field_" . $this->getField()->getId())->getDeletionFlag()) {
            $value = -1;
        }
        $this->setValue($value);
    }

    /**
     *
     */
    public function afterClone(): void
    {
        $field = ilDclCache::getCloneOf($this->getField()->getId(), ilDclCache::TYPE_FIELD);
        $record = ilDclCache::getCloneOf($this->getRecord()->getId(), ilDclCache::TYPE_RECORD);
        $record_field = ilDclCache::getRecordFieldCache($record, $field);

        if (!$record_field || !$record_field->getValue()) {
            return;
        }

        $file_old = new ilObjFile($record_field->getValue(), false);
        $file_new = $file_old->cloneObject(null, null, true);

        $this->setValue($file_new->getId(), true);
        $this->doUpdate();
    }
}
