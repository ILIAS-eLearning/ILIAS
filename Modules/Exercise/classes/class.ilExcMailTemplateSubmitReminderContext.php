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

use OrgUnit\PublicApi\OrgUnitUserService;

/**
 * Handles exercise Submit reminder mail placeholders
 * If all contexts are using the same placeholders,constructor etc. todo: create base class.
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcMailTemplateSubmitReminderContext extends ilMailTemplateContext
{
    public const ID = 'exc_context_submit_rmd';

    protected ilLanguage $lng;
    protected ilObjectDataCache $obj_data_cache;

    public function __construct(
        OrgUnitUserService $orgUnitUserService = null,
        ilMailEnvironmentHelper $envHelper = null,
        ilMailUserHelper $usernameHelper = null,
        ilMailLanguageHelper $languageHelper = null
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        parent::__construct(
            $orgUnitUserService,
            $envHelper,
            $usernameHelper,
            $languageHelper
        );

        $this->lng = $DIC->language();
        if (isset($DIC["ilObjDataCache"])) {
            $this->obj_data_cache = $DIC["ilObjDataCache"];
        }
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('exc');

        return $lng->txt('exc_mail_context_submit_reminder_title');
    }

    public function getDescription(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('exc');

        return $lng->txt('exc_mail_context_submit_reminder_info');
    }

    public function getSpecificPlaceholders(): array
    {
        $lng = $this->lng;
        $lng->loadLanguageModule('exc');

        $placeholders = array();

        $placeholders['assignment_title'] = array(
            'placeholder' => 'ASSIGNMENT_TITLE',
            'label' => $lng->txt('exc_mail_context_reminder_assignment_title')
        );
        $placeholders['exercise_title'] = array(
            'placeholder' => 'EXERCISE_TITLE',
            'label' => $lng->txt('exc_mail_context_reminder_exercise_title')
        );

        $placeholders['assignment_link'] = array(
            'placeholder' => 'ASSIGNMENT_LINK',
            'label' => $lng->txt('perma_link')
        );

        return $placeholders;
    }

    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null
    ): string {
        $ilObjDataCache = $this->obj_data_cache;

        if ($placeholder_id == 'assignment_title') {
            return ilExAssignment::lookupTitle((int) $context_parameters["ass_id"]);
        } else {
            if ($placeholder_id == 'exercise_title') {
                return $ilObjDataCache->lookupTitle((int) $context_parameters["exc_id"]);
            } else {
                if ($placeholder_id == 'assignment_link') {
                    return ilLink::_getLink(
                        $context_parameters["exc_ref"],
                        "exc",
                        array(),
                        "_" . $context_parameters["ass_id"]
                    );
                }
            }
        }

        return '';
    }
}
