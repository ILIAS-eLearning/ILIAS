<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Modal\LightboxDescriptionEnabledPage;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use \ILIAS\UI\Component\Modal\LightboxImagePage as ILightboxImagePage;

/**
 * Class LightboxImagePage
 *
 * Used to display an image in a lightbox page inside a lightbox modal.
 * If no description is provided, the alt tag of the image is substituted.
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LightboxImagePage implements LightboxDescriptionEnabledPage, ILightboxImagePage
{
    use ComponentHelper;

    protected Image $image;
    protected string $title;
    protected string $description;

    public function __construct(Image $image, string $title, string $description = '')
    {
        $this->image = $image;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDescription() : string
    {
        return $this->description ?: $this->image->getAlt();
    }

    /**
     * @inheritdoc
     */
    public function getComponent() : Image
    {
        return $this->image;
    }
}
