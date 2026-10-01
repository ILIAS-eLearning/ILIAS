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

use ILIAS\Data\ReferenceId;

/**
 * The message of an LTI Advantage launch of an LTI object. The parameters carry their LTI 1.1 names, which
 * celtic/lti turns into the claims of the id_token. They follow the privacy settings of the tool, as an
 * LTI 1.1 launch does.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformLaunchParameterBuilder
{
    private const string CONTEXT_TYPE_GROUP = 'http://purl.imsglobal.org/vocab/lis/v2/course#Group';
    private const string CONTEXT_TYPE_COURSE = 'http://purl.imsglobal.org/vocab/lis/v2/course#CourseOffering';
    public const string ROLE_PREFIX = 'http://purl.imsglobal.org/vocab/lis/v2/membership#';
    public const string ROLE_ADMINISTRATOR = 'Administrator';
    public const string ROLE_INSTRUCTOR = 'Instructor';
    public const string ROLE_LEARNER = 'Learner';

    /**
     * @return array the message parameters, the user id of the tool being the login hint of the launch
     * @throws ilWACException
     */
    public static function build(ilObjLTITool $object, ilCmiXapiUser $cmix_user, string $return_url): array
    {
        global $DIC;

        $tool = $object->getTool();

        $user_id = self::getUserId($tool->getPrivacyIdent(), $cmix_user->getUsrIdent(), $DIC->user());
        $role = $tool->getAlwaysLearner() ? self::ROLE_LEARNER : self::getRole($object->getRefId());

        $parameters = self::filter([
            'resource_link_id' => self::getResourceLinkId($object),
            'resource_link_title' => $object->getTitle(),
            'resource_link_description' => $object->getDescription(),
            'launch_presentation_document_target' => $object->isLaunchMethodEmbedded() ? 'iframe' : 'window',
            'launch_presentation_return_url' => $return_url,
        ] + self::buildForUser(
            $tool,
            $user_id,
            $cmix_user->getUsrIdent(),
            $role,
            $object->getRefId(),
            $object->getCustomParamsArray()
        ));

        $context_ref_id = (int) ($parameters['context_id'] ?? 0);

        return array_merge(
            $parameters,
            ilLTIAdvantagePlatformGradeService::getLaunchParameters($object, $context_ref_id),
            ilLTIAdvantagePlatformMembershipService::getLaunchParameters($object, $context_ref_id)
        );
    }

    /**
     * The role of the user as earlier releases gave it: administrators of ILIAS are administrators, admins and
     * tutors of the course or group the object is in are instructors, everybody else is a learner.
     */
    private static function getRole(int $ref_id): string
    {
        global $DIC;

        $rbac_review = $DIC->rbac()->review();
        $usr_id = $DIC->user()->getId();
        if (in_array(SYSTEM_ROLE_ID, $rbac_review->assignedGlobalRoles($usr_id))) {
            return self::ROLE_ADMINISTRATOR;
        }

        $context_type = ilObject::_lookupType($DIC->repositoryTree()->getParentId($ref_id), true);
        $roles = array_intersect(
            $rbac_review->assignedRoles($usr_id),
            array_keys($rbac_review->getParentRoleIds($ref_id, true))
        );
        foreach ($roles as $role_id) {
            $title = (string) ilObject::_lookupTitle($role_id);
            if (str_starts_with($title, 'il_' . $context_type . '_admin') || str_starts_with($title, 'il_' . $context_type . '_tutor')) {
                return self::ROLE_INSTRUCTOR;
            }
        }

        return self::ROLE_LEARNER;
    }

    /**
     * The user id the tool knows the user by, from the identity the launches of the object stored for them.
     */
    public static function getUserId(int $privacy_ident, string $usr_ident, ilObjUser $user): string
    {
        // a random identity is generated once, the user id is its part before the domain
        if ($privacy_ident === ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
            return (string) strstr($usr_ident, '@' . ilCmiXapiUser::getIliasUuid(), true);
        }

        return ilCmiXapiUser::getIdentAsId($privacy_ident, $user);
    }

    public static function getResourceLinkId(ilObjLTITool $object): string
    {
        $tool = $object->getTool();

        return $tool->getUseToolId() ? 'p' . $tool->getId() : (string) $object->getRefId();
    }

    /**
     * What every message ILIAS sends a tool tells about the user, the context and ILIAS, and the custom
     * parameters of the tool and of the object.
     *
     * @param array $object_custom_params the custom parameters of the object, which win over those of the tool
     * @return array
     */
    public static function buildForUser(
        ilLTITool $tool,
        string $user_id,
        string $email,
        string $role,
        int $ref_id,
        array $object_custom_params = []
    ): array {
        global $DIC;

        $user = $DIC->user();
        [$name_given, $name_family, $name_full] = self::getName($tool, $user);

        $parameters = [
            'user_id' => $user_id,
            'roles' => self::ROLE_PREFIX . $role,
            'lis_person_name_given' => $name_given,
            'lis_person_name_family' => $name_family,
            'lis_person_name_full' => $name_full,
            'lis_person_contact_email_primary' => $email,
            'launch_presentation_locale' => $DIC->language()->getLangKey(),
            'tool_consumer_instance_guid' => (string) ilCmiXapiUser::getIliasUuid(),
            'tool_consumer_instance_name' => (string) ($DIC->settings()->get('short_inst_name') ?: CLIENT_ID),
            'tool_consumer_instance_description' => ilObjSystemFolder::_getHeaderTitle(),
            'tool_consumer_instance_url' => (string) $DIC['static_url']->builder()->build('root', new ReferenceId(ROOT_FOLDER_ID)),
            'tool_consumer_instance_contact_email' => (string) $DIC->settings()->get('admin_email'),
            'tool_consumer_info_product_family_code' => 'ilias',
            'tool_consumer_info_version' => ILIAS_VERSION,
        ] + self::getContext($ref_id);

        if ($tool->getIncludeUserPicture()) {
            $parameters['user_image'] = ilObjLTITool::getIliasHttpPath() . '/' . $user->getPersonalPicturePath();
        }

        $custom = array_merge(ilObjLTITool::getToolCustomParamsArray($tool), $object_custom_params);
        foreach ($custom as $name => $value) {
            $parameters[str_starts_with($name, 'custom_') ? $name : 'custom_' . $name] = $value;
        }

        return self::filter($parameters);
    }

    /**
     * The given, family and full name of the user, as far as the privacy settings of the tool allow.
     *
     * @return array
     */
    public static function getName(ilLTITool $tool, ilObjUser $user): array
    {
        return match ($tool->getPrivacyName()) {
            ilLTITool::PRIVACY_NAME_FIRSTNAME => [$user->getFirstname(), '', $user->getFirstname()],
            ilLTITool::PRIVACY_NAME_LASTNAME => ['', $user->getLastname(), $user->getLastname()],
            ilLTITool::PRIVACY_NAME_FULLNAME => [$user->getFirstname(), $user->getLastname(), $user->getFullname()],
            default => ['', '', ''],
        };
    }

    /**
     * What the privacy settings keep back is left out instead of sent empty.
     *
     * @param array $parameters
     * @return array
     */
    private static function filter(array $parameters): array
    {
        return array_filter($parameters, static fn($value): bool => $value !== '');
    }

    /**
     * The outermost course or group the object is in, or else the innermost category.
     *
     * @param int $ref_id
     * @return array
     */
    private static function getContext(int $ref_id): array
    {
        global $DIC;

        $context = null;
        foreach (array_reverse($DIC->repositoryTree()->getPathFull($ref_id)) as $node) {
            if (in_array($node['type'], ['crs', 'grp'], true)) {
                $context = $node;
            } elseif ($context === null && in_array($node['type'], ['cat', 'root'], true)) {
                $context = $node;
            }
        }
        if ($context === null) {
            return [];
        }

        return [
            'context_id' => (string) $context['child'],
            'context_title' => (string) $context['title'],
            'context_label' => (string) $context['title'],
            'context_type' => $context['type'] === 'grp' ? self::CONTEXT_TYPE_GROUP : self::CONTEXT_TYPE_COURSE,
        ];
    }
}
