<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ILIAS\UI\Component\MainControls\SystemInfo;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Page
 */
class Standard implements Page\Standard
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var ModeInfo|null
     */
    private $mode_info;
    /**
     * @var mixed
     */
    private $content;
    /**
     * @var MetaBar|null
     */
    private $metabar;
    /**
     * @var    MainBar|null
     */
    private $mainbar;
    /**
     * @var    Breadcrumbs|null
     */
    private $breadcrumbs;
    /**
     * @var Image|null
     */
    private $logo;
    /**
     * @var Image|null
     */
    private $responsive_logo;
    /**
     * @var    footer|null
     */
    private $footer;
    /**
     * @var string
     */
    private $short_title;
    /**
     * @var string
     */
    private $view_title;
    /**
     * @var    string
     */
    private $title;
    /**
     * @var    bool
     */
    private $with_headers = true;
    /**
     * @var    bool
     */
    private $ui_demo = false;
    /**
     * @var array
     */
    protected $system_infos = [];
    /**
     * @var string
     */
    protected $text_direction = "ltr";
    /**
     * @var array
     */
    protected $meta_data = [];

    /**
     * Standard constructor.
     * @param array            $content
     * @param MetaBar|null     $metabar
     * @param MainBar|null     $mainbar
     * @param Breadcrumbs|null $locator
     * @param Image|null       $logo
     * @param Footer|null      $footer
     */
    public function __construct(
        array $content,
        MetaBar $metabar = null,
        MainBar $mainbar = null,
        Breadcrumbs $locator = null,
        Image $logo = null,
        Footer $footer = null,
        string $title = '',
        string $short_title = '',
        string $view_title = ''
    ) {
        $allowed = [\ILIAS\UI\Component\Component::class];
        $this->checkArgListElements("content", $content, $allowed);

        $this->content = $content;
        $this->metabar = $metabar;
        $this->mainbar = $mainbar;
        $this->breadcrumbs = $locator;
        $this->logo = $logo;
        $this->footer = $footer;
        $this->title = $title;
        $this->short_title = $short_title;
        $this->view_title = $view_title;
    }

    public function withMetabar(MetaBar $meta_bar) : Page\Standard
    {
        $clone = clone $this;
        $clone->metabar = $meta_bar;
        return $clone;
    }

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

    /**
     * @inheritDoc
     */
    public function withFooter(Footer $footer) : Page\Standard
    {
        $clone = clone $this;
        $clone->footer = $footer;
        return $clone;
    }

    public function hasMetabar() : bool
    {
        return ($this->metabar instanceof MetaBar);
    }

    public function hasMainbar() : bool
    {
        return ($this->mainbar instanceof MainBar);
    }

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

    public function getContent()
    {
        return $this->content;
    }

    public function getMetabar() : ?Metabar
    {
        return $this->metabar;
    }

    public function getMainbar() : ?Mainbar
    {
        return $this->mainbar;
    }

    public function getBreadcrumbs() : ?Breadcrumbs
    {
        return $this->breadcrumbs;
    }

    public function getLogo() : ?Image
    {
        return $this->logo;
    }

    public function getResponsiveLogo() : ?Image
    {
        return $this->responsive_logo;
    }

    public function getFooter() : ?Footer
    {
        return $this->footer;
    }

    /**
     * @param bool $use_headers
     */
    public function withHeaders($use_headers) : Page\Standard
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

    public function withUIDemo(bool $switch = true) : Standard
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

    public function withModeInfo(ModeInfo $mode_info) : \ILIAS\UI\Component\Layout\Page\Standard
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

    public function withNoFooter() : Standard
    {
        $clone = clone $this;
        $clone->footer = null;
        return $clone;
    }

    public function withSystemInfos(array $system_infos) : \ILIAS\UI\Component\Layout\Page\Standard
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


    public function withTextDirection(string $text_direction) : \ILIAS\UI\Component\Layout\Page\Standard
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
    
    public function withAdditionalMetaDatum(string $key, string $value) : \ILIAS\UI\Component\Layout\Page\Standard
    {
        $clone = clone $this;
        $clone->meta_data[$key] = $value;
        return $clone;
    }
    
    public function getMetaData() : array
    {
        return $this->meta_data;
    }
    
}
