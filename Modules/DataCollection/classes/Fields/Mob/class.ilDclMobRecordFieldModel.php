<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclMobRecordFieldModel
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Michael Herren <mh@studer-raimann.ch>
 *
 */
class ilDclMobRecordFieldModel extends ilDclBaseRecordFieldModel
{
    public function parseValue($value)
    {
        if ($value == -1) { //marked for deletion.
            return 0;
        }

        $media = $value;
        $has_save_confirmation = ($this->getRecord()->getTable()->getSaveConfirmation() && !isset($_GET['record_id']));
        $is_confirmed = (bool) (isset($_POST['save_confirmed']));

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
            $file_name = ilUtil::getASCIIFilename($media['name']);
            $file_name = str_replace(" ", "_", $file_name);
            $file = $mob_dir . "/" . $file_name;
            $title = $file_name;
            $location = $file_name;
            if ($has_save_confirmation) {
                $move_file = ilDclPropertyFormGUI::getTempFilename($_POST['ilfilehash'], 'field_' . $this->getField()->getId(), $media["name"], $media["type"]);
                ilUtil::moveUploadedFile($move_file, $file_name, $file);
            } else {
                ilUtil::moveUploadedFile($media['tmp_name'], $file_name, $file);
            }

            ilUtil::renameExecutables($mob_dir);
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
                                = ilObjMediaObject::_determineWidthHeight($format, "File", $file, "", true, false, $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH), (int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT));
                        } else {
                            $wh['width'] = (int) $field->getProperty(ilDclBaseFieldModel::PROP_WIDTH);
                            $wh['height'] = (int) $field->getProperty(ilDclBaseFieldModel::PROP_HEIGHT);
                        }

                        $location = ilObjMediaObject::_resizeImage($file, $wh['width'], $wh['height'], false);
                    }
                }
            }

            ilObjMediaObject::_saveUsage($mob->getId(), "dcl:html", $this->getRecord()->getTable()->getCollectionObject()->getId());
            $media_item->setFormat($format);
            $media_item->setLocation($location);
            $media_item->setLocationType("LocalFile");

            // FSX MediaPreview
            include_once("./Services/MediaObjects/classes/class.ilFFmpeg.php");
            if (ilFFmpeg::enabled() && ilFFmpeg::supportsImageExtraction($format)) {
                $med = $mob->getMediaItem("Standard");
                $mob_file = ilObjMediaObject::_getDirectory($mob->getId()) . "/" . $med->getLocation();
                $a_target_dir = ilObjMediaObject::_getDirectory($mob->getId());
                try {
                    $new_file = ilFFmpeg::extractImage($mob_file, "mob_vpreview.png", $a_target_dir, 1);
                } catch (Exception $e) {
                    ilUtil::sendFailure($e->getMessage(), true);
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
     * Function to parse incoming data from form input value $value. returns the strin/number/etc. to store in the database.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseExportValue($value)
    {
        $file = $value;
        if (is_numeric($file)) {
            $mob = new ilObjMediaObject($file, false);
            $mob_name = $mob->getTitle();

            return $mob_name;
        }

        return $file;
    }


    /**
     * @param ilConfirmationGUI $confirmation
     */
    public function addHiddenItemsToConfirmation(ilConfirmationGUI &$confirmation)
    {
        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $key => $value) {
                $confirmation->addHiddenItem('field_' . $this->field->getId() . '[' . $key . ']', $value);
            }
        }
    }


    /**
     * Returns sortable value for the specific field-types
     *
     * @param                           $value
     * @param ilDclBaseRecordFieldModel $record_field
     * @param bool|true                 $link
     *
     * @return int|string
     */
    public function parseSortingValue($value, $link = true)
    {
        $mob = new ilObjMediaObject($value, false);

        return $mob->getTitle();
    }


    /**
     * @inheritDoc
     */
    public function setValueFromForm($form)
    {
        $value = $form->getInput("field_" . $this->getField()->getId());
        if ($form->getItemByPostVar("field_" . $this->getField()->getId())->getDeletionFlag()) {
            $value = -1;
        }
        $this->setValue($value);
    }


    public function afterClone()
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
