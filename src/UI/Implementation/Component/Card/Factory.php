<?php
namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Image\Image;

/**
 * Implementation of factory for cards
 *
 * @author Jesús López <lopez@leifos.com>
 */
class Factory implements Component\Card\Factory
{
    public function standard($title, $image = null)
    {
        return new Standard($title, $image);
    }

    public function repositoryObject($title, $image)
    {
        return new RepositoryObject($title, $image);
    }
}
