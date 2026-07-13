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

namespace ILIAS\User\Settings\NewAccountMail;

use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class MailImplementation implements Mail
{
    private string $temp_file_path;
    public function __construct(
        private readonly string $lang_code,
        private readonly string $subject = '',
        private readonly string $body = '',
        private readonly string $salutation_none_specific = '',
        private readonly string $salutation_male = '',
        private readonly string $salutation_female = '',
        private readonly ?string $attachment_rid = null,
        private readonly ?string $legacy_attachment_filename = null
    ) {
        $this->temp_file_path = CLIENT_DATA_DIR . '/temp/namas/';
    }

    public function getLangCode(): string
    {
        return $this->lang_code;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getSalutationNoneSpecific(): string
    {
        return $this->salutation_none_specific;
    }

    public function getSalutationMale(): string
    {
        return $this->salutation_male;
    }

    public function getSalutationFemale(): string
    {
        return $this->salutation_female;
    }

    public function getAttachmentRid(): ?string
    {
        return $this->attachment_rid;
    }

    public function getAttachment(ResourceStorage $irss): ?MailAttachment
    {
        if ($this->attachment_rid !== null) {
            $rid = $irss->manage()->find($this->attachment_rid);
            if ($rid === null) {
                return null;
            }
            $this->ensureAttachmentFileExists($irss, $rid);
            return new MailAttachment(
                $this->temp_file_path . $this->lang_code,
                $irss->manage()->getCurrentRevision($rid)->getTitle()
            );
        }

        if ($this->legacy_attachment_filename !== null) {
            $path = '/' . implode(
                '/',
                array_map(
                    static fn(string $path_part): string => trim($path_part, '/'),
                    [
                        CLIENT_DATA_DIR . '/ilReg/reg_' . USER_FOLDER_ID,
                        $this->lang_code,
                    ]
                )
            );

            return new MailAttachment($path, $this->legacy_attachment_filename);
        }

        return null;
    }

    public function deleteAttachmentTempFile(): void
    {
        if (file_exists($this->temp_file_path . $this->lang_code)) {
            unlink($this->temp_file_path . $this->lang_code);
        }
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public function toStorage(): array
    {
        return [
            'subject' => [\ilDBConstants::T_TEXT, $this->subject],
            'body' => [\ilDBConstants::T_TEXT, $this->body],
            'sal_f' => [\ilDBConstants::T_TEXT, $this->salutation_female],
            'sal_m' => [\ilDBConstants::T_TEXT, $this->salutation_male],
            'sal_g' => [\ilDBConstants::T_TEXT, $this->salutation_none_specific],
            'att_rid' => [\ilDBConstants::T_TEXT, $this->attachment_rid]
        ];
    }

    private function ensureAttachmentFileExists(
        ResourceStorage $irss,
        ResourceIdentification $rid
    ): void {
        if (file_exists($this->temp_file_path . $this->lang_code)) {
            return;
        }

        if (!file_exists($this->temp_file_path)) {
            mkdir($this->temp_file_path);
        }

        file_put_contents(
            $this->temp_file_path . $this->lang_code,
            $irss->consume()->stream($rid)->getStream()->getContents()
        );
    }
}
