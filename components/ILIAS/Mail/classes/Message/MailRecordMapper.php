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

namespace ILIAS\Mail\Message;

use DateTimeImmutable;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

final readonly class MailRecordMapper
{
    /**
     * @param array<string, mixed>|null $row
     */
    public function fromRow(?array $row): ?MailRecordData
    {
        $row = $this->normalizeRow($row);
        if ($row === null) {
            return null;
        }

        $tpl_ctx_params = $row['tpl_ctx_params'] ?? null;
        if (is_array($tpl_ctx_params)) {
            $tpl_ctx_params = json_encode($tpl_ctx_params, JSON_THROW_ON_ERROR);
        } elseif ($tpl_ctx_params !== null) {
            $tpl_ctx_params = (string) $tpl_ctx_params;
        }

        return new MailRecordData(
            (int) $row['mail_id'],
            (int) $row['user_id'],
            (int) ($row['folder_id'] ?? 0),
            isset($row['sender_id']) ? (int) $row['sender_id'] : null,
            isset($row['send_time']) ? new DateTimeImmutable((string) $row['send_time']) : null,
            isset($row['m_status']) ? (string) $row['m_status'] : null,
            (string) $row['m_subject'],
            isset($row['import_name']) ? (string) $row['import_name'] : null,
            (bool) ($row['use_placeholders'] ?? false),
            (string) $row['m_message'],
            (string) $row['rcp_to'],
            (string) $row['rcp_cc'],
            (string) $row['rcp_bcc'],
            $row['attachments'],
            isset($row['tpl_ctx_id']) ? (string) $row['tpl_ctx_id'] : null,
            $tpl_ctx_params
        );
    }

    /**
     * @param  array<string, mixed>|null $row
     * @return array<string, mixed>|null
     */
    public function normalizeRow(?array $row): ?array
    {
        if (!is_array($row) || $row === []) {
            return null;
        }

        if (isset($row['attachments']) && is_string($row['attachments']) && str_contains($row['attachments'], '{')) {
            $unserialized_attachments = unserialize($row['attachments'], ['allowed_classes' => false]);
            $row['attachments'] = is_array($unserialized_attachments) ? $unserialized_attachments : null;
        } elseif (isset($row['attachments']) && is_string($row['attachments']) && $row['attachments'] !== '') {
            $row['attachments'] = new ResourceCollectionIdentification($row['attachments']);
        } else {
            $row['attachments'] = null;
        }

        if (isset($row['tpl_ctx_params']) && is_string($row['tpl_ctx_params'])) {
            $decoded = json_decode($row['tpl_ctx_params'], true, 512, JSON_THROW_ON_ERROR);
            $row['tpl_ctx_params'] = (array) ($decoded ?? []);
        } else {
            $row['tpl_ctx_params'] = [];
        }

        if (isset($row['mail_id'])) {
            $row['mail_id'] = (int) $row['mail_id'];
        }

        if (isset($row['user_id'])) {
            $row['user_id'] = (int) $row['user_id'];
        }

        if (isset($row['folder_id'])) {
            $row['folder_id'] = (int) $row['folder_id'];
        }

        if (isset($row['sender_id'])) {
            $row['sender_id'] = (int) $row['sender_id'];
        }

        if (isset($row['use_placeholders'])) {
            $row['use_placeholders'] = (bool) $row['use_placeholders'];
        }

        $null_to_string_properties = ['m_subject', 'm_message', 'rcp_to', 'rcp_cc', 'rcp_bcc'];
        foreach ($null_to_string_properties as $null_to_string_property) {
            if (!isset($row[$null_to_string_property])) {
                $row[$null_to_string_property] = '';
            }
        }

        return $row;
    }
}
