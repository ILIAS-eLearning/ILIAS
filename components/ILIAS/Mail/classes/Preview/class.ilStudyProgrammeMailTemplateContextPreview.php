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

class ilStudyProgrammeMailTemplateContextPreview extends ilStudyProgrammeMailTemplateContext
{
    public const ID = 'prg_context_manual_preview';

    protected ilLanguage $lng;

    public function getId(): string
    {
        return self::ID;
    }

    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ): string {
        switch ($placeholder_id) {
            case ilStudyProgrammeMailTemplateContext::TITLE:
                $string = 'programme title';
                break;
            case ilStudyProgrammeMailTemplateContext::DESCRIPTION:
                $string = 'programme description';
                break;
            case ilStudyProgrammeMailTemplateContext::TYPE:
                $string = 'prg subtype';
                break;
            case ilStudyProgrammeMailTemplateContext::LINK:
                $string = '<a href="#">Link</a>';
                break;
            case ilStudyProgrammeMailTemplateContext::ORG_UNIT:
                $string = 'OrgUnit';
                break;
            case ilStudyProgrammeMailTemplateContext::STATUS:
                $string = 'completed';
                break;
            case ilStudyProgrammeMailTemplateContext::COMPLETION_DATE:
                $string = $this->date2String((new \DateTimeImmutable())->sub((new DateInterval('P1D'))));
                break;
            case ilStudyProgrammeMailTemplateContext::COMPLETED_BY:
                $string = '1,2,3';
                break;
            case ilStudyProgrammeMailTemplateContext::POINTS_REQUIRED:
                $string = '90';
                break;
            case ilStudyProgrammeMailTemplateContext::POINTS_CURRENT:
                $string = '88';
                break;
            case ilStudyProgrammeMailTemplateContext::DEADLINE:
                $string = $this->date2String((new \DateTimeImmutable())->add((new DateInterval('P3D'))));
                break;
            case ilStudyProgrammeMailTemplateContext::VALIDITY:
                $string = $this->lng->txtlng('prg', 'prg_still_valid', 'de');
                break;
            case ilStudyProgrammeMailTemplateContext::EXPIRE_DATE:
                $string = $this->date2String((new \DateTimeImmutable())->add((new DateInterval('P5D'))));
                break;
            default:
                $string = '';
        }

        return $string;
    }
}
