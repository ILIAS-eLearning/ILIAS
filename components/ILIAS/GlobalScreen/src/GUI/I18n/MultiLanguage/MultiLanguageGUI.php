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

namespace ILIAS\GlobalScreen\GUI\I18n;

use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslatableItem;
use ILIAS\GlobalScreen\GUI\AbstractPonsGUI;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationsRepository;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationWorkflowForm;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\Data\URI;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 * @internal          Please do not use outside GlobalScreen
 * @ilCtrl_IsCalledBy ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI: ilCommonActionDispatcherGUI, ilAdministrationGUI
 */
class MultiLanguageGUI extends AbstractPonsGUI
{
    private Translator $translator;
    /**
     * @var string
     */
    public const CMD_DEFAULT = 'index';
    /**
     * @var string
     */
    public const CMD_LANGUAGE_SELECTION = 'selectLanguages';
    /**
     * @var string
     */
    public const CMD_SAVE_LANGUAGE_SELECTION = 'saveLanguages';
    /**
     * @var string
     */
    public const CMD_TRANSLATE_IN_MODAL = 'translateInAsyncModal';
    /**
     * @var string
     */
    public const CMD_SAVE_TRANSLATIONS = 'saveTranslations';
    private TranslationsRepository $repository;
    private Factory $ui_factory;
    private ServerRequestInterface $request;
    private TranslationWorkflowForm $workflow;
    private TranslatableItem $translatable_item;

    public function __construct(
        Pons $pons,
        ?TranslatableItem $translatable_item = null,
        private ?URI $back_target = null,
    ) {
        parent::__construct($pons);
        global $DIC;

        $this->ui_factory = $this->pons->out()->ui()->factory();
        $this->request = $this->pons->in()->request();
        $this->translator = $this->pons->i18n();

        $this->repository = $this->pons->i18n()->ml()->repository();

        $this->workflow = new TranslationWorkflowForm(
            $DIC->learningObjectMetadata(),
            $this->pons->out()->ui(),
            $this->repository,
            $this->translatable_item = $translatable_item ?? $this->repository->retrieveCurrent($this->pons),
        );
    }

    public function getTokensToKeep(): array
    {
        return [];
    }

    private function index(): void
    {
        // LISTING
        $translations = $this->repository->get($this->translatable_item);
        if ($translations->get() === []) {
            $content = $this->ui_factory->messageBox()->info(
                $this->pons->i18n()->translate('no_translations')
            );
        } else {
            $items = [];
            foreach ($translations->get() as $t) {
                $items[$this->pons->i18n()->translate('meta_l_' . $t->getLanguageCode())] = $t->getTranslation();
            }
            $content = $this->ui_factory->listing()->descriptive(
                $items
            );
            $content = $this->back_target === null ? $this->ui_factory->panel()->secondary()->legacy(
                $this->translator->translate('translations'),
                $this->ui_factory->legacy()->content(
                    $this->pons->out()->render($content)
                )
            ) : $content;
        }

        $prompt = $this->ui_factory->prompt()->standard(
            $this->pons->flow()->getHereAsURI(self::CMD_LANGUAGE_SELECTION)
        );

        // Edit Button
        $edit_button = $this->ui_factory
            ->button()
            ->standard(
                $this->translator->translate('edit_translations'),
                '#'
            )
            ->withOnClick($prompt->getShowSignal());

        global $DIC; // currently not used
        $DIC->toolbar()->addComponent(
            $edit_button
        );

        if ($this->back_target === null) {
            $this->pons->out()->out(
                $prompt,
                $content
            );
            return;
        }

        $this->pons->out()->outAsyncAsModal(
            'Translations',
            (string) $this->back_target,
            $edit_button,
            $this->ui_factory->divider()->horizontal(),
            $prompt,
            $content
        );
    }

    private function selectLanguages(): void
    {
        $this->pons->out()->outAsync(
            $this->workflow->asTranslationWorkflow(
                $this->pons->flow()->getHereAsURI(),
                $this->back_target ?? $this->pons->flow()->getHereAsURI(self::CMD_DEFAULT)
            )
        );
    }

    private function translateInAsyncModal(): void
    {
        $this->pons->out()->outAsync(
            $this->workflow->asTranslationModal(
                $this->pons->flow()->getHereAsURI(self::CMD_SAVE_TRANSLATIONS)
            )
        );
    }

    private function saveTranslations(): void
    {
        $form = $this->workflow->asTranslationModal(
            $this->pons->flow()->getHereAsURI(self::CMD_SAVE_TRANSLATIONS)
        );
        $form = $form->withRequest($this->request);
        if (($data = $form->getData()) === null) {
            $this->pons->out()->outAsync($form);
            return;
        }
        $this->pons->flow()->ctrl()->redirectToURL(
            (string) ($this->back_target ?? $this->pons->flow()->getHereAsURI(self::CMD_DEFAULT))
        );
    }

    // HELPERS AND NEEDED IMPLEMENATIONS

    public function executeCommand(): bool
    {
        $cmd = $this->pons->flow()->getCommand(self::CMD_TRANSLATE_IN_MODAL);

        match ($cmd) {
            default => $this->$cmd(),
        };
    }

}
