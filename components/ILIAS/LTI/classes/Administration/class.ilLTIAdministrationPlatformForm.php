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

use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Factory;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use Random\RandomException;

/**
 * Create and edit form of a platform that may launch ILIAS as an LTI tool.
 * LTI 1.1 and LTI Advantage platforms get separate forms. Both have the fields of the former form, stored in
 * lti_ext_consumer and lti_ext_consumer_otype. An LTI Advantage platform additionally stores its registration
 * once in lti2_consumer with ref_id 0, the table ceLTIc reads platforms from, with the URLs in the ceLTIc settings.
 * Older installations stored the LTI 1.3 registration per released object (ref_id > 0): those rows are read
 * as fallback and never changed, so their released objects keep working.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAdministrationPlatformForm
{
    private const array REGISTRATION_FIELDS = [
        "platform_id" => "lti_13_platform_id",
        "client_id" => "lti_13_client_id",
        "deployment_id" => "lti_13_deployment_id",
        "keyset_url" => "lti_13_keyset_url",
        "token_url" => "lti_13_token_url",
        "authentication_url" => "lti_13_authentication_url",
    ];

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        private readonly ilObjectDefinition $obj_definition,
        private readonly ilRbacReview $rbac_review,
        private readonly int $platform_id,
        private readonly string $version,
        private readonly Refinery $refinery
    ) {
    }

    public function isAdvantage(): bool
    {
        return $this->version === ilLTITool::VERSION_ADVANTAGE;
    }

    public function getForm(string $action): Form
    {
        $field = $this->ui_factory->input()->field();
        $values = $this->platform_id > 0 ? $this->read() : ["active" => false, "types" => []];
        $role_options = $this->getRoleOptions();
        $type_options = $this->getObjectTypeOptions();

        $inputs = [
            "title" => $field->text($this->lng->txt("title"))->withRequired(true)->withValue($values["title"] ?? ""),
            "description" => $field->text($this->lng->txt("description"))->withValue($values["description"] ?? ""),
            "prefix" => $field->text($this->lng->txt("prefix"))->withRequired(true)->withValue($values["prefix"] ?? ""),
            "language" => $field->select($this->lng->txt("language"), $this->getLanguageOptions())->withRequired(true),
            "types" => $field->multiSelect($this->lng->txt("act_lti_for_obj_type"), $type_options)
                ->withValue(array_values(array_intersect($values["types"], array_keys($type_options)))),
            "role" => $field->select($this->lng->txt("gbl_roles_to_users"), $role_options)->withRequired(true),
            "active" => $field->checkbox($this->lng->txt("active"))->withValue($values["active"]),
        ];
        if (isset($values["language"], $this->getLanguageOptions()[$values["language"]])) {
            $inputs["language"] = $inputs["language"]->withValue($values["language"]);
        }
        if (isset($values["role"], $role_options[$values["role"]])) {
            $inputs["role"] = $inputs["role"]->withValue($values["role"]);
        }

        $sections = [
            "general" => $field->section(
                $inputs,
                $this->lng->txt($this->platform_id > 0 ? "lti_edit_consumer" : "lti_create_consumer")
            ),
        ];
        if ($this->isAdvantage()) {
            $registration = $this->readRegistration();
            $inputs = [];
            foreach (self::REGISTRATION_FIELDS as $key => $txt) {
                $inputs[$key] = $field->text($this->lng->txt($txt))->withValue($registration[$key]);
            }
            // Dynamic Registration fills the registration in, so it may stay empty, but not half filled
            $sections["registration"] = $field->section($inputs + [
                "dynamic_registration_url" => $field->text(
                    $this->lng->txt("lti_dyn_reg_tool_url"),
                    $this->lng->txt($this->platform_id > 0 ? "lti_dyn_reg_tool_url_info" : "lti_dyn_reg_tool_url_after_save")
                )->withValue($this->platform_id > 0 ? $this->getDynamicRegistrationUrl() : "")->withDisabled(true),
                // read only data the platform needs: where it starts the login and posts the launch and the Deep
                // Linking request, and the key set of ILIAS. The objects released to the platform show their target link.
                "launch_url" => $field->text($this->lng->txt("lti_launch_url"))
                    ->withValue(ilLTIEndpoint::getUrl(ilLTIEndpoint::LAUNCH))->withDisabled(true),
                "deep_linking_url" => $field->text($this->lng->txt("lti_deep_linking_url"))
                    ->withValue(ilLTIEndpoint::getUrl(ilLTIEndpoint::LAUNCH))->withDisabled(true),
                "ilias_keyset_url" => $field->text($this->lng->txt("lti_con_key_type_jwk"))
                    ->withValue(ilLTIAdvantageKeyPair::getJwksUrl())->withDisabled(true),
            ], $this->lng->txt("lti_platform_registration"), $this->lng->txt("lti_platform_registration_info"))
                ->withAdditionalTransformation($this->refinery->custom()->constraint(
                    static fn(array $data): bool => count(array_unique(array_map(
                        static fn(string $key): bool => trim((string) $data[$key]) === "",
                        array_keys(self::REGISTRATION_FIELDS)
                    ))) === 1,
                    $this->lng->txt("lti_platform_registration_incomplete")
                ))
                ->withAdditionalTransformation($this->refinery->custom()->transformation(
                    static fn(array $data): array => array_map(
                        "trim",
                        array_intersect_key($data, self::REGISTRATION_FIELDS)
                    )
                ))
                // a launch names the platform by these ids, so two platforms cannot share them
                ->withAdditionalTransformation($this->refinery->custom()->constraint(
                    fn(array $data): bool => $data["platform_id"] === "" || in_array(
                        ilLTIPlatform::lookupIdByRegistration($data["platform_id"], $data["client_id"], $data["deployment_id"]),
                        [0, $this->platform_id],
                        true
                    ),
                    $this->lng->txt("lti_platform_registration_taken")
                ));
        }

        return $this->ui_factory->input()->container()->form()->standard($action, $sections);
    }

    /**
     * Stores the submitted form. Returns the form with its errors if the input is not valid, null otherwise.
     */
    public function save(string $action, ServerRequestInterface $request): ?Form
    {
        $form = $this->getForm($action)->withRequest($request);
        $data = $form->getData();
        if ($data === null) {
            return $form;
        }
        $registration = $data["registration"] ?? null;
        $data = $data["general"];

        $fields = [
            "title" => ["text", $data["title"]],
            "description" => ["text", $data["description"]],
            "prefix" => ["text", $data["prefix"]],
            "user_language" => ["text", $data["language"]],
            "role" => ["integer", (int) $data["role"]],
            "active" => ["integer", (int) $data["active"]],
        ];
        $platform_id = $this->platform_id;
        if ($platform_id > 0) {
            $this->db->update("lti_ext_consumer", $fields, ["id" => ["integer", $platform_id]]);
        } else {
            $platform_id = $this->db->nextId("lti_ext_consumer");
            $this->db->insert("lti_ext_consumer", $fields + ["id" => ["integer", $platform_id]]);
        }

        $this->db->manipulate("DELETE FROM lti_ext_consumer_otype WHERE consumer_id = " . $this->db->quote($platform_id, "integer"));
        foreach ((array) $data["types"] as $type) {
            $this->db->insert("lti_ext_consumer_otype", [
                "consumer_id" => ["integer", $platform_id],
                "object_type" => ["text", $type],
            ]);
        }

        if ($registration !== null) {
            // a new URL for Dynamic Registration is not used up by saving the form
            $settings = array_intersect_key(
                ilLTIPlatform::lookupRegistrationSettings($platform_id),
                [ilLTIAdvantageToolRegistration::SETTING_USED_TOKEN => true]
            );
            ilLTIPlatform::saveRegistration($platform_id, $data["title"], (bool) $data["active"], $registration, $settings);
        }

        return null;
    }

    /**
     * A new URL each time the form is shown, as each one can be used once.
     */
    private function getDynamicRegistrationUrl(): string
    {
        try {
            return ilLTIAdvantageToolRegistration::getRegistrationUrl($this->platform_id);
        } catch (ilException|RandomException $e) {
            global $DIC;

            $DIC->logger()->forComponent("lti")->error($e->getMessage());
            return "";
        }
    }

    /**
     * @return array
     */
    private function readRegistration(): array
    {
        // the registration of the platform, or the one of its first released object in older installations
        $this->db->setLimit(1);
        $row = $this->platform_id > 0 ? $this->db->fetchAssoc($this->db->query(
            "SELECT platform_id, client_id, deployment_id, settings FROM lti2_consumer WHERE lti_version = "
            . $this->db->quote(ilLTITool::VERSION_ADVANTAGE, "text") . " AND ext_consumer_id = "
            . $this->db->quote($this->platform_id, "integer") . " ORDER BY ref_id"
        )) : null;
        $settings = json_decode((string) ($row["settings"] ?? ""), true);
        $settings = is_array($settings) ? $settings : [];

        return [
            "platform_id" => (string) ($row["platform_id"] ?? ""),
            "client_id" => (string) ($row["client_id"] ?? ""),
            "deployment_id" => (string) ($row["deployment_id"] ?? ""),
            "keyset_url" => (string) ($settings["_jku"] ?? ""),
            "token_url" => (string) ($settings["_oauth2_access_token_url"] ?? ""),
            "authentication_url" => (string) ($settings["_authentication_request_url"] ?? ""),
        ];
    }

    /**
     * @return array
     */
    private function read(): array
    {
        $row = ilLTIPlatform::lookupAdministrationRow($this->platform_id);
        $types = ilLTIPlatform::lookupObjectTypes($this->platform_id);

        return [
            "title" => (string) ($row["title"] ?? ""),
            "description" => (string) ($row["description"] ?? ""),
            "prefix" => (string) ($row["prefix"] ?? ""),
            "language" => (string) ($row["user_language"] ?? ""),
            "role" => (string) ($row["role"] ?? ""),
            "active" => (bool) ($row["active"] ?? false),
            "types" => $types,
        ];
    }

    /**
     * @return array
     */
    private function getLanguageOptions(): array
    {
        $options = [];
        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            $options[$lang_key] = ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_" . $lang_key);
        }

        return $options;
    }

    /**
     * @return array
     */
    private function getObjectTypeOptions(): array
    {
        $options = [];
        foreach ($this->obj_definition->getLTIProviderTypes() as $type) {
            $options[$type] = $this->lng->txt("objs_" . $type);
        }

        return $options;
    }

    /**
     * @return array
     */
    private function getRoleOptions(): array
    {
        $options = [];
        foreach (array_diff($this->rbac_review->getGlobalRoles(), [SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID]) as $role_id) {
            $options[(string) $role_id] = ilObject::_lookupTitle((int) $role_id);
        }

        return $options;
    }
}
