<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

use ILIAS\Glossary\InternalDomainService;
use ILIAS\Glossary\InternalGUIService;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilTermDefinitionBulkCreationGUI
{
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;
    protected ilObjGlossary $glossary;
    protected \ILIAS\Glossary\Term\TermManager $term_manager;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        ilObjGlossary $glossary
    ) {
        $this->glossary = $glossary;
        $this->domain = $domain;
        $this->gui = $gui;
        $lng = $domain->lng();
        $lng->loadLanguageModule("glo");
        $this->term_manager = $domain
            ->term($glossary);
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("showCreationForm");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "showCreationForm",
                    "showConfirmationScreen",
                    "createTermDefinitionPairs"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function modifyToolbar(ilToolbarGUI $toolbar): void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $components = $this
            ->gui
            ->modal($lng->txt("glo_bulk_creation"))
            ->getAsyncTriggerButtonComponents(
                $lng->txt("glo_bulk_creation"),
                $ctrl->getLinkTarget($this, "showCreationForm", "", true),
                false
            );
        foreach ($components as $c) {
            $toolbar->addComponent($c);
        }
    }

    protected function showCreationForm(): void
    {
        $lng = $this->domain->lng();
        $this->gui
            ->modal($lng->txt("glo_bulk_creation"))
            ->form($this->getCreationForm())
            ->send();
    }

    protected function getCreationForm(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $form = $this
            ->gui
            ->form(self::class, "showConfirmationScreen")
            ->asyncModal()
            //->section("creation", $lng->txt("glo_bulk_data"))
            ->textarea(
                "bulk_data",
                $lng->txt("glo_term_definition_pairs"),
                $lng->txt("glo_term_definition_pairs_info"),
            )
            ->required();

        $session_lang = $this->term_manager->getSessionLang();
        if ($session_lang != "") {
            $s_lang = $session_lang;
        } else {
            $s_lang = $user->getLanguage();
        }
        $form->select(
            "term_language",
            $lng->txt("language"),
            ilMDLanguageItem::_getLanguages(),
            "",
            $s_lang
        )
             ->required();

        return $form;
    }

    protected function showConfirmationScreen(): void
    {
        $form = $this->getCreationForm();
        $lng = $this->domain->lng();
        if (!$form->isValid()) {
            $this->gui->modal($lng->txt("glo_bulk_creation"))
                      ->form($form)
                      ->send();
        }

        $language = $form->getData("term_language");
        $this->gui->modal($lng->txt("glo_bulk_creation"))
                  ->legacy($this->renderConfirmation(
                      $form->getData("bulk_data"),
                      $language
                  ))
                  ->send();
    }

    protected function renderConfirmation(string $data, string $language): string
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $button = $f->button()->standard(
            $lng->txt("glo_create_term_definition_pairs"),
            "#"
        )->withAdditionalOnLoadCode(static function (string $id) {
            return <<<EOT
            const glo_bulk_button = document.getElementById("$id");
            glo_bulk_button.addEventListener("click", (event) => {
                glo_bulk_button.closest(".modal").querySelector("form").submit();
            });
EOT;
        });

        $mbox = $f->messageBox()->confirmation(
            $lng->txt("glo_bulk_confirmation")
        )->withButtons([$button]);

        $ctrl->setParameter($this, "term_language", $language);
        $table = new ilTermDefinitionBulkCreationTableGUI(
            $this,
            "renderConfirmation",
            $data,
            $this->glossary
        );

        return $r->render($mbox) .
            $table->getHTML();
    }

    protected function createTermDefinitionPairs(): void
    {
        $main_tpl = $this->gui->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $request = $this->gui->editing()->request();

        $data = $request->getBulkCreationData();
        $language = $request->getTermLanguage();
        $this->term_manager->createTermDefinitionPairsFromBulkInputString($data, $language);
        $main_tpl->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        $ctrl->returnToParent($this);
    }
}
