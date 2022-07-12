<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\ViewControl\Pagination as PaginationInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

use ILIAS\Data\Range;

class Pagination implements PaginationInterface
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    protected int $total_entries = 0;
    protected int $page_size;
    protected int $current_page = 0;
    protected Signal $internal_signal;
    protected ?string $target_url = null;
    protected string $parameter_name = "pagination_offset";
    protected ?int $max_pages_shown = null;
    protected ?int $dd_threshold = null;
    protected string $dropdown_label;
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
        $this->dropdown_label = self::DEFAULT_DROPDOWN_LABEL;
    }

    /**
     * @inheritdoc
     */
    public function withResetSignals() : PaginationInterface
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * Set the internal signals for this component
     */
    protected function initSignals() : void
    {
        $this->internal_signal = $this->signal_generator->create();
    }

    /**
     * Get the internal signal that is triggered on click of a button.
     */
    public function getInternalSignal() : Signal
    {
        return $this->internal_signal;
    }

    /**
     * @inheritdoc
     */
    public function withTargetURL(string $url, string $parameter_name) : PaginationInterface
    {
        $clone = clone $this;
        $clone->target_url = $url;
        $clone->parameter_name = $parameter_name;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTargetURL() : ?string
    {
        return $this->target_url;
    }

    /**
     * @inheritdoc
     */
    public function getParameterName() : string
    {
        return $this->parameter_name;
    }

    /**
     * @inheritdoc
     */
    public function withTotalEntries(int $total) : PaginationInterface
    {
        $clone = clone $this;
        $clone->total_entries = $total;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withPageSize(int $size) : PaginationInterface
    {
        //raise, if size < 1
        $clone = clone $this;
        $clone->page_size = $size;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getPageSize() : int
    {
        return $this->page_size;
    }

    /**
     * @inheritdoc
     */
    public function withCurrentPage(int $page) : PaginationInterface
    {
        $clone = clone $this;
        $clone->current_page = $page;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentPage() : int
    {
        return $this->current_page;
    }

    protected function getOffset() : int
    {
        return $this->page_size * $this->current_page;
    }

    /**
     * @inheritdoc
     */
    public function withOnSelect(Signal $signal) : PaginationInterface
    {
        return $this->withTriggeredSignal($signal, 'select');
    }

    /**
     * @inheritdoc
     */
    public function getNumberOfPages() : int
    {
        $pages = ceil($this->total_entries / $this->page_size);
        return (int) $pages;
    }

    /**
     * @inheritdoc
     */
    public function withMaxPaginationButtons(int $amount) : PaginationInterface
    {
        $clone = clone $this;
        $clone->max_pages_shown = $amount;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getMaxPaginationButtons() : ?int
    {
        return $this->max_pages_shown;
    }

    protected function getPageLength() : int
    {
        if ($this->getOffset() + $this->page_size > $this->total_entries) {
            return $this->total_entries - $this->getOffset();
        }
        return $this->page_size;
    }

    /**
     * @inheritdoc
     */
    public function withDropdownAt(int $amount) : PaginationInterface
    {
        $clone = clone $this;
        $clone->dd_threshold = $amount;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getDropdownAt() : ?int
    {
        return $this->dd_threshold;
    }

    /**
     * @inheritdoc
     */
    public function withDropdownLabel(string $template) : PaginationInterface
    {
        $clone = clone $this;
        $clone->dropdown_label = $template;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getDropdownLabel() : string
    {
        return $this->dropdown_label;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDropdownLabel() : string
    {
        return self::DEFAULT_DROPDOWN_LABEL;
    }

    public function getRange() : ?Range
    {
        if ($this->getPageLength() < 1) {
            return null;
        }
        $f = new \ILIAS\Data\Factory();
        return $f->range($this->getOffset(), $this->getPageLength());
    }
}
