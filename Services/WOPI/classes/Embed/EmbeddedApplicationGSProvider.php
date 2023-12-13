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

namespace ILIAS\Services\WOPI\Embed;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\DI\Container;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Layout\Builder\PageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\StandardPagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\DecoratedPagePartProvider;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddedApplicationGSProvider extends AbstractModificationProvider
{
    private const USE_METABAR = 1;
    private const USE_MODE_INFO = 2;
    private int $display_mode = self::USE_MODE_INFO;
    public const EMBEDDED_APPLICATION = 'embedded_application';
    private SignalGeneratorInterface $signal_generator;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        global $DIC;
        $this->signal_generator = $DIC["ui.signal_generator"];
    }

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    public function getMainBarModification(CalledContexts $screen_context_stack): ?MainBarModification
    {
        if ($screen_context_stack->current()->getAdditionalData()->exists(self::EMBEDDED_APPLICATION)) {
            return $this->factory->mainbar()->withHighPriority()->withModification(
                fn(?MainBar $main_bar): ?MainBar => null
            );
        }
        return null;
    }

    public function getPageBuilderDecorator(CalledContexts $screen_context_stack): ?PageBuilderModification
    {
        if ($this->display_mode !== self::USE_MODE_INFO) {
            return null;
        }
        if (!$screen_context_stack->current()->getAdditionalData()->exists(self::EMBEDDED_APPLICATION)) {
            return null;
        }
        $embedded_application = $screen_context_stack->current()->getAdditionalData()->get(
            self::EMBEDDED_APPLICATION
        );
        if (!$embedded_application instanceof EmbeddedApplication) {
            return null;
        }

        return $this->factory->page()->withHighPriority()->withModification(
            function (PagePartProvider $page_part_provider): Page {
                $uif = $this->dic->ui()->factory();
                $builder = new StandardPageBuilder();
                $page_part_provider = new EmbeddedApplicationPagePartProvider($page_part_provider);

                return $builder->build($page_part_provider)
                               ->withModeInfo(
                                   $uif->mainControls()->modeInfo(
                                       $this->dic->language()->txt('close_wopi_editor'),
                                       new URI($_SERVER['HTTP_REFERER'])
                                   )
                               );
            }
        );
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack): ?MetaBarModification
    {
        if ($this->display_mode !== self::USE_METABAR) {
            return null;
        }
        if (!$screen_context_stack->current()->getAdditionalData()->exists(self::EMBEDDED_APPLICATION)) {
            return null;
        }

        $embedded_application = $screen_context_stack->current()->getAdditionalData()->get(
            self::EMBEDDED_APPLICATION
        );
        if (!$embedded_application instanceof EmbeddedApplication) {
            return null;
        }

        $button = $this->buildCloseButton($embedded_application);

        return $this->factory->metabar()->withHighPriority()->withModification(
            fn(?MetaBar $metabar): ?Metabar => $metabar !== null
                ? $metabar->withClearedEntries()
                          ->withAdditionalEntry(
                              'close_editor',
                              $button
                          )
                : null
        );
    }

    protected function buildCloseButton(
        EmbeddedApplication $embedded_application,
    ): \ILIAS\UI\Component\Button\Bulky {
        $uif = $this->dic->ui()->factory();
        $back_target = $embedded_application->getBackTarget();
        $signal = $this->signal_generator->create();
        $signal->addOption('target_url', (string) $back_target);

        $button = $uif->button()->bulky(
            $uif->symbol()->glyph()->close(),
            $this->dic->language()->txt('close'),
            (string) $back_target
        )->withOnClick(
            $signal
        )->withOnLoadCode(function ($id) use ($signal) {
            return "il.WOPI.bindCloseSignal('$id', '{$signal->getId()}');";
        });
        return $button;
    }
}
