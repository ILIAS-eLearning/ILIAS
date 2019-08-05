<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

/**
 * Class AbstractOriginalPagePartProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractOriginalPagePartProvider
{

    /**
     * @var PagePartProvider
     */
    protected $original;


    /**
     * AbstractOriginalPagePartProvider constructor.
     *
     * @param PagePartProvider $original
     */
    public function __construct(PagePartProvider $original) { $this->original = $original; }
}
