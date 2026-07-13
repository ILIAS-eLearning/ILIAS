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

namespace ILIAS\ILIASObject\Properties\Translations;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\Input\Field\Select as SelectInput;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundtripModal;
use ILIAS\Data\Factory as DataFactory;

/**
 * GUI class for object translation handling.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class TranslationGUI
{
    private const CMD_LIST_TRANSLATIONS = 'listTranslations';
    private const CMD_ADD_TRANSLATION = 'addTranslation';
    private const CMD_DEACTIVATE_CONTENT_MULTILANG = 'deactivateContentTranslation';
    private const CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION = 'activateContentTranslation';

    private Translations $translations;

    private bool $force_content_translation = false;
    private bool $supports_content_translation = true;

    public function __construct(
        private readonly \ilObject $object,
        private readonly \ilLanguage $lng,
        private readonly \ilAccess $access,
        private readonly \ilObjUser $user,
        private readonly \ilCtrl $ctrl,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly HTTPService $http,
        private readonly Refinery $refinery,
        private readonly \ilToolbarGUI $toolbar
    ) {
        $this->translations = $this->object->getObjectProperties()->getPropertyTranslations();
    }

    public function supportContentTranslation(bool $content_translation): void
    {
        $this->supports_content_translation = $content_translation;
    }

    /**
     * Some objects like learning modules do not support to translate only the title
     * and the description. If they acticate multilinguasm, they translate always
     * title, description AND content. They have to call setTitleDescrOnlyMode(false)
     * to indicate this. Other contexts, e.g. categories allow to only translate
     * title and description and activate the content multilinguasm separately.
     */
    public function forceContentTranslation(): void
    {
        $this->force_content_translation = true;
    }

    public function executeCommand(): void
    {
        $commands = [
            self::CMD_LIST_TRANSLATIONS,
            self::CMD_ADD_TRANSLATION,
            self::CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION,
            self::CMD_DEACTIVATE_CONTENT_MULTILANG
        ];

        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::CMD_LIST_TRANSLATIONS);
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'));
            $this->ctrl->redirectByClass(get_class($this->object) . 'GUI');
        }
        if (in_array($cmd, $commands)) {
            $this->$cmd();
        }
    }

    public function listTranslations(
        ?RoundtripModal $lang_modal = null
    ): void {
        $this->lng->loadLanguageModule($this->object->getType());

        $table = new TranslationsTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->refinery,
            $this->tpl,
            $this->http,
            $this->ctrl,
            $this->translations,
            $this->object->getObjectProperties(),
            (new DataFactory())->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getFormActionByClass(
                    self::class,
                    self::CMD_LIST_TRANSLATIONS
                )
            )
        );
        $table->runAction();
        $content = [
            'table' => $table->getTable()
        ];

        if ($this->translations->migrationMissing()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('missing_migration'));
            $this->tpl->setContent($this->ui_renderer->render($content));
            return;
        }

        if ($this->getArrayWithAddableLanguages() !== []) {
            $content['lang_modal'] = $this->addAddLanguagesToolbarActionAndRetrieveModal($lang_modal);
        }

        if ($this->supports_content_translation) {
            $content['content_trans_modal'] = $this->addContentTranslationToolbarActionAndRetrieveModal();
        }

        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    public function addTranslation(): void
    {
        $modal = $this->getAddLanguageModal()
            ->withRequest($this->http->request());
        $data = $modal->getData();
        if ($data === null) {
            $this->listTranslations($modal->withOnLoad($modal->getShowSignal()));
            return;
        }

        $this->translations = $this->translations->withLanguage($data[0]);
        $this->object->getObjectProperties()->storePropertyTranslations(
            $this->translations
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirectByClass($this->ctrl->getCurrentClassPath());
    }

    public function activateContentTranslation(): void
    {
        $data = $this->getActivateMultilingualityModal()
            ->withRequest($this->http->request())
            ->getData();

        if ($data === null) {
            return;
        }

        $this->translations = $this->translations->withLanguage(
            new Language(
                $data['lang'],
                $this->object->getTitle(),
                $this->object->getDescription(),
                true,
                true
            )
        );

        $this->object->getObjectProperties()->storePropertyTranslations(
            $this->translations
        );
        $this->listTranslations();
    }

    public function deactivateContentTranslation(): void
    {
        $this->translations = $this->translations->withDeactivatedContentTranslation();
        $this->object->getObjectProperties()->storePropertyTranslations(
            $this->translations
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('obj_cont_transl_deactivated'), true);
        $this->listTranslations();
    }

    private function addAddLanguagesToolbarActionAndRetrieveModal(
        ?RoundtripModal $modal = null
    ): RoundtripModal {
        $modal ??= $this->getAddLanguageModal();
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('obj_add_language'),
                $modal->getShowSignal()
            )
        );

        return $modal;
    }

    private function getAddLanguageModal(): RoundtripModal
    {
        $ff = $this->ui_factory->input()->field();
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('obj_add_language'),
            null,
            [
                $ff->group([
                    'language' => $this->buildLangSelectionInput()
                        ->withRequired(true),
                    'title' => $ff->text($this->lng->txt('title'))
                        ->withRequired(true),
                    'description' => $ff->textarea($this->lng->txt('description'))
                ])->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        static fn(array $vs): Language => new Language(
                            $vs['language'],
                            $vs['title'],
                            $vs['description']
                        )
                    )
                )
            ],
            $this->ctrl->getFormActionByClass(self::class, self::CMD_ADD_TRANSLATION)
        );
    }

    private function addContentTranslationToolbarActionAndRetrieveModal(): Modal
    {
        $lang_var_postfix = '_multilang';
        $deactivation_modal_text_tag = 'obj_deactivate_multilang_conf';
        if (!$this->force_content_translation) {
            $lang_var_postfix = '_content_lang';
            $deactivation_modal_text_tag = 'obj_deactivate_content_transl_conf';
        }

        if (!$this->force_content_translation && !$this->translations->getContentTranslationActivated()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('obj_multilang_title_descr_only'));
        }

        if (!$this->translations->getContentTranslationActivated()) {
            $activate_modal = $this->getActivateMultilingualityModal();
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('obj_activate' . $lang_var_postfix),
                    $activate_modal->getShowSignal()
                )
            );
            return $activate_modal;
        }

        $deactivate_modal = $this->getConfirmDeactivateMultilingualityModal($deactivation_modal_text_tag);
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('obj_deactivate' . $lang_var_postfix),
                $deactivate_modal->getShowSignal()
            )
        );
        return $deactivate_modal;
    }

    private function getConfirmDeactivateMultilingualityModal(string $text_tag): Modal
    {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt($text_tag),
            $this->ctrl->getFormActionByClass(self::class, self::CMD_DEACTIVATE_CONTENT_MULTILANG)
        )->withActionButtonLabel($this->lng->txt('confirm'));
    }

    private function getActivateMultilingualityModal(): RoundtripModal
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('confirm'),
            $this->ui_factory->legacy()->content($this->lng->txt('obj_select_base_lang')),
            [
                'lang' => $this->getMasterLangSelectionInput()
            ],
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION)
        );
    }

    private function buildLangSelectionInput(): SelectInput
    {
        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('language'),
            $this->getArrayWithAddableLanguages()
        );
    }

    private function getMasterLangSelectionInput(): Input
    {
        $options = array_reduce(
            $this->lng->getInstalledLanguages(),
            function (array $c, string $v): array {
                $c[$v] = $this->lng->txt("meta_l_{$v}");
                return $c;
            },
            []
        );

        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('obj_base_lang'),
            $options
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn($v) => in_array($v, array_keys($options)) ? $v : $this->lng->getDefaultLanguage()
            )
        )->withValue($this->user->getLanguage());
    }

    private function getArrayWithAddableLanguages(): array
    {
        $enabled_langs = $this->translations->getLanguages();
        return array_reduce(
            $this->lng->getInstalledLanguages(),
            function (array $c, string $v) use ($enabled_langs): array {
                if (!array_key_exists($v, $enabled_langs)) {
                    $c[$v] = $this->lng->txt("meta_l_{$v}");
                }
                return $c;
            },
            []
        );
    }
}
