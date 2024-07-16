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

namespace ILIAS\Test\Scoring\Marks;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Group;

/**
 * A class defining marks for assessment test objects
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 *
 * @version	$Id$
 * @ingroup components\ILIASTest
 */
class Mark
{
    public function __construct(
        private string $short_name = "",
        private string $official_name = "",
        private float $minimum_level = 0.0,
        private bool $passed = false
    ) {
    }

    /**
     * Stephan Kergomard, 2023-11-08: We need an explicit __unserialize function
     * here because of changes to the corresponding classes with ILIAS 8.
     */
    public function __unserialize(array $data): void
    {
        $this->short_name = $data['short_name'];
        $this->official_name = $data['short_name'];
        $this->minimum_level = (float) $data['minimum_level'];
        $this->passed = (int) $data['passed'];
    }

    public function getShortName(): string
    {
        return $this->short_name;
    }

    public function withShortName(string $short_name): self
    {
        $clone = clone $this;
        $clone->short_name = $short_name;
        return $clone;
    }

    public function getOfficialName(): string
    {
        return $this->official_name;
    }

    public function withOfficialName(string $official_name): self
    {
        $clone = clone $this;
        $clone->official_name = $official_name;
        return $clone;
    }

    public function getMinimumLevel(): float
    {
        return $this->minimum_level;
    }

    public function withMinimumLevel(float $minimum_level): self
    {
        if (($minimum_level >= 0.0) && ($minimum_level <= 100.0)) {
            $clone = clone $this;
            $clone->minimum_level = $minimum_level;
            return $clone;
        } else {
            throw new \Exception('Markstep: minimum level must be between 0 and 100');
        }
    }

    public function getPassed(): bool
    {
        return $this->passed;
    }

    public function withPassed(bool $passed): self
    {
        $clone = clone $this;
        $clone->passed = $passed;
        return $clone;
    }

    /**
     * @return array<\ILIAS\UI\Implementation\Component\Input\Input>
     */
    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        MarkSchema $mark_schema
    ): Group {
        $percent_trafo = $refinery->kindlyTo()->float();
        $percent_constraint = $refinery->custom()->constraint(
            static function (float $v): bool {
                if ($v > 100.0 || $v < 0.0) {
                    return false;
                }
                return true;
            },
            $lng->txt('tst_mark_minimum_level_invalid')
        );
        $mark_trafo = $refinery->custom()->transformation(
            static function (array $vs): Mark {
                return new self(
                    $vs['name'],
                    $vs['official_name'],
                    $vs['minimum_level'],
                    $vs['passed']
                );
            }
        );
        $missing_passed_check = $refinery->custom()->constraint(
            static function (Mark $v) use ($mark_schema) {
                if ($v->getPassed() === true) {
                    return true;
                }
                $mark_steps = $mark_schema->getMarkSteps();
                $mark_steps[] = $v;
                $local_schema = $mark_schema->withMarkSteps($mark_steps);
                if ($local_schema->checkForMissingPassed()) {
                    return false;
                }
                return true;
            },
            $lng->txt('no_passed_mark')
        );
        $missing_zero_check = $refinery->custom()->constraint(
            static function (Mark $v) use ($mark_schema) {
                if ($v->getMinimumLevel() > 0.0) {
                    return true;
                }
                $mark_steps = $mark_schema->getMarkSteps();
                $mark_steps[] = $v;
                $local_schema = $mark_schema->withMarkSteps($mark_steps);
                if ($local_schema->checkForMissingZeroPercentage()) {
                    return false;
                }
                return true;
            },
            $lng->txt('no_passed_mark')
        );
        return $f->group([
            'name' => $f->text($lng->txt('tst_mark_short_form'))
                ->withValue($this->getShortName())
                ->withRequired(true),
            'official_name' => $f->text($lng->txt('tst_mark_official_form'))
                ->withValue($this->getOfficialName())
                ->withRequired(true),
            'minimum_level' => $f->text($lng->txt('tst_mark_minimum_level'))
                ->withAdditionalTransformation($percent_trafo)
                ->withAdditionalTransformation($percent_constraint)
                ->withValue((string) $this->getMinimumLevel())
                ->withRequired(true),
            'passed' => $f->checkbox($lng->txt('tst_mark_passed'))
                ->withValue($this->getPassed())
        ])->withAdditionalTransformation($mark_trafo)
        ->withAdditionalTransformation($missing_passed_check)
        ->withAdditionalTransformation($missing_zero_check);
    }

    public function toStorage(): array
    {
        return [
            'short_name' => ['text', mb_substr($this->getShortName(), 0, 15)],
            'official_name' => ['text', mb_substr($this->getOfficialName(), 0, 50)],
            'minimum_level' => ['float', $this->getMinimumLevel()],
            'passed' => ['text', (int) $this->getPassed()],
            'tstamp' => ['integer', time()]
        ];
    }
}
