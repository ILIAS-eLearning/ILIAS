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

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services;
use ILIAS\GlobalScreen_\UI\Translator;
use ILIAS\UI\URLBuilderToken;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\Data\URI;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\KeyValue;

final class ilObjFooterUIHandling
{
    use Hasher;

    private ilGlobalTemplateInterface $main_tpl;
    private Renderer $ui_renderer;
    private Factory $ui_factory;


    public function __construct(
        private UIServices $ui,
        private Services $http,
        private ilTabsGUI $tabs_gui,
        private Translator $translator,
        private ilCtrlInterface $ctrl,
        private ilErrorHandling $error,
        private ilRbacSystem $rbac_system,
        private int $ref_id
    ) {
        $this->main_tpl = $this->ui->mainTemplate();
        $this->ui_renderer = $this->ui->renderer();
        $this->ui_factory = $this->ui->factory();
    }

    public function outAsyncAsModal(
        string $title,
        string $post_url,
        ?Component ...$components
    ): void {
        $is_form = count($components) === 1 && $components[0] instanceof Form;
        $are_interruptive = array_filter($components, fn($component): bool => $component instanceof KeyValue) !== [];

        $modal = match (true) {
            $is_form => $this->ui_factory->modal()->roundtrip(
                $title,
                null,
                $components[0]->getInputs(),
                $post_url
            ),
            $are_interruptive => $this->ui_factory->modal()->interruptive(
                $title,
                $this->translator->translate('confirm_delete'),
                $post_url
            )->withAffectedItems(
                array_map(
                    fn(KeyValue $item): \ILIAS\UI\Component\Modal\InterruptiveItem\KeyValue => $this->ui_factory->modal()->interruptiveItem()->keyValue(
                        $this->hash($item->getId()),
                        $item->getKey(),
                        $item->getValue()
                    ),
                    $components
                )
            ),
            default => $this->ui_factory->modal()->roundtrip(
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
        $components = array_filter($components, fn($component): bool => $component !== null);
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

    public function out(?Component ...$components): void
    {
        $components = array_filter($components, fn($component): bool => $component !== null);

        $this->main_tpl->setContent(
            $this->ui_renderer->render($components)
        );
    }
    public function render(?Component ...$components): string
    {
        $components = array_filter($components, fn($component): bool => $component !== null);

        return $this->ui_renderer->render($components);
    }

    public function buildMainTabs(): void
    {
        $this->tabs_gui->addTab(
            ilObjFooterAdministrationGUI::TAB_INDEX,
            $this->translator->translate('groups'),
            $this->ctrl->getLinkTargetByClass(
                ilObjFooterAdministrationGUI::class,
                ilObjFooterAdministrationGUI::CMD_DEFAULT
            )
        );
        $this->tabs_gui->addTab(
            ilObjFooterAdministrationGUI::TAB_PERMISSIONS,
            $this->translator->translate('perm_settings'),
            $this->ctrl->getLinkTargetByClass([ilObjFooterAdministrationGUI::class, ilPermissionGUI::class], 'perm')
        );
    }

    public function backToMainTab(): void
    {
        $this->tabs_gui->clearTargets();

        if (
            $this->ctrl->getCmd(ilFooterEntriesGUI::CMD_DEFAULT) !== ilFooterEntriesGUI::CMD_DEFAULT
            && $this->ctrl->getCmdClass() === strtolower(ilFooterEntriesGUI::class)
        ) {
            $this->tabs_gui->setBackTarget(
                $this->translator->translate('back'),
                $this->ctrl->getLinkTargetByClass(
                    ilFooterEntriesGUI::class,
                    ilFooterEntriesGUI::CMD_DEFAULT
                )
            );
            return;
        }

        $this->tabs_gui->setBackTarget(
            $this->translator->translate('back'),
            $this->ctrl->getLinkTargetByClass(
                ilObjFooterAdministrationGUI::class,
                ilObjFooterAdministrationGUI::CMD_DEFAULT
            )
        );
    }

    public function activateTab(string $tab): void
    {
        $this->tabs_gui->activateTab($tab);
    }

    public function requireReadable(): void
    {
        $this->require('read');
    }

    public function requireWritable(): void
    {
        $this->require('write');
    }

    public function hasPermission(string $permissions): bool
    {
        return $this->rbac_system->checkAccess($permissions, $this->ref_id);
    }

    public function require(string $permissions): void
    {
        if (!$this->hasPermission($permissions)) {
            $this->error->raiseError($this->translator->translate('msg_no_perm_read'), $this->error->WARNING);
        }
    }

    public function getHereAsURI(?string $cmd = null): URI
    {
        $uri = new URI((string) $this->http->request()->getUri());
        if ($cmd !== null) {
            return $uri->withParameter('cmd', $cmd);
        }
        return $uri;
    }
    public function buildURI(string $from_path): URI
    {
        $request = $this->http->request()->getUri();
        return new URI($request->getScheme() . '://' . $request->getHost() . '/' . ltrim($from_path, '/'));
    }

    public function sendMessageAndRedirect(
        string $type,
        string $message,
        string $target
    ): void {
        $this->main_tpl->setOnScreenMessage(
            $type,
            $message,
            true
        );
        $this->ctrl->redirectToURL($target);
    }

    public function saveIdentificationsToRequest(
        object|string $gui_class,
        string|URLBuilderToken $token,
        string $value
    ): void {
        $name = $token instanceof URLBuilderToken ? $token->getName() : $token;
        $this->ctrl->setParameterByClass(
            is_object($gui_class) ? $gui_class::class : $gui_class,
            $name,
            $this->hash($value)
        );
    }

    public function getIdentificationsFromRequest(string|URLBuilderToken $token): array
    {
        if ($token === null) {
            return [];
        }

        $query_params = $this->http->request()->getQueryParams(); // aka $_GET
        $name = $token instanceof URLBuilderToken ? $token->getName() : $token;
        $ids = $query_params[$name] ?? []; // array of field ids
        $ids = is_array($ids) ? $ids : [$ids];

        // all objects
        if (($ids[0] ?? null) === 'ALL_OBJECTS') {
            return []; // currently we cannot support all
        }

        // check interruptive items
        if (($interruptive_items = $this->http->request()->getParsedBody()['interruptive_items'] ?? false)) {
            foreach ($interruptive_items as $interruptive_item) {
                $ids[] = $interruptive_item;
            }
        }

        return array_map(fn($id): string => $this->unhash($id), $ids);
    }

}
