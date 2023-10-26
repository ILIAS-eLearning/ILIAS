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
 * abstract parent class that manages/holds the data for a question set configuration
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
abstract class ilTestQuestionSetConfig
{
    public function __construct(
        protected ilTree $tree,
        protected ilDBInterface $db,
        protected ilLanguage $lng,
        protected ilLogger $log,
        protected ilComponentRepository $component_repository,
        protected ilObjTest $test_obj,
        protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo
    ) {
    }

    abstract public function loadFromDb(): void;
    abstract public function saveToDb(): void;
    abstract public function cloneToDbForTestId(int $testId): void;
    abstract public function deleteFromDb(): void;

    public function areDepenciesInVulnerableState(): bool
    {
        return false;
    }

    public function getDepenciesInVulnerableStateMessage(ilLanguage $lng): string
    {
        return '';
    }

    public function areDepenciesBroken(): bool
    {
        return false;
    }

    public function getDepenciesBrokenMessage(ilLanguage $lng): string
    {
        return '';
    }

    public function isValidRequestOnBrokenQuestionSetDepencies(string $next_class, string $cmd): bool
    {
        return true;
    }

    public function getHiddenTabsOnBrokenDepencies(): array
    {
        return [];
    }

    abstract public function isQuestionSetConfigured(): bool;
    abstract public function doesQuestionSetRelatedDataExist(): bool;
    abstract public function removeQuestionSetRelatedData(): void;
    abstract public function cloneQuestionSetRelatedData(ilObjTest $clone_test_obj): void;

    public function getQuestionPoolPathString(int $pool_id): string
    {
        $ref_id = current(ilObject::_getAllReferences($pool_id));

        $path = new ilPathGUI();
        $path->enableTextOnly(true);
        return $path->getPath(ROOT_FOLDER_ID, (int) $ref_id);
    }

    public function getFirstQuestionPoolRefIdByObjId(int $pool_obj_id): int
    {
        $refs_ids = ilObject::_getAllReferences($pool_obj_id);
        $refs_id = current($refs_ids);

        return (int) $refs_id;
    }

    abstract public function isResultTaxonomyFilterSupported(): bool;
}
