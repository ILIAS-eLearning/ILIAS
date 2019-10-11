<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Modal\LightboxDescriptionEnabledPage;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class LightboxImagePage
 *
 * Used to display an image in a lightbox page inside a lightbox modal.
 * If no description is provided, the alt tag of the image is substituted.
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LightboxImagePage implements LightboxDescriptionEnabledPage
{
    use ComponentHelper;

    /**
     * @var Image
     */
    protected $image;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $description;


    /**
     * @param Image  $image
     * @param string $title
     * @param string $description
     */
    public function __construct(Image $image, $title, $description = '')
    {
        $this->checkArgInstanceOf('image', $image, Image::class);
        $this->checkStringArg('title', $title);
        $this->checkStringArg('description', $description);
        $this->image = $image;
        $this->title = $title;
        $this->description = $description;
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
    public function getDescription() : string
    {
        return $this->description ? $this->description : $this->image->getAlt();
    }


    /**
     * @inheritdoc
     */
    public function getComponent()
    {
        return $this->image;
    }
}
