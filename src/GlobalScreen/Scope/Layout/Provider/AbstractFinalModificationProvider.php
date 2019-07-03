<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\ContentModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\LogoModifier;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class AbstractFinalModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractFinalModificationProvider extends AbstractProvider implements FinalModificationProvider
{

    /**
     * @inheritDoc
     */
    public function getLogoModifier() : LogoModifier
    {
        return new class implements LogoModifier
        {

            public function getLogo(Image $current) : Image
            {
                return $current;
            }
        };
    }


    /**
     * @inheritDoc
     */
    public function getContentModifier() : ContentModifier
    {
        return new class implements ContentModifier
        {

            public function getContent(Legacy $current) : Legacy
            {
                return $current;
            }
        };
    }
}
