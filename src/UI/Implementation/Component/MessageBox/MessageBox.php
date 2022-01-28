<?php declare(strict_types=1);

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class MessageBox implements C\MessageBox\MessageBox
{
    use ComponentHelper;

    /**
     * @var array
     */
    private static array $types = [
        self::FAILURE,
        self::SUCCESS,
        self::INFO,
        self::CONFIRMATION
    ];

    private string $type;
    private string $message_text;
    private array $buttons = [];
    private array $links = [];

    public function __construct($type, string $message_text)
    {
        $this->checkArgIsElement("type", $type, self::$types, "message box type");
        $this->type = $type;
        $this->message_text = $message_text;
    }

    /**
     * @inheritdoc
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getMessageText() : string
    {
        return $this->message_text;
    }

    /**
     * @inheritdoc
     */
    public function getButtons() : array
    {
        return $this->buttons;
    }

    /**
     * @inheritdoc
     */
    public function getLinks() : array
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function withButtons(array $buttons) : C\MessageBox\MessageBox
    {
        $types = array(C\Component::class);
        $this->checkArgListElements("buttons", $buttons, $types);

        $clone = clone $this;
        $clone->buttons = $buttons;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLinks(array $links) : C\MessageBox\MessageBox
    {
        $types = array(C\Component::class);
        $this->checkArgListElements("links", $links, $types);

        $clone = clone $this;
        $clone->links = $links;
        return $clone;
    }
}
