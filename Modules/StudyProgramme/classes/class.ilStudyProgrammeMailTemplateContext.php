<?php declare(strict_types=1);

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

    public function getId() : string
    {
        return self::ID;
    }

    public function getTitle() : string
    {
        return $this->lng->txt('prg_mail_context_title');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('prg_mail_context_info');
    }

    /**
     * Return an array of placeholders
     */
    public function getSpecificPlaceholders() : array
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
                    $string = $obj->getSubType()->getTitle();
                }
                break;
            case self::LINK:
                $string = ilLink::_getLink($context_parameters['ref_id'], 'prg');
                break;
            case self::ORG_UNIT:
                $string = ilObjUser::lookupOrgUnitsRepresentation($recipient->getId());
                break;
            case self::STATUS:
                $string = $this->statusToRepr($progress->getStatus(), $recipient->getLanguage());
                break;
            case self::COMPLETION_DATE:
                $string = $this->date2String($progress->getCompletionDate());
                break;
            case self::COMPLETED_BY:
                $string = '';
                $id = $progress->getCompletionBy();
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
                $string = (string) $progress->getAmountOfPoints();
                break;
            case self::POINTS_CURRENT:
                $string = (string) $progress->getCurrentAmountOfPoints();
                break;
            case self::DEADLINE:
                $string = $this->date2String($progress->getDeadline());
                break;
            case self::VALIDITY:
                $now = new DateTimeImmutable();

                $string = '-';
                if (!is_null($progress->hasValidQualification($now))) {
                    $string = $this->lng->txtlng('prg', 'prg_not_valid', $recipient->getLanguage());
                    if ($progress->hasValidQualification($now)) {
                        $string = $this->lng->txtlng('prg', 'prg_still_valid', $recipient->getLanguage());
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

        $successfully_progress = array_filter($progress, static function (ilStudyProgrammeProgress $a) : bool {
            return $a->isSuccessful() || $a->isSuccessfulExpired() || $a->isAccredited();
        });

        if (count($successfully_progress) === 0) {
            return $progress[0];
        }

        usort($successfully_progress, static function (ilStudyProgrammeProgress $a, ilStudyProgrammeProgress $b) : int {
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

    protected function statusToRepr(int $status, string $lang) : string
    {
        if ($status === ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            return $this->lng->txtlng('prg', 'prg_status_in_progress', $lang);
        }
        if ($status === ilStudyProgrammeProgress::STATUS_COMPLETED) {
            return $this->lng->txtlng('prg', 'prg_status_completed', $lang);
        }
        if ($status === ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            return $this->lng->txtlng('prg', 'prg_status_accredited', $lang);
        }
        if ($status === ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            return $this->lng->txtlng('prg', 'prg_status_not_relevant', $lang);
        }
        if ($status === ilStudyProgrammeProgress::STATUS_FAILED) {
            return $this->lng->txtlng('prg', 'prg_status_failed', $lang);
        }
            
        throw new ilException("Unknown status: '$status'");
    }

    protected function date2String(DateTimeImmutable $date_time = null) : string
    {
        if (is_null($date_time)) {
            return '';
        }
        return $date_time->format(self::DATE_FORMAT);
    }
}
