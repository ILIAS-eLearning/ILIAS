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

use ILIAS\DI\Container;
use ILIAS\UI\Component\Item\Notification;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractTask;
use ILIAS\BackgroundTasks\Implementation\UI\StateTranslator;
use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\BackgroundTasks\Task\Job;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Progress\Bar;
use ILIAS\UI\Component\Component;
use ILIAS\BackgroundTasks\Task;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Class ilBTPopOverGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTPopOverGUI
{
    use StateTranslator;

    protected ILIAS\Data\Factory $data_factory;

    public function __construct(protected Container $dic)
    {
        // this is bad, we should inject this.
        $this->data_factory = new ILIAS\Data\Factory();
    }

    /**
     * Get the Notification Items. DOES NOT DO ANY PERMISSION CHECKS.
     */
    public function getNotificationItem(int $nr_buckets): Notification
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
     * @return Notification[]
     */
    protected function getAggregateItems(): array
    {
        $persistence = $this->dic->backgroundTasks()->persistence();
        $items = [];
        $observer_ids = $persistence->getBucketIdsOfUser($this->dic->user()->getId(), 'id', 'DESC');
        foreach ($persistence->loadBuckets($observer_ids) as $observer) {
            $items[] = $this->getItemForObserver($observer);
        }

        return $items;
    }


    public function getItemForObserver(Bucket $observer): Notification
    {
        $redirect_uri = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        $f = $this->dic->ui()->factory();

        $state = $observer->getState();
        $current_task = $observer->getCurrentTask();

        $progress_bar = $this->getProgressbar($observer);

        $icon = $f->symbol()->icon()->standard("bgtk", $this->txt("bg_task"));
        $title = $observer->getTitle() . ($state === State::SCHEDULED ? " ({$this->txt('scheduled')})" : "");

        if ($state === State::USER_INTERACTION) {
            $actions = $this->getUserInteractionContent($observer, $redirect_uri);
            $primary_action = array_pop($actions);
            if ($primary_action instanceof Button) {
                $title = $primary_action->withLabel($title);
            }
            $item = $f->item()->notification($title, $icon);
            $item = $item->withActions($f->dropdown()->standard($actions));
            $input = $current_task->getInput();
            $message = $current_task->getMessage($input);

            if (!empty($message)) {
                $item = $item->withDescription($message);
            }

            $item = $item->withAdditionalContent($this->getUIComponentAsLegacyContent($progress_bar));

            return $item->withCloseAction(
                $this->getCloseButtonAction($current_task->getRemoveOption(), $redirect_uri, $observer)
            );
        }

        $item = $f->item()->notification($title, $icon);

        if ($state === State::RUNNING && $this->hasBucketPossiblyFailed($observer)) {
            $item = $item->withCloseAction(
                $this->getCloseButtonAction($current_task->getAbortOption(), $redirect_uri, $observer)
            );
        }

        $item = $item->withCloseAction(
            $this->getCloseButtonAction($current_task->getAbortOption(), $redirect_uri, $observer)
        );

        return $item->withAdditionalContent($this->getUIComponentAsLegacyContent($progress_bar));
    }


    /**
     * @return Content[]|Shy[]
     */
    public function getUserInteractionContent(Bucket $observer, string $redirect_uri): array
    {
        $factory = $this->dic->ui()->factory();
        $language = $this->dic->language();
        $persistence = $this->dic->backgroundTasks()->persistence();
        $ctrl = $this->dic->ctrl();

        if (!$observer->getCurrentTask() instanceof UserInteraction) {
            return [$factory->legacy()->content('')];
        }
        /** @var UserInteraction $userInteraction */
        $userInteraction = $observer->getCurrentTask();
        $options = $userInteraction->getOptions($userInteraction->getInput());

        return array_map(
            function (Option $option) use ($ctrl, $factory, $observer, $persistence, $redirect_uri, $language): Shy {
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
    }

    private function getProgressbar(Bucket $observer): Bar
    {
        $progress_bar = $this->dic->ui()->factory()->progress()->bar(
            $this->txt('progress'),
            $this->data_factory->uri(ILIAS_HTTP_PATH . '/' . $this->getProgressStateUrl($observer))
        );

        // immediately start the progress bar after being rendered
        $progress_bar = $progress_bar->withAdditionalOnLoadCode(fn($id) => "
            il.UI.Progress.Bar.indeterminate(
                '{$progress_bar->getUpdateSignal()}',
                '{$this->txt('scheduled')}',
            );
        ");

        return $progress_bar;
    }

    public function getProgressBarState(Bucket $observer): \ILIAS\UI\Component\Progress\State\Bar\State
    {
        $task_not_responding_state = $this->dic->ui()->factory()->progress()->state()->bar()->failure($this->txt('task_might_be_failed'));

        if ($this->hasBucketPossiblyFailed($observer)) {
            return $task_not_responding_state;
        }

        $percentage = $observer->getOverallPercentage();
        if (100 > $percentage) {
            return $this->dic->ui()->factory()->progress()->state()->bar()->determinate($percentage, $this->txt('waiting'));
        }
        if (100 <= $percentage) {
            return $this->dic->ui()->factory()->progress()->state()->bar()->success($this->txt('completed'));
        }
        return $task_not_responding_state;
    }


    private function getCloseButtonAction(Option $option, string $redirect_uri, Bucket $observer): string
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

    protected function getUIComponentAsLegacyContent(Component $component): Content
    {
        return $this->dic->ui()->factory()->legacy()->content(
            $this->dic->ui()->renderer()->render($component),
        );
    }

    protected function hasBucketPossiblyFailed(Bucket $observer): bool
    {
        $task = $observer->getCurrentTask();
        $expected = $task instanceof Job ? $task->getExpectedTimeOfTaskInSeconds() : 0;
        return ($observer->getLastHeartbeat() < (time() - $expected));
    }

    private function getProgressStateUrl(Bucket $observer): string
    {
        $ctrl = $this->dic->ctrl();
        $persistence = $this->dic->backgroundTasks()->persistence();
        $ctrl->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::OBSERVER_ID, $persistence->getBucketContainerId($observer));

        return $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_PROGRESS_BAR_STATE);
    }

    private function getRefreshNotificationItemUrl(Bucket $observer): string
    {
        $ctrl = $this->dic->ctrl();
        $persistence = $this->dic->backgroundTasks()->persistence();
        $ctrl->setParameterByClass(
            ilBTControllerGUI::class,
            ilBTControllerGUI::OBSERVER_ID,
            $persistence->getBucketContainerId($observer)
        );

        return $ctrl->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_REFRESH_NOTIFICATION_ITEM);
    }


    private function addFromUrlToNextRequest(string $redirect_uri): void
    {
        $this->dic->ctrl()->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::FROM_URL, ilBTControllerGUI::hash($redirect_uri));
    }


    private function txt(string $id): string
    {
        return $this->dic->language()->txt($id);
    }
}
