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

namespace ILIAS\User\Settings\NewAccountMail;

use ILIAS\User\RedirectOnMissingWrite;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;

/**
 * @ilCtrl_Calls ILIAS\User\Settings\NewAccountMail\SettingsGUI: ILIAS\User\Settings\NewAccountMail\UploadHandlerGUI
 */
class SettingsGUI
{
    use RedirectOnMissingWrite;

    private readonly UploadHandlerGUI $upload_handler_gui;
    private readonly Stakeholder $stakeholder;

    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccess $access,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly TemplateEngineFactoryInterface $mail_template_engine_factory,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly ServerRequestInterface $request,
        private readonly ResourceStorage $irss,
        private readonly Repository $account_mail_repo
    ) {
        $this->stakeholder = new Stakeholder();
        $this->upload_handler_gui = new UploadHandlerGUI(
            $this->irss,
            $this->stakeholder
        );
    }

    public function executeCommand(): void
    {
        $this->redirectOnMissingWrite($this->access, $this->ctrl, $this->tpl, $this->lng);
        if ($this->ctrl->getNextClass() === strtolower(UploadHandlerGUI::class)) {
            $this->ctrl->forwardCommand($this->upload_handler_gui);
            return;
        }
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->$cmd();
    }

    private function showCmd(?StandardForm $form = null): void
    {
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
        $form = $this->buildForm()->withRequest($this->request);
        $data = $form ->getData();
        if ($data === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showCmd($form);
            return;
        }

        foreach ($data[0] as $lang_code => $new_account_mail) {
            $old_account_mail = $this->account_mail_repo->getFor($lang_code);

            $new_attachment_rid = $new_account_mail['attachment'] === []
                ? null
                : $new_account_mail['attachment'][0];

            if ($old_account_mail->getAttachmentRid() !== null
                && $old_account_mail->getAttachmentRid() !== $new_attachment_rid
                && ($rid = $this->irss->manage()->find($old_account_mail->getAttachmentRid())) !== null) {
                $this->irss->manage()->remove(
                    $rid,
                    $this->stakeholder
                );
                $old_account_mail->deleteAttachmentTempFile();
            }

            $this->account_mail_repo->store(
                new MailImplementation(
                    $lang_code,
                    trim($new_account_mail['subject']),
                    trim($new_account_mail['body']),
                    trim($new_account_mail['salutation_none_specific']),
                    trim($new_account_mail['salutation_male']),
                    trim($new_account_mail['salutation_female']),
                    $new_attachment_rid
                )
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->showCmd();
    }

    private function buildForm(): StandardForm
    {
        $ff = $this->ui_factory->input()->field();
        $inputs = [];
        foreach ($this->lng->getInstalledLanguages() as $lang_code) {
            $account_mail = $this->account_mail_repo->getFor($lang_code);

            $title = $this->lng->txt('meta_l_' . $lang_code);
            if ($lang_code === $this->lng->getDefaultLanguage()) {
                $title .= ' (' . $this->lng->txt('default') . ')';
            }

            $inputs[$lang_code] = $ff->section(
                [
                    'subject' => $ff->text($this->lng->txt('subject'))
                        ->withAdditionalTransformation(
                            $this->buildConvertCurlyBracesTrafo()
                        )->withAdditionalTransformation(
                            $this->buildValidateMailBodyConstraint()
                        )->withValue($this->convertCurlyBracesForFormOutput($account_mail->getSubject())),
                    'salutation_none_specific' => $ff->text($this->lng->txt('mail_salutation_general'))
                        ->withValue($account_mail->getSalutationNoneSpecific()),
                    'salutation_female' => $ff->text($this->lng->txt('mail_salutation_female'))
                        ->withValue($account_mail->getSalutationFemale()),
                    'salutation_male' => $ff->text($this->lng->txt('mail_salutation_male'))
                        ->withValue($account_mail->getSalutationMale()),
                    'body' => $ff->textarea($this->lng->txt('message_content'))
                        ->withAdditionalTransformation(
                            $this->buildConvertCurlyBracesTrafo()
                        )->withAdditionalTransformation(
                            $this->buildValidateMailBodyConstraint()
                        )->withValue($account_mail->getBody()),
                    'attachment' => $ff->file($this->upload_handler_gui, $this->lng->txt('attachment'))
                        ->withValue(
                            $account_mail->getAttachmentRid() === null
                                ? []
                                : [$account_mail->getAttachmentRid()]
                        )
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
                    $this->mail_template_engine_factory->getBasicEngine()->render($v, []);
                    return true;
                } catch (\Exception) {
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
