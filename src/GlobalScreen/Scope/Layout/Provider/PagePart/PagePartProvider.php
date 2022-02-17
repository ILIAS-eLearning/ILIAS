<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\Footer;
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
     * @return Legacy|null
     */
    public function getContent() : ?Legacy;

    /**
     * @return MetaBar|null
     */
    public function getMetaBar() : ?MetaBar;

    /**
     * @return MainBar|null
     */
    public function getMainBar() : ?MainBar;

    /**
     * @return Breadcrumbs|null
     */
    public function getBreadCrumbs() : ?Breadcrumbs;

    /**
     * @return Image|null
     */
    public function getLogo() : ?Image;


    /**
     * @return Footer|null
     */
    public function getFooter() : ?Footer;

    public function getTitle() : string;

    public function getShortTitle() : string;

    public function getViewTitle() : string;
}
