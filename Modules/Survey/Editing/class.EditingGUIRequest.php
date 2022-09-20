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

namespace ILIAS\Survey\Editing;

use ILIAS\Repository\BaseGUIRequest;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class EditingGUIRequest
{
    use BaseGUIRequest;

    protected array $params;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getSelectedQuestionTypes(): string
    {
        return $this->str("sel_question_types");
    }

    public function getNewId(): int
    {
        return $this->int("new_id");
    }

    public function getNewType(): string
    {
        return $this->str("new_type");
    }

    public function getFetchAll(): int
    {
        return $this->int("fetchall");
    }

    public function getTerm(): string
    {
        return $this->str("term");
    }

    /** @return string[] */
    public function getIds(): array
    {
        return $this->strArray("id");
    }

    /** @return string[] */
    public function getUserIds(): array
    {
        return $this->strArray("chbUser");
    }

    /** @return int[] */
    public function getAppraiseeIds(): array
    {
        return $this->intArray("appr_id");
    }

    public function getAppraiseeId(): int
    {
        return $this->int("appr_id");
    }

    public function getAppr360(): int
    {
        return $this->int("appr360");
    }

    public function getRate360(): int
    {
        return $this->int("rate360");
    }

    /** @return string[] */
    public function getCodes(): array
    {
        return $this->strArray("chb_code");
    }

    /** @return string[] */
    public function getCodesPar($key): array
    {
        return $this->strArray("chb_" . $key);
    }

    public function getCodeMailPart($key): string
    {
        return $this->str("m_" . $key);
    }

    public function getExternalText(): string
    {
        return $this->str("externaltext");
    }

    public function getSaveMessageTitle(): string
    {
        return $this->str("savemessagetitle");
    }

    public function getSaveMessage(): int
    {
        return $this->int("savemessage");
    }

    public function getPoolUsage(): int
    {
        return $this->int("usage");
    }

    public function getLang(): string
    {
        return $this->str("lang");
    }

    public function getNrOfCodes(): int
    {
        return $this->int("nrOfCodes");
    }

    /** @return string[] */
    public function getOrder(): array
    {
        return $this->strArray("order");
    }

    /** @return array[] */
    public function getBlockOrder(): array
    {
        return $this->arrayArray("block_order");
    }

    /** @return int[] */
    public function getObligatory(): array
    {
        return $this->intArray("obligatory");
    }

    public function getQuestionIdsFromString(): array
    {
        return explode(";", $this->str("question_ids"));
    }

    public function getSelectedPool(): int
    {
        return $this->int("sel_spl");
    }

    public function getDataType(): int
    {
        return $this->int("datatype");
    }

    /** @return int[] */
    public function getQuestionIds(): array
    {
        $qids = $this->intArray("q_id");
        if (count($qids) === 0) {
            $qids = $this->intArray("qids");
        }
        return $qids;
    }

    public function getQuestionId(): int
    {
        if (!$this->isArray("q_id")) {
            return $this->int("q_id");
        }
        return 0;
    }

    public function getBlockId(): int
    {
        return $this->int("bl_id");
    }

    // e.g. ilObjSurvey::PRINT_HIDE_LABELS
    public function getExportLabel(): int
    {
        return $this->int("export_label");
    }

    public function getPoolName(): string
    {
        return $this->str("name_spl");
    }

    /** @return int[] */
    public function getBlockIds(): array
    {
        return $this->intArray("cb");
    }

    public function getQuestionType(): int
    {
        return $this->int("qtype");
    }

    // this is set in the "create new question" dialog
    // and sets the page target position, "fst", "1" (after first page), "2", ...
    public function getTargetPosition(): string
    {
        return $this->str("pgov");
    }

    // e.g. set when adding questions from pool
    // "2a" (after question id "2"), "3b" (before question id "3")
    public function getTargetQuestionPosition(): string
    {
        return $this->str("pgov_pos");
    }

    /** @return int[] */
    public function getHeadings(): array
    {
        return $this->intArray("heading");
    }

    public function getPage(): int
    {
        return $this->int("pg");
    }

    public function getNewForSurvey(): int
    {
        return $this->int("new_for_survey");
    }

    public function getHForm($key): string
    {
        return $this->str("il_hform_" . $key);
    }

    public function getJump(): string
    {
        return $this->str("jump");
    }

    public function getCheckSum(): string
    {
        return $this->str("csum");
    }

    public function getOldPosition(): int
    {
        return $this->int("old_pos");
    }

    public function getCodeIds(): array
    {
        $ids = $this->str("new_ids");
        if ($ids !== "") {
            $ids = explode(";", $ids);
        } else {
            $ids = $this->strArray("chb_code");
        }
        return $ids;
    }

    public function getRaterIds(): array
    {
        $ids = $this->str("rater_id");
        if ($ids !== "") {
            $ids = explode(";", $ids);
        } else {
            $ids = $this->strArray("rtr_id");
        }
        return $ids;
    }

    public function getRaterId(): string
    {
        return $this->str("rater_id");
    }

    public function getRecipients(): string
    {
        return $this->str("recipients");
    }

    public function getMail(): string
    {
        return $this->str("mail");
    }

    public function getBaseClass(): string
    {
        return $this->str("baseClass");
    }

    public function getReturnedFromMail(): int
    {
        return $this->int("returned_from_mail");
    }

    public function getUseAnonymousId(): int
    {
        return $this->int("use_anonymous_id");
    }

    // constraint related...

    public function getStep(): int
    {
        return $this->int("step");
    }

    public function getStart(): int
    {
        return $this->int("start");
    }

    public function getPrecondition(): string
    {
        return $this->str("precondition");
    }

    public function getConstraintPar($key): string
    {
        return $this->str($key);
    }

    public function getIncludeElements(): array
    {
        return $this->strArray("includeElements");
    }

    // skill related

    public function getObjId(): int
    {
        return $this->int("obj_id");
    }

    public function getSelectedSkill(): string
    {
        return $this->str("selected_skill");
    }

    public function getSkillId(): int
    {
        return $this->int("sk_id");
    }

    public function getTrefId(): int
    {
        return $this->int("tref_id");
    }

    public function getSkill(): string
    {
        return $this->str("skill");
    }

    public function getThresholds(): array
    {
        return $this->strArray("threshold");
    }

    public function getPrintSelection(): string
    {
        return $this->str("print_selection");
    }
}
