<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Wrapper
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Wrapper extends File implements \ILIAS\UI\Component\Dropzone\File\Wrapper
{

    /**
     * @var Component[]
     */
    protected $components;

    /**
     * @var string
     */
    protected $title = "";


    /**
     * @param string                $url
     * @param Component[]|Component $content Component(s) being wrapped by this dropzone
     */
    public function __construct($url, $content)
    {
        parent::__construct($url);
        $this->components = $this->toArray($content);
        $types = array( Component::class );
        $this->checkArgListElements('content', $this->components, $types);
        $this->checkEmptyArray($this->components);
    }


    /**
     * @inheritdoc
     */
    public function withContent($content)
    {
        $clone = clone $this;
        $clone->components = $this->toArray($content);
        $types = array( Component::class );
        $this->checkArgListElements('content', $clone->components, $types);
        $this->checkEmptyArray($clone->components);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withTitle($title)
    {
        $this->checkStringArg("title", $title);
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->components;
    }


    /**
     * Checks if the passed array contains at least one element, throws a LogicException otherwise.
     *
     * @param array $array
     *
     * @throws \LogicException if the passed in argument counts 0
     */
    private function checkEmptyArray(array $array)
    {
        if (count($array) === 0) {
            throw new \LogicException("At least one component from the UI framework is required, otherwise
			the wrapper dropzone is not visible.");
        }
    }
}
