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

namespace ILIAS\Questions\Administration;

use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;

class ConfigurationGUI
{
    private const string CMD_DEFAULT = 'view';
    private const string CMD_SAVE = 'save';

    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly HTTP $http,
        private readonly Language $lng,
        private readonly Refinery $refinery,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ConfigurationRepository $repository
    ) {
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT) . 'Cmd';
        $this->$cmd();
    }

    private function viewCmd(
        ?StandardForm $form = null
    ): void {
        $this->tpl->setContent(
            $this->ui_renderer->render(
                $form ?? $this->buildSettingsForm()
            )
        );
    }

    private function saveCmd(): void
    {
        $form = $this->buildSettingsForm()->withRequest($this->http->request());
        $data = $form->getData();
        if ($data === null) {
            $this->viewCmd($form);
        }

        $this->repository->persistCreateMode(
            $data['default_user_settings']['create_mode']
        );

        $this->ctrl->redirectByClass(
            [
                \ilAdministrationGUI::class,
                \ilObjQuestionsGUI::class,
                self::class
            ]
        );
    }

    private function buildSettingsForm(): StandardForm
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTargetByClass(
                [
                    \ilAdministrationGUI::class,
                    \ilObjQuestionsGUI::class,
                    self::class
                ],
                self::CMD_SAVE
            ),
            [
                'default_user_settings' => $this->ui_factory->input()->field()->section(
                    [
                        'create_mode' => $this->repository->getInputForCreateMode(
                            $this->ui_factory->input()->field(),
                            $this->lng,
                            $this->refinery
                        )->withAdditionalTransformation(
                            $this->refinery->custom()->transformation(
                                static fn(string $v): CreateModes => CreateModes::tryFrom($v)
                                    ?? CreateModes::getDefaultMode()
                            )
                        )
                    ],
                    $this->lng->txt('default_user_settings')
                )
            ]
        );
    }
}
