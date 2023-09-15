<?php

declare(strict_types=1);

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

use OrgUnit\PublicApi\OrgUnitUserService;

class ilStudyProgrammeMailTemplateContext extends ilMailTemplateContext
{
    public const ID = 'prg_context_manual';

    private const TITLE = "prg_title";
    private const DESCRIPTION = "prg_description";
    private const TYPE = "prg_type";
    private const LINK = "prg_link";
    private const ORG_UNIT = "prg_orgus";
    private const STATUS = "prg_status";
    private const COMPLETION_DATE = "prg_completion_date";
    private const COMPLETED_BY = "prg_completion_by";
    private const POINTS_REQUIRED = "prg_points_required";
    private const POINTS_CURRENT = "prg_points_current";
    private const DEADLINE = "prg_deadline";
    private const EXPIRE_DATE = "prg_expiry_date";
    private const VALIDITY = "prg_validity";

    private const DATE_FORMAT = 'd-m-Y H:i:s';

    protected ilLanguage $lng;

    public function __construct(
        OrgUnitUserService $orgUnitUserService = null,
        ilMailEnvironmentHelper $envHelper = null,
        ilMailUserHelper $usernameHelper = null,
        ilMailLanguageHelper $languageHelper = null
    ) {
        parent::__construct(
            $orgUnitUserService,
            $envHelper,
            $usernameHelper,
            $languageHelper
        );

        global $DIC;

        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        return $this->lng->txt('prg_mail_context_title');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('prg_mail_context_info');
    }

    /**
     * Return an array of placeholders
     */
    public function getSpecificPlaceholders(): array
    {
        $placeholders = [];

        $placeholders[self::TITLE] = [
            'placeholder' => 'STUDY_PROGRAMME_TITLE',
            'label' => $this->lng->txt(self::TITLE)
        ];

        $placeholders[self::DESCRIPTION] = [
            'placeholder' => 'STUDY_PROGRAMME_DESCRIPTION',
            'label' => $this->lng->txt(self::DESCRIPTION)
        ];

        $placeholders[self::TYPE] = [
            'placeholder' => 'STUDY_PROGRAMME_TYPE',
            'label' => $this->lng->txt(self::TYPE)
        ];

        $placeholders[self::LINK] = [
            'placeholder' => 'STUDY_PROGRAMME_LINK',
            'label' => $this->lng->txt(self::LINK)
        ];

        $placeholders[self::ORG_UNIT] = [
            'placeholder' => 'STUDY_PROGRAMME_ORG_UNITS',
            'label' => $this->lng->txt(self::ORG_UNIT)
        ];

        $placeholders[self::STATUS] = [
            'placeholder' => 'STUDY_PROGRAMME_STATUS',
            'label' => $this->lng->txt(self::STATUS)
        ];

        $placeholders[self::COMPLETION_DATE] = [
            'placeholder' => 'STUDY_PROGRAMME_COMPLETION_DATE',
            'label' => $this->lng->txt(self::COMPLETION_DATE)
        ];

        $placeholders[self::COMPLETED_BY] = [
            'placeholder' => 'STUDY_PROGRAMME_COMPLETED_BY',
            'label' => $this->lng->txt(self::COMPLETED_BY)
        ];

        $placeholders[self::POINTS_REQUIRED] = [
            'placeholder' => 'STUDY_PROGRAMME_POINTS_REQUIRED',
            'label' => $this->lng->txt(self::POINTS_REQUIRED)
        ];

        $placeholders[self::POINTS_CURRENT] = [
            'placeholder' => 'STUDY_PROGRAMME_POINTS_CURRENT',
            'label' => $this->lng->txt(self::POINTS_CURRENT)
        ];

        $placeholders[self::DEADLINE] = [
            'placeholder' => 'STUDY_PROGRAMME_DEADLINE',
            'label' => $this->lng->txt(self::DEADLINE)
        ];

        $placeholders[self::EXPIRE_DATE] = [
            'placeholder' => 'STUDY_PROGRAMME_EXPIRE_DATE',
            'label' => $this->lng->txt(self::EXPIRE_DATE)
        ];

        $placeholders[self::VALIDITY] = [
            'placeholder' => 'STUDY_PROGRAMME_VALIDITY',
            'label' => $this->lng->txt(self::VALIDITY)
        ];

        return $placeholders;
    }

    /**
     * @inheritdocs
     */
    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ): string {
        if (is_null($recipient)) {
            return '';
        }

        if (!in_array($placeholder_id, [
            self::TITLE,
            self::DESCRIPTION,
            self::TYPE,
            self::LINK,
            self::ORG_UNIT,
            self::STATUS,
            self::COMPLETION_DATE,
            self::COMPLETED_BY,
            self::POINTS_REQUIRED,
            self::POINTS_CURRENT,
            self::DEADLINE,
            self::EXPIRE_DATE,
            self::VALIDITY
        ])) {
            return '';
        }

        /** @var ilObjStudyProgramme $obj */
        $prg = ilObjectFactory::getInstanceByRefId((int)$context_parameters['ref_id']);
        $assignments = $prg->getAssignmentsOfSingleProgramForUser($recipient->getId());
        $latest = $this->getLatestAssignment($assignments);
        $latest_successful = $this->getLatestSuccessfulAssignment($assignments);

        switch ($placeholder_id) {
            case self::TITLE:
                $string = $prg->getTitle();
                break;
            case self::DESCRIPTION:
                $string = $prg->getDescription();
                break;
            case self::TYPE:
                $string = '';
                if (!is_null($prg->getSubType())) {
                    $string = $prg->getSubType()->getTitle();
                }
                break;
            case self::LINK:
                $string = ilLink::_getLink((int)$context_parameters['ref_id'], 'prg') . ' ';
                break;
            case self::ORG_UNIT:
                $string = ilObjUser::lookupOrgUnitsRepresentation($recipient->getId());
                break;
            case self::STATUS:
                $string = $this->statusToRepr($latest->getProgressTree()->getStatus(), $recipient->getLanguage());
                break;
            case self::COMPLETION_DATE:
                $string = $this->date2String($latest->getProgressTree()->getCompletionDate());
                break;
            case self::COMPLETED_BY:
                $string = '';
                $id = $latest->getProgressTree()->getCompletionBy();
                if (!is_null($id) && ilObject::_exists($id)) {
                    $obj = ilObjectFactory::getInstanceByObjId($id);
                    if ($obj->getType() === 'usr') {
                        $string = ilObjUser::_lookupLogin($id);
                    } elseif ($ref_id = ilContainerReference::_lookupTargetRefId($id)) {
                        if (
                            ilObject::_exists($ref_id, true) &&
                            is_null(ilObject::_lookupDeletedDate($ref_id))
                        ) {
                            $string = ilContainerReference::_lookupTitle($id);
                        }
                    }
                }
                break;
            case self::POINTS_REQUIRED:
                $string = (string) $latest->getProgressTree()->getAmountOfPoints();
                break;
            case self::POINTS_CURRENT:
                $string = (string) $latest->getProgressTree()->getCurrentAmountOfPoints();
                break;
            case self::DEADLINE:
                $string = $this->date2String($latest->getProgressTree()->getDeadline());
                break;
            case self::VALIDITY:
                $string = '-';
                if ($latest_successful) {
                    $langvar = $latest_successful->getProgressTree()->isInvalidated() ? 'prg_not_valid' : 'prg_still_valid';
                    $string = $this->lng->txtlng('prg', $langvar, $recipient->getLanguage());
                }
                break;

            case self::EXPIRE_DATE:
                $string = '-';
                if ($latest_successful) {
                    $string = $this->date2String($latest_successful->getProgressTree()->getValidityOfQualification());
                }
                break;
            default:
                throw new \Exception("cannot resolve placeholder: " . $placeholder_id);
                $string = '';
        }

        return $string;
    }

    protected function getLatestAssignment(array $assignments): ilPRGAssignment
    {
        usort($assignments, static function (ilPRGAssignment $a, ilPRGAssignment $b): int {
            $a_dat =$a->getProgressTree()->getAssignmentDate();
            $b_dat =$b->getProgressTree()->getAssignmentDate();
            if ($a_dat > $b_dat) {
                return -1;
            } elseif ($a_dat < $b_dat) {
                return 1;
            } else {
                return 0;
            }
        });
        return array_shift($assignments);
    }

    protected function getLatestSuccessfulAssignment(array $assignments): ?ilPRGAssignment
    {
        $successful = array_filter(
            $assignments,
            fn ($ass) => $ass->getProgressTree()->isSuccessful()
        );
        if (count($successful) === 0) {
            return null;
        }

        usort($successful, static function (ilPRGAssignment $a, ilPRGAssignment $b): int {
            $a_dat =$a->getProgressTree()->getCompletionDate();
            $b_dat =$b->getProgressTree()->getCompletionDate();
            if ($a_dat > $b_dat) {
                return -1;
            } elseif ($a_dat < $b_dat) {
                return 1;
            } else {
                return 0;
            }
        });
        return array_shift($successful);
    }



    protected function statusToRepr(int $status, string $lang): string
    {
        if ($status === ilPRGProgress::STATUS_IN_PROGRESS) {
            return $this->lng->txtlng('prg', 'prg_status_in_progress', $lang);
        }
        if ($status === ilPRGProgress::STATUS_COMPLETED) {
            return $this->lng->txtlng('prg', 'prg_status_completed', $lang);
        }
        if ($status === ilPRGProgress::STATUS_ACCREDITED) {
            return $this->lng->txtlng('prg', 'prg_status_accredited', $lang);
        }
        if ($status === ilPRGProgress::STATUS_NOT_RELEVANT) {
            return $this->lng->txtlng('prg', 'prg_status_not_relevant', $lang);
        }
        if ($status === ilPRGProgress::STATUS_FAILED) {
            return $this->lng->txtlng('prg', 'prg_status_failed', $lang);
        }

        throw new ilException("Unknown status: '$status'");
    }

    protected function date2String(DateTimeImmutable $date_time = null): string
    {
        if (is_null($date_time)) {
            return '';
        }
        return $date_time->format(self::DATE_FORMAT);
    }
}
