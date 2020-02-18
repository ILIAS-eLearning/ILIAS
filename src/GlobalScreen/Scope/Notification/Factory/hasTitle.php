<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

/**
 * Interface hasTitle
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasTitle
{

    /**
     * @param string $title
     *
     * @return hasTitle
     */
    public function withTitle(string $title) : hasTitle;


    /**
     * @return string
     */
    public function getTitle() : string;


    /**
     * @param string $summary
     *
     * @return isItem
     */
    public function withSummary(string $summary) : isItem;


    /**
     * @return string
     */
    public function getSummary() : string;
}
