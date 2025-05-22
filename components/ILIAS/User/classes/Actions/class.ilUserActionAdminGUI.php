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

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

use Psr\Http\Message\ServerRequestInterface;

/**
 * User action administration GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionAdminGUI
{
    private ilRbacSystem $rbabsystem;
    private int $ref_id;
    private ServerRequestInterface $request;
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private Language $lng;
    private ilUserActionContext $action_context;
    private ilUserActionAdmin $user_action_admin;

    public function __construct(int $ref_id)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->rbabsystem = $DIC['rbacsystem'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->request = $DIC['http']->request();

        $this->user_action_admin = new ilUserActionAdmin($DIC['ilDB']);

        $this->ref_id = $ref_id;

        $this->lng->loadLanguageModule('usr');
    }

    public function setActionContext(?ilUserActionContext $a_val = null): void
    {
        $this->action_context = $a_val;
    }

    public function getActionContext(): ilUserActionContext
    {
        return $this->action_context;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('show');

        switch ($next_class) {
            default:
                if (in_array($cmd, ['show', 'save'])) {
                    $this->$cmd();
                }
        }
    }

    public function show(): void
    {
        if (!$this->rbabsystem->checkAccess('write', $this->ref_id)) {
            $this->ctrl->redirect($this, 'show');
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('user_actions_activation_info'));

        $inputs = [];

        foreach($this->getActions() as $action) {
            $inputs["{$action['action_comp_id']}:{$action['action_type_id']}"] =
                $this->ui_factory->input()->field()->checkbox($action["action_type_name"])
                    ->withValue($action['active']);
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->buildForm()
            )
        );
    }

    private function buildForm(): StandardForm
    {
        $inputs = [];

        foreach($this->getActions() as $action) {
            $inputs["{$action['action_comp_id']}:{$action['action_type_id']}"] =
                $this->ui_factory->input()->field()->checkbox($action["action_type_name"])
                    ->withValue($action['active']);
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, 'save'),
            $inputs
        );
    }

    public function save(): void
    {
        if (!$this->rbabsystem->checkAccess('write', $this->ref_id)) {
            $this->ctrl->redirect($this, 'show');
        }

        $form = $this->buildForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setContent($this->ui_renderer->render($form));
            return;
        }

        foreach ($this->getActions() as $action) {
            $this->user_action_admin->activateAction(
                $this->action_context->getComponentId(),
                $this->action_context->getContextId(),
                $action['action_comp_id'],
                $action['action_type_id'],
                $data["{$action['action_comp_id']}:{$action['action_type_id']}"] ?? false
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->show();
    }

    private function getActions(): \Generator
    {
        foreach ((new ilUserActionProviderFactory())->getProviders() as $p) {
            foreach ($p->getActionTypes() as $id => $name) {
                yield [
                    'action_comp_id' => $p->getComponentId(),
                    'action_type_id' => $id,
                    'action_type_name' => $name,
                    'active' => $this->user_action_admin->isActionActive(
                        $this->action_context->getComponentId(),
                        $this->action_context->getContextId(),
                        $p->getComponentId(),
                        $id
                    )
                ];
            }
        }
    }
}
