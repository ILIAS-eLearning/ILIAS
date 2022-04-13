<?php namespace ILIAS\GlobalScreen\Scope\Layout\Builder;

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Implementation\Component\Layout\Page\Standard;

/**
 * Interface PageBuilder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardPageBuilder implements PageBuilder
{

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;
    /**
     * @var \ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent
     */
    protected $meta;

    /**
     * StandardPageBuilder constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->meta = $DIC->globalScreen()->layout()->meta();
    }

    /**
     * @param PagePartProvider $parts
     * @return Page
     */
    public function build(PagePartProvider $parts) : Page
    {
        $header_image = $parts->getLogo();
        $responsive_header_image = $parts->getResponsiveLogo();
        $main_bar = $parts->getMainBar();
        $meta_bar = $parts->getMetaBar();
        $bread_crumbs = $parts->getBreadCrumbs();
        $footer = $parts->getFooter();
        $title = $parts->getTitle();
        $short_title = $parts->getShortTitle();
        $view_title = $parts->getViewTitle();

        $page = $this->ui->factory()->layout()->page()->standard(
            [$parts->getContent()],
            $meta_bar,
            $main_bar,
            $bread_crumbs,
            $header_image,
            $footer,
            $title,
            $short_title,
            $view_title
        );
        
        foreach ($this->meta->getMetaData()->getItems() as $meta_datum) {
            $page = $page->withAdditionalMetaDatum($meta_datum->getKey(), $meta_datum->getValue());
        }

        $page = $page->withSystemInfos($parts->getSystemInfos())
                        ->withTextDirection($this->meta->getTextDirection() ?? Standard::LTR);

        if (null !== $responsive_header_image) {
            $page = $page->withResponsiveLogo($responsive_header_image);
        }

        return $page;
    }
}
