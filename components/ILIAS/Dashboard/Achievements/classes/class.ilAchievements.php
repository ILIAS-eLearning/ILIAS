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

use ILIAS\DI\Container;

class ilAchievements
{
    private ilCertificateActiveValidator $validator;
    protected ilLearningHistoryService $learing_history;

    public const SERV_LEARNING_HISTORY = 1;
    public const SERV_COMPETENCES = 2;
    public const SERV_LEARNING_PROGRESS = 3;
    public const SERV_BADGES = 4;
    public const SERV_CERTIFICATES = 5;

    protected array $services = [
        self::SERV_LEARNING_HISTORY,
        self::SERV_COMPETENCES,
        self::SERV_LEARNING_PROGRESS,
        self::SERV_BADGES,
        self::SERV_CERTIFICATES
    ];

    protected ilSetting $setting;
    protected ilSetting $skmg_setting;

    public function __construct()
    {
        global $DIC;

        $this->setting = $DIC->settings();
        $this->learing_history = $DIC->learningHistory();
        $this->skmg_setting = new ilSetting('skmg');
        $this->validator = new ilCertificateActiveValidator();
    }

    public function isActive(int $service): bool
    {
        switch ($service) {
            case self::SERV_LEARNING_HISTORY:
                return $this->learing_history->isActive();

            case self::SERV_COMPETENCES:
                return (bool) $this->skmg_setting->get('enable_skmg');

            case self::SERV_LEARNING_PROGRESS:
                return ilObjUserTracking::_enabledLearningProgress() &&
                    (ilObjUserTracking::_hasLearningProgressOtherUsers() ||
                        ilObjUserTracking::_hasLearningProgressLearner());

            case self::SERV_BADGES:
                return ilBadgeHandler::getInstance()->isActive();

            case self::SERV_CERTIFICATES:
                return $this->validator->validate();
        }
        return false;
    }

    public function isAnyActive(): bool
    {
        foreach ($this->services as $s) {
            if ($this->isActive($s)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int[]
     */
    public function getActiveServices(): array
    {
        return array_filter($this->services, function ($s) {
            return $this->isActive($s);
        });
    }
}
