<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Maybe a separate service in the future. Needs a generic approach.
 *
 * Currently only the main menu (and personal desktop) should use this.
 *
 * @author killing@leifos.de
 * @ingroup ServicesPersonalDesktop
 */
class ilAchievements
{
    /**
     * @var ilCertificateActiveValidator
     */
    private $validator;

    /**
     * @var ilLearningHistoryService
     */
    protected $learing_history;

    // all services being covered under the achievements menu item
    public const SERV_LEARNING_HISTORY = 1;
    public const SERV_COMPETENCES = 2;
    public const SERV_LEARNING_PROGRESS = 3;
    public const SERV_BADGES = 4;
    public const SERV_CERTIFICATES = 5;

    // this also determines the order of tabs
    protected $services = [
        self::SERV_LEARNING_HISTORY,
        self::SERV_COMPETENCES,
        self::SERV_LEARNING_PROGRESS,
        self::SERV_BADGES,
        self::SERV_CERTIFICATES
    ];

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * @var ilSetting
     */
    protected $skmg_setting;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->setting = $DIC->settings();
        $this->learing_history = $DIC->learningHistory();
        $this->skmg_setting = new ilSetting("skmg");
        $this->validator = new ilCertificateActiveValidator();
    }

    /**
     * Is subservice active?
     *
     * @param int service
     * @return bool
     */
    public function isActive(int $service) : bool
    {
        switch ($service) {
            case self::SERV_LEARNING_HISTORY:
                return (bool) $this->learing_history->isActive();

            case self::SERV_COMPETENCES:
                return (bool) $this->skmg_setting->get("enable_skmg");

            case self::SERV_LEARNING_PROGRESS:
                return (bool) (ilObjUserTracking::_enabledLearningProgress() &&
                    (ilObjUserTracking::_hasLearningProgressOtherUsers() ||
                        ilObjUserTracking::_hasLearningProgressLearner()));

            case self::SERV_BADGES:
                return (bool) ilBadgeHandler::getInstance()->isActive();

            case self::SERV_CERTIFICATES:
                return $this->validator->validate();

        }
        return false;
    }

    /**
     * Is any subservice active?
     *
     * @return bool
     */
    public function isAnyActive() : bool
    {
        foreach ($this->services as $s) {
            if ($this->isActive($s)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get active services
     *
     * @return int[]
     */
    public function getActiveServices() : array
    {
        return array_filter($this->services, function ($s) {
            return $this->isActive($s);
        });
    }
}
