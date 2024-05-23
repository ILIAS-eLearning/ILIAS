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

class ilMailPreviewGUI
{
    protected ilMailTemplate $template_id;
    protected ilMailTemplatePlaceholderResolver $placeholder_resolver;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(ilMailTemplate $template, ilPreviewFactory $preview_factory)
    {
        global $DIC;
        $this->current_user = $DIC->user();
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();
        $curr_usr_lang = $this->current_user->getCurrentLanguage();
        if (isset($DIC->http()->request()->getQueryParams()['mtlanguage'])) {
            $language = $DIC->http()->request()->getQueryParams()['mtlanguage'];
        }

        $this->template = $template;
        $this->not_translated = false;
        $this->preview_factory = $preview_factory;
        $this->placeholder_resolver = $DIC["mail.template.placeholder.resolver"];
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * Render mail preview
     *
     * @return string
     */
    public function getHTML()
    {
        if ($this->not_translated) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("mail_template_not_translated"), true);
        }

        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $form->setTableWidth('100%');
        $form->setTitle($this->lng->txt("preview_for") . " " . $this->template->getTitle());

        $from = new ilCustomInputGUI($this->lng->txt('from'));
        $from->setHtml($this->current_user->getFullName());
        $form->addItem($from);

        $to = new ilCustomInputGUI($this->lng->txt('recipient'));
        $to->setHtml(ilUtil::htmlencodePlainString($this->lng->txt('user'), false));
        $form->addItem($to);

        $subject = new ilCustomInputGUI($this->lng->txt('subject'));
        $subject->setHtml(ilUtil::htmlencodePlainString($this->populatePlaceholder($this->template->getSubject()), true));
        $form->addItem($subject);

        $message = new ilCustomInputGUI($this->lng->txt('message'));
        $message->setHtml(
            $this->refinery->string()->markdown()->toHTML()->transform(
                $this->populatePlaceholder($this->template->getMessage())
            )
        );
        $form->addItem($message);

        return $form->getHtml();
    }

    protected function populatePlaceholder(string $message): string
    {
        return $this->placeholder_resolver->resolveForPreview($message);
    }
}
