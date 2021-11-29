<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use DateTimeImmutable;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\UI\Component\Item\Contribution as IContribution;
use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as IJavaScriptBindable;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Contribution extends Item implements IContribution, IJavaScriptBindable
{
    use JavaScriptBindable;

    protected ?string $contributor = null;
    protected ?DateTimeImmutable $createDatetime = null;
    protected DateFormat $dateFormat;
    protected ?Icon $lead_icon = null;
    protected ?Close $close = null;
    protected ?string $identifier = null;

    /**
     * @inheritdoc
     */
    public function __construct(string $quote, ?string $contributor = null,  ?DateTimeImmutable $createDatetime = null)
    {
        $this->desc = $quote;
        $this->contributor = $contributor;
        $this->createDatetime = $createDatetime;
        $this->dateFormat = (new \ILIAS\Data\Factory())->dateFormat()->standard();
        parent::__construct('');
    }

    /**
     * @inheritdoc
     */
    public function withClose(Close $close) : IContribution
    {
        $clone = clone $this;
        $clone->close = $close;
        return $clone;
    }

    public function getClose() : ?Close
    {
        return $this->close;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(Icon $lead) : IContribution
    {
        $clone = clone $this;
        $clone->lead_icon = $lead;
        return $clone;
    }

    public function getLeadIcon() : ?Icon
    {
        return $this->lead_icon;
    }

    /**
     * @inheritdoc
     */
    public function withContributor(string $contributor) : IContribution
    {
        $clone = clone $this;
        $clone->contributor = $contributor;
        return $clone;
    }

    public function getContributor() : ?string
    {
        return $this->contributor;
    }

    /**
     * @inheritdoc
     */
    public function withCreateDatetime(DateTimeImmutable $createDatetime) : IContribution
    {
        $clone = clone $this;
        $clone->createDatetime = $createDatetime;
        return $clone;
    }

    public function getCreateDatetime() : ?DateTimeImmutable
    {
        return $this->createDatetime;
    }

    /**
     * @inheritdoc
     */
    public function withDateFormat(DateFormat $dateFormat) : IContribution
    {
        $clone = clone $this;
        $clone->dateFormat = $dateFormat;
        return $clone;
    }

    public function getDateFormat() : DateFormat
    {
        return $this->dateFormat;
    }

    public function withIdentifier(string $identifier) : IContribution
    {
        $clone = clone $this;
        $clone->identifier = $identifier;
        return $clone;
    }

    public function getIdentifier() : ?string
    {
        return $this->identifier;
    }
}
