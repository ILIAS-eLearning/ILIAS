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
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;

trait FileDataRCHandling
{
    /**
     * @param list<string> $path_to_files
     */
    protected function getCurrentCollection(
        array $path_to_files,
        ilMailAttachmentStakeholder $stakeholder
    ): \ILIAS\ResourceStorage\Collection\ResourceCollection {
        set_error_handler(static function ($severity, $message, $file, $line): void {
            throw new ErrorException($message, $severity, 0, $file, $line);
        });

        try {
            global $DIC;
            $file_system = $DIC->filesystem()->storage();
            $rcid = $this->storage->collection()->id();
            $collection = $this->storage->collection()->get($rcid);
            foreach ($path_to_files as $path_to_file) {
                $base_dir = (new \ILIAS\FileDelivery\Setup\BaseDirObjective())::get();
                $path_to_file = str_replace($base_dir, '/', $path_to_file);
                $rid = $this->storage->manage()->stream(
                    $file_system->readStream($path_to_file),
                    $stakeholder,
                    md5(basename($path_to_file))
                );
                $collection->add($rid);
            }
            $this->storage->collection()->store($collection);
        } catch (Exception $e) {
            throw new Exception("Storing file into collection failed: " . $e->getMessage());
        } finally {
            restore_error_handler();
        }

        return $collection;
    }

    /**
     * @param array<string, mixed> $mail_data
     * @return list<string>
     */
    public function filesFromLegacyToIRSS(array $mail_data): array
    {
        $files = [];
        $path_to_files = [];
        foreach ($mail_data['attachments'] as $file) {
            $path_to_files[] = $this->fdm->getAbsoluteAttachmentPoolPathByFilename($file);
        }
        $collection = $this->getCurrentCollection($path_to_files, new ilMailAttachmentStakeholder());
        foreach ($collection->getResourceIdentifications() as $rcid) {
            $files[] = $rcid->serialize();
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $mail_data
     */
    public function getIdforCollection(array $mail_data): ?ResourceCollectionIdentification
    {
        $files = [];
        $path_to_files = [];
        foreach ($mail_data as $attachment) {
            $path_to_files[] = $this->fdm->getAbsoluteAttachmentPoolPathByFilename($attachment);
        }
        $collection = $this->getCurrentCollection($path_to_files, new ilMailAttachmentStakeholder());
        $rcid = $collection->getIdentification();

        return $rcid;
    }

    /**
     * @return list<string>
     */
    public function FilesFromIRSSToLegacy(ResourceCollectionIdentification $identification): array
    {
        $files = [];
        $collection = $this->storage->collection()->get($identification);
        $all_ids = $collection->getResourceIdentifications();
        foreach ($all_ids as $id) {
            $files[] = $id->serialize();
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $attachments
     * @return list<string>
     */
    protected function handleAttachments(array $attachments): array
    {
        $files = [];
        foreach ($attachments as $attachment) {
            $info = $this->upload_handler->getInfoResult($attachment);
            if ($info->getFileIdentifier() !== 'unknown') {
                $src = $this->upload_handler->getStreamConsumer($attachment);
                $stored = $this->fdm->storeAsAttachment(
                    $info->getName(),
                    (string) $src->getStream()
                );
                if ($stored === false) {
                    throw new Exception("File '" . $info->getName() . "' could not be stored");
                }
                $files[] = ilFileUtils::_sanitizeFilemame($info->getName());
                $this->upload_handler->removeFileForIdentifier($attachment);
            }
        }

        return $files;
    }
}
