<?php namespace ILIAS\LTI\Screen;

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\Button\Bulky;
use ILIAS\Data\URI;

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\TitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ShortTitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ViewTitleModification;
use ILIAS\Container\Screen\MemberViewLayoutProvider;

/**
 * Class LtiViewLayoutProvider
 *
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de>
 */
class LtiViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{

    protected function isLTIMode(): bool
    {
        return true;
        return false;
        //return $this->dic["lti"]->isActive();
    }


    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
        //return $this->context_collection->lti();
    }

    /**
     * @inheritDoc
     */
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        if(! $this->isLTIMode()) {
            return null;
        }

        //add css; I'd personally get rid of that...
        if (isset($_SESSION['lti_launch_css_url']) && $_SESSION['lti_launch_css_url'] != "") {
            $this->globalScreen()->layout()->meta()->addCss($_SESSION['lti_launch_css_url']);
        }
        $this->globalScreen()->layout()->meta()->addCss('./Services/LTI/templates/default/lti.css');

        return $this->factory->page()
            ->withModification(
                function (PagePartProvider $parts): Page {
                    $p = new StandardPageBuilder();
                    $page = $p->build($parts);

                    $mv_modeinfo = MemberViewLayoutProvider::getMemberViewModeInfo($this->dic);
                    if($mv_modeinfo) {
                        $page = $page->withModeInfo($mv_modeinfo);
                    }

                    return $page->withNoFooter();
                }
            )
            ->withHighPriority();
    }

    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        if(! $this->isLTIMode()) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->mainbar()
            ->withModification(
                function (MainBar $mainbar) : ?MainBar {
                    $f = $this->dic->ui()->factory();
                    $title = ($this->dic["lti"]->getHomeTitle() != "") ? $this->dic["lti"]->getHomeTitle() : "LTI Home";
                    $link = ($this->dic["lti"]->getHomeLink() != "") ? $this->dic["lti"]->getHomeLink() : "#";
                    $icon = $f->symbol()->icon()->standard('dshs', $title); //dashboard
                    $lti_home = $f->button()->bulky($icon, $title, $link);

                    $tools = $mainbar->getToolEntries();
                    $mainbar = $mainbar->withClearedEntries();
                    foreach ($tools as $id => $entry) {
                        $mainbar = $mainbar->withAdditionalToolEntry($id, $entry);
                    }
                    $mainbar = $mainbar->withAdditionalEntry('lti_home', $lti_home);
                    return $mainbar;
                }
            )
            ->withHighPriority();
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        if(! $this->isLTIMode()) {
            return null;
        }
        return $this->globalScreen()->layout()->factory()->metabar()
            ->withModification(
                function (MetaBar $metabar) : ?Metabar {
                    $f = $this->dic->ui()->factory();
                    $exit_symbol = $f->symbol()->glyph()->close();
                    $exit_txt = $this->dic['lti']->lng->txt('lti_exit');
                    $exit = $f->button()->bulky($exit_symbol, $exit_txt, $this->dic["lti"]->getCmdLink('exit'));

                    $metabar = $metabar->withClearedEntries();
                    $metabar = $metabar->withAdditionalEntry('exit', $exit);
                    return $metabar;
                }
            )
            ->withHighPriority();
    }

    public function getTitleModification(CalledContexts $screen_context_stack) : ?TitleModification
    {
        if(! $this->isLTIMode()) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->title()
            ->withModification(
                function (string $content) : string {
                    return $this->dic["lti"]->getTitleBar(true);
                }
            )
            ->withHighPriority();
    }

    public function getShortTitleModification(CalledContexts $screen_context_stack) : ?ShortTitleModification
    {
       if(! $this->isLTIMode()) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->short_title()
            ->withModification(
                function (string $content) : string {
                    return $this->dic["lti"]->getShortTitle() ?? '';
                }
            )
            ->withHighPriority();
    }

    public function getViewTitleModification(CalledContexts $screen_context_stack) : ?ViewTitleModification
    {
        if(! $this->isLTIMode()) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->view_title()
            ->withModification(
                function (string $content) : string {
                    return $this->dic["lti"]->getViewTitle() ?? '';
                }
            )
            ->withHighPriority();
    }

}
