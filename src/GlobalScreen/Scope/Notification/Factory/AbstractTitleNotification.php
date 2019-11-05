<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

/**
 * Class AbstractTitleNotification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractTitleNotification extends AbstractBaseNotification implements hasTitle, isItem
{

    /**
     * @var string
     */
    protected $title = "";


    /**
     * @inheritDoc
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @inheritDoc
     */
    public function withSummary(string $summary) : isItem
    {
        $clone = clone $this;
        $clone->summary = $summary;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSummary() : string
    {
        return $this->summary;
    }
}
