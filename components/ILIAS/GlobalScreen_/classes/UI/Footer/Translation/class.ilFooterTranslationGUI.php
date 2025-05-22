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

use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen_\UI\Translator;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen_\UI\UIHelper;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslatableItem;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationWorkflowForm;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepository;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;
use ILIAS\Data\URI;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFooterTranslationGUI: ilFooterGroupsGUI
 * @ilCtrl_isCalledBy ilFooterTranslationGUI: ilFooterEntriesGUI
 */
final class ilFooterTranslationGUI
{
    use Hasher;
    use UIHelper;

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
    private ilCtrlInterface $ctrl;
    private ServerRequestInterface $request;
    private TranslationWorkflowForm $workflow;

    public function __construct(
        private Container $dic,
        private Translator $translator,
        private ilObjFooterUIHandling $ui_handling,
        private TranslatableItem $translatable_item,
        private ?URI $back_target = null
    ) {
        $this->ui_factory = $this->dic->ui()->factory();
        $this->ctrl = $this->dic->ctrl();
        $this->request = $this->dic->http()->request();

        $this->repository = new TranslationsRepositoryDB(
            $this->dic->database()
        );

        $this->workflow = new TranslationWorkflowForm(
            $this->dic->learningObjectMetadata(),
            $this->dic->ui(),
            $this->repository,
            $this->translatable_item,
        );
    }

    private function index(): void
    {
        // LISTING
        $translations = $this->repository->get($this->translatable_item);
        if ($translations->get() === []) {
            $content = $this->ui_factory->messageBox()->info(
                $this->translator->translate('no_translations')
            );
        } else {
            $items = [];
            foreach ($translations->get() as $t) {
                $items[$this->translator->translate('meta_l_' . $t->getLanguageCode())] = $t->getTranslation();
            }
            $content = $this->ui_factory->listing()->descriptive(
                $items
            );
            $content = $this->back_target === null ? $this->ui_factory->panel()->secondary()->legacy(
                $this->translator->translate('translations'),
                $this->ui_factory->legacy()->content(
                    $this->ui_handling->render($content)
                )
            ) : $content;
        }

        $prompt = $this->ui_factory->prompt()->standard(
            $this->ui_handling->getHereAsURI(self::CMD_LANGUAGE_SELECTION)
        );

        // Edit Button
        $edit_button = $this->ui_factory
            ->button()
            ->standard(
                $this->translator->translate('edit_translations'),
                '#'
            )
            ->withOnClick($prompt->getShowSignal());

        $this->dic->toolbar()->addComponent(
            $edit_button
        );

        if ($this->back_target === null) {
            $this->ui_handling->out(
                $prompt,
                $content
            );
            return;
        }

        $this->ui_handling->outAsyncAsModal(
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
        $this->ui_handling->outAsync(
            $this->workflow->asTranslationWorkflow(
                $this->ui_handling->getHereAsURI(),
                $this->back_target ?? $this->ui_handling->getHereAsURI(self::CMD_DEFAULT)
            )
        );
    }

    private function translateInAsyncModal(): void
    {
        $this->ui_handling->outAsync(
            $this->workflow->asTranslationModal(
                $this->ui_handling->getHereAsURI(self::CMD_SAVE_TRANSLATIONS)
            )
        );
    }

    private function saveTranslations(): void
    {
        $form = $this->workflow->asTranslationModal(
            $this->ui_handling->getHereAsURI(self::CMD_SAVE_TRANSLATIONS)
        );
        $form = $form->withRequest($this->request);
        if (($data = $form->getData()) === null) {
            $this->ui_handling->outAsync($form);
            return;
        }
        $this->ctrl->redirectToURL(
            (string) ($this->back_target ?? $this->ui_handling->getHereAsURI(self::CMD_DEFAULT))
        );
    }

    // HELPERS AND NEEDED IMPLEMENATIONS

    public function executeCommand(): void
    {
        $this->ui_handling->requireReadable();

        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);

        switch ($cmd) {
            case self::CMD_DEFAULT:
            case self::CMD_LANGUAGE_SELECTION:
            case self::CMD_SAVE_LANGUAGE_SELECTION:
            case self::CMD_TRANSLATE_IN_MODAL:
            case self::CMD_SAVE_TRANSLATIONS:
            default:
                $this->ui_handling->backToMainTab();
                $this->ui_handling->requireWritable();
                $this->$cmd();
                break;
        }
    }

}
