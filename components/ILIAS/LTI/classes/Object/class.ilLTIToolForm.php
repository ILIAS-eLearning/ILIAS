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

use ceLTIc\LTI\Util;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Create and edit form of an external tool ILIAS may launch, stored in lti_ext_provider.
 * LTI 1.1 and LTI Advantage tools get separate forms: both share the general, privacy, learning progress,
 * launch and grouping fields of the former form and only differ in the authentication section.
 * The tool icon and the XML import are not supported yet.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolForm
{
    private const array CATEGORIES = ["organisation", "communication", "content", "assessment", "feedback"];
    private const array PRIVACY_IDENTS = [
        0 => "il_uuid_user_id",
        2 => "il_uuid_login",
        1 => "il_uuid_ext_account",
        5 => "il_uuid_SHA256",
        6 => "il_uuid_sha256url",
        4 => "il_uuid_random",
        3 => "real_email",
    ];
    private const array PRIVACY_NAMES = [0 => "none", 1 => "firstname", 2 => "lastname", 3 => "fullname"];
    private const string KEY_TYPE_RSA = "RSA_KEY";
    private const string KEY_TYPE_JWK = "JWK_KEYSET";
    private const string DEPRECATION_URL = "https://www.1edtech.org/lti-security-announcement-and-deprecation-schedule";

    private int $saved_id = 0;

    public function __construct(
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        private readonly Refinery $refinery,
        private readonly ilObjUser $user,
        private readonly int $tool_id,
        private readonly string $version,
        private readonly bool $in_administration = false
    ) {
    }

    public function isAdvantage(): bool
    {
        return $this->version === ilLTITool::VERSION_ADVANTAGE;
    }

    public function getForm(string $action, string $submit_label = ''): Form
    {
        $field = $this->ui_factory->input()->field();
        $row = $this->read();
        $text = fn(string $column) => (string) ($row[$column] ?? "");
        $flag = fn(string $column) => (bool) ($row[$column] ?? false);

        $general_inputs = [
            "title" => $field->text($this->lng->txt("lti_con_prov_title"))->withRequired(true)->withValue($text("title")),
            "description" => $field->text($this->lng->txt("lti_con_prov_description"))->withValue($text("description")),
        ];
        // whether a tool may still be used is decided by the administration only
        if ($this->in_administration) {
            $general_inputs["availability"] = $field->radio($this->lng->txt("lti_con_prov_availability"))
                ->withOption("2", $this->lng->txt("lti_con_prov_availability_create"))
                ->withOption("1", $this->lng->txt("lti_con_prov_availability_existing"))
                ->withOption("0", $this->lng->txt("lti_con_prov_availability_non"))
                ->withRequired(true)
                ->withValue((string) ($row["availability"] ?? 2));
        }
        $general = $field->section(
            $general_inputs,
            $this->lng->txt($this->tool_id > 0 ? "lti_form_provider_edit" : "lti_form_provider_create")
        );

        // the byline ends with the unique ILIAS platform id used in the generated user identifiers
        $privacy_ident = $field->radio(
            $this->lng->txt("conf_privacy_ident"),
            $this->lng->txt("conf_privacy_ident_info") . " " . ilCmiXapiUser::getIliasUuid() . ".ilias"
        );
        foreach (self::PRIVACY_IDENTS as $value => $name) {
            $privacy_ident = $privacy_ident->withOption(
                (string) $value,
                $this->lng->txt("conf_privacy_ident_" . $name),
                $this->lng->txt("conf_privacy_ident_" . $name . "_info")
            );
        }
        $privacy_name = $field->radio($this->lng->txt("conf_privacy_name"), $this->lng->txt("conf_privacy_name_info"));
        foreach (self::PRIVACY_NAMES as $value => $name) {
            $privacy_name = $privacy_name->withOption(
                (string) $value,
                $this->lng->txt("conf_privacy_name_" . $name),
                $this->lng->txt("conf_privacy_name_" . $name . "_info")
            );
        }
        $privacy = $field->section([
            "privacy_ident" => $privacy_ident->withValue((string) ($row["privacy_ident"] ?? 0)),
            "instructor_email" => $field->checkbox(
                $this->lng->txt("lti_con_prov_instructor_email"),
                $this->lng->txt("lti_con_prov_instructor_email_info")
            )->withValue($flag("instructor_send_email")),
            "privacy_name" => $privacy_name->withValue((string) ($row["privacy_name"] ?? 0)),
            "instructor_name" => $field->checkbox(
                $this->lng->txt("lti_con_prov_instructor_name"),
                $this->lng->txt("lti_con_prov_instructor_name_info")
            )->withValue($flag("instructor_send_name")),
            "inc_usr_pic" => $field->checkbox(
                $this->lng->txt("lti_con_prov_inc_usr_pic"),
                $this->lng->txt("lti_con_prov_inc_usr_pic_info")
            )->withValue($flag("inc_usr_pic")),
            "external_provider" => $field->checkbox(
                $this->lng->txt("lti_con_prov_external_provider"),
                $this->lng->txt("lti_con_prov_external_provider_info")
            )->withValue($flag("external_provider")),
        ], $this->lng->txt("lti_con_prov_privacy_settings"));

        $outcome = $field->optionalGroup([
            "mastery_score" => $field->numeric(
                $this->lng->txt("lti_con_prov_mastery_score_default"),
                $this->lng->txt("lti_con_prov_mastery_score_default_info")
            )->withStepSize(0.01)
                ->withRequired(true)
                ->withAdditionalTransformation($this->refinery->int()->isGreaterThanOrEqual(0))
                ->withAdditionalTransformation($this->refinery->int()->isLessThanOrEqual(100))
                ->withValue(round(100 * (float) ($row["mastery_score"] ?? 0.8), 2)),
        ], $this->lng->txt("lti_con_prov_has_outcome_service"), $this->lng->txt("lti_con_prov_has_outcome_service_info"));
        $learning_progress = $field->section([
            "has_outcome" => $flag("has_outcome") ? $outcome : $outcome->withValue(null),
        ], $this->lng->txt("lti_con_prov_learning_progress_options"));

        $xapi = $field->optionalGroup([
            "xapi_launch_url" => $field->text(
                $this->lng->txt("lti_con_prov_xapi_launch_url"),
                $this->lng->txt("lti_con_prov_xapi_launch_url_info")
            )->withRequired(true)->withValue($text("xapi_launch_url")),
            "xapi_launch_key" => $field->text($this->lng->txt("lti_con_prov_xapi_launch_key"))
                ->withRequired(true)->withValue($text("xapi_launch_key")),
            "xapi_launch_secret" => $field->text($this->lng->txt("lti_con_prov_xapi_launch_secret"))
                ->withRequired(true)->withValue($text("xapi_launch_secret")),
            "xapi_activity_id" => $field->text(
                $this->lng->txt("lti_con_prov_xapi_activity_id"),
                $this->lng->txt("lti_con_prov_xapi_activity_id_info")
            )->withValue($text("xapi_activity_id")),
        ], $this->lng->txt("lti_con_prov_use_xapi"), $this->lng->txt("lti_con_prov_use_xapi_info"));
        $launch = $field->section([
            "use_provider_id" => $field->checkbox(
                $this->lng->txt("lti_con_prov_use_provider_id"),
                $this->lng->txt("lti_con_prov_use_provider_id_info")
            )->withValue($flag("use_provider_id")),
            "always_learner" => $field->checkbox(
                $this->lng->txt("lti_con_prov_always_learner"),
                $this->lng->txt("lti_con_prov_always_learner_info")
            )->withValue($flag("always_learner")),
            "use_xapi" => $flag("use_xapi") ? $xapi : $xapi->withValue(null),
            "custom_params" => $field->textarea(
                $this->lng->txt("lti_con_prov_custom_params"),
                $this->lng->txt("lti_con_prov_custom_params_info")
            )->withValue($text("custom_params")),
        ], $this->lng->txt("lti_con_prov_launch_options"));

        $category = $field->radio($this->lng->txt("lti_con_prov_category"), $this->lng->txt("lti_con_prov_category_info"))
            ->withRequired(true);
        foreach (self::CATEGORIES as $name) {
            $category = $category->withOption($name, $this->lng->txt("rep_add_new_def_grp_" . $name));
        }
        $group = $field->section([
            "keywords" => $field->text($this->lng->txt("lti_con_prov_keywords"), $this->lng->txt("lti_con_prov_keywords_info"))
                ->withValue($text("keywords")),
            "category" => $category->withValue(in_array($text("category"), self::CATEGORIES, true) ? $text("category") : "content"),
        ], $this->lng->txt("lti_con_prov_group_options"));

        $hints = $field->section([
            "remarks" => $field->textarea($this->lng->txt("lti_con_prov_remarks"))->withValue($text("remarks")),
        ], $this->lng->txt("lti_con_prov_hints"));

        $form = $this->ui_factory->input()->container()->form()->standard($action, [
            "general" => $general,
            "authentication" => $this->getAuthentication($row),
            "privacy" => $privacy,
            "learning_progress" => $learning_progress,
            "launch" => $launch,
            "group" => $group,
            "hints" => $hints,
        ]);

        return $submit_label === '' ? $form : $form->withSubmitLabel($submit_label);
    }

    /**
     * Stores the submitted form. Returns the form with its errors if the input is not valid, null otherwise.
     * An own tool belongs to its creator and is only offered for creating objects, whatever the form says.
     */
    public function save(string $action, ServerRequestInterface $request, bool $own_tool = false): ?Form
    {
        $form = $this->getForm($action, $own_tool ? $this->lng->txt("lti_add_own_provider") : "")->withRequest($request);
        $data = $form->getData();
        if ($data === null) {
            return $form;
        }

        $fields = [
            "title" => ["text", $data["general"]["title"]],
            "description" => ["text", $data["general"]["description"]],
            "privacy_ident" => ["integer", (int) $data["privacy"]["privacy_ident"]],
            "instructor_send_email" => ["integer", (int) $data["privacy"]["instructor_email"]],
            "privacy_name" => ["integer", (int) $data["privacy"]["privacy_name"]],
            "instructor_send_name" => ["integer", (int) $data["privacy"]["instructor_name"]],
            "inc_usr_pic" => ["integer", (int) $data["privacy"]["inc_usr_pic"]],
            "external_provider" => ["integer", (int) $data["privacy"]["external_provider"]],
            "has_outcome" => ["integer", (int) ($data["learning_progress"]["has_outcome"] !== null)],
            "mastery_score" => ["float", ($data["learning_progress"]["has_outcome"]["mastery_score"] ?? 80) / 100],
            "use_provider_id" => ["integer", (int) $data["launch"]["use_provider_id"]],
            "always_learner" => ["integer", (int) $data["launch"]["always_learner"]],
            "use_xapi" => ["integer", (int) ($data["launch"]["use_xapi"] !== null)],
            "xapi_launch_url" => ["text", $data["launch"]["use_xapi"]["xapi_launch_url"] ?? ""],
            "xapi_launch_key" => ["text", $data["launch"]["use_xapi"]["xapi_launch_key"] ?? ""],
            "xapi_launch_secret" => ["text", $data["launch"]["use_xapi"]["xapi_launch_secret"] ?? ""],
            "xapi_activity_id" => ["text", $data["launch"]["use_xapi"]["xapi_activity_id"] ?? ""],
            "custom_params" => ["text", $data["launch"]["custom_params"]],
            "keywords" => ["text", $data["group"]["keywords"]],
            "category" => ["text", $data["group"]["category"]],
            "remarks" => ["text", $data["hints"]["remarks"]],
            "lti_version" => ["text", $this->version],
        ] + ($this->isAdvantage()
            ? $this->getAdvantageFields($data["authentication"])
            : $this->get1p1Fields($data["authentication"]));

        if ($this->in_administration) {
            $fields["availability"] = ["integer", (int) $data["general"]["availability"]];
        } elseif ($this->tool_id === 0) {
            $fields["availability"] = ["integer", ilLTITool::AVAILABILITY_CREATE];
        }

        if ($this->tool_id > 0) {
            ilLTITool::update($this->tool_id, $fields);
            $this->saved_id = $this->tool_id;
            return null;
        }

        $this->saved_id = ilLTITool::create(
            $fields + ["client_id" => ["text", Util::getRandomString(15)]],
            $this->user->getId(),
            !$own_tool
        );

        return null;
    }

    /**
     * The tool the last successful save stored.
     */
    public function getSavedId(): int
    {
        return $this->saved_id;
    }

    /**
     * The credentials of the tool, which are the only fields the two LTI versions do not share.
     */
    private function getAuthentication(array $row): ILIAS\UI\Component\Input\Field\Section
    {
        return $this->ui_factory->input()->field()->section(
            $this->isAdvantage() ? $this->getAdvantageInputs($row) : $this->get1p1Inputs($row),
            $this->lng->txt("lti_con_prov_authentication"),
            $this->isAdvantage() ? $this->getAdvantageByline() : ""
        );
    }

    /**
     * The warning an LTI 1.1 tool carries, which its form shows above itself.
     */
    public static function getDeprecationNotice(
        ILIAS\UI\Factory $ui_factory,
        ilLanguage $lng,
        string $version
    ): ?ILIAS\UI\Component\MessageBox\MessageBox {
        if ($version === ilLTITool::VERSION_ADVANTAGE) {
            return null;
        }

        $link = $ui_factory->link()->standard($lng->txt("lti_1p1_deprecated_link"), self::DEPRECATION_URL)
            ->withOpenInNewViewport(true);

        return $ui_factory->messageBox()->confirmation($lng->txt("lti_1p1_deprecated_info"))->withLinks([$link]);
    }

    private function getAdvantageByline(): string
    {
        return $this->lng->txt($this->tool_id === 0 ? "lti_con_version_1.3_before_id" : "lti13_hints");
    }

    /**
     * @return array
     */
    private function get1p1Inputs(array $row): array
    {
        $field = $this->ui_factory->input()->field();
        $key_global = $field->optionalGroup([
            "provider_key" => $field->text($this->lng->txt("lti_con_prov_key"))
                ->withRequired(true)->withValue((string) ($row["provider_key"] ?? "")),
            "provider_secret" => $field->text($this->lng->txt("lti_con_prov_secret"))
                ->withRequired(true)->withValue((string) ($row["provider_secret"] ?? "")),
        ], $this->lng->txt("lti_con_prov_provider_key_global"), $this->lng->txt("lti_con_prov_provider_key_global_info"));

        return [
            "provider_url" => $field->text($this->lng->txt("lti_con_prov_url"))
                ->withRequired(true)->withValue((string) ($row["provider_url"] ?? "")),
            "key_global" => ($row["provider_key_customizable"] ?? 1) ? $key_global->withValue(null) : $key_global,
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    private function get1p1Fields(array $data): array
    {
        return [
            "provider_url" => ["text", $data["provider_url"]],
            "provider_key_customizable" => ["integer", (int) ($data["key_global"] === null)],
            "provider_key" => ["text", $data["key_global"]["provider_key"] ?? ""],
            "provider_secret" => ["text", $data["key_global"]["provider_secret"] ?? ""],
        ];
    }

    /**
     * @return array
     */
    private function getAdvantageInputs(array $row): array
    {
        $field = $this->ui_factory->input()->field();
        $content_item = $field->optionalGroup([
            "content_item_url" => $field->text($this->lng->txt("lti_con_content_item_url"))
                ->withValue((string) ($row["content_item_url"] ?? "")),
        ], $this->lng->txt("lti_con_content_item"));
        $key_type = $field->switchableGroup([
            self::KEY_TYPE_RSA => $field->group([
                "public_key" => $field->textarea(
                    $this->lng->txt("lti_con_key_type_rsa_public_key"),
                    $this->lng->txt("lti_con_key_type_rsa_public_key_info")
                )->withRequired(true)->withValue((string) ($row["public_key"] ?? "")),
            ], $this->lng->txt("lti_con_key_type_rsa")),
            self::KEY_TYPE_JWK => $field->group([
                "public_keyset" => $field->text($this->lng->txt("lti_con_key_type_jwk_url"))
                    ->withRequired(true)->withValue((string) ($row["public_keyset"] ?? "")),
            ], $this->lng->txt("lti_con_key_type_jwk")),
        ], $this->lng->txt("lti_con_key_type"))->withRequired(true);
        if (in_array($row["key_type"] ?? "", [self::KEY_TYPE_RSA, self::KEY_TYPE_JWK], true)) {
            $key_type = $key_type->withValue($row["key_type"]);
        }

        $inputs = [
            "provider_url" => $field->text($this->lng->txt("lti_con_tool_url"))
                ->withRequired(true)->withValue((string) ($row["provider_url"] ?? "")),
            "initiate_login" => $field->text($this->lng->txt("lti_con_initiate_login_url"))
                ->withRequired(true)->withValue((string) ($row["initiate_login"] ?? "")),
            "redirection_uris" => $field->textarea($this->lng->txt("lti_con_redirection_uris"))
                ->withRequired(true)->withValue(str_replace(",", "\n", (string) ($row["redirection_uris"] ?? ""))),
            "key_type" => $key_type,
            "content_item" => ($row["content_item"] ?? false) ? $content_item : $content_item->withValue(null),
            "grade_synchronization" => $field->checkbox(
                $this->lng->txt("lti_con_grade_synchronization"),
                $this->lng->txt("lti_con_grade_synchronization_info")
            )->withValue((bool) ($row["grade_synchronization"] ?? false)),
        ];

        if ($this->tool_id === 0) {
            return $inputs;
        }

        // read only data the tool needs when it is registered without dynamic registration
        $platform_data = [
            "lti_13_platform_id" => ILIAS_HTTP_PATH,
            "lti_13_client_id" => (string) ($row["client_id"] ?? ""),
            "lti_13_deployment_id" => (string) $this->tool_id,
            "lti_13_keyset_url" => ILIAS_HTTP_PATH . "/lticerts.php",
            "lti_13_token_url" => ILIAS_HTTP_PATH . "/ltitoken.php",
            "lti_13_authentication_url" => ILIAS_HTTP_PATH . "/ltiauth.php",
        ];
        foreach ($platform_data as $txt => $value) {
            $inputs[$txt] = $field->text($this->lng->txt($txt))->withValue($value)->withDisabled(true);
        }

        return $inputs;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getAdvantageFields(array $data): array
    {
        [$key_type, $key_data] = $data["key_type"];
        preg_match_all('/\S+/', (string) $data["redirection_uris"], $redirection_uris);

        return [
            "provider_url" => ["text", $data["provider_url"]],
            "initiate_login" => ["text", $data["initiate_login"]],
            "redirection_uris" => ["text", implode(",", $redirection_uris[0])],
            "key_type" => ["text", $key_type],
            "public_key" => ["text", $key_data["public_key"] ?? ""],
            "public_keyset" => ["text", $key_data["public_keyset"] ?? ""],
            "content_item" => ["integer", (int) ($data["content_item"] !== null)],
            "content_item_url" => ["text", $data["content_item"]["content_item_url"] ?? ""],
            "grade_synchronization" => ["integer", (int) $data["grade_synchronization"]],
            // an LTI Advantage tool keeps the LTI 1.1 key empty and customizable
            "provider_key_customizable" => ["integer", 1],
            "provider_key" => ["text", ""],
            "provider_secret" => ["text", ""],
        ];
    }

    /**
     * @return array
     */
    private function read(): array
    {
        return $this->tool_id === 0 ? [] : ilLTITool::read($this->tool_id);
    }
}
