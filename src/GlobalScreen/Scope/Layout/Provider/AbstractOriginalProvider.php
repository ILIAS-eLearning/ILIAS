<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

/**
 * Class AbstractOriginalProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractOriginalProvider
{

    /**
     * @var PagePartProvider
     */
    protected $original;


    /**
     * AbstractOriginalProvider constructor.
     *
     * @param PagePartProvider $original
     */
    public function __construct(PagePartProvider $original) { $this->original = $original; }
}
