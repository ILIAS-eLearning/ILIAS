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

namespace ILIAS\User\Profile\Prompt;

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Factory as Refinery;

use Psr\Http\Message\ServerRequestInterface;

class SettingsGUI
{
    private Settings $prompt_settings;

    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private \ilGlobalTemplateInterface $tpl,
        private readonly ServerRequestInterface $request,
        private readonly Refinery $refinery,
        private readonly Repository $prompt_repository
    ) {
        $this->prompt_settings = $this->prompt_repository->getSettings();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["show", "save"])) {
                    $this->$cmd();
                }
        }
    }

    public function show(): void
    {
        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->buildForm()
            )
        );
    }

    public function save(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setContent(
                $this->ui_renderer->render($form)
            );
            return;
        }

        $this->prompt_settings = $this->prompt_settings->withFormData($data);
        $this->prompt_repository->saveSettings($this->prompt_settings);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'));
        $this->show();
    }

    private function buildForm(): StandardForm
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, 'save'),
            $this->prompt_settings->toForm($this->ui_factory, $this->lng, $this->refinery)
        );
    }
}
