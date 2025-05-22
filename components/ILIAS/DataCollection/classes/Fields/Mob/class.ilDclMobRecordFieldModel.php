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

class ilDclMobRecordFieldModel extends ilDclFileRecordFieldModel
{
    public function parseValue($value)
    {
        if ($value === -1) {
            return null;
        }

        $media = $value;

        $has_record_id = $this->http->wrapper()->query()->has('record_id');
        $is_confirmed = $this->http->wrapper()->post()->has('save_confirmed');
        $has_save_confirmation = ($this->getRecord()->getTable()->getSaveConfirmation() && !$has_record_id);

        if (($media['tmp_name'] ?? '') !== '' && (!$has_save_confirmation || $is_confirmed)) {
            $mob = new ilObjMediaObject();
            $mob->setTitle($media['name']);
            $mob->create();
            $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
            if (!is_dir($mob_dir)) {
                $mob->createDirectory();
            }
            $media_item = new ilMediaItem();
            $mob->addMediaItem($media_item);
            $media_item->setPurpose('Standard');
            $file_name = ilFileUtils::getASCIIFilename($media['name']);
            $file_name = str_replace(' ', '_', $file_name);
            $target_file_path = $mob_dir . '/' . $file_name;
            $location = $file_name;

            if ($has_save_confirmation) {
                $ilfilehash = $this->http->wrapper()->post()->retrieve(
                    'ilfilehash',
                    $this->refinery->kindlyTo()->string()
                );

                $move_file = ilDclPropertyFormGUI::getTempFilename(
                    $ilfilehash,
                    'field_' . $this->getField()->getId(),
                    $media['name'],
                    $media['type']
                );
            } else {
                if (!$this->upload->hasBeenProcessed()) {
                    $this->upload->process();
                }

                if (!$this->upload->hasUploads()) {
                    throw new ilException($this->lng->txt('upload_error_file_not_found'));
                }
                $move_file = $media['tmp_name'];
            }

            ilFileUtils::rename($move_file, $target_file_path);
            ilFileUtils::renameExecutables($mob_dir);

            $format = ilObjMediaObject::getMimeType($target_file_path);

            ilObjMediaObject::_saveUsage(
                $mob->getId(),
                'dcl:html',
                $this->getRecord()->getTable()->getCollectionObject()->getId()
            );
            $media_item->setFormat($format);
            $media_item->setLocation($location);
            $media_item->setLocationType('LocalFile');

            if (ilFFmpeg::enabled() && ilFFmpeg::supportsImageExtraction($format)) {
                $med = $mob->getMediaItem('Standard');
                $mob_file = ilObjMediaObject::_getDirectory($mob->getId()) . '/' . $med->getLocation();
                $a_target_dir = ilObjMediaObject::_getDirectory($mob->getId());
                ilFFmpeg::extractImage($mob_file, 'mob_vpreview.png', $a_target_dir);
            }

            $mob->update();
            $return = $mob->getId();
            if ($this->value !== null) {
                $this->removeData();
            }
        } else {
            if (($media['tmp_name'] ?? '') !== '') {
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
            return $mob->getTitle();
        }

        return $file;
    }

    /**
     * Returns sortable value for the specific field-types
     * @param int $value
     */
    public function parseSortingValue($value, bool $link = true): string
    {
        $mob = new ilObjMediaObject($value);

        return $mob->getTitle();
    }

    protected function removeData(): void
    {
        $mob = new ilObjMediaObject($this->value);
        ilObjMediaObject::_removeUsage(
            $mob->getId(),
            'dcl:html',
            $this->getRecord()->getTable()->getCollectionObject()->getId()
        );
        $mob->delete();
    }

    public function afterClone(): void
    {
        $field = ilDclCache::getCloneOf((int) $this->getField()->getId(), ilDclCache::TYPE_FIELD);
        $record = ilDclCache::getCloneOf($this->getRecord()->getId(), ilDclCache::TYPE_RECORD);
        $record_field = ilDclCache::getRecordFieldCache($record, $field);

        if (!$record_field->getValue()) {
            return;
        }

        $mob_old = new ilObjMediaObject($record_field->getValue());
        $mob_new = $mob_old->duplicate();

        $this->setValue($mob_new->getId(), true);
        $this->doUpdate();
    }
}
