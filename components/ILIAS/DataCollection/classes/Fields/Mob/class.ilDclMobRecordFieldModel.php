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

        $mob->addMediaItemFromUpload(
            'Standard',
            $this->upload->getResults()[$value[self::FILE_TMP_NAME]],
            $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string())
        );
        $mob->update();

        ilObjMediaObject::_saveUsage(
            $mob->getId(),
            'dcl:html',
            $this->getRecord()->getTable()->getCollectionObject()->getId()
        );

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
            $mob->delete();
        }
    }

    public function afterClone(): void
    {
        if ($this->value !== null) {
            $value = null;
            if (ilObjMediaObject::_exists($this->value)) {
                $origin = new ilObjMediaObject($this->value);
                $new = $origin->duplicate();
                $value = $new->getId();
            }
            $this->setValue($value, true);
            $this->doUpdate();
        }
    }
}
