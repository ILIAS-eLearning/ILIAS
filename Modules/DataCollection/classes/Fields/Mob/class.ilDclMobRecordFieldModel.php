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

/**
 * Class ilDclMobRecordFieldModel
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Michael Herren <mh@studer-raimann.ch>
 */
class ilDclMobRecordFieldModel extends ilDclBaseRecordFieldModel
{
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * @param array|int $value
     * @return array|string
     * @throws ilException
     * @throws ilFileUtilsException
     * @throws ilMediaObjectsException
     */
    public function parseValue($value)
    {
        if ($value == -1) { //marked for deletion.
            return 0;
        }

        $media = $value;

        $hasRecordId = $this->http->wrapper()->query()->has('record_id');

        $has_save_confirmation = ($this->getRecord()->getTable()->getSaveConfirmation() && $hasRecordId);

        $has_save_confirmed = $this->http->wrapper()->post()->has('save_confirmed');
        $is_confirmed = $has_save_confirmed;

        if (is_array($media) && $media['tmp_name'] != "" && (!$has_save_confirmation || $is_confirmed)) {
            $mob = new ilObjMediaObject();
            $mob->setTitle($media['name']);
            $mob->create();
            $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
            if (!is_dir($mob_dir)) {
                $mob->createDirectory();
            }
            $media_item = new ilMediaItem();
            $mob->addMediaItem($media_item);
            $media_item->setPurpose("Standard");
            $file_name = ilFileUtils::getASCIIFilename($media['name']);
            $file_name = str_replace(" ", "_", $file_name);
            $file = $mob_dir . "/" . $file_name;
            $title = $file_name;
            $location = $file_name;
            if ($has_save_confirmation) {

                $ilfilehash = $this->http->wrapper()->post()->retrieve('ilfilehash',
                    $this->refinery->kindlyTo()->string());

                $move_file = ilDclPropertyFormGUI::getTempFilename($ilfilehash,
                    'field_' . $this->getField()->getId(), $media["name"], $media["type"]);
                ilFileUtils::rename($move_file, $file);
            } else {
                ilFileUtils::moveUploadedFile($media['tmp_name'], $file_name, $file);
            }

            ilFileUtils::renameExecutables($mob_dir);
            // Check image/video
            $format = ilObjMediaObject::getMimeType($file);

            if ($format == 'image/jpeg') {
                list($width, $height, $type, $attr) = getimagesize($file);
                $field = $this->getField();
                $new_width = $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH);
                $new_height = $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT);
                if ($new_width || $new_height) {
                    //only resize if it is bigger, not if it is smaller
                    if ($new_height < $height && $new_width < $width) {
                        //resize proportional
                        if (!$new_height || !$new_width) {
                            $format = ilObjMediaObject::getMimeType($file);
                            $wh
                                = ilObjMediaObject::_determineWidthHeight(
                                $format,
                                "File",
                                $file,
                                "",
                                true,
                                false,
                                $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH),
                                (int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT)
                            );
                        } else {
                            $wh['width'] = (int) $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH);
                            $wh['height'] = (int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT);
                        }

                        $location = ilObjMediaObject::_resizeImage($file, $wh['width'], $wh['height']);
                    }
                }
            }

            ilObjMediaObject::_saveUsage($mob->getId(), "dcl:html",
                $this->getRecord()->getTable()->getCollectionObject()->getId());
            $media_item->setFormat($format);
            $media_item->setLocation($location);
            $media_item->setLocationType("LocalFile");

            // FSX MediaPreview
            if (ilFFmpeg::enabled() && ilFFmpeg::supportsImageExtraction($format)) {
                $med = $mob->getMediaItem("Standard");
                $mob_file = ilObjMediaObject::_getDirectory($mob->getId()) . "/" . $med->getLocation();
                $a_target_dir = ilObjMediaObject::_getDirectory($mob->getId());
                try {
                    $new_file = ilFFmpeg::extractImage($mob_file, "mob_vpreview.png", $a_target_dir, 1);
                } catch (Exception $e) {
                    $this->main_tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                }
            }

            $mob->update();
            $return = $mob->getId();
            // handover for save-confirmation
        } else {
            if (is_array($media) && isset($media['tmp_name']) && $media['tmp_name'] != '') {
                $return = $media;
            } else {
                $return = $this->getValue();
            }
        }

        return $return;
    }

    /**
     * Function to parse incoming data from form input value $value. returns the int|string to store in the database.
     * @param int|string $value
     * @return int|string
     */
    public function parseExportValue($value)
    {
        $file = $value;
        if (is_numeric($file)) {
            $mob = new ilObjMediaObject($file);
            $mob_name = $mob->getTitle();

            return $mob_name;
        }

        return $file;
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation) : void
    {
        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $key => $value) {
                $confirmation->addHiddenItem('field_' . $this->field->getId() . '[' . $key . ']', $value);
            }
        }
    }

    /**
     * Returns sortable value for the specific field-types
     * @param int $value
     */
    public function parseSortingValue($value, bool $link = true) : string
    {
        $mob = new ilObjMediaObject($value);

        return $mob->getTitle();
    }

    public function setValueFromForm(ilPropertyFormGUI $form) : void
    {
        $value = $form->getInput("field_" . $this->getField()->getId());
        if ($form->getItemByPostVar("field_" . $this->getField()->getId())->getDeletionFlag()) {
            $value = -1;
        }
        $this->setValue($value);
    }

    public function afterClone() : void
    {
        $field = ilDclCache::getCloneOf($this->getField()->getId(), ilDclCache::TYPE_FIELD);
        $record = ilDclCache::getCloneOf($this->getRecord()->getId(), ilDclCache::TYPE_RECORD);
        $record_field = ilDclCache::getRecordFieldCache($record, $field);

        if (!$record_field || !$record_field->getValue()) {
            return;
        }

        $mob_old = new ilObjMediaObject($record_field->getValue());
        $mob_new = $mob_old->duplicate();

        $this->setValue($mob_new->getId(), true);
        $this->doUpdate();
    }
}
