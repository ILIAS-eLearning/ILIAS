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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use DateTimeImmutable;
use Closure;
use Exception;

class Marshal
{
    public function __construct(private readonly Refinery $refinery)
    {
    }

    public function dateTime(): Convert
    {
        $from = $this->refinery->in()->series([
            $this->custom(fn(string $s) => '@' . ($s ?: '0')),
            $this->refinery->to()->dateTime(),
        ]);

        $to = $this->custom(fn(DateTimeImmutable $x) => (string) $x->getTimeStamp());

        return new Convert($from, $to);
    }

    public function boolean(): Convert
    {
        // kindlyTo()->bool() doesn't count an empty string as `false`:
        $from = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->bool(),
            $this->custom(fn(string $s): bool => !($s === '' || $this->error('Value could not be transformed to bool.')))
        ]);

        $to = $this->refinery->kindlyTo()->string();

        return new Convert($from, $to);
    }

    public function nullable(Convert $convert): Convert
    {
        $from = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $convert->fromString(),
        ]);

        $to = $this->refinery->byTrying([
            $this->refinery->in()->series([$this->refinery->null(), $this->refinery->always('')]),
            $convert->toString(),
        ]);

        return new Convert($from, $to);
    }

    public function string(): Convert
    {
        return new Convert(
            $this->refinery->identity(),
            $this->refinery->identity()
        );
    }

    private function custom(Closure $map): Transformation
    {
        return $this->refinery->custom()->transformation($map);
    }

    private function error(string $message): void
    {
        throw new Exception($message);
    }
}
