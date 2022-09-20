<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Singleton class that stores all privacy settings
 * @author  Stefan Meyer <meyer@leifos.de>
 * @ingroup Services/PrivacySecurity
 */
class ilPrivacySettings
{
    private static ?ilPrivacySettings $instance = null;
    private ilDBInterface $db;
    private ilSetting $settings;
    private ilObjUser $user;

    private bool $export_course;
    private bool $export_group;
    private bool $export_learning_sequence = false;
    private bool $export_confirm_course = false;
    private bool $export_confirm_group = false;
    private bool $export_confirm_learning_sequence = false;

    private bool $participants_list_course_enabled = true;

    private bool $fora_statistics;
    private bool $anonymous_fora;
    private bool $rbac_log;
    private int $rbac_log_age;
    private bool $show_grp_access_times;
    private bool $show_crs_access_times;
    private bool $show_lso_access_times;
    private int $ref_id;
    private int $sahs_protocol_data;
    private bool $export_scorm;
    private bool $comments_export;

    /**
     * Private constructor: use _getInstance()
     * @access private
     * @param
     */
    private function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->db = $DIC->database();
        $this->user = $DIC->user();

        $this->read();
    }

    public static function getInstance(): ilPrivacySettings
    {
        if (!self::$instance instanceof ilPrivacySettings) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPrivacySettingsRefId(): int
    {
        return $this->ref_id;
    }

    public function enabledCourseExport(): bool
    {
        return $this->export_course;
    }

    public function enabledGroupExport(): bool
    {
        return $this->export_group;
    }

    public function enabledLearningSequenceExport(): bool
    {
        return $this->export_learning_sequence;
    }

    public function participantsListInCoursesEnabled(): bool
    {
        return $this->participants_list_course_enabled;
    }

    public function enableParticipantsListInCourses(bool $a_status): void
    {
        $this->participants_list_course_enabled = $a_status;
    }

    /**
     * Check if a user has the permission to access approved user profile fields, course related user data and custom user data
     */
    public function checkExportAccess(int $a_ref_id, int $a_user_id = 0): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $rbacsystem = $DIC->rbac()->system();

        $user_id = $a_user_id ?: $this->user->getId();
        if (ilObject::_lookupType($a_ref_id, true) == 'crs') {
            return $this->enabledCourseExport() and $ilAccess->checkAccessOfUser(
                $user_id,
                'manage_members',
                '',
                $a_ref_id
            ) and $rbacsystem->checkAccessOfUser(
                $user_id,
                'export_member_data',
                $this->getPrivacySettingsRefId()
            );
        } elseif (ilObject::_lookupType($a_ref_id, true) == 'grp') {
            return $this->enabledGroupExport() and $ilAccess->checkAccessOfUser(
                $user_id,
                'manage_members',
                '',
                $a_ref_id
            ) and $rbacsystem->checkAccessOfUser(
                $user_id,
                'export_member_data',
                $this->getPrivacySettingsRefId()
            );
        } elseif (ilObject::_lookupType($a_ref_id, true) == 'lso') {
            return $this->enabledLearningSequenceExport() and $ilAccess->checkAccessOfUser(
                $user_id,
                'manage_members',
                '',
                $a_ref_id
            ) and $rbacsystem->checkAccessOfUser(
                $user_id,
                'export_member_data',
                $this->getPrivacySettingsRefId()
            );
        }
        return false;
    }

    public function enableCourseExport(bool $a_status): void
    {
        $this->export_course = $a_status;
    }

    public function enableGroupExport(bool $a_status): void
    {
        $this->export_group = $a_status;
    }

    public function enableLearningSequenceExport(bool $a_status): void
    {
        $this->export_learning_sequence = $a_status;
    }

    /**
     * write access to property fora statitics
     */
    public function enableForaStatistics(bool $a_status): void
    {
        $this->fora_statistics = $a_status;
    }

    /**
     * read access to property enable fora statistics
     */
    public function enabledForaStatistics(): bool
    {
        return $this->fora_statistics;
    }

    /**
     * write access to property anonymous fora
     */
    public function enableAnonymousFora(bool $a_status): void
    {
        $this->anonymous_fora = $a_status;
    }

    /**
     * read access to property enable anonymous fora
     */
    public function enabledAnonymousFora(): bool
    {
        return $this->anonymous_fora;
    }

    /**
     * write access to property rbac_log
     */
    public function enableRbacLog(bool $a_status): void
    {
        $this->rbac_log = $a_status;
    }

    /**
     * read access to property enable rbac log
     */
    public function enabledRbacLog(): bool
    {
        return $this->rbac_log;
    }

    /**
     * write access to property rbac log age
     */
    public function setRbacLogAge(int $a_age): void
    {
        $this->rbac_log_age = $a_age;
    }

    /**
     * read access to property rbac log age
     */
    public function getRbacLogAge(): int
    {
        return $this->rbac_log_age;
    }

    public function confirmationRequired(string $a_type): bool
    {
        switch ($a_type) {
            case 'crs':
                return $this->courseConfirmationRequired();

            case 'grp':
                return $this->groupConfirmationRequired();

            case 'lso':
                return $this->learningSequenceConfirmationRequired();
        }
        return false;
    }

    public function courseConfirmationRequired(): bool
    {
        return $this->export_confirm_course;
    }

    public function groupConfirmationRequired(): bool
    {
        return $this->export_confirm_group;
    }

    public function learningSequenceConfirmationRequired(): bool
    {
        return $this->export_confirm_learning_sequence;
    }

    public function setCourseConfirmationRequired(bool $a_status): void
    {
        $this->export_confirm_course = $a_status;
    }

    public function setGroupConfirmationRequired(bool $a_status): void
    {
        $this->export_confirm_group = (bool) $a_status;
    }

    public function setLearningSequenceConfirmationRequired(bool $a_status): void
    {
        $this->export_confirm_learning_sequence = $a_status;
    }

    /**
     * Show group last access times
     */
    public function showGroupAccessTimes(bool $a_status): void
    {
        $this->show_grp_access_times = $a_status;
    }

    /**
     * check if group access time are visible
     */
    public function enabledGroupAccessTimes(): bool
    {
        return $this->show_grp_access_times;
    }

    /**
     * show course access times
     */
    public function showCourseAccessTimes(bool $a_status): void
    {
        $this->show_crs_access_times = $a_status;
    }

    /**
     * check if access time are enabled in lso
     */
    public function enabledLearningSequenceAccessTimes(): bool
    {
        return $this->show_lso_access_times;
    }

    /**
     * show lso access times
     */
    public function showLearningSequenceAccessTimes(bool $a_status): void
    {
        $this->show_lso_access_times = $a_status;
    }

    /**
     * check if access time are enabled in courses
     */
    public function enabledCourseAccessTimes(): bool
    {
        return $this->show_crs_access_times;
    }

    public function enabledAccessTimesByType(string $a_obj_type): bool
    {
        switch ($a_obj_type) {
            case 'crs':
                return $this->enabledCourseAccessTimes();

            case 'grp':
                return $this->enabledGroupAccessTimes();

            case 'lso':
                return $this->enabledLearningSequenceAccessTimes();
        }
        return false;
    }

    /**
     * Save settings
     */
    public function save(): void
    {
        $this->settings->set('ps_export_confirm', (string) $this->courseConfirmationRequired());
        $this->settings->set('ps_export_confirm_group', (string) $this->groupConfirmationRequired());
        $this->settings->set(
            'ps_export_confirm_learning_sequence',
            (string) $this->learningSequenceConfirmationRequired()
        );
        $this->settings->set('ps_export_course', (string) $this->enabledCourseExport());
        $this->settings->set('ps_export_group', (string) $this->enabledGroupExport());
        $this->settings->set('ps_export_learning_sequence', (string) $this->enabledLearningSequenceExport());
        $this->settings->set('enable_fora_statistics', (string) $this->enabledForaStatistics());
        $this->settings->set('enable_anonymous_fora', (string) $this->enabledAnonymousFora());
        $this->settings->set('ps_access_times', (string) $this->enabledGroupAccessTimes());
        $this->settings->set('ps_crs_access_times', (string) $this->enabledCourseAccessTimes());
        $this->settings->set('rbac_log', (string) $this->enabledRbacLog());
        $this->settings->set('rbac_log_age', (string) $this->getRbacLogAge());
        $this->settings->set('enable_sahs_pd', (string) $this->enabledSahsProtocolData());
        $this->settings->set('ps_export_scorm', (string) $this->enabledExportSCORM());

        $this->settings->set('participants_list_courses', (string) $this->participantsListInCoursesEnabled());
        $this->settings->set('comments_export', (string) $this->enabledCommentsExport());
    }

    /**
     * read settings
     */
    private function read(): void
    {
        $query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data " .
            "WHERE tree.parent = " . $this->db->quote(SYSTEM_FOLDER_ID, 'integer') . " " .
            "AND object_data.type = 'ps' " .
            "AND object_reference.ref_id = tree.child " .
            "AND object_reference.obj_id = object_data.obj_id";
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $this->ref_id = (int) ($row["ref_id"] ?? 0);

        $this->export_course = (bool) $this->settings->get('ps_export_course', null);
        $this->export_group = (bool) $this->settings->get('ps_export_group', null);
        $this->export_learning_sequence = (bool) $this->settings->get('ps_export_learning_sequence', null);
        $this->export_confirm_course = (bool) $this->settings->get('ps_export_confirm', null);
        $this->export_confirm_group = (bool) $this->settings->get('ps_export_confirm_group', null);
        $this->export_confirm_learning_sequence = (bool) $this->settings->get('ps_export_confirm_learning_sequence', null);
        $this->fora_statistics = (bool) $this->settings->get('enable_fora_statistics', null);
        $this->anonymous_fora = (bool) $this->settings->get('enable_anonymous_fora', null);
        $this->show_grp_access_times = (bool) $this->settings->get('ps_access_times', null);
        $this->show_crs_access_times = (bool) $this->settings->get('ps_crs_access_times', null);
        $this->show_lso_access_times = (bool) $this->settings->get('ps_lso_access_times', null);
        $this->rbac_log = (bool) $this->settings->get('rbac_log', null);
        $this->rbac_log_age = (int) $this->settings->get('rbac_log_age', "6");
        $this->sahs_protocol_data = (int) $this->settings->get('enable_sahs_pd', "0");
        $this->export_scorm = (bool) $this->settings->get('ps_export_scorm', null);
        $this->enableParticipantsListInCourses((bool) $this->settings->get(
            'participants_list_courses',
            (string) $this->participantsListInCoursesEnabled()
        ));
        $this->enableCommentsExport((bool) $this->settings->get('comments_export', null));
    }

    /**
     * validate settings
     * @return int 0, if everything is ok, an error code otherwise
     */
    public function validate(): int
    {
        return 0;
    }

    public function enabledSahsProtocolData(): int
    {
        return (int) $this->sahs_protocol_data;
    }

    public function enableSahsProtocolData(int $status): void
    {
        $this->sahs_protocol_data = (int) $status;
    }

    // show and export protocol data with name
    public function enabledExportSCORM(): bool
    {
        return $this->export_scorm;
    }

    public function enableExportSCORM(int $a_status): void
    {
        $this->export_scorm = (bool) $a_status;
    }

    /**
     * Enable comments export
     */
    public function enableCommentsExport(bool $a_status): void
    {
        $this->comments_export = (bool) $a_status;
    }

    /**
     * Enable comments export
     * @return bool
     */
    public function enabledCommentsExport(): bool
    {
        return $this->comments_export;
    }
}
