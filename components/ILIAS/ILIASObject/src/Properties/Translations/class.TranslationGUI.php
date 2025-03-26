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
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use Psr\Http\Message\RequestInterface;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\Modal\Modal;
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
    private const CMD_DELETE_TRANSLATIONS = 'deleteTranslations';
    private const CMD_SAVE_LANGUAGES = 'saveLanguages';
    private const CMD_CONFIRM_REMOVE_LANGUAGES = 'confirmDeleteTranslations';
    private const CMD_DEACTIVATE_CONTENT_MULTILANG = 'deactivateContentTranslation';
    private const CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION = 'activateContentTranslation';

    private Translations $translations;

    private bool $force_content_translation = false;
    private bool $hide_description = false;
    private bool $support_content_translation = true;

    public function __construct(
        private readonly \ilObject $object,
        private readonly Language $lng,
        private readonly \ilAccess $access,
        private readonly \ilObjUser $user,
        private readonly \ilCtrl $ctrl,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ArrayBasedRequestWrapper $post_wrapper,
        private readonly RequestInterface $request,
        private readonly Refinery $refinery,
        private readonly \ilToolbarGUI $toolbar
    ) {
        $this->translations = $this->object->getObjectProperties()->getPropertyTranslations();
    }

    public function hideDescription(bool $hide): void
    {
        $this->hide_description = $hide;
    }

    public function supportContentTranslation(bool $content_translation): void
    {
        $this->support_content_translation = $content_translation;
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
            self::CMD_DELETE_TRANSLATIONS,
            self::CMD_CONFIRM_REMOVE_LANGUAGES,
            self::CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION,
            self::CMD_DEACTIVATE_CONTENT_MULTILANG
        ];

        $this->ctrl->getNextClass($this);
        if (!$this->access->checkAccess('write', '', $this->obj_gui->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'));
            $this->ctrl->redirect($this->obj_gui);
        }
        $cmd = $this->ctrl->getCmd(self::CMD_LIST_TRANSLATIONS);
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'));
            $this->ctrl->redirectByClass(get_class($this->object) . 'GUI');
        }
        if (in_array($cmd, $commands)) {
            $this->$cmd();
        }
    }

    public function listTranslations(bool $get_post_values = false, bool $add = false): void
    {
        $this->lng->loadLanguageModule($this->object->getType());

        $content = [
            'table' => (new TranslationsTable(
                $this->ui_factory,
                $this->lng,
                $this->request,
                $this->translations,
                (new DataFactory())->uri(
                    ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                        self::class,
                        self::CMD_LIST_TRANSLATIONS
                    )
                )
            ))->getTable()
        ];

        if ($this->translations->migrationMissing()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('missing_migration'));
            $this->tpl->setContent($this->ui_renderer->render($content));
            return;
        }

        $content['lang_modal'] = $this->getAddLanguagesModal();
        if (!$this->force_content_translation || $this->translations->getContentTranslationActivated()) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('obj_add_languages'),
                    $content['lang_modal']->getShowSignal()
                )
            );
        }

        if ($this->support_content_translation) {
            $content['content_trans_modal'] = $this->addContentTranslationToolbarActionAndRetrieveCorrespondingModal();
        }

        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    private function getAddLanguagesModal(): Modal
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('confirm'),
            null,
            [
                'langs' => $this->getMultiLangSelectionInput()
            ],
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE_LANGUAGES)
        );
    }

    private function addContentTranslationToolbarActionAndRetrieveCorrespondingModal(): ?Modal
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
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_DEACTIVATE_CONTENT_MULTILANG)
        )->withActionButtonLabel($this->lng->txt('confirm'));
    }

    private function getActivateMultilingualityModal(): Modal
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('confirm'),
            $this->ui_factory->legacy()->content($this->lng->txt('obj_select_master_lang')),
            [
                'lang' => $this->getMasterLangSelectionInput()
            ],
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION)
        );
    }

    public function getMultiLangSelectionInput(bool $add = false): Input
    {
        $enabled_langs = $this->translations->getLanguages();
        $options = array_reduce(
            $this->lng->getInstalledLanguages(),
            function (array $c, string $v) use ($enabled_langs): array {
                if (!array_key_exists($v, $enabled_langs)) {
                    $c[$v] = $this->lng->txt("meta_l_{$v}");
                }
                return $c;
            },
            []
        );

        $master_lang = $this->translations->getMasterLanguage();
        $trafo = $this->refinery->custom()->transformation(
            function (array $vs) use ($master_lang) {
                $langs = [];
                foreach ($vs as $v) {
                    if ($v !== $master_lang && $v !== '') {
                        $langs[] = $v;
                    }
                }
                return $langs;
            }
        );
        return $this->ui_factory->input()->field()->multiSelect(
            $this->lng->txt('obj_additional_langs'),
            $options
        )->withAdditionalTransformation($trafo);
    }

    public function getMasterLangSelectionInput(): Input
    {
        $options = array_reduce(
            $this->lng->getInstalledLanguages(),
            function (array $c, string $v): array {
                $c[$v] = $this->lng->txt("meta_l_{$v}");
                return $c;
            },
            []
        );

        $trafo = $this->refinery->custom()->transformation(
            fn($v) => in_array($v, array_keys($options)) ? $v : $this->lng->getDefaultLanguage()
        );

        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('obj_master_lang'),
            $options
        )->withAdditionalTransformation($trafo)
            ->withValue($this->user->getLanguage());
    }

    public function activateContentTranslation(): void
    {
        $data = $this->getActivateMultilingualityModal()
            ->withRequest($this->request)
            ->getData();
        $obj_trans = $this->translations->withMasterLanguage($data['lang']);
        if (!in_array($data['lang'], $obj_trans->getLanguages())) {
            $obj_trans = $obj_trans->withAdditionalLanguage(
                $data['lang'],
                $this->object->getTitle(),
                $this->object->getDescription(),
                true
            );
        }

        $this->object->getObjectProperties()->storePropertyTranslations(
            $obj_trans->withContentTranslationActivated(true)
        );
        $this->ctrl->redirectByClass(self::class, self::CMD_LIST_TRANSLATIONS);
    }

    public function deactivateContentTranslation(): void
    {
        $this->object->getObjectProperties()->storePropertyTranslations(
            $this->translations->withContentTranslationActivated(false)
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('obj_cont_transl_deactivated'), true);

        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function confirmDeleteTranslations(): void
    {
        $this->lng->loadLanguageModule('meta');
        $trafo = $this->retrieveTrafoToRemoveDefaultLang();

        $languages = $this->post_wrapper->has('lang')
            ? $this->post_wrapper->retrieve(
                'lang',
                $trafo
            )
            : [];

        $to_be_deleted = $this->post_wrapper->has('check')
            ? $this->post_wrapper->retrieve(
                'check',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];

        if (count($to_be_deleted) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
        }

        $cgui = new \ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt('obj_conf_delete_lang'));
        $cgui->setCancel($this->lng->txt('cancel'), self::CMD_LIST_TRANSLATIONS);
        $cgui->setConfirm($this->lng->txt('remove'), self::CMD_DELETE_TRANSLATIONS);

        foreach (array_keys($to_be_deleted) as $index) {
            if (!array_key_exists($index, $languages)) {
                continue;
            }
            $cgui->addItem('lang[]', $languages[$index], $this->lng->txt('meta_l_' . $languages[$index]));
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteTranslations(): void
    {
        $trafo = $this->retrieveTrafoToRemoveDefaultLang();
        $langs_to_be_deleted = $this->post_wrapper->has('lang')
            ? $this->post_wrapper->retrieve(
                'lang',
                $trafo
            )
            : [];

        $this->object->getObjectProperties()->storePropertyTranslations(
            array_reduce(
                $langs_to_be_deleted,
                static fn(Translations $c, Language $v): Translations => $v->withoutLanguage($v),
                $this->translations
            )
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    private function retrieveTrafoToRemoveDefaultLang(): callable
    {
        $default_lang = $this->translations->getDefaultLanguage();
        return $this->refinery->custom()->transformation(
            function (?array $vs) use ($default_lang) {
                if ($vs === null) {
                    return [];
                }

                $langs = [];
                foreach ($vs as $k => $v) {
                    if ($v !== $default_lang) {
                        $langs[$k] = (string) $v;
                    }
                }
                return $langs;
            }
        );
    }
}
