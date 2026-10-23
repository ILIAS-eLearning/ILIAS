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
     * @return list<string>
     */
    public function FilesFromIRSSToLegacy(ResourceCollectionIdentification $identification): array
    {
        return $this->fdm->getRidsFromCollection($identification);
    }

    /**
     * Uploaded files are already part of the compose stage. The stage is reduced to the files
     * of the submitted form, no files in the form means no attachments.
     *
     * @param list<string> $form_attachment_rids
     * @throws ilMailAttachmentsTotalSizeLimitExceededException
     */
    protected function attachmentsFromFormUpload(array $form_attachment_rids): MailAttachments
    {
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

        $stage_rcid = $this->fdm->restrictStageTo($form_attachment_rids);

        return $stage_rcid !== null
            ? MailAttachments::fromIrss($stage_rcid)
            : MailAttachments::empty();
    }

    /**
     * Drafts, scheduled and forwarded mails keep their collection, the compose stage works on a copy
     */
    protected function stageAttachmentsFromMailAttachments(MailAttachments $attachments): MailAttachments
    {
        if ($attachments->isEmpty()) {
            return MailAttachments::empty();
        }

        if ($attachments->isIrss()) {
            $stage_rcid = $this->fdm->copyCollection($attachments->rcid());

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
