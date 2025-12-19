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

use ILIAS\FileUpload\Location;

class ilDclMobRecordFieldModel extends ilDclFileRecordFieldModel
{
    protected function handleFileUpload(array $value, bool $has_save_confirmation): int
    {
        $mob = new ilObjMediaObject();
        $mob->setTitle($value[self::FILE_NAME]);
        $mob->create();
        $mob->createDirectory();
        $target_file_path = ilObjMediaObject::_getRelativeDirectory($mob->getId());

        if ($has_save_confirmation) {
            $move_file = ilDclPropertyFormGUI::getTempFilename(
                $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string()),
                'field_' . $this->getField()->getId(),
                $value[self::FILE_NAME],
                $value[self::FILE_TYPE]
            );
            $this->getField()->getFileSystem()->write($target_file_path . '/' . str_replace(' ', '_', $value[self::FILE_NAME]), file_get_contents($move_file));
        } else {
            $this->upload->moveFilesTo(ilObjMediaObject::_getRelativeDirectory($mob->getId()), Location::WEB);
        }

        $file = $this->getField()->getFileSystem()->listContents(ilObjMediaObject::_getRelativeDirectory($mob->getId()))[0];
        $format = $this->getField()->getFileSystem()->getMimeType($file->getPath());

        ilObjMediaObject::_saveUsage(
            $mob->getId(),
            'dcl:html',
            $this->getRecord()->getTable()->getCollectionObject()->getId()
        );
        $media_item = new ilMediaItem();
        $media_item->setPurpose('Standard');
        $media_item->setFormat($format);
        $media_item->setLocation(basename($file->getPath()));
        $media_item->setLocationType('LocalFile');
        $mob->addMediaItem($media_item);

        if (ilFFmpeg::enabled() && ilFFmpeg::supportsImageExtraction($format)) {
            $dir = ilObjMediaObject::_getDirectory($mob->getId());
            ilFFmpeg::extractImage($dir . '/' . basename($file->getPath()), 'mob_vpreview.png', $dir);
        }

        $mob->update();
        return $mob->getId();
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
        if (ilObjMediaObject::_exists($this->value)) {
            $mob = new ilObjMediaObject($this->value);
            $this->getField()->getFileSystem()->deleteDir(ilObjMediaObject::_getRelativeDirectory($mob->getId()));
            $mob->delete();
        }
    }

    public function afterClone(): void
    {
        if ($this->value !== null) {
            $value = null;
            if (ilObjMediaObject::_exists($this->value)) {
                $origin = new ilObjMediaObject($this->value);
                $path = $origin::_getRelativeDirectory($origin->getId()) . '/' . $origin->getTitle();
                if ($this->getField()->getFileSystem()->has($path)) {
                    $new = $origin->duplicate();
                    $new->createDirectory();
                    $new_path = $origin::_getRelativeDirectory($new->getId()) . '/' . $new->getTitle();
                    $this->getField()->getFileSystem()->copy($path, $new_path);
                    $value = $new->getId();
                }
            }
            $this->setValue($value, true);
            $this->doUpdate();
        }
    }
}
