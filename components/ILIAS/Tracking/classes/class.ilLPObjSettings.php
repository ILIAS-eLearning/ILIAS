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

use ILIAS\Tracking\Factory as TrackingFactory;
use ILIAS\Tracking\FactoryInterface as TrackingFactoryInterface;
use ILIAS\Tracking\DB\FactoryInterface as TrackingDBFactoryInterface;
use ILIAS\Tracking\DB\LPSettings\Element\LPSettingsInterface as TrackingDBLPSettingsInterface;
use ILIAS\Tracking\Status\CollectionInterface as LPStatusCollectionInterface;

class ilLPObjSettings
{
    public const int LP_MODE_DEACTIVATED = 0;
    public const int LP_MODE_TLT = 1;
    public const int LP_MODE_VISITS = 2;
    public const int LP_MODE_MANUAL = 3;
    public const int LP_MODE_OBJECTIVES = 4;
    public const int LP_MODE_COLLECTION = 5;
    public const int LP_MODE_SCORM = 6;
    public const int LP_MODE_TEST_FINISHED = 7;
    public const int LP_MODE_TEST_PASSED = 8;
    public const int LP_MODE_EXERCISE_RETURNED = 9;
    public const int LP_MODE_EVENT = 10;
    public const int LP_MODE_MANUAL_BY_TUTOR = 11;
    public const int LP_MODE_SCORM_PACKAGE = 12;
    public const int LP_MODE_UNDEFINED = 13;
    public const int LP_MODE_PLUGIN = 14;
    public const int LP_MODE_COLLECTION_TLT = 15;
    public const int LP_MODE_COLLECTION_MANUAL = 16;
    public const int LP_MODE_QUESTIONS = 17;
    public const int LP_MODE_SURVEY_FINISHED = 18;
    public const int LP_MODE_VISITED_PAGES = 19;
    public const int LP_MODE_CONTENT_VISITED = 20;
    public const int LP_MODE_COLLECTION_MOBS = 21;
    public const int LP_MODE_STUDY_PROGRAMME = 22;
    public const int LP_MODE_INDIVIDUAL_ASSESSMENT = 23;
    public const int LP_MODE_CMIX_COMPLETED = 24;
    public const int LP_MODE_CMIX_COMPL_WITH_FAILED = 25;
    public const int LP_MODE_CMIX_PASSED = 26;
    public const int LP_MODE_CMIX_PASSED_WITH_FAILED = 27;
    public const int LP_MODE_CMIX_COMPLETED_OR_PASSED = 28;
    public const int LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED = 29;
    public const int LP_DEFAULT_VISITS = 30;
    public const int LP_MODE_LTI_OUTCOME = 31;
    public const int LP_MODE_COURSE_REFERENCE = 32;
    public const int LP_MODE_CONTRIBUTION_TO_DISCUSSION = 33;

    protected ilObjectDataCache $objectDataCache;
    protected static TrackingFactoryInterface $tracking_factory;
    protected static LPStatusCollectionInterface $status_collection;
    protected TrackingDBFactoryInterface $tracking_db_factory;
    protected TrackingDBLPSettingsInterface $lp_settings;

    public function __construct(int $a_obj_id)
    {
        global $DIC;
        self::initTrackingFactory();
        $this->tracking_db_factory = self::$tracking_factory->db();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $entry_exists = $this->tracking_db_factory->lpSettings()->repository()->isLPSettingsEntryInDB($a_obj_id);
        if (!$entry_exists) {
            $olp = ilObjectLP::getInstance($a_obj_id);
            $this->lp_settings = $this->tracking_db_factory->lpSettings()->element()->lpSettings()
                ->withObjectId($a_obj_id)
                ->withObjType($this->objectDataCache->lookupType($a_obj_id))
                ->withUMode($olp->getDefaultMode())
                ->withVisits(self::LP_DEFAULT_VISITS);
        }
        if ($entry_exists) {
            $this->lp_settings = $this->tracking_db_factory->lpSettings()->repository()->readLPSettings($a_obj_id);
        }
    }

    protected static function initTrackingFactory(): void
    {
        if (!isset(self::$tracking_factory)) {
            self::$tracking_factory = new TrackingFactory();
        }
    }

    protected static function initStatusCollection(): void
    {
        self::initTrackingFactory();
        if (!isset(self::$status_collection)) {
            self::$status_collection = self::$tracking_factory->status()->allLPStatusImplementations();
        }
    }

    public function cloneSettings(int $a_new_obj_id): bool
    {
        $this->tracking_db_factory->lpSettings()->repository()->writeLPSettings(
            $this->lp_settings
                ->withObjectId($a_new_obj_id)
        );
        return true;
    }

    public function getVisits(): int
    {
        return $this->lp_settings->getVisits();
    }

    public function getMode(): int
    {
        return $this->lp_settings->getUMode();
    }

    public function getObjId(): int
    {
        return $this->lp_settings->getObjectId();
    }

    public function getObjType(): string
    {
        return $this->lp_settings->getObjType();
    }

    public function setVisits(
        int $a_visits
    ): void {
        $this->lp_settings = $this->lp_settings
            ->withVisits($a_visits);
    }

    public function setMode(
        int $a_mode
    ): void {
        $this->lp_settings = $this->lp_settings
            ->withUMode($a_mode);
    }

    public function read(): bool
    {
        $new_lp_settings = $this->tracking_db_factory->lpSettings()->repository()->readLPSettings($this->lp_settings->getObjectId());
        if (is_null($new_lp_settings)) {
            return false;
        }
        $this->lp_settings = $new_lp_settings;
        return true;
    }

    public function update(
        bool $a_refresh_lp = true
    ): bool {
        return $this->insert($a_refresh_lp);
    }

    public function insert(
        bool $a_refresh_lp = true
    ): bool {
        $new_entry = $this->tracking_db_factory->lpSettings()->repository()->isLPSettingsEntryInDB($this->lp_settings->getObjectId());
        $this->tracking_db_factory->lpSettings()->repository()->writeLPSettings($this->lp_settings);
        $this->read();
        if ($a_refresh_lp || $new_entry) {
            ilLPStatusWrapper::_refreshStatus($this->getObjId());
        }
        return true;
    }

    public static function _lookupVisits(
        int $a_obj_id
    ): int {
        self::initTrackingFactory();
        $tracking_db_factory = self::$tracking_factory->db();
        $lp_settings = $tracking_db_factory->lpSettings()->repository()->readLPSettings($a_obj_id);
        return is_null($lp_settings)
            ? self::LP_DEFAULT_VISITS
            : $lp_settings->getVisits();
    }

    public static function _lookupDBModeForObjects(
        array $a_obj_ids
    ): array {
        self::initTrackingFactory();
        $tracking_db_factory = self::$tracking_factory->db();
        $lp_settings = $tracking_db_factory->lpSettings()->repository()->readLPSettingsCollection(...$a_obj_ids);
        $db_modes = [];
        if (is_null($lp_settings)) {
            return $db_modes;
        }
        foreach ($lp_settings as $lp_setting) {
            $db_modes[$lp_setting->getObjectId()] = $lp_setting->getUMode();
        }
        return $db_modes;
    }

    public static function _lookupDBMode(
        int $a_obj_id
    ): ?int {
        self::initTrackingFactory();
        $tracking_db_factory = self::$tracking_factory->db();
        $lp_settings = $tracking_db_factory->lpSettings()->repository()->readLPSettings($a_obj_id);
        return is_null($lp_settings)
            ? null
            : $lp_settings->getUMode();
    }

    public static function _mode2Text(
        int $a_mode
    ): string {
        self::initStatusCollection();
        $status = self::$status_collection->getElementByStatusId((string) $a_mode);
        return is_null($status) ? '' : $status->getLabel();
    }

    public static function _mode2InfoText(
        int $a_mode
    ): string {
        self::initStatusCollection();
        $status = self::$status_collection->getElementByStatusId((string) $a_mode);
        return is_null($status) ? '' : $status->getInfo();
    }

    public static function getClassMap(): array
    {
        self::initStatusCollection();
        $res = [];
        foreach (self::$status_collection as $status) {
            $res[$status->getLPStatusId()] = $status::class;
        }
        return $res;
    }

    public static function _deleteByObjId(
        int $a_obj_id
    ): void {
        self::initTrackingFactory();
        $tracking_db_factory = self::$tracking_factory->db();
        $tracking_db_factory->lpSettings()->repository()->deleteLPSettings($a_obj_id);
    }

    public static function _delete(
        int $a_obj_id
    ): bool {
        ilLPObjSettings::_deleteByObjId($a_obj_id);
        return true;
    }
}
