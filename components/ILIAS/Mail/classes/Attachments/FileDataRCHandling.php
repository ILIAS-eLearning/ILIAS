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
     * @return list<string>
     */
    public function FilesFromIRSSToLegacy(ResourceCollectionIdentification $identification): array
    {
        return $this->fdm->getRidsFromCollection($identification);
    }

    /**
     * @param list<string> $form_attachment_rids
     * @throws ilMailAttachmentsTotalSizeLimitExceededException
     */
    protected function attachmentsFromFormUpload(
        array $form_attachment_rids,
        ?MailAttachments $stage_attachments = null
    ): MailAttachments {
        // The submitted form is the single source of truth: no files in the form means no attachments
        if ($form_attachment_rids === []) {
            return MailAttachments::empty();
        }

        $limit = $this->fdm->getAttachmentsTotalSizeLimit();
        $total_size = 0;
        foreach ($form_attachment_rids as $attachment) {
            $info = $this->upload_handler->getInfoResult($attachment);
            if ($info->getFileIdentifier() !== 'unknown') {
                $total_size += $info->getSize();
            }
        }
        if ($limit !== null && $total_size > $limit) {
            throw new ilMailAttachmentsTotalSizeLimitExceededException(
                $this->lng->txt('mail_max_size_attachments_total_error') . ' ' . ilUtil::formatSize((int) $limit)
            );
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
            return MailAttachments::empty();
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
     * @throws ilMailAttachmentsTotalSizeLimitExceededException
     */
    protected function handleAttachments(array $attachments): ResourceCollectionIdentification
    {
        $limit = $this->fdm->getAttachmentsTotalSizeLimit();
        $total_size = 0;
        foreach ($attachments as $attachment) {
            $info = $this->upload_handler->getInfoResult($attachment);
            if ($info->getFileIdentifier() !== 'unknown') {
                $total_size += $info->getSize();
            }
        }
        if ($limit !== null && $total_size > $limit) {
            throw new ilMailAttachmentsTotalSizeLimitExceededException(
                $this->lng->txt('mail_max_size_attachments_total_error') . ' ' . ilUtil::formatSize((int) $limit)
            );
        }

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
            $stage_rcid = $this->fdm->createCollectionReferencingResourcesOf($attachments->rcid());

            return $stage_rcid !== null
                ? MailAttachments::fromIrss($stage_rcid)
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
