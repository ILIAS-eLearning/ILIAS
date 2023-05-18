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

namespace ILIAS\Modules\Test;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\DI\Container;
use ilObjTest;
use ilObject;
use ilSession;
use ilTestSession;
use ilTestAccess;
use Closure;

class AccessFileUploadAnswer implements SimpleAccess
{
    /** @var Container */
    private $container;
    /** @var Readable */
    private $readable;
    /** @var callable(int): int */
    private $object_id_of_test_id;
    /** @var callable(int): string[] */
    private $references_of;
    /** @var callable(string): mixed */
    private $session;
    /** @var callable(int, int, int): bool */
    private $checkResultsAccess;
    /** @var Incident */
    private $incident;

    /**
     * @param Container $container
     * @param Readable $readable
     * @param callable(int): int $object_id_of_test_id
     * @param callable(int): int[] $references_of
     * @param callable(string): mixed $session
     * @param callable(int, int, int): bool $checkResultsAccess
     * @param Incident $incident
     */
    public function __construct(
        Container $container,
        Readable $readable,
        $object_id_of_test_id = [ilObjTest::class, '_getObjectIDFromTestID'],
        $references_of = [ilObject::class, '_getAllReferences'],
        $session = [ilSession::class, 'get'],
        callable $checkResultsAccess = null,
        Incident $incident = null
    ) {
        $this->container = $container;
        $this->readable = $readable;
        $this->incident = $incident ?? new Incident();
        $this->object_id_of_test_id = Closure::fromCallable($object_id_of_test_id);
        $this->references_of = Closure::fromCallable($references_of);
        $this->session = Closure::fromCallable($session);
        $this->checkResultsAccess = $checkResultsAccess ?? static function (int $reference, int $test_id, int $active_id): bool {
            $access = new ilTestAccess($reference, $test_id);
            return $access->checkResultsAccessForActiveId($active_id);
        };
    }

    public function isPermitted(string $path): Result
    {
        $path_and_test = $this->pathAndTestId($path);

        if (!$path_and_test) {
            return new Error('Not a file upload path of test answers.');
        }
        if (!$path_and_test['test']) {
            return new Ok(false);
        }

        $object_id = (int) ($this->object_id_of_test_id)($path_and_test['test']);
        if (!$object_id) {
            return new Ok(false);
        }

        $references = array_map('intval', ($this->references_of)($object_id));

        return new Ok($this->readable->references($references) && $this->roleBasedCheck($path_and_test['test'], $references, $path_and_test['path']));
    }

    private function isAnonymous() : bool
    {
        return $this->container->user()->isAnonymous() || !$this->container->user()->getId();
    }

    private function accessCodeOk(string $file, int $test_id) : bool
    {
        $code = ($this->session)(ilTestSession::ACCESS_CODE_SESSION_INDEX)[$test_id] ?? false;

        return $code && $this->userDidUpload($test_id, $file, $code);
    }

    private function userDidUpload(int $test_id, string $file, string $code = null) : bool
    {
        $where = [
            'active_id = active_fi',
            'user_fi = %s',
            'value1 = %s',
            'anonymous_id ' . (null === $code ? 'IS' : '=') . ' %s',
            'test_fi = %s',
        ];

        $result = $this->container->database()->queryF(
            'SELECT 1 FROM tst_solutions WHERE EXISTS (SELECT 1 FROM tst_active WHERE ' . implode(' AND ', $where) . ')',
            ['integer', 'text', 'text', 'integer'],
            [$this->container->user()->getId(), $file, $code, $test_id]
        );

        return (bool) $this->container->database()->numRows($result);
    }

    private function activeIdOfFile(string $file, int $test) : ?int
    {
        $is_upload_question = 'EXISTS (SELECT 1 FROM qpl_qst_type INNER JOIN qpl_questions ON question_type_id = question_type_fi WHERE type_tag = %s AND tst_solutions.question_fi = qpl_questions.question_id)';
        $is_in_test = 'EXISTS (SELECT 1 FROM tst_active WHERE test_fi = %s AND active_id = active_fi)';

        $result = $this->container->database()->queryF(
            "SELECT active_fi, value1 FROM tst_solutions WHERE $is_upload_question AND $is_in_test",
            ['text', 'integer'],
            ['assFileUpload', $test]
        );

        while (($row = $this->container->database()->fetchAssoc($result))) {
            if ($row['value1'] === $file) {
                return (int) $row['active_fi'];
            }
        }

        return null;
    }

    /**
     * @param int $test_id
     * @param string[] $references
     * @param string $file
     *
     * @return bool
     */
    private function roleBasedCheck(int $test_id, array $references, string $file) : bool
    {
        return $this->isAnonymous() ? $this->accessCodeOk($file, $test_id) : $this->canAccessResults($test_id, $references, $file) || $this->userDidUpload($test_id, $file);
    }

    /**
     * @param int $test_id
     * @param string[] $references
     * @param string $file
     *
     * @return bool
     */
    private function canAccessResults(int $test_id, array $references, string $file) : bool
    {
        $active_id = $this->activeIdOfFile($file, $test_id);
        if (!$active_id) {
            return false;
        }

        return $this->incident->any(function (int $reference) use ($test_id, $active_id): bool {
            return ($this->checkResultsAccess)($reference, $test_id, $active_id);
        }, $references);
    }

    /**
     * @param string $path
     *
     * @return null|array{test: int, path: string}
     */
    private function pathAndTestId(string $path) : ?array
    {
        $results = [];
        if (!preg_match(':/assessment/tst_(\d+)/.*/([^/]+)$:', $path, $results)) {
            return null;
        }

        return [
            'test' => (int) $results[1],
            'path' => $results[2],
        ];
    }
}
