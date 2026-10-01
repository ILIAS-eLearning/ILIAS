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

use ceLTIc\LTI\Service\Membership;

/**
 * The Names and Role Provisioning Services of ILIAS as LTI Advantage platform (ltiservices.php): a tool that
 * may read them gets the members of the course or group of one of its objects, with their roles. A member is
 * described as a launch of the object describes the user, under the privacy settings of the tool: with a
 * random user id per object, only the members who launched the object are known to the tool.
 *
 * The whole list is answered at once, filtered by role if the tool asks so.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformMembershipService
{
    private const string PATH_PATTERN = '@^/membership/(\d+)/(\d+)/?$@';
    private const string SERVICE_VERSION = '2.0';
    private const string STATUS_ACTIVE = 'Active';
    private const string STATUS_INACTIVE = 'Inactive';

    /**
     * The parameters of a launch that give the tool the membership URL of the context, which celtic/lti turns
     * into the claim of the service. None when the tool may not read the members or the context has none.
     *
     * @return array
     */
    public static function getLaunchParameters(ilObjLTITool $object, int $context_ref_id): array
    {
        if (!$object->getTool()->isNamesRoles() || !in_array(ilObject::_lookupType($context_ref_id, true), ['crs', 'grp'], true)) {
            return [];
        }

        return [
            'custom_context_memberships_v2_url' => self::getUrl($context_ref_id, $object->getId()),
            'custom_nrps_versions' => self::SERVICE_VERSION,
        ];
    }

    private static function getUrl(int $context_ref_id, int $obj_id): string
    {
        return ilLTIAdvantagePlatformServiceRequest::getUrl('/membership/' . $context_ref_id . '/' . $obj_id);
    }

    /**
     * Answers one request to the service.
     *
     * @param string $path the path after ltiservices.php
     * @param array $query the query parameters
     * @return array the HTTP status, the headers and the body of the response
     */
    public function handle(string $method, string $path, string $authorization, array $query): array
    {
        try {
            if (preg_match(self::PATH_PATTERN, $path, $matches) !== 1) {
                throw new DomainException('Unknown service path ' . $path, 404);
            }
            if (strtoupper($method) !== 'GET') {
                return ilLTIAdvantagePlatformServiceRequest::refuseMethod('GET');
            }

            $tool = ilLTIAdvantagePlatformServiceRequest::authorize($authorization, [Membership::$SCOPE]);
            if (!$tool->isNamesRoles()) {
                throw new DomainException('The tool ' . $tool->getId() . ' may not read the members', 403);
            }
            $context_ref_id = (int) $matches[1];
            $object = ilLTIAdvantagePlatformServiceRequest::getObject($context_ref_id, (int) $matches[2], $tool);
            $participants = match (ilObject::_lookupType($context_ref_id, true)) {
                'crs' => ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjId($context_ref_id)),
                'grp' => ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjId($context_ref_id)),
                default => throw new DomainException('The context ' . $context_ref_id . ' has no members', 404),
            };

            $role = (string) ($query['role'] ?? '');
            $members = [];
            foreach ($this->getRoles($participants, $tool) as $usr_id => $roles) {
                $member = $role === '' || array_intersect([$role, ilLTIAdvantagePlatformLaunchParameterBuilder::ROLE_PREFIX . $role], $roles) !== []
                    ? $this->getMember($object, $usr_id, $roles, $participants->isBlocked($usr_id))
                    : null;
                if ($member !== null) {
                    $members[] = $member;
                }
            }

            return ilLTIAdvantagePlatformServiceRequest::respond(Membership::MEDIA_TYPE_MEMBERSHIPS_NRPS, [
                'id' => self::getUrl($context_ref_id, $object->getId()),
                'context' => [
                    'id' => (string) $context_ref_id,
                    'label' => ilObject::_lookupTitle(ilObject::_lookupObjId($context_ref_id)),
                    'title' => ilObject::_lookupTitle(ilObject::_lookupObjId($context_ref_id)),
                ],
                'members' => $members,
            ]);
        } catch (DomainException $e) {
            return ilLTIAdvantagePlatformServiceRequest::refuse('membership service', $e);
        }
    }

    /**
     * The roles of the members by user, as a launch gives them: admins and tutors are instructors, members
     * are learners, and everybody is a learner for a tool that always gets learners.
     *
     * @return array
     */
    private function getRoles(ilParticipants $participants, ilLTITool $tool): array
    {
        $instructor = $tool->getAlwaysLearner()
            ? ilLTIAdvantagePlatformLaunchParameterBuilder::ROLE_LEARNER
            : ilLTIAdvantagePlatformLaunchParameterBuilder::ROLE_INSTRUCTOR;

        $roles = [];
        foreach ([
            $instructor => array_merge($participants->getAdmins(), $participants->getTutors()),
            ilLTIAdvantagePlatformLaunchParameterBuilder::ROLE_LEARNER => $participants->getMembers(),
        ] as $role => $usr_ids) {
            foreach ($usr_ids as $usr_id) {
                $roles[(int) $usr_id][] = ilLTIAdvantagePlatformLaunchParameterBuilder::ROLE_PREFIX . $role;
            }
        }

        return array_map(static fn(array $user_roles): array => array_values(array_unique($user_roles)), $roles);
    }

    /**
     * The member as the tool knows the user, null when the tool cannot know them.
     *
     * @param array $roles
     * @return array|null
     */
    private function getMember(ilObjLTITool $object, int $usr_id, array $roles, bool $blocked): ?array
    {
        if (!ilObjUser::_exists($usr_id)) {
            return null;
        }
        $tool = $object->getTool();
        $user = new ilObjUser($usr_id);
        $privacy_ident = $tool->getPrivacyIdent();
        $usr_ident = new ilCmiXapiUser($object->getId(), $usr_id, $privacy_ident)->getUsrIdent();
        if ($usr_ident === '') {
            // a random identity only exists once the user launched the object
            if ($privacy_ident === ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
                return null;
            }
            $usr_ident = ilCmiXapiUser::getIdent($privacy_ident, $user);
        }
        [$name_given, $name_family, $name_full] = ilLTIAdvantagePlatformLaunchParameterBuilder::getName($tool, $user);

        return array_filter([
            'status' => $blocked ? self::STATUS_INACTIVE : self::STATUS_ACTIVE,
            'user_id' => ilLTIAdvantagePlatformLaunchParameterBuilder::getUserId($privacy_ident, $usr_ident, $user),
            'roles' => $roles,
            'name' => $name_full,
            'given_name' => $name_given,
            'family_name' => $name_family,
            'email' => $usr_ident,
        ], static fn($value): bool => $value !== '');
    }
}
