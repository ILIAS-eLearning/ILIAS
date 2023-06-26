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
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input\InputGroup;

class Pagination extends ViewControl implements VCInterface\Pagination
{
    use InputGroup;

    public const DEFAULT_DROPDOWN_LABEL_OFFSET = 'pagination offset';
    public const DEFAULT_DROPDOWN_LABEL_LIMIT = 'pagination limit';
    protected const DEFAULT_LIMITS = [5, 10, 25, 50, 100, 250, 500, \PHP_INT_MAX];
    protected const NUMBER_OF_VISIBLE_SECTIONS = 7;

    protected Signal $internal_selection_signal;
    protected array $options;
    protected ?int $total_count = null;
    protected int $visible_entries;


    protected Signal $change_signal;

    public function __construct(
        FieldFactory $field_factory,
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        string $label_offset,
        protected string $label_limit
    ) {
        $this->inputs = [
            $field_factory->hidden(), //offset
            $field_factory->hidden()  //limit
        ];

        parent::__construct($data_factory, $refinery, $label_offset);

        $this->internal_selection_signal = $signal_generator->create();
        $this->visible_entries = self::NUMBER_OF_VISIBLE_SECTIONS;
        $this->operations[] = $this->getRangeTransform();
    }

    public function withOnChange(Signal $change_signal): self
    {
        $clone = clone $this;
        $clone->change_signal = $change_signal;
        return $clone;
    }

    public function getOnChangeSignal(): ?Signal
    {
        return $this->change_signal ?? null;
    }

    protected function getRangeTransform(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function ($v): Range {
                list($offset, $limit) = array_map('intval', $v);
                $offset = $offset ?? 0;
                if (is_null($limit)) {
                    $options = $this->getLimitOptions();
                    $limit = array_shift($options);
                };
                return $this->data_factory->range($offset, $limit);
            }
        );
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
