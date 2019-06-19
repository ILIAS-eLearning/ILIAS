<?php namespace ILIAS\GlobalScreen\Scope\Layout\Builder;

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Interface PageBuilder
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardPageBuilder implements PageBuilder
{

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;


    /**
     * StandardPageBuilder constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
    }


    /**
     * @param PagePartProvider $parts
     *
     * @return Page
     */
    public function build(PagePartProvider $parts) : Page
    {
        $header_image = $parts->getLogo();
        $main_bar = $parts->getMainBar();
        $meta_bar = $parts->getMetaBar();

        $bread_crumbs = $parts->getBreadCrumbs();

        return $this->ui->factory()->layout()->page()->standard([$parts->getContent()], $meta_bar, $main_bar, $bread_crumbs, $header_image);
    }
}
