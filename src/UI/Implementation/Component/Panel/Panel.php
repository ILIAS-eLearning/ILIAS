<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Component;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Panel implements C\Panel\Panel
{
    use ComponentHelper;

    /**
     * @var Component[]|Component
     */
    private $content;
    protected string $title;
    protected ?C\Dropdown\Standard $actions = null;

    /**
     * @param Component[]|Component $content
     */
    public function __construct(string $title, $content)
    {
        $content = $this->toArray($content);
        $types = [Component::class];
        $this->checkArgListElements("content", $content, $types);

        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions) : C\Panel\Panel
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
