<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ILIAS\UI\Component\MainControls\SystemInfo;
use ILIAS\UI\Component\Toast\Container;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Component;

class Standard implements Page\Standard
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var mixed
     */
    private $content;
    private ?ModeInfo $mode_info = null;
    private ?MetaBar $metabar;
    private ?MainBar $mainbar;
    private ?Breadcrumbs $breadcrumbs;
    private ?Image $logo;
    private ?Image $responsive_logo;
    private string $favicon_path;
    private ?Container $overlay;
    private ?Footer $footer;
    private string $short_title;
    private string $view_title;
    private string $title;
    private bool $with_headers = true;
    private bool $ui_demo = false;
    protected array $system_infos = [];
    protected string $text_direction = "ltr";
    protected array $meta_data = [];
    
    public function __construct(
        array $content,
        ?MetaBar $metabar = null,
        ?MainBar $mainbar = null,
        ?Breadcrumbs $locator = null,
        ?Image $logo = null,
        ?Image $responsive_logo = null,
        string $favicon_path = '',
        ?Container $overlay = null,
        ?Footer $footer = null,
        string $title = '',
        string $short_title = '',
        string $view_title = ''
    ) {
        $allowed = [Component::class];
        $this->checkArgListElements("content", $content, $allowed);

        $this->content = $content;
        $this->metabar = $metabar;
        $this->mainbar = $mainbar;
        $this->breadcrumbs = $locator;
        $this->logo = $logo;
        $this->responsive_logo = $responsive_logo;
        $this->favicon_path = $favicon_path;
        $this->overlay = $overlay;
        $this->footer = $footer;
        $this->title = $title;
        $this->short_title = $short_title;
        $this->view_title = $view_title;
    }

    /**
     * @inheritDoc
     */
    public function withMetabar(MetaBar $meta_bar) : Page\Standard
    {
        $clone = clone $this;
        $clone->metabar = $meta_bar;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withMainbar(MainBar $main_bar) : Page\Standard
    {
        $clone = clone $this;
        $clone->mainbar = $main_bar;
        return $clone;
    }

    public function withLogo(Image $logo) : Page\Standard
    {
        $clone = clone $this;
        $clone->logo = $logo;
        return $clone;
    }

    public function withResponsiveLogo(Image $logo) : Page\Standard
    {
        $clone = clone $this;
        $clone->responsive_logo = $logo;
        return $clone;
    }

    public function withFaviconPath(string $path) : Standard
    {
        $clone = clone $this;
        $clone->favicon_path = $path;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withFooter(Footer $footer) : Page\Standard
    {
        $clone = clone $this;
        $clone->footer = $footer;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function hasMetabar() : bool
    {
        return ($this->metabar instanceof MetaBar);
    }

    /**
     * @inheritDoc
     */
    public function hasMainbar() : bool
    {
        return ($this->mainbar instanceof MainBar);
    }

    /**
     * @inheritDoc
     */
    public function hasLogo() : bool
    {
        return ($this->logo instanceof Image);
    }

    public function hasResponsiveLogo() : bool
    {
        return ($this->responsive_logo instanceof Image);
    }

    public function hasFooter() : bool
    {
        return ($this->footer instanceof Footer);
    }

    /**
     * @inheritdoc
     */
    public function getContent() : array
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function getMetabar() : ?MetaBar
    {
        return $this->metabar;
    }

    /**
     * @inheritdoc
     */
    public function getMainbar() : ?MainBar
    {
        return $this->mainbar;
    }

    /**
     * @inheritdoc
     */
    public function getBreadcrumbs() : ?Breadcrumbs
    {
        return $this->breadcrumbs;
    }

    /**
     * @inheritdoc
     */
    public function getLogo() : ?Image
    {
        return $this->logo;
    }

    public function getResponsiveLogo() : ?Image
    {
        return $this->responsive_logo;
    }

    public function getFaviconPath() : string
    {
        return $this->favicon_path;
    }

    public function getFooter() : ?Footer
    {
        return $this->footer;
    }

    public function withHeaders(bool $use_headers) : Page\Standard
    {
        $clone = clone $this;
        $clone->with_headers = $use_headers;
        return $clone;
    }

    public function getWithHeaders() : bool
    {
        return $this->with_headers;
    }

    public function getIsUIDemo() : bool
    {
        return $this->ui_demo;
    }

    public function withUIDemo(bool $switch = true) : Page\Standard
    {
        $clone = clone $this;
        $clone->ui_demo = $switch;
        return $clone;
    }

    public function withTitle(string $title) : Page\Standard
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withShortTitle(string $title) : Page\Standard
    {
        $clone = clone $this;
        $clone->short_title = $title;
        return $clone;
    }

    public function getShortTitle() : string
    {
        return $this->short_title;
    }

    public function withViewTitle(string $title) : Page\Standard
    {
        $clone = clone $this;
        $clone->view_title = $title;
        return $clone;
    }

    public function getViewTitle() : string
    {
        return $this->view_title;
    }

    public function withModeInfo(ModeInfo $mode_info) : Page\Standard
    {
        $clone = clone $this;
        $clone->mode_info = $mode_info;
        return $clone;
    }

    public function getModeInfo() : ?ModeInfo
    {
        return $this->mode_info;
    }

    public function hasModeInfo() : bool
    {
        return $this->mode_info instanceof ModeInfo;
    }

    public function withNoFooter() : Page\Standard
    {
        $clone = clone $this;
        $clone->footer = null;
        return $clone;
    }
    
    public function withAdditionalMetaDatum(string $key, string $value) : Page\Standard
    {
        $clone = clone $this;
        $clone->meta_data[$key] = $value;
        return $clone;
    }
    
    public function getMetaData() : array
    {
        return $this->meta_data;
    }
    
    public function withSystemInfos(array $system_infos) : Page\Standard
    {
        $this->checkArgListElements("system_infos", $system_infos, [SystemInfo::class]);
        $clone = clone $this;
        $clone->system_infos = $system_infos;
        return $clone;
    }

    public function getSystemInfos() : array
    {
        return $this->system_infos;
    }

    public function hasSystemInfos() : bool
    {
        return count($this->system_infos) > 0;
    }


    public function withTextDirection(string $text_direction) : Page\Standard
    {
        $this->checkArgIsElement(
            "Text Direction",
            $text_direction,
            [self::LTR,self::RTL],
            implode('/', [self::LTR,self::RTL])
        );
        $clone = clone $this;
        $clone->text_direction = $text_direction;
        return $clone;
    }

    public function getTextDirection() : string
    {
        return $this->text_direction;
    }

    public function hasOverlay() : bool
    {
        return $this->overlay instanceof Container;
    }

    public function getOverlay() : ?Container
    {
        return $this->overlay;
    }
}
