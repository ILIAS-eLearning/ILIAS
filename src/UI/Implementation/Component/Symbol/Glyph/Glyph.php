<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

class Glyph implements C\Symbol\Glyph\Glyph
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    private static array $types = [
        self::SETTINGS,
        self::COLLAPSE,
        self::COLLAPSE_HORIZONTAL,
        self::EXPAND,
        self::ADD,
        self::REMOVE,
        self::UP,
        self::DOWN,
        self::BACK,
        self::NEXT,
        self::SORT_ASCENDING,
        self::SORT_DESCENDING,
        self::USER,
        self::MAIL,
        self::NOTIFICATION,
        self::TAG,
        self::NOTE,
        self::COMMENT,
        self::BRIEFCASE,
        self::LIKE,
        self::LOVE,
        self::DISLIKE,
        self::LAUGH,
        self::ASTOUNDED,
        self::SAD,
        self::ANGRY,
        self::EYEOPEN,
        self::EYECLOSED,
        self::ATTACHMENT,
        self::RESET,
        self::APPLY,
        self::SEARCH,
        self::HELP,
        self::CALENDAR,
        self::TIME,
        self::CLOSE,
        self::MORE,
        self::DISCLOSURE,
        self::LANGUAGE,
        self::LOGIN,
        self::LOGOUT,
        self::BULLETLIST,
        self::NUMBEREDLIST,
        self::LISTINDENT,
        self::LISTOUTDENT,
        self::FILTER
    ];

    private string $type;
    private ?string $action;
    private string $aria_label;
    private array $counters;
    private bool $highlighted;
    private bool $active = true;

    public function __construct(string $type, string $aria_label, string $action = null)
    {
        $this->checkArgIsElement("type", $type, self::$types, "glyph type");

        $this->type = $type;
        $this->aria_label = $aria_label;
        $this->action = $action;
        $this->counters = array();
        $this->highlighted = false;
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
    public function getAriaLabel() : string
    {
        return $this->aria_label;
    }

    /**
     * @inheritdoc
     */
    public function getAction() : ?string
    {
        return $this->action;
    }

    /**
     * @inheritdoc
     */
    public function getCounters() : array
    {
        return array_values($this->counters);
    }

    /**
     * @inheritdoc
     */
    public function withCounter(Counter $counter) : C\Symbol\Glyph\Glyph
    {
        $clone = clone $this;
        $clone->counters[$counter->getType()] = $counter;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isHighlighted() : bool
    {
        return $this->highlighted;
    }

    /**
     * @inheritdoc
     */
    public function withHighlight() : C\Symbol\Glyph\Glyph
    {
        $clone = clone $this;
        $clone->highlighted = true;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function withUnavailableAction() : C\Symbol\Glyph\Glyph
    {
        $clone = clone $this;
        $clone->active = false;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal) : C\Clickable
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal) : C\Clickable
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }

    /**
    * @inheritdoc
    */
    public function withAction($action) : C\Symbol\Glyph\Glyph
    {
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }
}
