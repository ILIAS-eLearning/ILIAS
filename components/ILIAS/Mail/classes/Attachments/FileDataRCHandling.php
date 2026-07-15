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
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

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
     * @return list<string>
     */
    public function FilesFromIRSSToLegacy(ResourceCollectionIdentification $identification): array
    {
        return $this->fdm->getRidsFromCollection($identification);
    }

    /**
     * @param list<string> $form_attachment_rids
     */
    protected function attachmentsFromFormUpload(
        array $form_attachment_rids,
        ?MailAttachments $stage_attachments = null
    ): MailAttachments {
        if ($form_attachment_rids === []) {
            return $stage_attachments ?? MailAttachments::empty();
        }

        $resource_identifications = [];
        foreach ($form_attachment_rids as $attachment) {
            $found = $this->storage->manage()->find($attachment);
            if ($found === null) {
                continue;
            }
            $resource_identifications[] = $found;
        }

        if ($resource_identifications === []) {
            return $stage_attachments ?? MailAttachments::empty();
        }

        $stage_rcid = ($stage_attachments instanceof MailAttachments && $stage_attachments->isIrss())
            ? $stage_attachments->rcid()
            : null;

        if ($stage_rcid !== null && $this->fdm->collectionContainsResources($stage_rcid, $resource_identifications)) {
            return MailAttachments::fromIrss($stage_rcid);
        }

        return MailAttachments::fromIrss(
            $this->fdm->createCollectionFromResourceIdentifications($resource_identifications)
        );
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

    protected function stageAttachmentsFromMailAttachments(MailAttachments $attachments): MailAttachments
    {
        if ($attachments->isEmpty()) {
            return MailAttachments::empty();
        }

        if ($attachments->isIrss()) {
            $foreign = iterator_to_array(
                $this->fdm->getCollection($attachments->rcid())->getResourceIdentifications(),
                false
            );
            $cloned_rcid = $this->fdm->createCollectionFromForeignResources($foreign);

            return $cloned_rcid !== null
                ? MailAttachments::fromIrss($cloned_rcid)
                : MailAttachments::empty();
        }

        $rcid = $this->fdm->createCollectionFromPoolFilenames($attachments->legacyFilenames());

        return $rcid !== null
            ? MailAttachments::fromIrss($rcid)
            : MailAttachments::empty();
    }

    /**
     * @return list<string>
     */
    protected function formRidsFromMailAttachments(MailAttachments $attachments): array
    {
        if ($attachments->isIrss()) {
            return $this->FilesFromIRSSToLegacy($attachments->rcid());
        }

        return [];
    }
}
