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
use Closure;

class AccessQuestionImage implements SimpleAccess
{
    /** @var Readable */
    private $readable;

    public function __construct(Readable $readable)
    {
        $this->readable = $readable;
    }

    public function isPermitted(string $path): Result
    {
        $object_id = $this->objectId($path);
        if (!$object_id) {
            return new Error('Not a question image path of test questions.');
        }

        return new Ok($this->readable->objectId($object_id));
    }

    private function objectId(string $path): ?int
    {
        $results = [];
        if (!preg_match(':/assessment/(\d+)/(\d+)/images/([^/]+)$:', $path, $results)) {
            return null;
        }

        return (int) $results[1];
    }
}
