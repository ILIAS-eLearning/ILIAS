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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Range;

class Pagination extends ViewControl implements VCInterface\Pagination
{
    public const DEFAULT_DROPDOWN_LABEL_OFFSET = 'pagination offset';
    public const DEFAULT_DROPDOWN_LABEL_LIMIT = 'pagination limit';
    protected const DEFAULT_LIMITS = [5, 10, 25, 50, 100, 250, 500, \PHP_INT_MAX];
    protected const NUMBER_OF_VISIBLE_SECTIONS = 7;

    protected Signal $internal_selection_signal;
    protected array $options;
    protected ?int $total_count = null;
    protected int $visible_entries;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        string $label_offset,
        protected string $label_limit
    ) {
        parent::__construct($data_factory, $refinery, $label_offset);
        $this->internal_selection_signal = $signal_generator->create();
        $this->visible_entries = self::NUMBER_OF_VISIBLE_SECTIONS;
        //$this->operations[] = $this->getRangeTransform();
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_null($value) || is_string($value);
    }

    protected function getRangeTransform(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function ($v): Range {
                $v = array_map('intval', explode(':', $v));
                $potential_range_starts = array_map(
                    fn ($r) => $r->getStart(),
                    $this->getRanges($this->data_factory->range(...$v))
                );
                return $this->data_factory->range(
                    $this->roundToClosestOption($v[0], $potential_range_starts),
                    $v[1]
                );
            }
        );
    }

    protected function roundToClosestOption(int $search, array $options): int
    {
        return array_reduce(
            $options,
            fn ($a, $b) => abs($b - $search) <= abs($a - $search) ? $b : $a
        );
    }

    protected function getRanges(Range $range): array
    {
        list($offset, $limit) = $range->unpack();
        $ret = [];
        if ($limit + 1 > $offset) {
            return [$this->data_factory->range(0, $limit)];
        }

        foreach (range(0, $offset, $limit + 1) as $start) {
            $ret[] = $this->data_factory->range($start, $limit);
        }
        return $ret;
    }

    public function getInternalSignal(): Signal
    {
        return $this->internal_selection_signal;
    }

    public function withLimitOptions(array $options): self
    {
        $clone = clone $this;
        $clone->options = $options;
        return $clone;
    }

    public function getLimitOptions(): array
    {
        return $this->options ?? self::DEFAULT_LIMITS;
    }

    public function getLabelLimit(): string
    {
        return $this->label_limit;
    }

    public function withNumberOfVisibleEntries(int $no_entries): self
    {
        $clone = clone $this;
        $clone->visible_entries = $no_entries;
        return $clone;
    }

    public function getNumberOfVisibleEntries(): int
    {
        return $this->visible_entries;
    }

    public function getTotalCount(): ?int
    {
        return $this->total_count;
    }

    public function withTotalCount(int $total_count = null): self
    {
        $clone = clone $this;
        $clone->total_count = $total_count;
        return $clone;
    }
}
