<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class InterruptiveItem
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class InterruptiveItem implements \ILIAS\UI\Component\Modal\InterruptiveItem
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Image
     */
    protected $icon;

    /**
     * @param string $id
     * @param string $title
     * @param Image $icon
     * @param string $description
     */
    public function __construct($id, $title, Image $icon = null, $description = '')
    {
        $this->checkStringArg('title', $title);
        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;
        $this->description = $description;
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
