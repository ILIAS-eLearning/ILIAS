<?php

declare(strict_types=1);

use OrgUnit\PublicApi\OrgUnitUserService;

class ilStudyProgrammeMailTemplateContext extends ilMailTemplateContext
{
    const ID = 'prg_context_manual';

    const TITLE = "prg_title";
    const DESCRIPTION = "prg_description";
    const TYPE = "prg_type";
    const LINK = "prg_link";
    const ORG_UNIT = "prg_orgus";
    const STATUS = "prg_status";
    const COMPLETION_DATE = "prg_completion_date";
    const COMPLETED_BY = "prg_completion_by";
    const POINTS_REQUIRED = "prg_points_required";
    const POINTS_CURRENT = "prg_points_current";
    const DEADLINE = "prg_deadline";
    const EXPIRE_DATE = "prg_expiry_date";
    const VALIDITY = "prg_validity";

    /**
     * @var ilLanguage
     */
    protected $lng;

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

    /**
     * @return string
     */
    public function getId() : string
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->lng->txt('prg_mail_context_title');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->lng->txt('prg_mail_context_info');
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    public function getSpecificPlaceholders() : array
    {
        $placeholders = array();

        $placeholders[self::TITLE] = array(
            'placeholder' => 'STUDY_PROGRAMME_TITLE',
            'label' => $this->lng->txt(self::TITLE)
        );

        $placeholders[self::DESCRIPTION] = array(
            'placeholder' => 'STUDY_PROGRAMME_DESCRIPTION',
            'label' => $this->lng->txt(self::DESCRIPTION)
        );

        $placeholders[self::TYPE] = array(
            'placeholder' => 'STUDY_PROGRAMME_TYPE',
            'label' => $this->lng->txt(self::TYPE)
        );

        $placeholders[self::LINK] = array(
            'placeholder' => 'STUDY_PROGRAMME_LINK',
            'label' => $this->lng->txt(self::LINK)
        );

        $placeholders[self::ORG_UNIT] = array(
            'placeholder' => 'STUDY_PROGRAMME_ORG_UNITS',
            'label' => $this->lng->txt(self::ORG_UNIT)
        );

        $placeholders[self::STATUS] = array(
            'placeholder' => 'STUDY_PROGRAMME_STATUS',
            'label' => $this->lng->txt(self::STATUS)
        );

        $placeholders[self::COMPLETION_DATE] = array(
            'placeholder' => 'STUDY_PROGRAMME_COMPLETION_DATE',
            'label' => $this->lng->txt(self::COMPLETION_DATE)
        );

        $placeholders[self::COMPLETED_BY] = array(
            'placeholder' => 'STUDY_PROGRAMME_COMPLETED_BY',
            'label' => $this->lng->txt(self::COMPLETED_BY)
        );

        $placeholders[self::POINTS_REQUIRED] = array(
            'placeholder' => 'STUDY_PROGRAMME_POINTS_REQUIRED',
            'label' => $this->lng->txt(self::POINTS_REQUIRED)
        );

        $placeholders[self::POINTS_CURRENT] = array(
            'placeholder' => 'STUDY_PROGRAMME_POINTS_CURRENT',
            'label' => $this->lng->txt(self::POINTS_CURRENT)
        );

        $placeholders[self::DEADLINE] = array(
            'placeholder' => 'STUDY_PROGRAMME_DEADLINE',
            'label' => $this->lng->txt(self::DEADLINE)
        );

        $placeholders[self::EXPIRE_DATE] = array(
            'placeholder' => 'STUDY_PROGRAMME_EXPIRE_DATE',
            'label' => $this->lng->txt(self::EXPIRE_DATE)
        );

        $placeholders[self::VALIDITY] = array(
            'placeholder' => 'STUDY_PROGRAMME_VALIDITY',
            'label' => $this->lng->txt(self::VALIDITY)
        );

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
    ) : string {
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

        $obj_id = ilObject::_lookupObjectId($context_parameters['ref_id']);

        /** @var ilObjStudyProgramme $obj */
        $obj = ilObjectFactory::getInstanceByRefId($context_parameters['ref_id']);

        $progress = $this->getNewestProgressForUser($obj, $recipient->getId());

        switch ($placeholder_id) {
            case self::TITLE:
                $string = ilObject::_lookupTitle($obj_id);
                break;
            case self::DESCRIPTION:
                $string = ilObject::_lookupDescription($obj_id);
                break;
            case self::TYPE:
                $string = '';
                if (!is_null($obj->getSubType())) {
                    $string = (string) $obj->getSubType()->getTitle();
                }
                break;
            case self::LINK:
                $string = ilLink::_getLink($context_parameters['ref_id'], 'prg');
                break;
            case self::ORG_UNIT:
                $string = ilObjUser::lookupOrgUnitsRepresentation($recipient->getId());
                break;
            case self::STATUS:
                $string = $obj->statusToRepr($progress->getStatus());
                break;
            case self::COMPLETION_DATE:
                $string = $this->date2String($progress->getCompletionDate());
                break;
            case self::COMPLETED_BY:
                $string = '';
                $id = $progress->getCompletionBy();
                if (!is_null($id) && ilObject::_exists($id)) {
                    $obj = ilObjectFactory::getInstanceByObjId($id);
                    if ($obj->getType() == 'usr') {
                        $string = (string) ilObjUser::_lookupLogin($id);
                    } else {
                        if ($ref_id = ilContainerReference::_lookupTargetRefId($id)) {
                            if (
                                ilObject::_exists($ref_id, true) &&
                                is_null(ilObject::_lookupDeletedDate($ref_id))
                            ) {
                                $string = ilContainerReference::_lookupTitle($id);
                            }
                        }
                    }
                }
                break;
            case self::POINTS_REQUIRED:
                $string = (string) $progress->getAmountOfPoints();
                break;
            case self::POINTS_CURRENT:
                $string = (string) $progress->getCurrentAmountOfPoints();
                break;
            case self::DEADLINE:
                $string = $this->date2String($progress->getDeadline());
                break;
            case self::VALIDITY:
                $string = $this->lng->txt('prg_quali_not_valid');
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $val_of_qual = $progress->getValidityOfQualification();

                if (!is_null($val_of_qual)) {
                    $vq_date = $val_of_qual->format('Y-m-d H:i:s');
                    if ($vq_date > $now) {
                        $string = $this->lng->txt('prg_quali_still_valid');
                    }
                }
                break;
            case self::EXPIRE_DATE:
                $string = $this->date2String($progress->getValidityOfQualification());
                break;
            default:
                $string = '';
        }

        return $string;
    }

    protected function getNewestProgressForUser(ilObjStudyProgramme $obj, int $user_id) : ilStudyProgrammeProgress
    {
        $progress = $obj->getProgressesOf($user_id);

        $successfully_progress = array_filter($progress, function (ilStudyProgrammeProgress $a) {
            return $a->isSuccessful() || $a->isSuccessfulExpired() || $a->isAccredited();
        });

        if (count($successfully_progress) == 0) {
            return $progress[0];
        }

        usort($successfully_progress, function (ilStudyProgrammeProgress $a, ilStudyProgrammeProgress $b) {
            if ($a->getCompletionDate() > $b->getCompletionDate()) {
                return -1;
            } elseif ($a->getCompletionDate() < $b->getCompletionDate()) {
                return 1;
            } else {
                return 0;
            }
        });

        return array_shift($successfully_progress);
    }

    protected function statusToRepr(int $status) : string
    {
        if ($status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            return $this->lng->txt("prg_status_in_progress");
        }
        if ($status == ilStudyProgrammeProgress::STATUS_COMPLETED) {
            return $this->lng->txt("prg_status_completed");
        }
        if ($status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            return $this->lng->txt("prg_status_accredited");
        }
        if ($status == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            return $this->lng->txt("prg_status_not_relevant");
        }
        if ($status == ilStudyProgrammeProgress::STATUS_FAILED) {
            return $this->lng->txt("prg_status_failed");
        }
        throw new ilException("Unknown status: '$status'");
    }

    protected function date2String(DateTime $date_time = null) : string
    {
        if (is_null($date_time)) {
            return '';
        }

        return $date_time->format('d-m-Y H:i:s');
    }
}
