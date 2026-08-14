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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Services;

class ilDclFileRecordFieldModel extends ilDclBaseRecordFieldModel
{
    protected const string FILE_TMP_NAME = 'tmp_name';
    protected const string FILE_NAME = "name";
    protected const string FILE_TYPE = "type";

    protected Services $irss;
    protected ilDataCollectionStakeholder $stakeholder;
    protected FileUpload $upload;

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        global $DIC;
        parent::__construct($record, $field);
        $this->stakeholder = new ilDataCollectionStakeholder();
        $this->irss = $DIC->resourceStorage();
        $this->upload = $DIC->upload();
    }

    public function delete(): void
    {
        if ($this->value !== null) {
            $this->removeData();
        }
        parent::delete();
    }

    protected function removeData(): void
    {
        if (null !== $rid = $this->irss->manage()->find($this->getValue())) {
            $this->irss->manage()->remove($rid, $this->stakeholder);
        }
    }

    public function parseExportValue($value): mixed
    {
        $rid = $this->irss->manage()->find($value);
        if ($rid === null || null === $revision = $this->irss->manage()->getCurrentRevision($rid)) {
            return $this->lng->txt('file_not_found');
        }
        return $revision->getTitle();
    }

    public function parseSortingValue($value, bool $link = true): mixed
    {
        $rid = $this->irss->manage()->find($value);
        if ($rid === null || null === $revision = $this->irss->manage()->getCurrentRevision($rid)) {
            return $this->lng->txt('file_not_found');
        }
        return $revision->getTitle();
    }

    public function afterClone(): void
    {
        if ($this->value !== null) {
            $value = null;
            $rid = $this->irss->manage()->find($this->value);
            if ($rid !== null) {
                $current = $this->irss->manage()->getCurrentRevision($rid);
                if ($current !== null) {
                    $new_rid = $this->irss->manage()->clone($current->getIdentification());
                    $value = $new_rid->serialize();
                }
            }
            $this->setValue($value, true);
            $this->doUpdate();
        }
    }
}
