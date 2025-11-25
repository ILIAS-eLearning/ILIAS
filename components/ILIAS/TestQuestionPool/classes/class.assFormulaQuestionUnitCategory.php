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
 * Formula Question Unit Category
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ingroup components\ILIASTestQuestionPool
 */
class assFormulaQuestionUnitCategory
{
    private int $id = 0;
    private string $category = '';
    private int $question_fi = 0;

    public function initFormArray(array $data): void
    {
        $this->id = (int) $data['category_id'];
        $this->category = $data['category'];
        $this->question_fi = (int) $data['question_fi'];
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getSanitizedCategory(): string
    {
        return $this->sanitizeString($this->getCategory());
    }

    public function setQuestionFi(int $question_fi): void
    {
        $this->question_fi = $question_fi;
    }

    public function getQuestionFi(): int
    {
        return $this->question_fi;
    }

    public function getDisplayString(): string
    {
        global $DIC;

        $category = $this->getCategory();
        $txt = $DIC->language()->txt("qpl_qst_formulaquestion_{$category}");
        return strcmp("-qpl_qst_formulaquestion_{$category}-", $txt) !== 0
            ? $this->sanitizeString($txt)
            : $this->getSanitizedCategory();
    }

    private function sanitizeString(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
    }
}
