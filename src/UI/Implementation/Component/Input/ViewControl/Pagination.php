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
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Input\NameSource;

class Pagination extends ViewControlInput implements VCInterface\Pagination, HasInputGroup
{
    use ComponentHelper;
    use GroupDecorator;

    public const DEFAULT_LIMITS = [5, 10, 25, 50, 100, 250, 500, \PHP_INT_MAX];
    public const FNAME_OFFSET = 'offset';
    public const FNAME_LIMIT = 'limit';
    protected const NUMBER_OF_VISIBLE_SECTIONS = 7;

    protected Signal $internal_selection_signal;
    protected array $options;
    protected ?int $total_count = null;
    protected int $number_of_entries;
    protected string $label_limit = '';

    public function __construct(
        FieldFactory $field_factory,
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator
    ) {
        parent::__construct($data_factory, $refinery);

        $this->setInputGroup(
            $field_factory->group([
                self::FNAME_OFFSET => $field_factory->hidden(),
                self::FNAME_LIMIT => $field_factory->hidden(),
            ])
            ->withAdditionalTransformation($this->getRangeTransform())
            ->withAdditionalTransformation($this->getCorrectOffsetForPageSize())
        );

        $this->internal_selection_signal = $signal_generator->create();
        $this->number_of_entries = self::NUMBER_OF_VISIBLE_SECTIONS;
    }

    protected function getRangeTransform(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function ($v): Range {
                if (is_null($v)) {
                    $limit = current($this->getLimitOptions());
                } else {
                    list(self::FNAME_OFFSET => $offset, self::FNAME_LIMIT => $limit) = array_map('intval', $v);
                };
                return $this->data_factory->range($offset, $limit);
            }
        );
    }

    protected function getCorrectOffsetForPageSize(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function ($v): Range {
                list($offset, $limit) = $v->unpack();
                if($limit === 0) {
                    $limit = current($this->getLimitOptions());
                }
                $current_page = floor($offset / $limit);
                $offset = $current_page * $limit;
                return $this->data_factory->range((int)$offset, $limit);
            }
        );
    }

    public function getInternalSignal(): Signal
    {
        return $this->internal_selection_signal;
    }

    public function withLimitOptions(array $options): self
    {
        $this->checkArgListElements('options', $options, 'int');
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

    public function withAriaLabelLimit(string $label_limit): self
    {
        $clone = clone $this;
        $clone->label_limit = $label_limit;
        return $clone;
    }

    public function withLabelOffset(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    public function withNumberOfVisibleEntries(int $number_of_entries): self
    {
        $clone = clone $this;
        $clone->number_of_entries = $number_of_entries;
        return $clone;
    }

    public function getNumberOfVisibleEntries(): int
    {
        return $this->number_of_entries;
    }

    public function getTotalCount(): ?int
    {
        return $this->total_count;
    }

    public function withTotalCount(?int $total_count): self
    {
        $clone = clone $this;
        $clone->total_count = $total_count;
        return $clone;
    }
}
