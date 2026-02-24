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

/**
 * @noinspection AutoloadingIssuesInspection
 */
class ilDclFileRecordFieldModel extends ilDclBaseRecordFieldModel
{
    use ilDclFileFieldHelper;

    protected const FILE_TMP_NAME = 'tmp_name';
    protected const FILE_NAME = "name";
    protected const FILE_TYPE = "type";

    protected \ILIAS\ResourceStorage\Services $irss;
    protected ilDataCollectionStakeholder $stakeholder;
    protected \ILIAS\FileUpload\FileUpload $upload;

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        global $DIC;
        parent::__construct($record, $field);
        $this->stakeholder = new ilDataCollectionStakeholder();
        $this->irss = $DIC->resourceStorage();
        $this->upload = $DIC->upload();
    }

    public function parseValue($value)
    {
        $has_record_id = $this->http->wrapper()->query()->has('record_id');
        $is_confirmed = $this->http->wrapper()->post()->has('save_confirmed');
        $has_save_confirmation = ($this->getRecord()->getTable()->getSaveConfirmation() && !$has_record_id);

        if (($value[self::FILE_TMP_NAME] ?? '') !== '' && (!$has_save_confirmation || $is_confirmed)) {
            return $this->handleFileUpload($value, $has_save_confirmation);
        } else {
            if (($value[self::FILE_TMP_NAME] ?? '') !== '') {
                return $value;
            } else {
                return $this->getValue();
            }
        }
    }

    protected function handleFileUpload(array $value, bool $has_save_confirmation): mixed
    {
        if ($has_save_confirmation) {
            $move_file = ilDclPropertyFormGUI::getTempFilename(
                $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string()),
                'field_' . $this->getField()->getId(),
                $value[self::FILE_NAME],
                $value[self::FILE_TYPE]
            );

            $file_stream = ILIAS\Filesystem\Stream\Streams::ofResource(fopen($move_file, 'rb'));
        } else {
            $move_file = $value[self::FILE_TMP_NAME];

            $file_stream = Streams::ofResource(fopen($move_file, 'rb'));
        }

        $file_title = $value[self::FILE_NAME] ?? basename($move_file);

        $old = $this->getValue();
        if (is_string($old) && ($rid = $this->irss->manage()->find($old)) !== null) {
            $this->irss->manage()->replaceWithStream($rid, $file_stream, $this->stakeholder, $file_title);
        } else {
            $rid = $this->irss->manage()->stream($file_stream, $this->stakeholder, $file_title);
        }

        return $rid->serialize();
    }

    public function setValueFromForm(ilPropertyFormGUI $form): void
    {
        if ($this->value !== null && $form->getItemByPostVar("field_" . $this->getField()->getId())->getDeletionFlag()) {
            $this->removeData();
            $this->setValue(null, true);
            $this->doUpdate();
        }
        parent::setValueFromForm($form);
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

    public function parseExportValue($value)
    {
        return $this->valueToFileTitle($value);
    }

    public function parseSortingValue($value, bool $link = true)
    {
        return $this->valueToFileTitle($value);
    }

    public function afterClone(): void
    {
        if ($this->value !== null) {
            $value = null;
            $current = $this->valueToCurrentRevision($this->value);
            if ($current !== null) {
                $new_rid = $this->irss->manage()->clone($current->getIdentification());
                $value = $new_rid->serialize();
            }
            $this->setValue($value, true);
            $this->doUpdate();
        }
    }
}
