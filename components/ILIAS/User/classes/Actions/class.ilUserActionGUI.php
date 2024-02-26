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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

/**
 * A class that provides a collection of actions on users
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionGUI
{
    private ilUserActionAdmin $user_action_admin;
    private ilUserActionCollector $user_action_collector;

    public function __construct(
        private ilUserActionProviderFactory $user_action_provider_factory,
        private ilUserActionContext $user_action_context,
        private ilGlobalTemplateInterface $tpl,
        private UIFactory $ui_factory,
        private Renderer $ui_renderer,
        private ilLanguage $lng,
        ilDBInterface $db,
        int $user_id
    ) {
        $this->lng->loadLanguageModule('usr');
        $this->user_action_admin = new ilUserActionAdmin($db);
        $this->user_action_collector = new ilUserActionCollector(
            $user_id,
            new ilGalleryUserActionContext(),
            new ilUserActionProviderFactory(),
            new ilUserActionAdmin($db)
        );
    }

    public function init(): void
    {
        foreach ($this->user_action_provider_factory->getProviders() as $prov) {
            foreach ($prov->getActionTypes() as $act_type => $txt) {
                if ($this->user_action_admin->isActionActive(
                    $this->user_action_context->getComponentId(),
                    $this->user_action_context->getContextId(),
                    $prov->getComponentId(),
                    $act_type
                )) {
                    foreach ($prov->getJsScripts($act_type) as $script) {
                        $this->tpl->addJavaScript($script);
                    }
                }
            }
        }
    }

    public function renderDropDown(int $target_user_id): string
    {
        $action_collection = $this->user_action_collector->getActionsForTargetUser($target_user_id);
        $actions = [];
        foreach ($action_collection->getActions() as $action) {
            $action_link = $this->ui_factory->link()->standard($action->getText(), $action->getHref());
            if ($action->getData() !== []) {
                $data = $action->getData();
                $action_link = $action_link->withAdditionalOnLoadCode(
                    static function ($id) use ($data): string {
                        $js = '';
                        foreach($data as $key => $datum) {
                            $js .= "{$id}.setAttribute('data-{$key}', '{$datum}');";
                        }
                        return $js;
                    }
                );
                $actions[] = $action_link;
            }
        }
        $action_list = $this->ui_factory->dropdown()->standard($actions)
            ->withAriaLabel($this->lng->txt('user_actions'));
        return $this->ui_renderer->render($action_list);
    }
}
