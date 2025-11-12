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

namespace ILIAS\User\Badges;

use ILIAS\User\Profile\Profile;
use ILIAS\Language\Language;
use Psr\Http\Message\RequestInterface;

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ProfileBadge implements \ilBadgeType, \ilBadgeAuto
{
    public function __construct(
        private readonly Language $lng,
        private readonly \ilSetting $setting,
        private readonly RequestInterface $request,
        private readonly Profile $profile
    ) {
    }

    public function getId(): string
    {
        return 'profile';
    }

    public function getCaption(): string
    {
        return $this->lng->txt('badge_user_profile');
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function getValidObjectTypes(): array // Missing array type.
    {
        return ['bdga'];
    }

    public function getConfigGUIInstance(): ?\ilBadgeTypeGUI
    {
        return new ProfileBadgeGUI(
            $this->lng,
            $this->request
        );
    }

    public function evaluate(int $user_id, array $params, ?array $config): bool
    {
        $user = new \ilObjUser($user_id);

        if ($this->setting->get('user_portfolios') !== '1'
            || array_filter(
                \ilObjPortfolio::getPortfoliosOfUser($user_id),
                static fn(array $v): bool => $v['is_default'] === 1
            ) !== []) {
            // is profile public?
            if (!in_array($user->getPref('public_profile'), ['y', 'g'])) {
                return false;
            }
        }

        if ($config === null || !isset($config['profile'])) {
            return true;
        }

        foreach ($config['profile'] as $long_field_id) {
            $field = $this->profile->getFieldByIdentifier(mb_substr($long_field_id, 4));
            if ($field === null) {
                continue;
            }
            if (!$field->isPublishedByUser($user)
                || empty($field->retrieveValueFromUser($user))) {
                return false;
            }
        }

        return true;
    }
}
