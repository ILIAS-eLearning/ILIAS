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

use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

trait FileDataRCHandling
{
    /**
     * @param list<string> $path_to_files
     */
    protected function getCurrentCollection(
        array $path_to_files
    ): \ILIAS\ResourceStorage\Collection\ResourceCollection {
        $rcid = $this->fdm->createCollectionFromPaths($path_to_files);
        if ($rcid === null) {
            throw new Exception('Storing file into collection failed: no files found');
        }

        return $this->fdm->getCollection($rcid);
    }

    /**
     * @param array<string, mixed> $mail_data
     * @return list<string>
     */
    public function filesFromLegacyToIRSS(array $mail_data): array
    {
        $attachments = $mail_data['attachments'] ?? null;
        if (!$attachments instanceof MailAttachments || !$attachments->isLegacy()) {
            throw new InvalidArgumentException('Legacy mail attachments expected.');
        }

        $path_to_files = [];
        foreach ($attachments->legacyFilenames() as $file) {
            $path_to_files[] = $this->fdm->getAbsoluteAttachmentPoolPathByFilename($file);
        }
        $collection = $this->getCurrentCollection($path_to_files);

        return $this->fdm->getRidsFromCollection($collection->getIdentification());
    }

    /**
     * @param list<string> $filenames
     */
    public function getIdforCollection(array $filenames): ?ResourceCollectionIdentification
    {
        return $this->fdm->createCollectionFromPoolFilenames($filenames);
    }

    /**
     * @return list<string>
     */
    public function FilesFromIRSSToLegacy(ResourceCollectionIdentification $identification): array
    {
        return $this->fdm->getRidsFromCollection($identification);
    }

    /**
     * @param array<string, mixed> $attachments
     */
    protected function handleAttachments(array $attachments): ResourceCollectionIdentification
    {
        $resource_identifications = [];
        foreach ($attachments as $attachment) {
            $info = $this->upload_handler->getInfoResult($attachment);
            if ($info->getFileIdentifier() === 'unknown') {
                continue;
            }
            $found = $this->storage->manage()->find($attachment);
            if ($found === null) {
                throw new Exception("File '" . $info->getName() . "' could not be found in IRSS");
            }
            $resource_identifications[] = $found;
        }

        if ($resource_identifications === []) {
            throw new Exception('No attachments could be stored');
        }

        return $this->fdm->createCollectionFromResourceIdentifications($resource_identifications);
    }
}
