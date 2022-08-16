<?php

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractTask;
use ILIAS\BackgroundTasks\Implementation\UI\StateTranslator;
use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ilBTPopOverGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTPopOverGUI
{
    use StateTranslator;
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;


    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->dic = $dic;
    }


    /**
     * Get the Notification Items. DOES NOT DO ANY PERMISSION CHECKS.
     */
    public function getNotificationItem(int $nr_buckets) : ILIAS\UI\Component\Item\Notification
    {
        $ui_factory = $this->dic->ui()->factory();

        $title = $ui_factory->link()->standard($this->txt('background_tasks'), '#');
        $icon = $ui_factory->symbol()->icon()->standard('bgtk', $this->txt('background_tasks'));

        return $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription("$nr_buckets {$this->txt('background_tasks')}")
            ->withAggregateNotifications($this->getAggregateItems());
    }


    /**
     * @return ILIAS\UI\Component\Item\Notification[]
     */
    protected function getAggregateItems() : array
    {
        $persistence = $this->dic->backgroundTasks()->persistence();
        $items = [];
        $observer_ids = $persistence->getBucketIdsOfUser($this->dic->user()->getId(), 'id', 'DESC');
        foreach ($persistence->loadBuckets($observer_ids) as $observer) {
            $items[] = $this->getItemForObserver($observer);
        }

        return $items;
    }


    public function getItemForObserver(Bucket $observer) : ILIAS\UI\Component\Item\Notification
    {
        $redirect_uri = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        $f = $this->dic->ui()->factory();

        $state = (int) $observer->getState();
        $current_task = $observer->getCurrentTask();

        $icon = $f->symbol()->icon()->standard("bgtk", $this->txt("bg_task"));
        $title = $observer->getTitle() . ($state === State::SCHEDULED ? " ({$this->txt('scheduled')})" : "");


        $description_text = $this->txt('background_tasks');

        if ($state === State::USER_INTERACTION) {
            $actions = $this->getUserInteractionContent($observer, $redirect_uri);
            $primary_action = array_pop($actions);
            if ($primary_action instanceof Button) {
                $title = $primary_action->withLabel($title);
            }

            $item = $f->item()->notification($title, $icon);
            $item = $this->getItemWithOnCloseDecoration($item);

//            $item = $item->withProperties([
//                $this->dic->language()->txt('nc_mail_prop_time') => \ilDatePresentation::formatDate(
//                    new \ilDateTime(time(), IL_CAL_UNIX)
//                )
//            ]);

            $item = $item->withActions($f->dropdown()->standard($actions));
            $input = $current_task->getInput();
            $message = $current_task->getMessage($input);

            if ((!empty($message)) and ($message != null)) {
                $item = $item->withDescription($message);
            } else {
                $item = $item->withAdditionalContent($this->getProgressbar($observer));
            }

            return $item->withCloseAction(
                $this->getCloseButtonAction($current_task->getRemoveOption(), $redirect_uri, $observer)
            );
        }

        $item = $f->item()->notification($title, $icon);
        $item = $this->getItemWithOnCloseDecoration($item);

        if ($state === State::RUNNING) {
            $url = $this->getRefreshUrl($observer);
            //Running Items probably need to refresh themselves, right?
            $item = $item->withAdditionalOnLoadCode(function ($id) use ($url) {
                //Note this is only for demo purposes, adapt as needed.
                return "var notification_item = il.UI.item.notification.getNotificationItemObject($('#$id'));
                    il.BGTask.refreshItem(notification_item,'$url');";
            });

            $expected = $current_task->getExpectedTimeOfTaskInSeconds();
            $possibly_failed = ($observer->getLastHeartbeat() < (time() - $expected));
            if ($possibly_failed === true) {
                $item = $item->withDescription($this->txt('task_might_be_failed'));
                $item = $item->withCloseAction(
                    $this->getCloseButtonAction($current_task->getAbortOption(), $redirect_uri, $observer)
                );
            }
        }

        return $item->withAdditionalContent($this->getDefaultCardContent($observer));
    }

    protected function getItemWithOnCloseDecoration(ILIAS\UI\Component\Item\Notification $item) : ILIAS\UI\Component\Item\Notification
    {
        $description_text = $this->txt('background_tasks');
        return $item->withAdditionalOnLoadCode(function ($id) use ($description_text) {
            return "il.BGTask.updateDescriptionOnClose('#$id','$description_text');";
        });
    }

    private function getDefaultCardContent(Bucket $observer) : Legacy
    {
        return $this->getProgressbar($observer);
    }


    /**
     * @return Shy[]
     */
    public function getUserInteractionContent(Bucket $observer, string $redirect_uri) : array
    {
        $factory = $this->dic->ui()->factory();
        $language = $this->dic->language();
        $persistence = $this->dic->backgroundTasks()->persistence();
        $ctrl = $this->dic->ctrl();

        if (!$observer->getCurrentTask() instanceof UserInteraction) {
            return [$factory->legacy('')];
        }
        /** @var UserInteraction $userInteraction */
        $userInteraction = $observer->getCurrentTask();
        $options = $userInteraction->getOptions($userInteraction->getInput());

        $shy_buttons = array_map(
            function (UserInteraction\Option $option) use ($ctrl, $factory, $observer, $persistence, $redirect_uri, $language) {
                $ctrl->setParameterByClass(
                    ilBTControllerGUI::class,
                    ilBTControllerGUI::FROM_URL,
                    ilBTControllerGUI::hash("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}")
                );
                $ctrl->setParameterByClass(
                    ilBTControllerGUI::class,
                    ilBTControllerGUI::SELECTED_OPTION,
                    $option->getValue()
                );
                $ctrl->setParameterByClass(
                    ilBTControllerGUI::class,
                    ilBTControllerGUI::OBSERVER_ID,
                    $persistence->getBucketContainerId($observer)
                );
                $this->addFromUrlToNextRequest($redirect_uri);

                return $factory->button()
                    ->shy(
                        $language->txt($option->getLangVar()),
                        $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_USER_INTERACTION)
                    );
            },
            $options
        );

        return $shy_buttons;
    }


    private function getProgressbar(Bucket $observer) : Legacy
    {
        $percentage = $observer->getOverallPercentage();

        switch (true) {
            case ((int) $percentage === 100):
                $running = "";
                $content = $this->dic->language()->txt("completed");
                break;
            case ((int) $observer->getState() === State::USER_INTERACTION):
                $running = "";
                $content = $this->dic->language()->txt("waiting");
                break;
            default:
                $running = "active";
                $content = "{$percentage}%";
                break;
        }

        return $this->dic->ui()->factory()->legacy(" <div class='progress'>
                    <div class='progress-bar progress-bar-striped {$running}' role='progressbar' aria-valuenow='{$percentage}'
                        aria-valuemin='0' aria-valuemax='100' style='width:{$percentage}%'>
                        {$content}
                    </div>
				</div> ");
    }


    private function getCloseButtonAction(UserInteraction\Option $option, $redirect_uri, Bucket $observer) : string
    {
        $ctrl = $this->dic->ctrl();
        $persistence = $this->dic->backgroundTasks()->persistence();
        $ctrl->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::OBSERVER_ID, $persistence->getBucketContainerId($observer));
        $this->addFromUrlToNextRequest($redirect_uri);
        $ctrl->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::IS_ASYNC, "true");

        switch ($option->getValue()) {
            case AbstractTask::MAIN_ABORT:
                $action = $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_ABORT);
                break;
            case AbstractTask::MAIN_REMOVE:
                $action = $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_REMOVE);
                break;
            default:
                $ctrl->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::SELECTED_OPTION, $option->getValue());
                $action = $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_USER_INTERACTION);
                break;
        }

        return $action;
    }


    private function getRefreshUrl(Bucket $observer) : string
    {
        $ctrl = $this->dic->ctrl();
        $persistence = $this->dic->backgroundTasks()->persistence();
        $ctrl->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::OBSERVER_ID, $persistence->getBucketContainerId($observer));

        return $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_GET_REPLACEMENT_ITEM);
    }


    private function addFromUrlToNextRequest(string $redirect_uri) : void
    {
        $this->dic->ctrl()->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::FROM_URL, ilBTControllerGUI::hash($redirect_uri));
    }


    private function txt(string $id) : string
    {
        return $this->dic->language()->txt($id);
    }
}
