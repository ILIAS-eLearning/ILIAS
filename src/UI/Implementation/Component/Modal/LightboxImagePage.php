<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Modal\LightboxDescriptionEnabledPage;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Modal\LightboxImagePage as ILightboxImagePage;

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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description ?: $this->image->getAlt();
    }

    /**
     * @inheritdoc
     */
    public function getComponent(): Image
    {
        return $this->image;
    }
}
