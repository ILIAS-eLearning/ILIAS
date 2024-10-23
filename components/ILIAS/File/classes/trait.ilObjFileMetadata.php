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

use ILIAS\MetaData\Paths\PathInterface as Path;
use ILIAS\MetaData\Paths\Filters\FilterType;

/**
 * Trait ilObjFileMetadata
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilObjFileMetadata
{
    protected ?bool $no_meta_data_creation = null;

    protected function updateFileData(): void
    {
        global $DIC;
        $check_existing = $DIC->database()->queryF(
            'SELECT file_id FROM file_data WHERE file_id = %s',
            ['integer'],
            [$this->getId()]
        );
        if ($check_existing->numRows() === 0) {
            $DIC->database()->insert('file_data', $this->getArrayForDatabase());
        } else {
            $DIC->database()->update(
                'file_data',
                $this->getArrayForDatabase(),
                ['file_id' => ['integer', $this->getId()]]
            );
        }
    }

    /**
     * The basic properties of a file object are stored in table object_data.
     * This is not sufficient for a file object. Therefore we create additional
     * properties in table file_data.
     * This method has been put into a separate operation, to allow a WebDAV Null resource
     * (class.ilObjNull.php) to become a file object.
     */
    public function createProperties(bool $a_upload = false): void
    {
        global $DIC;

        // New Item
        if (isset($this->ref_id)) {
            $default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($this->ref_id);
            if ($default_visibility === "public") {
                ilBlockSetting::_write("news", "public_notifications", 1, 0, $this->getId());
            }
        }
        $this->updateFileData();

        // no meta data handling for file list files
        if ($this->getMode() !== self::MODE_FILELIST) {
            $this->createMetaData();
        }
    }

    public function setNoMetaDataCreation(bool $a_status)
    {
        $this->no_meta_data_creation = $a_status;
    }

    protected function beforeCreateMetaData(): bool
    {
        return !(bool) $this->no_meta_data_creation;
    }

    protected function beforeUpdateMetaData(): bool
    {
        return !(bool) $this->no_meta_data_creation;
    }

    /**
     * create file object meta data
     */
    protected function doCreateMetaData(): void
    {
        global $DIC;

        // add file size and format to LOM
        $DIC->learningObjectMetadata()->manipulate($this->getId(), 0, $this->getType())
                                      ->prepareCreateOrUpdate($this->getPathToSize(), (string) $this->getFileSize())
//                                      ->prepareCreateOrUpdate($this->getPathToFirstFormat(), $this->getFileType()) // TIDI thwors exception
                                      ->prepareCreateOrUpdate($this->getPathToVersion(), (string) $this->getVersion())
                                      ->execute();
    }

    protected function beforeMDUpdateListener(string $a_element): bool
    {
        global $DIC;

        // Check file extension
        // Removing the file extension is not allowed
        if ($a_element !== 'General') {
            return true;
        }

        $paths = $DIC->learningObjectMetadata()->paths();

        $title = $DIC->learningObjectMetadata()->read(
            $this->getId(),
            0,
            $this->getType(),
            $paths->title()
        )->firstData($paths->title())->value();

        $title = $this->appendSuffixToTitle($title, $this->getFileName());

        $DIC->learningObjectMetadata()->manipulate($this->getId(), 0, $this->getType())
                                      ->prepareCreateOrUpdate($paths->title(), $title)
                                      ->execute();

        return true;
    }

    protected function doMDUpdateListener(string $a_element): void
    {
        global $DIC;

        // handling for technical section
        if ($a_element !== 'Technical') {
            return;
        }

        $first_format = $DIC->learningObjectMetadata()->read(
            $this->getId(),
            0,
            $this->getType(),
            $this->getPathToFirstFormat()
        )->firstData($this->getPathToFirstFormat())->value();

        $this->setFileType($first_format);
    }

    /**
     * update meta data
     */
    protected function doUpdateMetaData(): void
    {
        global $DIC;

        $DIC->learningObjectMetadata()->manipulate($this->getId(), 0, $this->getType())
                                      ->prepareCreateOrUpdate($this->getPathToSize(), (string) $this->getFileSize())
                                      ->prepareCreateOrUpdate($this->getPathToFirstFormat(), $this->getFileType())
                                      ->prepareCreateOrUpdate($this->getPathToVersion(), (string) $this->getVersion())
                                      ->execute();
    }

    /**
     * update copyright meta data
     */
    protected function updateCopyright(): void
    {
        global $DIC;

        $lom_services = $DIC->learningObjectMetadata();

        $copyright_id = $this->getCopyrightID();
        if (!$lom_services->copyrightHelper()->isCopyrightSelectionActive() || $copyright_id === null) {
            return;
        }

        $lom_services->copyrightHelper()->prepareCreateOrUpdateOfCopyrightFromPreset(
            $lom_services->manipulate($this->getId(), 0, $this->getType()),
            $copyright_id
        )->execute();
    }

    protected function getPathToSize(): Path
    {
        global $DIC;

        return $DIC->learningObjectMetadata()
                   ->paths()
                   ->custom()
                   ->withNextStep('technical')
                   ->withNextStep('size')
                   ->get();
    }

    protected function getPathToFirstFormat(): Path
    {
        global $DIC;

        return $DIC->learningObjectMetadata()
                   ->paths()
                   ->custom()
                   ->withNextStep('technical')
                   ->withNextStep('format')
                   ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
                   ->get();
    }

    protected function getPathToVersion(): Path
    {
        global $DIC;

        return $DIC->learningObjectMetadata()
                   ->paths()
                   ->custom()
                   ->withNextStep('lifeCycle')
                   ->withNextStep('version')
                   ->withNextStep('string')
                   ->get();
    }
}
