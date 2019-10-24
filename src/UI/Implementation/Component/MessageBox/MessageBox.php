<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class MessageBox implements C\MessageBox\MessageBox
{
    use ComponentHelper;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message_text;

    /**
     * @var	array
     */
    private $buttons = [];

    /**
     * @var	array
     */
    private $links = [];

    /**
     * @var array
     */
    private static $types = array(self::FAILURE
    , self::SUCCESS
    , self::INFO
    , self::CONFIRMATION
    );

    /**
     * @param $type
     */
    public function __construct($type, $message_text)
    {
        $this->checkArgIsElement("type", $type, self::$types, "message box type");
        $this->checkStringArg("message_text", $message_text);
        $this->type = $type;
        $this->message_text = $message_text;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getMessageText()
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
    public function withButtons(array $buttons)
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
    public function withLinks(array $links)
    {
        $types = array(C\Component::class);
        $this->checkArgListElements("links", $links, $types);

        $clone = clone $this;
        $clone->links = $links;
        return $clone;
    }
}
