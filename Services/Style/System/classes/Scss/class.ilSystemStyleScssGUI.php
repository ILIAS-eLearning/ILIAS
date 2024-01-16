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
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Factory as Refinery;

class ilSystemStyleScssGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSkinStyleContainer $style_container;
    protected ilSystemStyleScssSettings $scss_folder;
    protected ilSystemStyleMessageStack $message_stack;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected ServerRequestInterface $request;
    protected ilToolbarGUI $toolbar;
    protected Refinery $refinery;
    protected ilSystemStyleConfig $config;
    protected string $style_id;

    public function __construct(
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        Factory $ui_factory,
        Renderer $renderer,
        ServerRequestInterface $request,
        ilToolbarGUI $toolbar,
        Refinery $refinery,
        ilSkinFactory $factory,
        string $skin_id,
        string $style_id
    ) {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->request = $request;
        $this->toolbar = $toolbar;
        $this->refinery = $refinery;
        $this->style_id = $style_id;

        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);

        $this->style_container = $factory->skinStyleContainerFromId($skin_id, $this->message_stack);
        $this->scss_folder = new ilSystemStyleScssSettings($this->style_container->getScssSettingsPath($style_id));
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $this->addResetToolbar();
        $form = null;

        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'update':
                $form = $this->update();
                break;
            case 'reset':
                $this->reset();
                $form = $this->edit();
                break;
            case 'edit':
            case '':
                $form = $this->edit();
                break;
        }
        $components = $this->message_stack->getUIComponentsMessages($this->ui_factory);
        if ($form) {
            $components[] = $form;
        }

        $this->tpl->setContent($this->renderer->render($components));
    }

    protected function addResetToolbar(): void
    {
        $this->toolbar->addComponent($this->ui_factory->button()->standard(
            $this->lng->txt('reset_variables'),
            $this->ctrl->getLinkTarget($this, 'reset')
        ));
    }

    protected function reset(): void
    {
        $style = $this->style_container->getSkin()->getStyle($this->style_id);
        $this->scss_folder = $this->style_container->copySettingsFromDefault($style);
        try {
            $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('scss_folder_reset')));
            $this->style_container->compileScss($style->getId());
        } catch (ilSystemStyleException $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt($e->getMessage()),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }
    }

    protected function checkRequirements(): bool
    {
        $scss_path = $this->style_container->getScssFilePath($this->style_id);

        $pass = $this->checkScssInstallation();

        if (file_exists($scss_path)) {
            $scss_file_path = $this->style_container->getScssFilePath($this->style_id);
            $content = '';
            try {
                $content = file_get_contents($scss_file_path);
            } catch (Exception $e) {
                $this->message_stack->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('can_not_read_scss_folder') . ' ' . $scss_file_path,
                        ilSystemStyleMessage::TYPE_ERROR
                    )
                );
                $pass = false;
            }
            if ($content) {
                $reg_exp = '/' . preg_quote($this->style_container->getScssSettingsFolderName(), '/') . '/';

                if (!preg_match($reg_exp, $content)) {
                    $this->message_stack->addMessage(
                        new ilSystemStyleMessage(
                            $this->lng->txt('scss_variables_file_not_included') . ' ' . $this->style_container->getScssSettingsFolderName()
                            . ' ' . $this->lng->txt('in_main_scss_folder') . ' ' . $scss_path,
                            ilSystemStyleMessage::TYPE_ERROR
                        )
                    );
                    $pass = false;
                }
            }
        } else {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('scss_folder_does_not_exist') . $scss_path,
                    ilSystemStyleMessage::TYPE_ERROR
                )
            );
            $pass = false;
        }
        return $pass;
    }

    protected function checkScssInstallation(): bool
    {
        $pass = true;

        if (!PATH_TO_SCSS) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('no_scss_path_set'), ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        } elseif (!shell_exec(PATH_TO_SCSS . " -h")) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('invalid_scss_path'), ilSystemStyleMessage::TYPE_ERROR)
            );
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('provided_scss_path') . ' ' . PATH_TO_SCSS,
                    ilSystemStyleMessage::TYPE_ERROR
                )
            );
            $pass = false;
        }

        if (!$pass && shell_exec('which sass')) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('scss_scss_installation_detected') . shell_exec('which sass'),
                    ilSystemStyleMessage::TYPE_ERROR
                )
            );
        }

        return $pass;
    }

    protected function edit(): Form
    {
        $modify = true;

        if (!$this->checkRequirements()) {
            $this->message_stack->prependMessage(
                new ilSystemStyleMessage($this->lng->txt('scss_can_not_be_modified'), ilSystemStyleMessage::TYPE_ERROR)
            );
            $modify = false;
        }

        return $this->initSystemStyleScssForm($modify);
    }

    public function initSystemStyleScssForm(bool $modify = true): Form
    {
        $f = $this->ui_factory->input();
        $category_sections = [];
        foreach ($this->scss_folder->getCategories() as $category) {
            $variables_inptus = [];
            foreach ($this->scss_folder->getVariablesPerCategory($category->getName()) as $variable) {
                $info = $this->scss_folder->getRefAndCommentAsString($variable->getName(), $this->lng->txt('usages'));
                $save_closure = function ($v) use ($variable) {
                    $variable->setValue($v);
                };
                $variables_inptus[] = $f->field()->text($variable->getName(), $info)
                                        //->withRequired(true)
                                        ->withDisabled(!$modify)
                                        ->withValue($variable->getValue())
                                        ->withAdditionalTransformation($this->refinery->custom()->transformation($save_closure));
            }

            if(count($variables_inptus) > 0) {
                $category_sections[] = $f->field()->section(
                    $variables_inptus,
                    $category->getName(),
                    $category->getComment()
                );
            }
        }

        $form_section = $f->field()->section(
            $category_sections,
            $this->lng->txt('adapt_scss'),
            $this->lng->txt('adapt_scss_description')
        );

        return $f->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'update'),
            [$form_section]
        )->withSubmitLabel($this->lng->txt('update_variables'));
    }

    public function update(): Form
    {
        $form = $this->initSystemStyleScssForm();
        $form = $form->withRequest($this->request);

        if (!$form->getData()) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('scss_variables_empty_might_have_changed'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            return $form;
        }

        try {
            $this->scss_folder->write();
            $this->style_container->compileScss($this->style_id);
            $skin = $this->style_container->getSkin();
            $skin->getVersionStep($skin->getVersion());
            $this->style_container->updateSkin($skin);
            $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('scss_folder_updated')));
        } catch (Exception $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt($e->getMessage()),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        return $form;
    }
}
