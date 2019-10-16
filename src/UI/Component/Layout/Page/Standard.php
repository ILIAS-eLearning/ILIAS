<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ILIAS\UI\Component\MainControls\Mainbar;
use ILIAS\UI\Component\MainControls\Metabar;

/**
 * This describes the Page.
 */
interface Standard extends Page, JavaScriptBindable
{

    /**
     * @param Metabar $meta_bar
     *
     * @return Standard
     */
    public function withMetabar(Metabar $meta_bar) : Standard;

    /**
     * @param Mainbar $main_bar
     *
     * @return Standard
     */
    public function withMainbar(MainBar $main_bar) : Standard;

    /**
     * @param Image $logo
     *
     * @return Standard
     */
    public function withLogo(Image $logo) : Standard;

    /**
     * @return bool
     */
    public function hasMetabar() : bool;

    /**
     * @return bool
     */
    public function hasMainbar() : bool;

    /**
     * @return bool
     */
    public function hasLogo() : bool;

    /**
     * @return Metabar|null
     */
    public function getMetabar();

    /**
     * @return Mainbar|null
     */
    public function getMainbar();

    /**
     * @return Breadcrumbs|null
     */
    public function getBreadcrumbs();

    /**
     * @return Image|null
     */
    public function getLogo();

    /**
     * @return Footer|null
     */
    public function getFooter();


    /**
     * @param string $title
     *
     * @return Standard
     */
    public function withTitle(string $title) : Standard;


    /**
     * @return string
     */
    public function getTitle() : string;


    /**
     * @param ModeInfo $head_info
     *
     * @return Standard
     */
    public function withModeInfo(ModeInfo $head_info) : Standard;


    /**
     * @return ModeInfo|null
     */
    public function getModeInfo() : ?ModeInfo;


    /**
     * @return bool
     */
    public function hasModeInfo() : bool;
}
