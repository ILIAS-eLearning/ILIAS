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

/**
 * Collects actions from all action providers
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionCollector
{
    private ilUserActionCollection $collection;

    public function __construct(
        private int $user_id,
        private ilUserActionContext $action_context,
        private ilUserActionProviderFactory $user_action_provider_factory,
        private ilUserActionAdmin $user_action_admin
    ) {
        $this->collection = new ilUserActionCollection();
    }

    public function getActionsForTargetUser(int $target_user): ilUserActionCollection
    {
        foreach ($this->user_action_provider_factory->getProviders() as $provider) {
            if (!$this->hasProviderActiveActions($provider)) {
                continue;
            }
            $provider->setUserId($this->user_id);
            $coll = $provider->collectActionsForTargetUser($target_user);
            foreach ($coll->getActions() as $action) {
                if ($this->user_action_admin->isActionActive(
                    $this->action_context->getComponentId(),
                    $this->action_context->getContextId(),
                    $provider->getComponentId(),
                    $action->getType()
                )) {
                    $this->collection->addAction($action);
                }
            }
        }

        return $this->collection;
    }

    protected function hasProviderActiveActions(ilUserActionProvider $provider): bool
    {
        foreach ($provider->getActionTypes() as $act_type => $act_txt) {
            if ($this->user_action_admin->isActionActive(
                $this->action_context->getComponentId(),
                $this->action_context->getContextId(),
                $provider->getComponentId(),
                $act_type
            )) {
                return true;
            }
        }
        return false;
    }
}
