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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use Psr\Http\Message\RequestInterface;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\Modal\Modal;

/**
 * GUI class for object translation handling.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslationGUI
{
    protected const CMD_LIST_TRANSLATIONS = 'listTranslations';
    protected const CMD_SAVE_TRANSLATIONS = 'saveTranslations';
    protected const CMD_ADD_TRANSLATION = 'addTranslation';
    protected const CMD_DELETE_TRANSLATIONS = 'deleteTranslations';
    protected const CMD_SAVE_LANGUAGES = 'saveLanguages';
    protected const CMD_CONFIRM_REMOVE_LANGUAGES = 'confirmDeleteTranslations';
    protected const CMD_SET_FALLBACK = 'setFallback';
    protected const CMD_DEACTIVATE_CONTENT_MULTILANG = 'deactivateContentMultiLang';
    protected const CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION = 'saveContentTranslationActivation';
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;
    protected ArrayBasedRequestWrapper $post_wrapper;
    protected RequestInterface $request;
    protected Refinery $refinery;

    protected ilObjectGUI $obj_gui;
    protected ilObject $obj;
    protected ilObjectTranslation $obj_trans;

    protected bool $title_descr_only = true;
    protected bool $hide_description = false;
    protected bool $fallback_lang_mode = true;
    protected bool $support_content_translation = true;

    public function __construct($obj_gui)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->toolbar = $DIC['ilToolbar'];
        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->request = $DIC->http()->request();
        $this->refinery = $DIC['refinery'];


        $this->obj_gui = $obj_gui;
        $this->obj = $obj_gui->getObject();

        $this->obj_trans = ilObjectTranslation::getInstance($this->obj->getId());
    }

    public function hideDescription(bool $hide): void
    {
        $this->hide_description = $hide;
    }

    public function supportContentTranslation(bool $content_translation): void
    {
        $this->support_content_translation = $content_translation;
    }

    private function getTableValuesByObjects(): array
    {
        $data = [];
        foreach ($this->obj_trans->getLanguages() as $k => $v) {
            $data[$k]['default'] = (int) $v->isDefault();
            $data[$k]['title'] = $v->getTitle();
            $data[$k]['desc'] = $v->getDescription();
            $data[$k]['lang'] = $v->getLanguageCode();
        }
        return $data;
    }

    private function getTableValuesByRequest(): array
    {
        $vals = [];

        $titles = $this->post_wrapper->has('title')
            ? $this->post_wrapper->retrieve(
                'title',
                $this->refinery->to()->listOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $descriptions = $this->post_wrapper->has('desc')
            ? $this->post_wrapper->retrieve(
                'desc',
                $this->refinery->to()->listOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $languages = $this->post_wrapper->has('lang')
            ? $this->post_wrapper->retrieve(
                'lang',
                $this->refinery->to()->listOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $default = $this->post_wrapper->has('default')
            ? $this->post_wrapper->retrieve(
                'default',
                $this->refinery->kindlyTo()->int()
            )
            : '';

        foreach ($titles as $k => $v) {
            $vals[] = [
                'title' => $v,
                'desc' => $descriptions[$k],
                'lang' => $languages[$k],
                'default' => ($default == $k)
            ];
        }
        return $vals;
    }

    /**
     * Some objects like learning modules do not support to translate only the title
     * and the description. If they acticate multilinguasm, they translate always
     * title, description AND content. They have to call setTitleDescrOnlyMode(false)
     * to indicate this. Other contexts, e.g. categories allow to only translate
     * title and description and activate the content multilinguasm separately.
     */
    public function setTitleDescrOnlyMode(bool $val): void
    {
        $this->title_descr_only = $val;
    }

    public function getTitleDescrOnlyMode(): bool
    {
        return $this->title_descr_only;
    }

    public function setEnableFallbackLanguage(bool $val): void
    {
        $this->fallback_lang_mode = $val;
    }

    public function getEnableFallbackLanguage(): bool
    {
        return $this->fallback_lang_mode;
    }

    public function executeCommand(): void
    {
        $commands = [
            self::CMD_LIST_TRANSLATIONS,
            self::CMD_SAVE_TRANSLATIONS,
            self::CMD_ADD_TRANSLATION,
            self::CMD_DELETE_TRANSLATIONS,
            self::CMD_CONFIRM_REMOVE_LANGUAGES,
            self::CMD_SAVE_LANGUAGES,
            self::CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION,
            self::CMD_DEACTIVATE_CONTENT_MULTILANG,
            self::CMD_SET_FALLBACK
        ];

        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::CMD_LIST_TRANSLATIONS);
        if (in_array($cmd, $commands)) {
            $this->$cmd();
        }
    }

    public function listTranslations(bool $get_post_values = false, bool $add = false): void
    {
        $this->lng->loadLanguageModule(ilObject::_lookupType($this->obj->getId()));

        $add_langs_modal = $this->getAddLanguagesModal();
        if ($this->getTitleDescrOnlyMode() || $this->obj_trans->getContentActivated()) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('obj_add_languages'),
                    $add_langs_modal->getShowSignal()
                )
            );
        }

        if ($this->support_content_translation) {
            $content_translation_modal = $this->addContentTranslationToolbarActionAndRetrieveCorrespondingModal();
        }

        $table = new ilObjectTranslation2TableGUI(
            $this,
            self::CMD_LIST_TRANSLATIONS,
            !$this->hide_description,
            $this->obj_trans->getMasterLanguage(),
            $this->fallback_lang_mode,
            $this->obj_trans->getFallbackLanguage()
        );
        if ($get_post_values) {
            $table->setData($this->getTableValuesByRequest());
        } else {
            $table->setData($this->getTableValuesByObjects());
        }
        $page_content = $table->getHTML() . $this->ui_renderer->render($add_langs_modal);

        if (isset($content_translation_modal)) {
            $page_content .= $this->ui_renderer->render($content_translation_modal);
        }

        $this->tpl->setContent($page_content);
    }

    private function getAddLanguagesModal(): Modal
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('confirm'),
            $this->ui_factory->legacy($this->lng->txt('obj_select_master_lang')),
            [
                'langs' => $this->getMultiLangFormInput(true)
            ],
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE_LANGUAGES)
        );
    }

    private function addContentTranslationToolbarActionAndRetrieveCorrespondingModal(): ?Modal
    {
        $lang_var_postfix = '_multilang';
        $deactivation_modal_text_tag = 'obj_deactivate_multilang_conf';
        if ($this->getTitleDescrOnlyMode()) {
            $lang_var_postfix = '_content_lang';
            $deactivation_modal_text_tag = 'obj_deactivate_content_transl_conf';
        }

        if ($this->getTitleDescrOnlyMode() && !$this->obj_trans->getContentActivated()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('obj_multilang_title_descr_only'));
        }

        $activate_modal = $this->getActivateMultilingualityModal();
        if (!$this->obj_trans->getContentActivated()) {
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
            $this->ui_factory->legacy($this->lng->txt('obj_select_master_lang')),
            [
                'lang' => $this->getMultiLangFormInput()
            ],
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE_CONTENT_TRANSLATION_ACTIVATION)
        );
    }

    public function saveTranslations(): void
    {
        // default language set?
        if (!$this->post_wrapper->has('default') && $this->obj_trans->getMasterLanguage() === '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_default_language'));
            $this->listTranslations(true);
            return;
        }

        // all languages set?
        $languages = $this->post_wrapper->has('lang')
            ? $this->post_wrapper->retrieve(
                'lang',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : [];
        if (array_key_exists('', $languages)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_language_selected'));
            $this->listTranslations(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($languages)) < count($languages)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_multi_language_selected'));
            $this->listTranslations(true);
            return;
        }

        // save the stuff
        $this->obj_trans->setLanguages([]);

        $titles = $this->post_wrapper->has('title')
            ? $this->post_wrapper->retrieve(
                'title',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : [];
        $descriptions = $this->post_wrapper->has('desc')
            ? $this->post_wrapper->retrieve(
                'desc',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : [];

        $post_default = $this->post_wrapper->has('default')
            ? $this->post_wrapper->retrieve(
                'default',
                $this->refinery->kindlyTo()->int()
            )
            : null;

        $check = $this->post_wrapper->has('check')
            ? $this->post_wrapper->retrieve(
                'check',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];

        if ($this->obj_trans->getFallbackLanguage() !== '') {
            $obj_store_lang = $this->obj_trans->getFallbackLanguage();
        } else {
            $obj_store_lang = ($this->obj_trans->getMasterLanguage() != '')
                ? $this->obj_trans->getMasterLanguage()
                : $languages[$post_default];
        }

        foreach ($titles as $k => $v) {
            // update object data if default
            $is_default = ($post_default === $k);

            // ensure master language is set as default
            if ($this->obj_trans->getMasterLanguage() != '') {
                $is_default = ($this->obj_trans->getMasterLanguage() === $languages[$k]);
            }
            if ($languages[$k] === $obj_store_lang) {
                $this->obj->setTitle(ilUtil::stripSlashes($v));
                $this->obj->setDescription(ilUtil::stripSlashes($descriptions[$k] ?? ''));
            }

            $this->obj_trans->addLanguage(
                ilUtil::stripSlashes($languages[$k]),
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($descriptions[$k] ?? ''),
                $is_default
            );
        }
        $this->obj_trans->save();
        if (method_exists($this->obj, 'setObjectTranslation')) {
            $this->obj->setObjectTranslation($this->obj_trans);
        }
        $this->obj->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function getMultiLangFormInput(bool $add = false): Input
    {
        $options = ilMDLanguageItem::_getLanguages();

        if ($add) {
            $master_lang = $this->obj_trans->getMasterLanguage();
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

        $trafo = $this->refinery->custom()->transformation(
            fn($v) => in_array($v, ilMDLanguageItem::_getPossibleLanguageCodes()) ? $v : $this->lng->getDefaultLanguage()
        );

        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('obj_master_lang'),
            $options
        )->withAdditionalTransformation($trafo)
            ->withValue($this->user->getLanguage());
    }

    public function saveContentTranslationActivation(): void
    {
        $data = $this->getActivateMultilingualityModal()
            ->withRequest($this->request)
            ->getData();
        $this->obj_trans->setMasterLanguage($data['lang']);
        $this->obj_trans->addLanguage(
            $data['lang'],
            $this->obj->getTitle(),
            $this->obj->getDescription(),
            true
        );
        $this->obj_trans->setDefaultTitle($this->obj->getTitle());
        $this->obj_trans->setDefaultDescription($this->obj->getDescription());
        $this->obj_trans->save();

        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function deactivateContentMultiLang(): void
    {
        if (!$this->getTitleDescrOnlyMode()) {
            $this->obj_trans->setMasterLanguage('');
            $this->obj_trans->setLanguages([]);
            $this->obj_trans->save();
        }
        $this->obj_trans->deactivateContentTranslation();
        if ($this->getTitleDescrOnlyMode()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('obj_cont_transl_deactivated'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('obj_multilang_deactivated'), true);
        }

        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function saveLanguages(): void
    {
        $data = $this->getAddLanguagesModal()
            ->withRequest($this->request)
            ->getData();

        if ($data['langs'] === null || $data['langs'] === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
        }

        foreach ($data['langs'] as $lang) {
            $this->obj_trans->addLanguage($lang, '', '', false);
        }

        $this->obj_trans->save();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
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

        $cgui = new ilConfirmationGUI();
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

        foreach ($langs_to_be_deleted as $lang) {
            $this->obj_trans->removeLanguage($lang);
        }
        $this->obj_trans->save();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    private function retrieveTrafoToRemoveDefaultLang(): callable
    {
        $default_lang = $this->obj_trans->getDefaultLanguage();
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

    public function setFallback(): void
    {
        // default language set?
        $checkboxes = $this->post_wrapper->has('check')
            ? $this->post_wrapper->retrieve(
                'check',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            )
            : [];

        if ($checkboxes === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('obj_select_one_language'));
            $this->listTranslations(true);
            return;
        }
        $checked = key($checkboxes);

        $languages = $this->post_wrapper->has('lang')
            ? $this->post_wrapper->retrieve(
                'lang',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $fallback_lang = $languages[$checked];
        if ($fallback_lang !== $this->obj_trans->getFallbackLanguage()) {
            $this->obj_trans->setFallbackLanguage($fallback_lang);
        } else {
            $this->obj_trans->setFallbackLanguage('');
        }
        $this->obj_trans->save();
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }
}
