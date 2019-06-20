<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use ILIAS\GlobalScreen\Scope\Layout\Modifier\BreadCrumbsModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\ContentModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\LogoModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\MainBarModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\MetaBarModifier;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Class InstancePagePartProvider
 *
 * @package ILIAS\GlobalScreen\Scope\Layout\Provider
 */
class InstancePagePartProvider implements PagePartProvider
{

    /**
     * @var PagePartProvider
     */
    private $original;
    /**
     * @var ContentModifier
     */
    private $content_provider;
    /**
     * @var MetaBarModifier
     */
    private $meta_bar_provider;
    /**
     * @var MainBarModifier
     */
    private $main_bar_provider;
    /**
     * @var BreadCrumbsModifier
     */
    private $bread_crumbs_provider;
    /**
     * @var LogoModifier
     */
    private $logo_provider;


    /**
     * InstancePagePartProvider constructor.
     *
     * @param PagePartProvider $original
     */
    public function __construct(PagePartProvider $original)
    {
        $this->original = $original;
        $this->content_provider = new class($original) extends AbstractOriginalPagePartProvider implements ContentModifier
        {

            /**
             * @inheritDoc
             */
            public function getContent(Legacy $current) : Legacy
            {
                return $this->original->getContent();
            }
        };
        $this->meta_bar_provider = new class($original) extends AbstractOriginalPagePartProvider implements MetaBarModifier
        {

            /**
             * @inheritDoc
             */
            public function getMetaBar(MetaBar $current) : MetaBar
            {
                return $this->original->getMetaBar();
            }
        };
        $this->main_bar_provider = new class($original) extends AbstractOriginalPagePartProvider implements MainBarModifier
        {

            /**
             * @inheritDoc
             */
            public function getMainBar(MainBar $current) : MainBar
            {
                return $this->original->getMainBar();
            }
        };
        $this->bread_crumbs_provider = new class($original) extends AbstractOriginalPagePartProvider implements BreadCrumbsModifier
        {

            /**
             * @inheritDoc
             */
            public function getBreadCrumbs(Breadcrumbs $current) : Breadcrumbs
            {
                return $this->original->getBreadCrumbs();
            }
        };
        $this->logo_provider = new class($original) extends AbstractOriginalPagePartProvider implements LogoModifier
        {

            /**
             * @inheritDoc
             */
            public function getLogo(Image $current) : Image
            {
                return $this->original->getLogo();
            }
        };
    }


    /**
     * @param ContentModifier $content_provider
     */
    public function setContentProvider(ContentModifier $content_provider) : void
    {
        $this->content_provider = $content_provider;
    }


    /**
     * @param MetaBarModifier $meta_bar_provider
     */
    public function setMetaBarProvider(MetaBarModifier $meta_bar_provider) : void
    {
        $this->meta_bar_provider = $meta_bar_provider;
    }


    /**
     * @param MainBarModifier $main_bar_provider
     */
    public function setMainBarProvider(MainBarModifier $main_bar_provider) : void
    {
        $this->main_bar_provider = $main_bar_provider;
    }


    /**
     * @param BreadCrumbsModifier $bread_crumbs_provider
     */
    public function setBreadCrumbsProvider(BreadCrumbsModifier $bread_crumbs_provider) : void
    {
        $this->bread_crumbs_provider = $bread_crumbs_provider;
    }


    /**
     * @param LogoModifier $logo_provider
     */
    public function setLogoProvider(LogoModifier $logo_provider) : void
    {
        $this->logo_provider = $logo_provider;
    }


    /**
     * @inheritDoc
     */
    public function getContent() : Legacy
    {
        return $this->content_provider->getContent($this->original->getContent());
    }


    /**
     * @inheritDoc
     */
    public function getMetaBar() : MetaBar
    {
        return $this->meta_bar_provider->getMetaBar($this->original->getMetaBar());
    }


    /**
     * @inheritDoc
     */
    public function getMainBar() : MainBar
    {
        return $this->main_bar_provider->getMainBar($this->original->getMainBar());
    }


    /**
     * @inheritDoc
     */
    public function getBreadCrumbs() : Breadcrumbs
    {
        return $this->bread_crumbs_provider->getBreadCrumbs($this->original->getBreadCrumbs());
    }


    /**
     * @inheritDoc
     */
    public function getLogo() : Image
    {
        return $this->logo_provider->getLogo($this->original->getLogo());
    }
}
