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

namespace ILIAS\GlobalScreen\GUI\Output;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services;
use ILIAS\GlobalScreen\GUI\Hasher;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Tabs\Tabs;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Image\Image;
use Psr\Http\Message\ResponseInterface;
use ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\Data\URI;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Output
{
    use Hasher;

    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        private UIServices $ui_services,
        private Services $http,
        private Translator $translator,
        private Tabs $tabs,
        private \ilToolbarGUI $toolbar,
    ) {
        $this->ui_factory = $this->ui_services->factory();
        $this->ui_renderer = $this->ui_services->renderer();
        $this->main_tpl = $this->ui_services->mainTemplate();
    }

    public function response(): ResponseInterface
    {
        return $this->http->response();
    }

    public function ui(): UIServices
    {
        return $this->ui_services;
    }

    public function toolbar(): \ilToolbarGUI
    {
        return $this->toolbar;
    }

    public function success(string $message, bool $keep = true): void
    {
        $this->message('success', $message, $keep);
    }

    public function info(string $message, bool $keep = true): void
    {
        $this->message('info', $message, $keep);
    }

    public function error(string $message, bool $keep = true): void
    {
        $this->message('failure', $message, $keep);
    }

    public function message(string $type, string $message, bool $keep = true): void
    {
        $this->main_tpl->setOnScreenMessage($type, $message, $keep);
    }

    public function outAsyncAsConfirmation(
        string $title,
        string $message,
        string $button_text,
        URI|string $post_url,
        InterruptiveItem ...$components
    ): void {
        if ($post_url instanceof URI) {
            $post_url = (string) $post_url;
        }
        $modal = $this
            ->ui_factory
            ->modal()
            ->interruptive(
                $title,
                $message,
                $post_url
            )
            ->withAffectedItems(
                $components
            )
            ->withActionButtonLabel($button_text);

        $this->outAsync($modal);
    }

    public function outAsyncAsModal(
        string $title,
        URI|string $post_url,
        ?Component ...$components
    ): void {
        if ($post_url instanceof URI) {
            $post_url = (string) $post_url;
        }

        $is_form = count($components) === 1 && $components[0] instanceof Form;
        $are_interruptive = array_filter(
            $components,
            fn(?Component $component): bool => $component instanceof InterruptiveItem
        ) !== [];

        $modal = match (true) {
            $is_form => $this
                ->ui_factory
                ->modal()
                ->roundtrip(
                    $title,
                    null,
                    $components[0]->getInputs(),
                    $post_url
                ),
            $are_interruptive => $this
                ->ui_factory
                ->modal()
                ->interruptive(
                    $title,
                    $this->translator->translate('confirm_delete'),
                    $post_url
                )
                ->withAffectedItems(
                    $components
                ),
            default => $this
                ->ui_factory
                ->modal()
                ->roundtrip(
                    $title,
                    $components,
                    [],
                    $post_url
                )
        };

        $this->outAsync($modal);
    }

    public function outAsync(?Component ...$components): void
    {
        $components = array_filter($components, fn(?Component $component): bool => $component !== null);
        $string = $this->ui_renderer->renderAsync($components);
        $response = $this->http->response()->withBody(
            Streams::ofString(
                $string
            )
        );
        $this->http->saveResponse($response);
        $this->http->sendResponse();
        $this->http->close();
    }

    public function outString(string $string): void
    {
        $this->tabs->show();
        $this->main_tpl->setContent($string);
    }

    public function out(?Component ...$components): void
    {
        $this->tabs->show();
        $components = array_filter($components, fn(?Component $component): bool => $component !== null);

        $this->main_tpl->setContent(
            $this->ui_renderer->render($components)
        );
    }

    public function render(?Component ...$components): string
    {
        $components = array_filter($components, fn(?Component $component): bool => $component !== null);

        return $this->ui_renderer->render($components);
    }

    public function nok(bool $as_image = false): Icon|Image
    {
        if ($as_image) {
            return $this->ui_factory->image()->standard(
                'assets/images/standard/icon_unchecked.svg',
                ''
            );
        }

        return $this->ui_factory->symbol()->icon()->custom(
            'assets/images/standard/icon_unchecked.svg',
            '',
            'small'
        );
    }

    public function ok(bool $as_image = false): Icon|Image
    {
        if ($as_image) {
            return $this->ui_factory->image()->standard(
                'assets/images/standard/icon_checked.svg',
                ''
            );
        }

        return $this->ui_factory->symbol()->icon()->custom(
            'assets/images/standard/icon_checked.svg',
            '',
            'small'
        );
    }

}
