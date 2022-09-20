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

class ilSystemStyleLessGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSkinStyleContainer $style_container;
    protected ilSystemStyleLessFile $less_file;
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
        $this->less_file = new ilSystemStyleLessFile($this->style_container->getLessVariablesFilePath($style_id));
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
        $this->less_file = $this->style_container->copyVariablesFromDefault($style);
        try {
            $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('less_file_reset')));
            $this->style_container->compileLess($style->getId());
        } catch (ilSystemStyleException $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt($e->getMessage()),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }
    }

    protected function checkRequirements(): bool
    {
        $less_path = $this->style_container->getLessFilePath($this->style_id);

        $pass = $this->checkLessInstallation();

        if (file_exists($less_path)) {
            $less_variables_name = $this->style_container->getLessVariablesName($this->style_id);
            $content = '';
            try {
                $content = file_get_contents($less_path);
            } catch (Exception $e) {
                $this->message_stack->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('can_not_read_less_file') . ' ' . $less_path,
                        ilSystemStyleMessage::TYPE_ERROR
                    )
                );
                $pass = false;
            }
            if ($content) {
                $reg_exp = '/' . preg_quote($less_variables_name, '/') . '/';

                if (!preg_match($reg_exp, $content)) {
                    $this->message_stack->addMessage(
                        new ilSystemStyleMessage(
                            $this->lng->txt('less_variables_file_not_included') . ' ' . $less_variables_name
                            . ' ' . $this->lng->txt('in_main_less_file') . ' ' . $less_path,
                            ilSystemStyleMessage::TYPE_ERROR
                        )
                    );
                    $pass = false;
                }
            }
        } else {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('less_file_does_not_exist') . $less_path,
                    ilSystemStyleMessage::TYPE_ERROR
                )
            );
            $pass = false;
        }
        return $pass;
    }

    protected function checkLessInstallation(): bool
    {
        $pass = true;

        if (!PATH_TO_LESSC) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('no_less_path_set'), ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        } elseif (!shell_exec(PATH_TO_LESSC)) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('invalid_less_path'), ilSystemStyleMessage::TYPE_ERROR)
            );
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('provided_less_path') . ' ' . PATH_TO_LESSC,
                    ilSystemStyleMessage::TYPE_ERROR
                )
            );
            $pass = false;
        }

        if (!$pass && shell_exec('which lessc')) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('less_less_installation_detected') . shell_exec('which lessc'),
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
                new ilSystemStyleMessage($this->lng->txt('less_can_not_be_modified'), ilSystemStyleMessage::TYPE_ERROR)
            );
            $modify = false;
        }

        return $this->initSystemStyleLessForm($modify);
    }

    public function initSystemStyleLessForm(bool $modify = true): Form
    {
        $f = $this->ui_factory->input();
        $category_section = [];
        foreach ($this->less_file->getCategories() as $category) {
            $variables_inptus = [];
            foreach ($this->less_file->getVariablesPerCategory($category->getName()) as $variable) {
                $info = $this->less_file->getRefAndCommentAsString($variable->getName(), $this->lng->txt('usages'));
                $save_closure = function ($v) use ($variable) {
                    $variable->setValue($v);
                };
                $variables_inptus[] = $f->field()->text($variable->getName(), $info)
                                        //->withRequired(true)
                                        ->withDisabled(!$modify)
                                        ->withValue($variable->getValue())
                                        ->withAdditionalTransformation($this->refinery->custom()->transformation($save_closure));
            }

            $category_section[] = $f->field()->section(
                $variables_inptus,
                $category->getName(),
                $category->getComment()
            );
        }

        $form_section = $f->field()->section(
            $category_section,
            $this->lng->txt('adapt_less'),
            $this->lng->txt('adapt_less_description')
        );

        return $f->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'update'),
            [$form_section]
        )->withSubmitCaption($this->lng->txt('update_variables'));
    }

    public function update(): Form
    {
        $form = $this->initSystemStyleLessForm();
        $form = $form->withRequest($this->request);

        if (!$form->getData()) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('less_variables_empty_might_have_changed'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            return $form;
        }

        try {
            $this->less_file->write();
            $this->style_container->compileLess($this->style_id);
            $skin = $this->style_container->getSkin();
            $skin->getVersionStep($skin->getVersion());
            $this->style_container->updateSkin($skin);
            $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('less_file_updated')));
        } catch (Exception $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt($e->getMessage()),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        return $form;
    }
}
