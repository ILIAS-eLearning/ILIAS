<?php namespace ILIAS\LTI\Screen;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TopLinkItemRenderer;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\Button\Bulky;
use ILIAS\Data\URI;
use ilMemberViewSettings;
use ilObject;
use ilLink;

/**
 * Class LtiViewLayoutProvider
 *
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de>
 */
class LtiViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->lti();
    }

    /**
     * @inheritDoc
     */

    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        $this->dic->logger()->lti()->info("getPageBuilderDecorator");
        if ($this->dic["lti"]->isActive()) {
            if (isset($_SESSION['lti_launch_css_url']) && $_SESSION['lti_launch_css_url'] != "") {
                $this->globalScreen()->layout()->meta()->addCss($_SESSION['lti_launch_css_url']);
            }
            $this->globalScreen()->layout()->meta()->addCss('./Services/LTI/templates/default/lti.css');
            $mv = ilMemberViewSettings::getInstance();
            $isMemberView = false;
            $ref_id = "";
            $url = "";
            if ($mv->isActive()) {
                $this->dic->logger()->lti()->info("memberView isActive in LTI Mode");
                $isMemberView = true;
                $ref_id = $mv->getCurrentRefId();
                $url = new URI(ilLink::_getLink(
                    $ref_id,
                    ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
                    array('mv' => 0)
                ));
            }
            return $this->factory->page()->withHighPriority()->withModification(
                function (PagePartProvider $parts) use($isMemberView,$ref_id,$url): Page {
                    $this->dic->logger()->lti()->info("withModification");
                    $page = $this->getPage($parts);
                    if ($isMemberView) {
                        return $page->withModeInfo($this->dic->ui()->factory()->mainControls()->modeInfo($this->dic->language()->txt('mem_view_long'), $url));
                    }
                    else {
                        return $page;
                    }
                }
            );
        }
        return null;
    }

    private function getTitle(): String {
       return $this->dic["lti"]->getTitleBar(true); 
    }
    
    private function getShortTitle() : String {
       return ($this->dic["lti"]->getShortTitle()) ? $this->dic["lti"]->getShortTitle() : "";
    }
    
    private function getViewTitle() : String {
       return ($this->dic["lti"]->getViewTitle()) ? $this->dic["lti"]->getViewTitle() : "";
    }
    
    private function getMetaBar() : MetaBar {
        $f = $this->dic->ui()->factory();
        $close = $f->button()->close();
        $exit_symbol = $f->symbol()->glyph()->remove();
        $exit_txt = $this->dic['lti']->lng->txt('lti_exit');
        $exit = $f->button()->bulky($exit_symbol,$exit_txt,$this->dic["lti"]->getCmdLink('exit'));
        $metabar = $f->mainControls()->metaBar()->withAdditionalEntry('exit', $exit);
        return $metabar;
    }

    private function getMainBar() : Mainbar {
        $if = $this->globalScreen()->identification()->core($this);
        $f = $this->dic->ui()->factory();
        $mb = $f->mainControls()->mainBar();
        $title = ($this->dic["lti"]->getHomeTitle() != "") ? $this->dic["lti"]->getHomeTitle() : "LTI Home";
        $link = ($this->dic["lti"]->getHomeLink() != "") ? $this->dic["lti"]->getHomeLink() : "#";
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/home.svg"), $title);
        $lti_home = $this->globalScreen()->mainbar()->topLinkItem($if->identifier('mm_lti_home'))
            ->withSymbol($icon)
            ->withTitle($title)
            ->withAction($link);
        $renderer = new TopLinkItemRenderer();
        $item = $renderer->getComponentWithContent($lti_home);
        $more_btn = $f->button()->bulky(
        $f->symbol()->icon()->standard('', ''),
            'more',
            '#'
        );
        return $mb->withAdditionalEntry("lti_home",$item)->withMoreButton($more_btn);
    }

    private function getPage(PagePartProvider $parts) : Page {
        $header_image = $parts->getLogo();
        $main_bar = $this->getMainBar();
        $meta_bar = $this->getMetaBar();
        $bread_crumbs = $parts->getBreadCrumbs();
        $footer = null;
        $title = $this->getTitle();
        $short_title = ($this->getShortTitle()) ? $this->getShortTitle() : $parts->getShortTitle();
        $view_title = ($this->getViewTitle()) ? $this->getViewTitle() : $parts->getViewTitle();
        
        return $this->dic->ui()->factory()->layout()->page()->standard(
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
    }
}
