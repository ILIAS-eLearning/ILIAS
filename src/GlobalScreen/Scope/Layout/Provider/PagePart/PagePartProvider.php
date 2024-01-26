<?php

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

declare(strict_types=1);
namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\HeadInfo;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\SystemInfo;

/**
 * Interface PagePartProvider
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
     * @return Image|null
     */
    public function getResponsiveLogo() : ?Image;

    /**
     * @return SystemInfo[]
     */
    public function getSystemInfos() : array;

    /**
     * @return Footer|null
     */
    public function getFooter() : ?Footer;

    public function getTitle() : string;

    public function getShortTitle() : string;

    public function getViewTitle() : string;
}
