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

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

class ilTestQuestionPoolFileUploadQuestionMigration implements Migration
{
    private ?ilResourceStorageMigrationHelper $helper = null;

    public function getLabel(): string
    {
        return 'File Upload Question Migration';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new \ilResourceStorageMigrationHelper(
            new \assFileUploadStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $res = $db->query(
            'SELECT
                tst_solutions.solution_id AS solution_id,
                tst_active.user_fi AS user_id,
                tst_solutions.question_fi AS question_id,
                tst_solutions.active_fi AS active_id,
                tst_active.test_fi AS test_id,
                tst_solutions.value1 AS filename,
                tst_solutions.value2 AS revision_name
            FROM tst_solutions
                     INNER JOIN qpl_qst_fileupload ON qpl_qst_fileupload.question_fi = tst_solutions.question_fi
                     INNER JOIN tst_active ON tst_active.active_id =  tst_solutions.active_fi
            WHERE tst_solutions.value2 != "rid";'
        );

        $res = $db->fetchAssoc($res);

        // read common data for all files of this question id
        $user_id = (int) $res['user_id'];
        $active_id = (int) $res['active_id'];
        $test_id = (int) $res['test_id'];
        $question_id = (int) $res['question_id'];
        $filename = $res['filename'];
        $revision_name = $res['revision_name'];
        $solution_id = (int) $res['solution_id'];

        // build path to file
        $path = $this->buildAbsolutPath(
            $test_id,
            $active_id,
            $question_id,
            $filename
        );

        $rid = null;

        if (file_exists($path)) {
            $rid = $this->helper->movePathToStorage(
                $path,
                $user_id,
                null,
                static function () use ($revision_name): string {
                    return $revision_name;
                }
            );
        }
        if ($rid !== null) {
            $rid = $rid->serialize(); // no files found
        }

        // store the rid in as value1 and 'rid' as value2
        $db->update(
            'tst_solutions',
            [
                'value1' => ['string', $rid],
                'value2' => ['string', 'rid']
            ],
            [
                'solution_id' => ['integer', $solution_id]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $database = $this->helper->getDatabase();
        $res = $database->query(
            "SELECT COUNT(*) as count
            FROM tst_solutions
            INNER JOIN qpl_qst_fileupload ON qpl_qst_fileupload.question_fi = tst_solutions.question_fi
            INNER JOIN tst_active ON tst_active.active_id =  tst_solutions.active_fi
            WHERE tst_solutions.value2 != 'rid';"
        );

        return (int) $database->fetchAssoc($res)['count'];
    }

    protected function buildAbsolutPath(
        int $test_id,
        int $active_id,
        int $question_id,
        string $filename
    ): string {
        return CLIENT_WEB_DIR
            . '/assessment'
            . '/tst_' . $test_id
            . '/' . $active_id
            . '/' . $question_id
            . '/files/'
            . $filename;
    }

}
