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

    /**
     * @return array the message parameters, the user id of the tool being the login hint of the launch
     */
    public static function build(ilObjLTITool $object, ilCmiXapiUser $cmix_user, string $return_url): array
    {
        global $DIC;

        $user = $DIC->user();
        $tool = $object->getTool();

        $roles = $DIC->access()->checkAccess('write', '', $object->getRefId()) && !$tool->getAlwaysLearner()
            ? 'Instructor'
            : 'Learner';

        $user_id = ilCmiXapiUser::getIdentAsId($tool->getPrivacyIdent(), $user);
        if ($tool->getPrivacyIdent() === ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
            $user_id = (string) strstr($cmix_user->getUsrIdent(), '@' . ilCmiXapiUser::getIliasUuid(), true);
        }

        [$name_given, $name_family, $name_full] = match ($tool->getPrivacyName()) {
            ilLTITool::PRIVACY_NAME_FIRSTNAME => [$user->getFirstname(), '', $user->getFirstname()],
            ilLTITool::PRIVACY_NAME_LASTNAME => ['', $user->getLastname(), $user->getLastname()],
            ilLTITool::PRIVACY_NAME_FULLNAME => [$user->getFirstname(), $user->getLastname(), $user->getFullname()],
            default => ['', '', ''],
        };

        $parameters = [
            'resource_link_id' => $tool->getUseToolId() ? 'p' . $tool->getId() : (string) $object->getRefId(),
            'resource_link_title' => $object->getTitle(),
            'resource_link_description' => $object->getDescription(),
            'user_id' => $user_id,
            'roles' => $roles,
            'lis_person_name_given' => $name_given,
            'lis_person_name_family' => $name_family,
            'lis_person_name_full' => $name_full,
            'lis_person_contact_email_primary' => $cmix_user->getUsrIdent(),
            'launch_presentation_locale' => $DIC->language()->getLangKey(),
            'launch_presentation_document_target' => $object->isLaunchMethodEmbedded() ? 'iframe' : 'window',
            'launch_presentation_return_url' => $return_url,
            'tool_consumer_instance_guid' => (string) ilCmiXapiUser::getIliasUuid(),
            'tool_consumer_instance_name' => (string) ($DIC->settings()->get('short_inst_name') ?: CLIENT_ID),
            'tool_consumer_instance_description' => ilObjSystemFolder::_getHeaderTitle(),
            'tool_consumer_instance_url' => ilLink::_getLink(ROOT_FOLDER_ID, 'root'),
            'tool_consumer_instance_contact_email' => (string) $DIC->settings()->get('admin_email'),
            'tool_consumer_info_product_family_code' => 'ilias',
            'tool_consumer_info_version' => ILIAS_VERSION,
        ] + self::getContext($object->getRefId());

        if ($tool->getIncludeUserPicture()) {
            $parameters['user_image'] = ilObjLTITool::getIliasHttpPath() . '/' . $user->getPersonalPicturePath();
        }

        $custom = array_merge(ilObjLTITool::getToolCustomParamsArray($tool), $object->getCustomParamsArray());
        foreach ($custom as $name => $value) {
            $parameters[str_starts_with($name, 'custom_') ? $name : 'custom_' . $name] = $value;
        }

        // what the privacy settings keep back is left out instead of sent empty
        return array_filter($parameters, static fn($value): bool => $value !== '');
    }

    /**
     * The outermost course or group the object is in, or else the innermost category.
     *
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
