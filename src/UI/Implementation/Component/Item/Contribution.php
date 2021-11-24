<?php declare(strict_types=1);

/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ilDateTime;
use ILIAS\UI\Component\Item\Contribution as IContribution;
use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as IJavaScriptBindable;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ilObjUser;

class Contribution extends Item implements IContribution, IJavaScriptBindable
{
    use JavaScriptBindable;

    protected ?ilObjUser $user = null;
    protected ?ilDateTime $datetime = null;
    protected ?Icon $lead_icon = null;
    protected ?Close $close = null;
    protected ?string $identifier = null;

    /**
     * @inheritdoc
     */
    public function __construct(string $content, ?ilObjUser $user = null, ?ilDateTime $datetime = null)
    {
        $this->desc = $content;
        $this->user = $user;
        $this->datetime = $datetime;
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
    public function withUser(ilObjUser $user) : IContribution
    {
        $clone = clone $this;
        $clone->user = $user;
        return $clone;
    }

    public function getUser() : ?ilObjUser
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function withDateTime(ilDateTime $datetime) : IContribution
    {
        $clone = clone $this;
        $clone->datetime = $datetime;
        return $clone;
    }

    public function getDateTime() : ?ilDateTime
    {
        return $this->dateTime;
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
