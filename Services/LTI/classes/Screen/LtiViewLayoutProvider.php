<?php /**
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

namespace ILIAS\LTI\Screen;

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
    const GS_EXIT_LTI = 'lti_exit_mode';

    protected function isLTIExitMode(CalledContexts $screen_context_stack) : bool
    {
        $data_collection = $screen_context_stack->current()->getAdditionalData();
        $is_exit_mode = $data_collection->is(self::GS_EXIT_LTI, true);
        return $is_exit_mode;
    }

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->lti();
    }

    /**
     * @inheritDoc
     */
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        $this->globalScreen()->layout()->meta()->addCss('./Services/LTI/templates/default/lti.css');
        $is_exit_mode = $this->isLTIExitMode($screen_context_stack);
        $external_css = ($is_exit_mode) ? '' : $this->dic["lti"]->getExternalCss();
        if ($external_css !== '') {
            $this->globalScreen()->layout()->meta()->addCss($external_css);
        }

        return $this->factory->page()
            ->withModification(
                function (PagePartProvider $parts) : Page {
                    $p = new StandardPageBuilder();
                    $page = $p->build($parts);

                    $mv_modeinfo = MemberViewLayoutProvider::getMemberViewModeInfo($this->dic);
                    if ($mv_modeinfo) {
                        $page = $page->withModeInfo($mv_modeinfo);
                    }

                    return $page->withNoFooter();
                }
            )
            ->withHighPriority();
    }

    /**
     * @inheritDoc
     */
    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        $is_exit_mode = $this->isLTIExitMode($screen_context_stack);

        return $this->globalScreen()->layout()->factory()->mainbar()
            ->withModification(
                function (?MainBar $mainbar) use ($is_exit_mode) : ?MainBar {
                    if ($mainbar === null) {
                        return null;
                    }
                    $tools = $mainbar->getToolEntries();
                    $mainbar = $mainbar->withClearedEntries();
                    if ($is_exit_mode) {
                        return $mainbar;
                    }
                    foreach ($tools as $id => $entry) {
                        $mainbar = $mainbar->withAdditionalToolEntry($id, $entry);
                    }
                    //$mainbar = $mainbar->withAdditionalEntry('lti_home', $lti_home);
                    return $mainbar;
                }
            )
            ->withHighPriority();
    }

    /**
     * @inheritDoc
     */
    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        $is_exit_mode = $this->isLTIExitMode($screen_context_stack);

        return $this->globalScreen()->layout()->factory()->metabar()
            ->withModification(
                function (?MetaBar $metabar) use ($is_exit_mode, $screen_context_stack) : ?Metabar {
                    if ($metabar === null) {
                        return null;
                    }
                    $metabar = $metabar->withClearedEntries();
                    if ($is_exit_mode) {
                        return $metabar;
                    }
                    $f = $this->dic->ui()->factory();
                    $exit_symbol = $f->symbol()->glyph()->close();
                    $exit_txt = $this->dic['lti']->lng->txt('lti_exit');
                    $exit = $f->button()->bulky($exit_symbol, $exit_txt, $this->dic["lti"]->getCmdLink('exit'));
                    $metabar = $metabar->withAdditionalEntry('exit', $exit);
                    return $metabar;
                }
            )
            ->withHighPriority();
    }

    /**
     * @inheritDoc
     */
    public function getTitleModification(CalledContexts $screen_context_stack) : ?TitleModification
    {
        $is_exit_mode = $this->isLTIExitMode($screen_context_stack);

        return $this->globalScreen()->layout()->factory()->title()
            ->withModification(
                function (?string $content) use ($is_exit_mode) : ?string {
                    if ($is_exit_mode) {
                        return $this->dic["lti"]->getTitleForExitPage();
                    }
                    return $this->dic["lti"]->getTitle();
                }
            )
            ->withHighPriority();
    }
}
