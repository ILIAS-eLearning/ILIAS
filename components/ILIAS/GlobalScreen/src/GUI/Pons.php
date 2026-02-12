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

namespace ILIAS\GlobalScreen\GUI;

use ILIAS\HTTP\Services;
use ILIAS\Refinery;
use ILIAS\Data\Factory;
use ILIAS\DI\UIServices;
use ILIAS\DI\RBACServices;
use ILIAS\GlobalScreen\GUI\Tabs\Tabs;
use ILIAS\GlobalScreen\GUI\Access\Access;
use ILIAS\GlobalScreen\GUI\Input\Input;
use ILIAS\GlobalScreen\GUI\Output\Output;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Flow\Flow;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationsRepository;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslatableItem;
use ILIAS\GlobalScreen\GUI\Flow\Command;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Pons
{
    private Input $input;
    private Translator $translator;
    private Output $output;
    private Flow $flow;
    private Access $access;
    private Tabs $tabs;
    private ?PonsGUI $latest_gui = null;

    public function __construct(
        Services $http,
        UIServices $ui_services,
        private \ilCtrlInterface $ctrl,
        Refinery\Factory $refinery,
        Factory $data_factory,
        \ilLanguage $lng,
        RBACServices $rbac_services,
        \ilObjUser $user,
        \ilTabsGUI $tabs,
        \ilToolbarGUI $toolbar,
        array $language_modules = [],
        ?TranslationsRepository $translations_repository = null,
    ) {
        $this->flow = new Flow(
            $http,
            $ctrl,
        );

        $this->input = new Input(
            $http,
            $refinery,
            $data_factory,
            $lng,
            $this->flow
        );

        $this->translator = new Translator(
            $lng,
            $translations_repository,
            ...$language_modules
        );

        $this->tabs = new Tabs(
            $this->translator,
            $ctrl,
            $tabs,
            $this->flow
        );

        $this->output = new Output(
            $ui_services,
            $http,
            $this->translator,
            $this->tabs,
            $toolbar
        );

        $this->access = new Access(
            $rbac_services,
            $http,
            $refinery,
        );
    }

    public function handle(string $entry_tab, array $post_handling_classes = []): bool
    {
        $next_class = $this->ctrl->getNextClass();
        $tab = $this->tabs->structure()->getById($entry_tab);

        if (empty($next_class)) {
            if ($tab === null) {
                throw new \RuntimeException('Handling Class not found');
            }

            $this->tabs->activate($entry_tab);

            return false; // we delegate this further
        }
        if ($next_class === strtolower(MultiLanguageGUI::class)) {
            if ($this->latest_gui !== null) {
                $item = $this->latest_gui->getCurrentItem();
                $back_target = $this->flow->getTargetURI(
                    $tab?->getHandlingClass() ?? ''
                );
            }

            $back_target ??= $this->flow->getParentAsURI('');
            $this->ctrl->forwardCommand(
                new MultiLanguageGUI(
                    $this,
                    $item instanceof TranslatableItem ? $item : null,
                    $back_target,
                )
            );
            return true;
        }

        if (in_array(strtolower($next_class), array_map('strtolower', $post_handling_classes), true)) {
            return false; // we delegate this further
        }

        $tab = $this->tabs->structure()->getByHandlingClass($next_class);

        if ($tab === null) {
            throw new \RuntimeException("Handling Class `$next_class` not found");
        }

        // check permissions
        $this->access->require($tab->getPermission());

        $handling_class = $tab->getHandlingClass();
        if (is_a($handling_class, PonsGUI::class, true)) {
            $this->latest_gui = new $handling_class($this);

            // try to check access early
            $command = $this->flow->getCommand(PonsGUI::CMD_DEFAULT);
            if (method_exists($this->latest_gui, $command)) {
                $method = new \ReflectionMethod($this->latest_gui, $command);
                $attributes = $method->getAttributes(Command::class);
                foreach ($attributes as $attribute) {
                    $instance = $attribute->newInstance();
                    try {
                        $this->access->require($instance->getPermissions());
                    } catch (\Exception $e) {
                        $this->out()->error($e->getMessage());
                        return false;
                    }
                }
            }
        }

        return (bool) $this->ctrl->forwardCommand($this->latest_gui);
    }

    public function in(): Input
    {
        return $this->input;
    }

    public function i18n(): Translator
    {
        return $this->translator;
    }

    public function out(): Output
    {
        return $this->output;
    }

    public function flow(): Flow
    {
        return $this->flow;
    }

    public function access(): Access
    {
        return $this->access;
    }

    public function tabs(): Tabs
    {
        return $this->tabs;
    }

    public function latestGUI(): ?PonsGUI
    {
        return $this->latest_gui;
    }

    public function setGeneralLanguagePrefix(string $prefix): void
    {
        $this->translator->setGeneralPrefix($prefix);
    }

    /**
     * @description this is only for convenience, but the constructor is still public and testing is possible as well
     */
    public static function fromDIC(
        array $language_modules = [],
        ?TranslationsRepository $translations_repository = null,
    ): self {
        global $DIC;
        return new self(
            $DIC->http(),
            $DIC->ui(),
            $DIC->ctrl(),
            $DIC->refinery(),
            new Factory(),
            $DIC->language(),
            $DIC->rbac(),
            $DIC->user(),
            $DIC['ilTabs'],
            $DIC['ilToolbar'],
            $language_modules,
            $translations_repository
        );
    }
}
