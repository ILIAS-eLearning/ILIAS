<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use Exception;

trait DeriveApplyToFromTransform
{
    /**
     * @inheritDoc
     */
    abstract public function transform($from);

    /**
     * @inheritDoc
     */
    public function applyTo(Result $result): Result
    {
        return $result->then(function ($value): Result {
            try {
                return new Ok($this->transform($value));
            } catch (Exception $exception) {
                return new Error($exception);
            }
        });
    }
}
