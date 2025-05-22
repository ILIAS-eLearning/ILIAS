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

namespace ILIAS\User\Settings\System;

use ILIAS\User\RedirectOnMissingWrite;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @ilCtrl_Calls ILIAS\User\Settings\System\NewAccountMailSettingsGUI: ILIAS\User\Settings\System\MailAttachmentUploadHandlerGUI
 */
class NewAccountMailSettingsGUI
{
    use RedirectOnMissingWrite;

    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccess $access,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly \ilMustacheFactory $mail_mustache_factory,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly ServerRequestInterface $request
    ) {
    }

    public function executeCommand(): void
    {
        if ($this->ctrl->getNextClass() === strtolower(MailAttachmentUploadHandlerGUI::class)) {
            $this->ctrl->forwardCommand(
                new MailAttachmentUploadHandlerGUI()
            );
            return;
        }
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->$cmd();
    }

    private function showCmd(?StandardForm $form = null): void
    {
        $this->redirectOnMissingWrite($this->access, $this->ctrl, $this->tpl, $this->lng);

        $content = [
            $form ?? $this->buildForm(),
            $this->ui_factory->panel()->standard(
                $this->lng->txt('mail_nacc_use_placeholder'),
                $this->ui_factory->listing()->characteristicValue()->text([
                    '&lbrace;&lbrace;MAIL_SALUTATION&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_salutation'),
                    '&lbrace;&lbrace;FIRST_NAME&rbrace;&rbrace;' => $this->lng->txt('firstname'),
                    '&lbrace;&lbrace;LAST_NAME&rbrace;&rbrace;' => $this->lng->txt('lastname'),
                    '&lbrace;&lbrace;EMAIL&rbrace;&rbrace;' => $this->lng->txt('email'),
                    '&lbrace;&lbrace;LOGIN&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_login'),
                    '&lbrace;&lbrace;PASSWORD&rbrace;&rbrace;' => $this->lng->txt('password'),
                    '&lbrace;&lbrace;#IF_PASSWORD&rbrace;&rbrace;...&lbrace;&lbrace;/IF_PASSWORD&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_pw_block'),
                    '&lbrace;&lbrace;#IF_NO_PASSWORD&rbrace;&rbrace;...&lbrace;&lbrace;/IF_NO_PASSWORD&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_no_pw_block'),
                    '&lbrace;&lbrace;ADMIN_MAIL&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_admin_mail'),
                    '&lbrace;&lbrace;ILIAS_URL&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_ilias_url'),
                    '&lbrace;&lbrace;INSTALLATION_NAME&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_installation_name'),
                    '&lbrace;&lbrace;TARGET&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_target'),
                    '&lbrace;&lbrace;TARGET_TITLE&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_target_title'),
                    '&lbrace;&lbrace;TARGET_TYPE&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_target_type'),
                    '&lbrace;&lbrace;#IF_TARGET&rbrace;&rbrace;...&lbrace;&lbrace;/IF_TARGET&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_target_block'),
                    '&lbrace;&lbrace;#IF_TIMELIMIT&rbrace;&rbrace;...&lbrace;&lbrace;/IF_TIMELIMIT&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_if_timelimit'),
                    '&lbrace;&lbrace;TIMELIMIT&rbrace;&rbrace;' => $this->lng->txt('mail_nacc_timelimit')
                ])
            )
        ];

        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    private function saveCmd(): void
    {
        $this->redirectOnMissingWrite($this->access, $this->ctrl, $this->tpl, $this->lng);
        $form = $this->buildForm()->withRequest($this->request);
        $data = $form ->getData();
        if ($data === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showCmd($form);
            return;
        }

        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            \ilObjUserFolder::_writeNewAccountMail(
                $lang_key,
                $data[0][$lang_key]['subject'],
                $data[0][$lang_key]['salutation_none_specific'],
                $data[0][$lang_key]['salutation_female'],
                $data[0][$lang_key]['salutation_male'],
                $data[0][$lang_key]['body']
            );

            /*if ($_FILES['att_' . $lang_key]['tmp_name']) {
                \ilObjUserFolder::_updateAccountMailAttachment(
                    $lang_key,
                    $_FILES['att_' . $lang_key]['tmp_name'],
                    $_FILES['att_' . $lang_key]['name']
                );
            }

            if ($this->user_request->getMailAttDelete($lang_key)) {
                \ilObjUserFolder::_deleteAccountMailAttachment($lang_key);
            } */
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->showCmd();
    }

    private function buildForm(): StandardForm
    {
        $ff = $this->ui_factory->input()->field();
        $inputs = [];
        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            $account_mail = \ilObjUserFolder::_lookupNewAccountMail($lang_key);

            $title = $this->lng->txt('meta_l_' . $lang_key);
            if ($lang_key === $this->lng->getDefaultLanguage()) {
                $title .= ' (' . $this->lng->txt('default') . ')';
            }

            $inputs[$lang_key] = $ff->section(
                [
                    'subject' => $ff->text($this->lng->txt('subject'))
                        ->withAdditionalTransformation(
                            $this->buildValidateMailBodyConstraint()
                        )->withAdditionalTransformation(
                            $this->buildValidateMailBodyConstraint()
                        )->withValue(
                            $this->convertCurlyBracesForFormOutput($account_mail['subject'] ?? '')
                        ),
                    'salutation_none_specific' => $ff->text($this->lng->txt('mail_salutation_general'))
                        ->withValue($account_mail['sal_g'] ?? ''),
                    'salutation_female' => $ff->text($this->lng->txt('mail_salutation_female'))
                        ->withValue($account_mail['sal_f'] ?? ''),
                    'salutation_male' => $ff->text($this->lng->txt('mail_salutation_male'))
                        ->withValue($account_mail['sal_m'] ?? ''),
                    'body' => $ff->textarea($this->lng->txt('message_content'))
                        ->withAdditionalTransformation(
                            $this->buildValidateMailBodyConstraint()
                        )->withAdditionalTransformation(
                            $this->buildValidateMailBodyConstraint()
                        )->withValue(
                            $this->convertCurlyBracesForFormOutput($account_mail['body'] ?? '')
                        ),
                    'attachment' => $ff->file(
                        new MailAttachmentUploadHandlerGUI(),
                        $this->lng->txt('attachment')
                    )->withMaxFiles(10)
                ],
                $title
            );
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, 'save'),
            [$ff->section(
                $inputs,
                $this->lng->txt('user_new_account_mail'),
                $this->lng->txt('user_new_account_mail_desc')
            )]
        );
    }

    private function buildValidateMailBodyConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            function ($v): bool {
                try {
                    $this->mail_mustache_factory->getBasicEngine()->render($v, []);
                    return true;
                } catch (Exception) {
                    return false;
                }
            },
            $this->lng->txt('mail_template_invalid_tpl_syntax')
        );
    }

    private function buildConvertCurlyBracesTrafo(): Transformation
    {
        return $this->refinery->custom()->transformation(
            static fn(string $v) => str_replace(['&#123;&#123;', '&#125;&#125;'], ['{{', '}}'], $v)
        );
    }

    private function convertCurlyBracesForFormOutput(string $value): string
    {
        return str_replace(['{{', '}}'], ['&#123;&#123;', '&#125;&#125;'], $value);
    }
}
