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

namespace ILIAS\Glossary\Media;

use Generator;
use ilCtrl;
use ilObjGlossary;
use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ilGlossaryDefPageGUI;
use ilGlossaryTermGUI;
use ilTermDefinitionEditorGUI;

class TermPageRetrieval implements SubObjectRetrieval
{
    protected array $term_data;

    public function __construct(
        protected ilObjGlossary $glossary,
        protected ilCtrl $ctrl
    ) {
    }

    protected function getTermData(): array
    {
        if (isset($this->term_data)) {
            return $this->term_data;
        }
        $this->term_data = [];
        foreach ($this->glossary->getTermList() as $term) {
            $this->term_data[$term['id']] = $term;
        }
        return $this->term_data;
    }

    /**
     * @return string[]
     */
    public function getPossibleTypes(): Generator
    {
        yield 'term:pg';
    }

    /**
     * @return int[]
     */
    public function getAllIDsForType(string $type): Generator
    {
        if ($type !== 'term:pg') {
            return;
        }
        foreach ($this->getTermData() as $term) {
            yield (int) $term['id'];
        }
    }

    public function getLinkToSubObject(string $type, int $id): string
    {
        if ($type !== 'term:pg') {
            return '';
        }

        $this->ctrl->setParameterByClass(ilGlossaryDefPageGUI::class, 'term_id', $id);
        $link = $this->ctrl->getLinkTargetByClass([
            ilGlossaryTermGUI::class,
            ilTermDefinitionEditorGUI::class,
            ilGlossaryDefPageGUI::class
        ], 'edit');
        $this->ctrl->clearParameterByClass(ilGlossaryDefPageGUI::class, 'term_id');
        return $link;
    }

    public function getTitleOfSubObject(string $type, int $id): string
    {
        if ($type !== 'term:pg') {
            return '';
        }
        return $this->getTermData()[$id]['term'] ?? '';
    }
}
