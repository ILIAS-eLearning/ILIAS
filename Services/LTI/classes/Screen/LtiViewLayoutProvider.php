<?php namespace ILIAS\LTI\Screen;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\Button\Bulky;

/**
 * Class LtiViewLayoutProvider
 *
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de>
 */
class LtiViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{

    /**
     * @inheritDoc
     */
     
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->lti();
    }
    
    /**
     * This is a basic wip implementation for leaving the lti session with an exit button
     *
     * There is an ongoing discussion about a HeaderInfo bar for different ILIAS modes:
     *
     * https://docu.ilias.de/goto_docu_wiki_wpage_5979_1357.html
     *
     * and a PR Feature Discussion:
     *
     * https://github.com/ILIAS-eLearning/ILIAS/pull/2251
     *
     */
    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        $this->dic->logger()->lti()->info("isActive: " . $this->dic["lti"]->isActive());
        
        if ($this->dic["lti"]->isActive()) {
            if (isset($_SESSION['lti_launch_css_url']) && $_SESSION['lti_launch_css_url'] != "") {
                $this->globalScreen()->layout()->meta()->addCss($_SESSION['lti_launch_css_url']);
            }
            return $this->globalScreen()
                ->layout()
                ->factory()
                ->metabar()
                ->withModification(function (MetaBar $current) : ?MetaBar {
                    $f = $this->dic->ui()->factory();
                    $close = $f->button()->close();
                    $exit_symbol = $f->symbol()->glyph()->remove();
                    $exit = $f->button()->bulky($exit_symbol, "exit", $this->dic["lti"]->getCmdLink('exit'));
                    $metabar = $f->mainControls()->metaBar()->withAdditionalEntry('exit', $exit);
                    return $metabar;
                })->withHighPriority();
        } else {
            return null;
        }
    }

    /**
    * This is a basic wip implemantion which is hiding the complete navigation MainBar.
    * We are planning to create an own LTI MainBar with a minimal TopItem Entry p.e. "LTI Home" with slate like this (needs to be discussed):
    *
    * LtiRoot Object
    * -- Separator --
    * LastVisited Items
    * -- Separator --
    * Delete VistiedItems (?)
    */
     
    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        if ($this->dic["lti"]->isActive()) {
            return $this->globalScreen()
                ->layout()
                ->factory()
                ->mainbar()
                ->withModification(function (MainBar $current) : ?MainBar {
                    return null;
                })->withHighPriority();
        } else {
            return null;
        }
    }
}
