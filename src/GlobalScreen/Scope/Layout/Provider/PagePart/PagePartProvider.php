<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Interface PagePartProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PagePartProvider
{

    /**
     * @return Legacy
     */
    public function getContent() : ?Legacy;


    /**
     * @return MetaBar
     */
    public function getMetaBar() : ?MetaBar;


    /**
     * @return MainBar
     */
    public function getMainBar() : ?MainBar;


    /**
     * @return Breadcrumbs
     */
    public function getBreadCrumbs() : ?Breadcrumbs;


    /**
     * @return Image
     */
    public function getLogo() : ?Image;
}
