<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Image\Image;

/**
 * Implementation of factory for cards
 *
 * @author Jesús López <lopez@leifos.com>
 */
class Factory implements C\Card\Factory
{
    public function standard(string $title, Image $image = null) : C\Card\Standard
    {
        return new Standard($title, $image);
    }

    public function repositoryObject(string $title, Image $image) : C\Card\RepositoryObject
    {
        return new RepositoryObject($title, $image);
    }
}
