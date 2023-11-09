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

namespace ILIAS\LegalDocuments\Intercept;

use ILIAS\LegalDocuments\Value\Target;
use Closure;
use ILIAS\LegalDocuments\Intercept;

final class LazyIntercept implements Intercept
{
    /** @var Closure(): Intercept */
    private Closure $intercept;

    /**
     * @param Closure(): Intercept $create_intercept
     */
    public function __construct(Closure $create_intercept)
    {
        $this->intercept = function () use ($create_intercept): Intercept {
            $intercept = $create_intercept();
            $this->intercept = static fn(): Intercept => $intercept;
            return $intercept;
        };
    }

    public function intercept(): bool
    {
        return ($this->intercept)()->intercept();
    }

    public function id(): string
    {
        return ($this->intercept)()->id();
    }

    public function target(): Target
    {
        return ($this->intercept)()->target();
    }
}
