<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use Closure;
use DateTimeImmutable;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class Notification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotification extends AbstractTitleNotification implements isItem, hasTitle, canHaveSymbol, hasActions
{

    /**
     * @var Symbol
     */
    private $symbol;
    /**
     * @var array
     */
    private $additional_actions = [];
    /**
     * @var int
     */
    private $progress;
    /**
     * @var string
     */
    private $action;
    /**
     * @var DateTimeImmutable
     */
    protected $date;
    /**
     * @var string
     */
    protected $summary;
    /**
     * @var Closure
     */
    protected $close_action_callback;


    /**
     * @inheritDoc
     */
    public function withDate(DateTimeImmutable $date_time_immutable) : isItem
    {
        $clone = clone $this;
        $clone->date = $date_time_immutable;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getDate() : DateTimeImmutable
    {
        return $this->date;
    }


    /**
     * @inheritDoc
     */
    public function withCloseActionCallback(Closure $callback) : isItem
    {
        $clone = clone $this;
        $clone->close_action_callback = $callback;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getCloseActionCallback() : Closure
    {
        return $this->close_action_callback;
    }


    /**
     * @inheritDoc
     */
    public function withAction(string $action) : isItem
    {
        $clone = clone $this;
        $clone->action = $action;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function hasAction() : bool
    {
        return is_string($this->action);
    }


    /**
     * @inheritDoc
     */
    public function getAction() : string
    {
        return $this->action;
    }


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : canHaveSymbol
    {
        $clone = clone $this;
        $clone->symbol = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->symbol instanceof Symbol);
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }


    /**
     * @inheritDoc
     */
    public function withAdditionalAction(string $title, string $action) : isItem
    {
        $clone = clone $this;
        $clone->additional_actions[$title] = $action;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getAdditionalActions() : array
    {
        return $this->additional_actions;
    }


    /**
     * @inheritDoc
     */
    public function withProgress(int $progress) : isItem
    {
        $clone = clone $this;
        $clone->progress = $progress;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function hasProgress() : bool
    {
        return is_int($this->progress);
    }


    /**
     * @inheritDoc
     */
    public function getProgress() : int
    {
        return $this->progress;
    }
}
