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

use ceLTIc\LTI\Context;
use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Service\Membership;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\User;

/**
 * The Names and Role Provisioning Services of a platform, read by ILIAS as LTI Advantage tool. celtic/lti sends
 * the requests with an access token and follows the pages; the members are read here because the library drops
 * their status, and only active members may enter ILIAS. The ids of the members the platform reports as
 * inactive or deleted are kept apart, so that they can lose the roles their launches gave them.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantageToolMembership extends Membership
{
    private const string STATUS_ACTIVE = 'Active';
    private const int MAX_PAGES = 100;

    /**
     * @var string[] the ids of the members of the last read that are not active
     */
    private array $inactive_user_ids = [];

    /**
     * The service of the context of a launch, null when the launch names none.
     */
    public static function forContext(?Context $context): ?self
    {
        $url = $context?->getSetting('custom_context_memberships_v2_url') ?? '';

        return $url !== '' ? new self($context, $url, self::MEDIA_TYPE_MEMBERSHIPS_NRPS) : null;
    }

    /**
     * The active members of the context, each one with the id the platform knows them by, null when the
     * platform did not answer with a membership container.
     *
     * @return array|null User objects of celtic/lti by user id
     */
    public function getActiveMembers(): ?array
    {
        $members = [];
        $this->inactive_user_ids = [];
        $url = $this->endpoint;
        $pages = 0;
        do {
            $this->endpoint = $url;
            $http = $this->send('GET');
            $page = $http->ok ? ($http->responseJson->members ?? null) : null;
            if (!is_array($page)) {
                return null;
            }
            foreach ($page as $member) {
                $user = $this->toUser($member);
                if ($user !== null) {
                    $members[$user->ltiUserId] = $user;
                } elseif (is_object($member) && is_string($member->user_id ?? null) && $member->user_id !== '') {
                    $this->inactive_user_ids[] = $member->user_id;
                }
            }
            $url = $http->hasRelativeLink('next') ? $http->getRelativeLink('next') : null;
        } while ($url !== null && ++$pages < self::MAX_PAGES);

        return $members;
    }

    /**
     * @return string[] the ids of the members the last read of getActiveMembers() found inactive or deleted
     */
    public function getInactiveUserIds(): array
    {
        return $this->inactive_user_ids;
    }

    /**
     * The member as celtic/lti describes the user of a launch, null when the member is not active or has no id.
     */
    private function toUser(mixed $member): ?User
    {
        $user_id = is_object($member) ? ($member->user_id ?? null) : null;
        if (!is_string($user_id) || $user_id === '' || ($member->status ?? self::STATUS_ACTIVE) !== self::STATUS_ACTIVE) {
            return null;
        }

        $text = static fn(string $name): string => is_string($member->$name ?? null) ? $member->$name : '';
        $user = new User();
        $user->ltiUserId = $user_id;
        $user->setNames($text('given_name'), $text('family_name'), $text('name'));
        $user->setEmail($text('email'));
        $user->roles = Tool::parseRoles(
            array_values(array_filter(is_array($member->roles ?? null) ? $member->roles : [], 'is_string')),
            LtiVersion::V1P3
        );

        return $user;
    }
}
