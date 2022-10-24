<?php

declare(strict_types=0);

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

class ilTimingsCronReminder extends ilCronJob
{
    private static array $objects_information;

    private array $users_with_exceeded_timings;
    private array $users;
    private int $now;

    protected ilLogger $log;
    protected ilLanguage $lng;
    protected ilLanguage $user_lang;
    protected ilDBInterface $db;
    protected ilObjectDataCache $obj_data_cache;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->log = $DIC->logger()->crs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');
        $this->db = $DIC->database();
        $this->obj_data_cache = $DIC['ilObjDataCache'];

        self::$objects_information = array();
        $this->users_with_exceeded_timings = array();
        $this->users = array();
        $this->now = time();
    }

    public function getId(): string
    {
        return 'crs_timings_reminder';
    }

    public function getTitle(): string
    {
        return $this->lng->txt('timings_reminder_notifications');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('timings_reminder_notifications_info');
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function hasCustomSettings(): bool
    {
        return false;
    }

    public function run(): ilCronJobResult
    {
        $this->log->debug('Start.');

        $result = new ilCronJobResult();

        $this->gatherUsers();
        $this->gatherUsersWithExceededTimings();
        $this->getNewExceededObjectForUser();
        $this->getFreshlyStartedObjectsForUser();

        $result->setStatus(ilCronJobResult::STATUS_OK);

        $this->log->debug('End');

        return $result;
    }

    /**
     * Read all active users
     */
    protected function gatherUsers(): void
    {
        $now = time();
        $query = $this->db->queryF(
            'SELECT usr_id FROM usr_data WHERE 
									(active = 1 AND time_limit_unlimited = 1) OR 
									(active = 1 AND time_limit_unlimited = 0 AND time_limit_from < %s AND time_limit_until > %s)',
            array('integer', 'integer'),
            array($now, $now)
        );
        while ($row = $this->db->fetchAssoc($query)) {
            $usr_id = (int) $row['usr_id'];
            $this->users[$usr_id] = $usr_id;
        }
        $this->log->debug('Found ' . count($this->users) . ' users.');
    }

    protected function gatherUsersWithExceededTimings(): void
    {
        $this->users_with_exceeded_timings = ilTimingsUser::lookupTimingsExceededByUser($this->users);
        $this->log->debug('Found ' . count($this->users_with_exceeded_timings) . ' users with exceeded timings.');
    }

    protected function getNewExceededObjectForUser(): void
    {
        $users_with_exceeded_objects = array();

        if (is_array($this->users_with_exceeded_timings) && $this->users_with_exceeded_timings !== []) {
            foreach ($this->users_with_exceeded_timings as $key => $user_id) {
                $objects = $this->getExceededObjectsForUser($user_id);
                if (is_array($objects) && $objects !== []) {
                    $obj_data = array();
                    $already_notified = $this->getAlreadySentNotifications($user_id);
                    $objects = array_diff_key($objects, $already_notified);
                    foreach (array_keys($objects) as $ref_id) {
                        $detail_data = $this->getInformationForRefId($ref_id);
                        $obj_data[$ref_id] = $detail_data;
                    }
                    if ($obj_data !== []) {
                        $users_with_exceeded_objects[$user_id] = $obj_data;
                    }
                }
            }
            $this->log->debug('Found ' . count($users_with_exceeded_objects) . ' users with new exceeded timings.');

            $this->buildExceededMails($users_with_exceeded_objects);
        }
    }

    protected function getFreshlyStartedObjectsForUser(): void
    {
        $users_with_new_started_object = array();

        if (is_array($this->users) && $this->users !== []) {
            foreach ($this->users as $key => $user_id) {
                $objects = $this->getObjectsWithTimingsForUser($user_id);
                if (is_array($objects) && $objects !== []) {
                    $obj_data = array();
                    $already_notified = $this->getAlreadySentNotifications($user_id, false);
                    $this->log->debug('User_id ' . $user_id . ' was already notified for ' . count($already_notified) . ' elements ');
                    $objects = array_diff_key($objects, $already_notified);
                    foreach ($objects as $ref_id => $v) {
                        $obj_data[$ref_id] = $this->getInformationForRefId($ref_id);

                        if (is_array($v)) {
                            if ((isset($v['end']) && isset($v['start'])) && $v['end'] > $this->now) {
                                if ($v['start'] < $this->now) {
                                    $users_with_new_started_object[$user_id][$ref_id] = $obj_data[$ref_id];
                                }
                            } else {
                                $this->log->debug('End is already older than today no notification send for user_id ' . $user_id . ' on ref_id ' . $ref_id);
                            }
                        }
                    }
                }
            }
            $this->log->debug('Found ' . count($users_with_new_started_object) . ' users with freshly started timings.');

            $this->buildFreshlyStartedMails($users_with_new_started_object);
        }
    }

    protected function buildExceededMails(array $users_with_exceeded_objects): void
    {
        $this->log->debug('Start.');
        if (is_array($users_with_exceeded_objects)) {
            $this->log->debug('...found ' . count($users_with_exceeded_objects));
            foreach ($users_with_exceeded_objects as $user_id => $exceeded_objects) {
                $tpl = $this->buildTopMailBody($user_id, 'timings_cron_reminder_exceeded_start');
                $has_exceeded = $this->fillObjectListForMailBody($exceeded_objects, $tpl);

                if ($has_exceeded) {
                    $this->sendExceededMail($user_id, $exceeded_objects, $tpl->get());
                    $this->log->debug('start sending exceeded mail to user: ' . $user_id);
                }
            }
        } else {
            $this->log->warning('no array given.');
        }

        $this->log->debug('end.');
    }

    protected function buildFreshlyStartedMails(array $users_with_freshly_started_objects): void
    {
        $this->log->debug('start.');
        if (is_array($users_with_freshly_started_objects)) {
            $this->log->debug('...found ' . count($users_with_freshly_started_objects));
            foreach ($users_with_freshly_started_objects as $user_id => $freshly_started_objects) {
                $tpl = $this->buildTopMailBody($user_id, 'timings_cron_reminder_freshly_start');
                $has_freshly_started = $this->fillObjectListForMailBody($freshly_started_objects, $tpl);

                if ($has_freshly_started) {
                    $this->sendFreshlyStartedMail($user_id, $freshly_started_objects, $tpl->get());
                }
            }
        } else {
            $this->log->debug('no array given.');
        }

        $this->log->debug('end.');
    }

    protected function buildTopMailBody(int $user_id, string $language_variable): ilTemplate
    {
        $this->log->debug('start...');
        $tpl = new ilTemplate('tpl.crs_timings_cron_reminder_mail.html', true, true, 'Modules/Course');

        $this->getUserLanguage($user_id);
        $this->buildMailSalutation($user_id, $tpl);
        $tpl->setVariable('START_BODY', $this->user_lang->txt($language_variable));
        $this->log->debug('for user: ' . $user_id . ' end.');
        return $tpl;
    }

    protected function fillObjectListForMailBody(array $objects, ilTemplate $tpl): bool
    {
        $has_elements = false;
        foreach ($objects as $object_id => $object_details) {
            if ($object_details['type'] == 'fold') {
                $tpl->setCurrentBlock('items');
                $tpl->setVariable('HREF', $object_details['url']);
                $tpl->setVariable('ITEM_TITLE', $object_details['title']);
                $tpl->parseCurrentBlock();
                $has_elements = true;
            }
        }
        $tpl->setVariable('INSTALLATION_SIGNATURE', \ilMail::_getInstallationSignature());
        $this->log->debug('found elements: ' . $has_elements);
        return $has_elements;
    }

    protected function getUserLanguage(int $user_id): void
    {
        $this->log->debug('start...');
        $this->user_lang = ilLanguageFactory::_getLanguageOfUser($user_id);
        $this->user_lang->loadLanguageModule('crs');
        $this->user_lang->loadLanguageModule('mail');
        $this->log->debug('user language for user ' . $user_id . ' is ' . $this->user_lang->getLangKey() . ' end.');
    }

    protected function buildMailSalutation(int $user_id, ilTemplate $tpl): void
    {
        $name = ilObjUser::_lookupName($user_id);
        if (is_array($name)) {
            $salutation = $this->user_lang->txt('mail_salutation_n') . ' ';
            if (($name['gender'] ?? "") != '') {
                $salutation .= $this->user_lang->txt('salutation_' . $name['gender']) . ' ';
            }
            if ($name['title'] != '') {
                $salutation .= $name['title'] . ' ';
            }
            $tpl->setVariable('SALUTATION', $salutation);
            $tpl->setVariable('FIRSTNAME', $name['firstname']);
            $tpl->setVariable('LASTNAME', $name['lastname']);
            $this->log->debug('Salutation: ' . $salutation . ' Firstname: ' . $name['firstname'] . ' Lastname: ' . $name['lastname']);
        } else {
            $this->log->debug('did not get an array from _lookupName.');
        }
    }

    protected function sendExceededMail(int $user_id, array $ref_ids, string $mail_body): void
    {
        $login = \ilObjUser::_lookupLogin($user_id);
        if ($login != '') {
            $mail = new ilMail(ANONYMOUS_USER_ID);
            if ($this->hasUserActivatedNotification($user_id)) {
                $mail->enqueue(
                    $login,
                    '',
                    '',
                    $this->user_lang->txt('timings_cron_reminder_exceeded_subject'),
                    $mail_body,
                    [],
                    true
                );
                $this->log->debug('...mail send for user ' . $user_id . ' to mail ' . $login . ' has exceeded timings for ' . $mail_body);
                $this->markExceededInDatabase($user_id, $ref_ids);
            } else {
                $this->log->debug('... no mail was sent because user ' . $user_id . ' has deactivated their notifications and has no coaches assigned.');
            }
        } else {
            $this->log->debug('Not send. User ' . $user_id . ' has no email.');
        }
    }

    protected function sendFreshlyStartedMail(int $user_id, array $ref_ids, string $mail_body): void
    {
        $login = \ilObjUser::_lookupLogin($user_id);

        if ($login != '' && $this->hasUserActivatedNotification($user_id)) {
            $mail = new ilMail(ANONYMOUS_USER_ID);
            $mail->enqueue(
                $login,
                '',
                '',
                $this->user_lang->txt('timings_cron_reminder_started_subject'),
                $mail_body,
                [],
                true
            );
            $this->log->debug('...mail send for user ' . $user_id . ' to mail ' . $login . ' has freshly started timings for ' . $mail_body);
            $this->markFreshlyStartedInDatabase($user_id, $ref_ids);
        } else {
            $this->log->debug('Not send. User ' . $user_id . ' has no email.');
        }
    }

    protected function markExceededInDatabase(int $user_id, array $ref_ids): void
    {
        foreach (array_keys($ref_ids) as $ref_id) {
            $this->db->manipulateF(
                'INSERT INTO ' . ilCourseConstants::CRON_TIMINGS_EXCEEDED_TABLE . ' (user_id, ref_id, sent) VALUES ' .
                ' (%s,%s,%s)',
                array('integer', 'integer', 'integer'),
                array($user_id, $ref_id, $this->now)
            );

            $this->log->debug('ilTimingsCronReminder->markExceededInDatabase: Marked exceeded in Database. User ' . $user_id . ' ref_id ' . $ref_id);
        }
    }

    protected function markFreshlyStartedInDatabase(int $user_id, array $ref_ids): void
    {
        foreach (array_keys($ref_ids) as $ref_id) {
            $this->db->manipulateF(
                'INSERT INTO ' . ilCourseConstants::CRON_TIMINGS_STARTED_TABLE . ' (user_id, ref_id, sent) VALUES ' .
                ' (%s,%s,%s)',
                array('integer', 'integer', 'integer'),
                array($user_id, $ref_id, $this->now)
            );

            $this->log->debug('ilTimingsCronReminder->markFreshlyStartedInDatabase: Marked freshly started in Database. User ' . $user_id . ' ref_id ' . $ref_id);
        }
    }

    protected function getAlreadySentNotifications(int $user_id, bool $for_exceeded = true): array
    {
        $ref_ids = array();
        $table = ilCourseConstants::CRON_TIMINGS_EXCEEDED_TABLE;

        if (!$for_exceeded) {
            $table = ilCourseConstants::CRON_TIMINGS_STARTED_TABLE;
        }

        $result = $this->db->queryF(
            'SELECT * FROM ' . $table . ' WHERE ' .
            'user_id = %s',
            array('integer'),
            array($user_id)
        );

        while ($record = $this->db->fetchAssoc($result)) {
            $ref_ids[$record['ref_id']] = $record['ref_id'];
        }
        return $ref_ids;
    }

    protected function getInformationForRefId(int $ref_id): array
    {
        if (!array_key_exists($ref_id, self::$objects_information)) {
            $obj_id = $this->obj_data_cache->lookupObjId($ref_id);
            $type = $this->obj_data_cache->lookupType($obj_id);
            $value = array('title' => $this->obj_data_cache->lookupTitle($obj_id),
                           'type' => $type,
                           'url' => ilLink::_getLink($ref_id, $type),
                           'obj_id' => $obj_id
            );
            self::$objects_information[$ref_id] = $value;

            $this->log->debug('ilTimingsCronReminder->getInformationForRefId: ...cached object information for => ' . $value['type'] . ' => ' . $value['title']);
        }
        return self::$objects_information[$ref_id];
    }

    protected function getExceededObjectsForUser(int $user_id): array
    {
        $tmp = [];
        return ilTimingsUser::lookupTimings(array($user_id), $tmp, true);
    }

    protected function getObjectsWithTimingsForUser(int $user_id): array
    {
        $meta = array();
        $timings_obj_list = ilTimingsUser::lookupTimings(array($user_id), $meta, false);
        return $meta[$user_id];
    }

    protected function hasUserActivatedNotification(int $user_id): bool
    {
        return true;
    }
}
