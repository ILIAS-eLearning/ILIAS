<?php

declare(strict_types=1);

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

/**
 * GUI class for object translation handling.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslationGUI
{
    public const CMD_LIST_TRANSLATIONS = "listTranslations";
    public const CMD_SAVE_TRANSLATIONS = "saveTranslations";
    public const CMD_ADD_TRANSLATION = "addTranslation";
    public const CMD_DELETE_TRANSLATIONS = "deleteTranslations";
    public const CMD_ADD_LANGUAGES = "addLanguages";
    public const CMD_SAVE_LANGUAGES = "saveLanguages";
    public const CMD_CONFIRM_REMOVE_LANGUAGES = "confirmRemoveLanguages";
    public const CMD_REMOVE_LANGUAGES = "removeLanguages";
    public const CMD_SET_FALLBACK = "setFallback";
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $post_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected ilObjectGUI $obj_gui;
    protected ilObject $obj;
    protected ilObjectTranslation $obj_trans;

    protected bool $title_descr_only = true;
    protected bool $fallback_lang_mode = true;

    public function __construct($obj_gui)
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->refinery = $DIC->refinery();


        $this->obj_gui = $obj_gui;
        $this->obj = $obj_gui->getObject();

        $this->obj_trans = ilObjectTranslation::getInstance($this->obj->getId());
    }

    private function getTableValuesByObjects(): array
    {
        $data = [];
        foreach ($this->obj_trans->getLanguages() as $k => $v) {
            $data[$k]["default"] = (int) $v->isDefault();
            $data[$k]["title"] = $v->getTitle();
            $data[$k]["desc"] = $v->getDescription();
            $data[$k]["lang"] = $v->getLanguageCode();
        }
        return $data;
    }

    private function getTableValuesByRequest(): array
    {
        $vals = [];

        $titles = $this->post_wrapper->has("title")
            ? $this->post_wrapper->retrieve(
                "title",
                $this->refinery->to()->listOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $descriptions = $this->post_wrapper->has("desc")
            ? $this->post_wrapper->retrieve(
                "desc",
                $this->refinery->to()->listOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $languages = $this->post_wrapper->has("lang")
            ? $this->post_wrapper->retrieve(
                "lang",
                $this->refinery->to()->listOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $default = $this->post_wrapper->has("default")
            ? $this->post_wrapper->retrieve(
                "default",
                $this->refinery->kindlyTo()->int()
            )
            : '';

        foreach ($titles as $k => $v) {
            $vals[] = [
                "title" => $v,
                "desc" => $descriptions[$k],
                "lang" => $languages[$k],
                "default" => ($default == $k)
            ];
        }
        return $vals;
    }

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
            "activateContentMultilinguality",
            self::CMD_CONFIRM_REMOVE_LANGUAGES,
            self::CMD_REMOVE_LANGUAGES,
            "confirmDeactivateContentMultiLang",
            self::CMD_SAVE_LANGUAGES,
            "saveContentTranslationActivation",
            "deactivateContentMultiLang",
            self::CMD_ADD_LANGUAGES,
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

        if ($this->getTitleDescrOnlyMode() || $this->obj_trans->getContentActivated()) {
            $this->toolbar->addButton(
                $this->lng->txt("obj_add_languages"),
                $this->ctrl->getLinkTarget($this, self::CMD_ADD_LANGUAGES)
            );
        }

        if ($this->getTitleDescrOnlyMode()) {
            if (!$this->obj_trans->getContentActivated()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("obj_multilang_title_descr_only"));
                $this->toolbar->addButton(
                    $this->lng->txt("obj_activate_content_lang"),
                    $this->ctrl->getLinkTarget($this, "activateContentMultilinguality")
                );
            } else {
                $this->toolbar->addButton(
                    $this->lng->txt("obj_deactivate_content_lang"),
                    $this->ctrl->getLinkTarget($this, "confirmDeactivateContentMultiLang")
                );
            }
        } else {
            if ($this->obj_trans->getContentActivated()) {
                $this->toolbar->addButton(
                    $this->lng->txt("obj_deactivate_multilang"),
                    $this->ctrl->getLinkTarget($this, "confirmDeactivateContentMultiLang")
                );
            } else {
                $this->toolbar->addButton(
                    $this->lng->txt("obj_activate_multilang"),
                    $this->ctrl->getLinkTarget($this, "activateContentMultilinguality")
                );
                return;
            }
        }

        $table = new ilObjectTranslation2TableGUI(
            $this,
            self::CMD_LIST_TRANSLATIONS,
            true,
            "Translation",
            $this->obj_trans->getMasterLanguage(),
            $this->fallback_lang_mode,
            $this->obj_trans->getFallbackLanguage()
        );
        if ($get_post_values) {
            $table->setData($this->getTableValuesByRequest());
        } else {
            $table->setData($this->getTableValuesByObjects());
        }
        $this->tpl->setContent($table->getHTML());
    }

    public function saveTranslations(bool $delete_checked = false): void
    {
        // default language set?
        if (!$this->post_wrapper->has("default") && $this->obj_trans->getMasterLanguage() === "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
            $this->listTranslations(true);
            return;
        }

        // all languages set?
        $languages = $this->post_wrapper->has("lang")
            ? $this->post_wrapper->retrieve(
                "lang",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : [];
        if (array_key_exists("", $languages)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($languages)) < count($languages)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_multi_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // save the stuff
        $this->obj_trans->setLanguages([]);

        $titles = $this->post_wrapper->has("title")
            ? $this->post_wrapper->retrieve(
                "title",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : [];
        $descriptions = $this->post_wrapper->has("desc")
            ? $this->post_wrapper->retrieve(
                "desc",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : [];

        $post_default = $this->post_wrapper->has("default")
            ? $this->post_wrapper->retrieve(
                "default",
                $this->refinery->kindlyTo()->int()
            )
            : null;

        $check = $this->post_wrapper->has('check')
            ? $this->post_wrapper->retrieve(
                "check",
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];

        if ($this->obj_trans->getFallbackLanguage() !== "") {
            $obj_store_lang = $this->obj_trans->getFallbackLanguage();
        } else {
            $obj_store_lang = ($this->obj_trans->getMasterLanguage() != "")
                ? $this->obj_trans->getMasterLanguage()
                : $languages[$post_default];
        }

        foreach ($titles as $k => $v) {
            if ($delete_checked && isset($check[$k])) {
                continue;
            }
            // update object data if default
            $is_default = ($post_default === $k);

            // ensure master language is set as default
            if ($this->obj_trans->getMasterLanguage() != "") {
                $is_default = ($this->obj_trans->getMasterLanguage() === $languages[$k]);
            }
            if ($languages[$k] === $obj_store_lang) {
                $this->obj->setTitle(ilUtil::stripSlashes($v));
                $this->obj->setDescription(ilUtil::stripSlashes($descriptions[$k]));
            }

            $this->obj_trans->addLanguage(
                ilUtil::stripSlashes($languages[$k]),
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($descriptions[$k]),
                $is_default
            );
        }
        $this->obj_trans->save();
        if (method_exists($this->obj, "setObjectTranslation")) {
            $this->obj->setObjectTranslation($this->obj_trans);
        }
        $this->obj->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function deleteTranslations(): void
    {
        $titles = $this->post_wrapper->has('title')
            ? $this->post_wrapper->retrieve(
                "title",
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];
        $check = $this->post_wrapper->has('check')
            ? $this->post_wrapper->retrieve(
                "check",
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];

        foreach ($titles as $k => $v) {
            if (isset($check[$k])) {
                // default translation cannot be deleted
                if (
                    !$this->post_wrapper->has("default") ||
                    $k != $this->post_wrapper->retrieve("default", $this->refinery->kindlyTo()->string())
                ) {
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
                    $this->listTranslations();
                    return;
                }
            }
        }
        $this->saveTranslations(true);
    }

    /**
     * Activate multi language (-> master language selection)
     */
    public function activateContentMultilinguality(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("obj_select_master_lang"));
        $form = $this->getMultiLangForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function getMultiLangForm(bool $add = false): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        // master language
        if (!$add) {
            $options = ilMDLanguageItem::_getLanguages();
            $si = new ilSelectInputGUI($this->lng->txt("obj_master_lang"), "master_lang");
            $si->setOptions($options);
            $si->setValue($this->user->getLanguage());
            $form->addItem($si);
        }

        // additional languages
        if ($add) {
            $options = ilMDLanguageItem::_getLanguages();
            $options = ["" => $this->lng->txt("please_select")] + $options;
            $si = new ilSelectInputGUI($this->lng->txt("obj_additional_langs"), "additional_langs");
            $si->setOptions($options);
            $si->setMulti(true);
            $form->addItem($si);
        }

        if ($add) {
            $form->setTitle($this->lng->txt("obj_add_languages"));
            $form->addCommandButton(self::CMD_SAVE_LANGUAGES, $this->lng->txt("save"));
        } else {
            if ($this->getTitleDescrOnlyMode()) {
                $form->setTitle($this->lng->txt("obj_activate_content_lang"));
            } else {
                $form->setTitle($this->lng->txt("obj_activate_multilang"));
            }
            $form->addCommandButton("saveContentTranslationActivation", $this->lng->txt("save"));
        }
        $form->addCommandButton(self::CMD_LIST_TRANSLATIONS, $this->lng->txt("cancel"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public function saveContentTranslationActivation(): void
    {
        $form = $this->getMultiLangForm();
        if ($form->checkInput()) {
            $ml = $form->getInput("master_lang");
            $this->obj_trans->setMasterLanguage($ml);
            $this->obj_trans->addLanguage(
                $ml,
                $this->obj->getTitle(),
                $this->obj->getDescription(),
                true
            );
            $this->obj_trans->setDefaultTitle($this->obj->getTitle());
            $this->obj_trans->setDefaultDescription($this->obj->getDescription());
            $this->obj_trans->save();
        }

        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function confirmDeactivateContentMultiLang(): void
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        if ($this->getTitleDescrOnlyMode()) {
            $cgui->setHeaderText($this->lng->txt("obj_deactivate_content_transl_conf"));
        } else {
            $cgui->setHeaderText($this->lng->txt("obj_deactivate_multilang_conf"));
        }

        $cgui->setCancel($this->lng->txt("cancel"), self::CMD_LIST_TRANSLATIONS);
        $cgui->setConfirm($this->lng->txt("confirm"), "deactivateContentMultiLang");
        $this->tpl->setContent($cgui->getHTML());
    }

    public function deactivateContentMultiLang(): void
    {
        if (!$this->getTitleDescrOnlyMode()) {
            $this->obj_trans->setMasterLanguage("");
            $this->obj_trans->setLanguages([]);
            $this->obj_trans->save();
        }
        $this->obj_trans->deactivateContentTranslation();
        if ($this->getTitleDescrOnlyMode()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("obj_cont_transl_deactivated"), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("obj_multilang_deactivated"), true);
        }

        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }

    public function addLanguages(): void
    {
        $form = $this->getMultiLangForm(true);
        $this->tpl->setContent($form->getHTML());
    }

    public function saveLanguages(): void
    {
        $form = $this->getMultiLangForm(true);
        if ($form->checkInput()) {
            $ad = $form->getInput("additional_langs");
            if (is_array($ad)) {
                $ml = $this->obj_trans->getMasterLanguage();
                foreach ($ad as $l) {
                    if ($l != $ml && $l != "") {
                        $this->obj_trans->addLanguage($l, "", "", false);
                    }
                }
            }
            $this->obj_trans->save();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function confirmRemoveLanguages(): void
    {
        $this->lng->loadLanguageModule("meta");

        $languages = $this->post_wrapper->has("lang")
            ? $this->post_wrapper->retrieve(
                "lang",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            )
            : null;

        if (!is_array($languages) || count($languages) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("obj_conf_delete_lang"));
            $cgui->setCancel($this->lng->txt("cancel"), self::CMD_LIST_TRANSLATIONS);
            $cgui->setConfirm($this->lng->txt("remove"), self::CMD_REMOVE_LANGUAGES);

            foreach ($languages as $i) {
                $cgui->addItem("lang[]", $i, $this->lng->txt("meta_l_" . $i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    public function setFallback(): void
    {
        // default language set?
        $checkboxes = $this->post_wrapper->has("check")
            ? $this->post_wrapper->retrieve(
                "check",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            )
            : [];

        if ($checkboxes === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("obj_select_one_language"));
            $this->listTranslations(true);
            return;
        }
        $checked = key($checkboxes);

        $languages = $this->post_wrapper->has("lang")
            ? $this->post_wrapper->retrieve(
                "lang",
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            )
            : [];

        $fallback_lang = $languages[$checked];
        if ($fallback_lang !== $this->obj_trans->getFallbackLanguage()) {
            $this->obj_trans->setFallbackLanguage($fallback_lang);
        } else {
            $this->obj_trans->setFallbackLanguage("");
        }
        $this->obj_trans->save();
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, self::CMD_LIST_TRANSLATIONS);
    }
}
