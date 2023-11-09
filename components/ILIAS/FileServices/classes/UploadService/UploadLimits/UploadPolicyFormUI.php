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
 */

declare(strict_types=1);

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Transformation;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class UploadPolicyFormUI
{
    public const INPUT_SECTION_GENERAL = "general_section";
    public const INPUT_SECTION_AUDIENCE = "audience_section";
    public const INPUT_SECTION_VALIDITY = "validity_section";

    public const HIDDEN_INPUT_POLICY_ID = "policy_id";
    public const INPUT_FIELD_TITLE = "title";
    public const INPUT_FIELD_UPLOAD_LIMIT = "upload_limit";
    public const INPUT_FIELD_AUDIENCE = "audience";
    public const INPUT_FIELD_AUDIENCE_TYPE = "audience_type";
    public const INPUT_FIELD_AUDIENCE_DATA = "audience_data";
    public const INPUT_OPTION_ALL_USERS = "all_users";
    public const INPUT_OPTION_GLOBAL_ROLES = "global_roles";
    public const INPUT_FIELD_GLOBAL_ROLES = "global_roles";
    public const INPUT_FIELD_VALID_UNTIL = "valid_until";
    public const INPUT_FIELD_ACTIVE = "active";

    protected const LABEL_INPUT_SECTION_GENERAL = "general";
    protected const LABEL_INPUT_SECTION_AUDIENCE = "policy_audience";
    protected const LABEL_INPUT_SECTION_VALIDITY = "policy_validity";

    protected const LABEL_INPUT_FIELD_TITLE = "title";
    protected const LABEL_INPUT_FIELD_UPLOAD_LIMIT = "policy_upload_limit";
    protected const LABEL_INPUT_FIELD_AUDIENCE = "policy_audience";
    protected const LABEL_INPUT_OPTION_ALL_USERS = "all_users";
    protected const LABEL_INPUT_OPTION_GLOBAL_ROLES = "all_global_roles";
    protected const LABEL_INPUT_FIELD_GLOBAL_ROLES = "all_global_roles";
    protected const LABEL_INPUT_FIELD_VALID_UNTIL = "policy_valid_until";
    protected const LABEL_INPUT_FIELD_ACTIVE = "active";

    protected const BYLINE_INPUT_FIELD_TITLE = "policy_title_desc";
    protected const BYLINE_INPUT_FIELD_UPLOAD_LIMIT = "policy_upload_limit_desc";
    protected const BYLINE_INPUT_OPTION_ALL_USERS = "policy_audience_all_users_option_desc";
    protected const BYLINE_INPUT_OPTION_GLOBAL_ROLES = "policy_audience_global_roles_option_desc";
    protected const BYLINE_INPUT_FIELD_VALID_UNTIL = "policy_valid_until_desc";


    protected StandardForm $form;


    public function __construct(
        protected UploadPolicyDBRepository $upload_policy_db_repository,
        protected ilCtrlInterface $ctrl,
        protected HttpServices $http,
        protected ilLanguage $language,
        protected ilRbacReview $rbac_review,
        protected RefineryFactory $refinery,
        protected UIFactory $ui_factory
    ) {
        $policy_id = null;
        $upload_policy = null;

        // Retrieve upload_policy if request contains policy_id
        if ($this->http->wrapper()->query()->has(UploadPolicy::POLICY_ID)) {
            $policy_id = $this->http->wrapper()->query()->retrieve(
                UploadPolicy::POLICY_ID,
                $this->refinery->kindlyTo()->int()
            );
            $upload_policy = $this->upload_policy_db_repository->get($policy_id);
        }

        // Create hidden input (policy_id)
        $policy_id_hidden_input = $this->ui_factory->input()->field()->hidden()->withValue((string) $policy_id);

        // Create general section and its inputs (title and upload_limit)
        $policy_title_input = $this->ui_factory->input()->field()->text(
            $this->language->txt(self::LABEL_INPUT_FIELD_TITLE),
            $this->language->txt(self::BYLINE_INPUT_FIELD_TITLE)
        )->withValue(
            $upload_policy?->getTitle() ?? ""
        )->withRequired(true);
        $upload_limit_input = $this->ui_factory->input()->field()->numeric(
            $this->language->txt(self::LABEL_INPUT_FIELD_UPLOAD_LIMIT),
            $this->language->txt(self::BYLINE_INPUT_FIELD_UPLOAD_LIMIT)
        )->withValue(
            $upload_policy?->getUploadLimitInMB()
        )->withRequired(true);
        $general_section = $this->ui_factory->input()->field()->section(
            [
                self::INPUT_FIELD_TITLE => $policy_title_input,
                self::INPUT_FIELD_UPLOAD_LIMIT => $upload_limit_input
            ],
            $this->language->txt(self::LABEL_INPUT_SECTION_GENERAL)
        );

        // Create audience section and its input (audience)
        $all_users_option = $this->ui_factory->input()->field()->group(
            [],
            $this->language->txt(self::LABEL_INPUT_OPTION_ALL_USERS),
            $this->language->txt(self::BYLINE_INPUT_OPTION_ALL_USERS)
        );
        $global_role_input = $this->ui_factory->input()->field()->multiSelect(
            $this->language->txt(self::LABEL_INPUT_FIELD_GLOBAL_ROLES) . ":",
            $this->getGlobalRoles()
        )->withValue(
            $upload_policy?->getAudience()['global_roles'] ?? []
        );
        $global_roles_option = $this->ui_factory->input()->field()->group(
            [
                self::INPUT_FIELD_GLOBAL_ROLES => $global_role_input
            ],
            $this->language->txt(self::LABEL_INPUT_OPTION_GLOBAL_ROLES),
            $this->language->txt(self::BYLINE_INPUT_OPTION_GLOBAL_ROLES)
        );
        $audience_input = $this->ui_factory->input()->field()->switchableGroup(
            [
                self::INPUT_OPTION_ALL_USERS => $all_users_option,
                self::INPUT_OPTION_GLOBAL_ROLES => $global_roles_option
            ],
            $this->language->txt(self::LABEL_INPUT_FIELD_AUDIENCE)
        )->withValue(
            ($upload_policy?->getAudienceType() === UploadPolicy::AUDIENCE_TYPE_GLOBAL_ROLE) ? self::INPUT_OPTION_GLOBAL_ROLES : self::INPUT_OPTION_ALL_USERS
        )->withRequired(true);
        $audience_section = $this->ui_factory->input()->field()->section(
            [
                self::INPUT_FIELD_AUDIENCE => $audience_input
            ],
            $this->language->txt(self::LABEL_INPUT_SECTION_AUDIENCE)
        )->withAdditionalTransformation(
            $this->getAudienceTransformation()
        );

        // Create validity section and its inputs (valid_until and active)
        $valid_until_input = $this->ui_factory->input()->field()->dateTime(
            $this->language->txt(self::LABEL_INPUT_FIELD_VALID_UNTIL),
            $this->language->txt(self::BYLINE_INPUT_FIELD_VALID_UNTIL)
        )->withValue(
            $upload_policy?->getValidUntil()
        );
        $active_input = $this->ui_factory->input()->field()->checkbox(
            $this->language->txt(self::LABEL_INPUT_FIELD_ACTIVE)
        )->withValue(
            $upload_policy?->isActive()
        );
        $validity_section = $this->ui_factory->input()->field()->section(
            [
                self::INPUT_FIELD_VALID_UNTIL => $valid_until_input,
                self::INPUT_FIELD_ACTIVE => $active_input
            ],
            $this->language->txt(self::LABEL_INPUT_SECTION_VALIDITY)
        );

        // Create form
        $this->form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTargetByClass(ilUploadLimitsOverviewGUI::class, ilUploadLimitsOverviewGUI::CMD_SAVE_UPLOAD_POLICY),
            [
                self::INPUT_SECTION_GENERAL => $general_section,
                self::INPUT_SECTION_AUDIENCE => $audience_section,
                self::INPUT_SECTION_VALIDITY => $validity_section,
                self::HIDDEN_INPUT_POLICY_ID => $policy_id_hidden_input,
            ]
        );
    }


    public function getForm(): StandardForm
    {
        return $this->form;
    }


    protected function getGlobalRoles(): array
    {
        $global_roles = $this->rbac_review->getRolesForIDs(
            $this->rbac_review->getGlobalRoles(),
            false
        );

        $roles = [];
        foreach ($global_roles as $global_role) {
            $roles[$global_role['rol_id']] = $global_role['title'];
        }

        return $roles;
    }

    protected function getAudienceTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(function ($audience_section): array {
            switch ($audience_section[self::INPUT_FIELD_AUDIENCE][0]) {
                case self::INPUT_OPTION_GLOBAL_ROLES:
                    $audience_type = UploadPolicy::AUDIENCE_TYPE_GLOBAL_ROLE;
                    break;
                case self::INPUT_OPTION_ALL_USERS:
                default:
                    $audience_type = UploadPolicy::AUDIENCE_TYPE_ALL_USERS;
                    break;
            }

            return [
                self::INPUT_FIELD_AUDIENCE_TYPE => $audience_type,
                self::INPUT_FIELD_AUDIENCE_DATA => $audience_section[self::INPUT_FIELD_AUDIENCE][1]
            ];
        });
    }
}
