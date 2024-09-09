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

namespace ILIAS\Mail\Folder;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ilUIFilterService;
use ilLanguage;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Filter input for mail folders
 */
class MailFilterUI
{
    private FilterComponent $filter;
    private ?string $sender;

    public function __construct(
        private readonly string $target_url,
        private readonly bool $lucene_enabled,
        private readonly MailFolderData $folder,
        private readonly Factory $ui_factory,
        private readonly ilUIFilterService $filter_service,
        private readonly ilLanguage $lng,
        private readonly DateTimeZone $user_time_zone
    ) {
        $inputs = [];
        if ($this->folder->hasIncomingMails()) {
            $inputs['sender'] = $this->ui_factory->input()->field()->text($this->lng->txt('mail_filter_sender'));
        } else {
            $inputs['recipients'] = $this->ui_factory->input()->field()->text($this->lng->txt('mail_filter_recipients'));
        }

        $inputs['subject'] = $this->ui_factory->input()->field()->text($this->lng->txt('mail_filter_subject'));
        $inputs['body'] = $this->ui_factory->input()->field()->text($this->lng->txt('mail_filter_body'));

        if ($this->lucene_enabled) {
            $inputs['attachment'] = $this->ui_factory->input()->field()->text($this->lng->txt('mail_filter_attach'));
        }

        $inputs['display'] = $this->ui_factory->input()->field()->multiSelect($this->lng->txt('mail_filter_display'), [
            'read' => $this->lng->txt('mail_filter_show_read'),
            'unread' => $this->lng->txt('mail_filter_show_unread'),
            'user' => $this->lng->txt('mail_filter_show_user_mails'),
            'system' => $this->lng->txt('mail_filter_show_system_mails'),
            'with_attachment' => $this->lng->txt('mail_filter_show_with_attachments'),
            'without_attachment' => $this->lng->txt('mail_filter_show_without_attachment')
        ]);
        $inputs['period'] = $this->ui_factory->input()->field()->duration($this->lng->txt('mail_filter_period'))
                                                               ->withTimezone($this->user_time_zone->getName());

        $this->filter = $this->filter_service->standard(
            self::class,
            $this->target_url,
            //elements
            $inputs,
            // initially rendered
            array_map(fn($value) => true, $inputs),
            false,
            false
        );
    }

    /**
     * Get the filter UI component
     */
    public function getComponent(): FilterComponent
    {
        return $this->filter;
    }

    /**
     * Get the user entered filter data
     */
    public function getData(): MailFilterData
    {
        $data = $this->filter_service->getData($this->filter);

        $is_unread = null;
        $is_system = null;
        $has_attachment = null;

        // contrary filter options are only applied, if only one of them is set
        if (is_array($display = $data['display'] ?? null)) {
            if (in_array('read', $display) xor in_array('unread', $display)) {
                $is_unread = in_array('unread', $display);
            }
            if (in_array('system', $display) xor in_array('user', $display)) {
                $is_system = in_array('system', $display);
            }
            if (in_array('with_attachment', $display) xor in_array('without_attachment', $display)) {
                $has_attachment = in_array('with_attachment', $display);
            }
        }

        $start = null;
        if (!empty($data['period'][0])) {
            $start = new DateTimeImmutable($data['period'][0] . ' 00:00:00', $this->user_time_zone);
        }
        $end = null;
        if (!empty($data['period'][1])) {
            $end = new DateTimeImmutable($data['period'][1] . ' 23:59:59', $this->user_time_zone);
        }

        return new MailFilterData(
            empty($data['sender']) ? null : (string) $data['sender'],
            empty($data['recipients']) ? null : (string) $data['recipients'],
            empty($data['subject']) ? null : (string) $data['subject'],
            empty($data['body']) ? null : $data['body'],
            empty($data['attachment']) ? null : (string) $data['attachment'],
            $start,
            $end,
            $is_unread,
            $is_system,
            $has_attachment
        );
    }
}
