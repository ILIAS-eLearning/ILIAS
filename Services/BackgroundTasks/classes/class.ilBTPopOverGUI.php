<?php

use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractTask;
use ILIAS\BackgroundTasks\Implementation\UI\StateTranslator;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\Modules\OrgUnit\ARHelper\DIC;
use ILIAS\UI\Factory;

/**
 * Class ilBTPopOverGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTPopOverGUI
{
    use DIC;
    use StateTranslator;
    /**
     * @var  Persistence
     */
    protected $btPersistence;


    /**
     * ilBTPopOverGUI constructor.
     *
     * @param \ILIAS\UI\Factory                  $uiFactory
     * @param \ILIAS\BackgroundTasks\Persistence $btPersistence
     * @param \ilLanguage                        $lng
     * @param \ilCtrl                            $ctrl
     */
    public function __construct(Factory $uiFactory, Persistence $btPersistence, \ilLanguage $lng, ilCtrl $ctrl)
    {
        $this->btPersistence = $btPersistence;
        $this->lng()->loadLanguageModule('background_tasks');
    }


    /**
     * Get the content for the popover as ui element. DOES NOT DO ANY PERMISSION CHECKS.
     *
     * @param int  $user_id
     * @param null $redirect_uri
     *
     * @return \ILIAS\UI\Component\Component[]
     */
    public function getPopOverContent($user_id, $redirect_uri, $replace_url = '')
    {
        $r = $this->ui()->renderer();
        $f = $this->ui()->factory();
        $persistence = $this->dic()->backgroundTasks()->persistence();

        $observer_ids = $this->btPersistence->getBucketIdsOfUser($user_id, 'id', 'DESC');
        $observers = $this->btPersistence->loadBuckets($observer_ids);

        $metas = $persistence->getBucketMetaOfUser($this->user()->getId());
        $user_inter = count(array_filter($metas, function (BucketMeta $meta) {
            return $meta->getState() == State::USER_INTERACTION;
        }));

        $po_content = new ilTemplate("tpl.popover_content.html", true, true, "Services/BackgroundTasks");
        $po_content->setVariable("BACKGROUND_TASKS_TOTAL", count($metas));
        $po_content->setVariable("BACKGROUND_TASKS_USER_INTERACTION", $user_inter);

        $bucket = new ilTemplate("tpl.bucket.html", true, true, "Services/BackgroundTasks");

        foreach ($observers as $observer) {
            $state = (int) $observer->getState();
            $current_task = $observer->getCurrentTask();

            switch ($state) {
                case State::USER_INTERACTION:
                    $bucket->setVariable("CONTENT", $r->render($this->getProgressbar($observer)));
                    $bucket->setVariable("INTERACTIONS", $r->render([
                        $this->getUserInteractionContent($observer, $redirect_uri),
                    ]));
                    break;
                case State::RUNNING:
                    $expected = (int) $current_task->getExpectedTimeOfTaskInSeconds();
                    $possibly_failed = (bool) ($observer->getLastHeartbeat() < (time() - $expected));

                    if ($possibly_failed) {
                        $bucket->setCurrentBlock('failed');
                        $bucket->setVariable("ALERT", $this->lng()->txt('task_might_be_failed'));
                        $bucket->parseCurrentBlock();
                        $this->addButton($current_task->getAbortOption(), $redirect_uri, $bucket, $observer);
                    }
                    $bucket->setVariable("CONTENT", $r->render($this->getDefaultCardContent($observer)));
                    break;
                default:
                    $bucket->setVariable("CONTENT", $r->render($this->getDefaultCardContent($observer)));
                    break;
            }

            if ($state === State::USER_INTERACTION) {
                $this->addButton($current_task->getRemoveOption(), $redirect_uri, $bucket, $observer);
            }

            $bucket->setCurrentBlock("bucket");
            $bucket_title = $observer->getTitle() . ($state
                                                     == State::SCHEDULED ? " ({$this->lng()->txt("scheduled")})" : "");
            $bucket->setVariable("BUCKET_TITLE", $bucket_title);
            if ($observer->getDescription()) {
                $bucket->setVariable("BUCKET_DESCRIPTION", $observer->getDescription());
            }
            $bucket->parseCurrentBlock();
        }
        $po_content->setVariable("CONTENT", $bucket->get());
        $uiElement = $f->legacy($po_content->get());

        return $uiElement;
    }


    /**
     * @param \ILIAS\BackgroundTasks\Bucket $observer
     *
     * @return \ILIAS\UI\Component\Legacy\Legacy
     */
    public function getDefaultCardContent(Bucket $observer)
    {
        $progressbar = $this->getProgressbar($observer);

        return $progressbar;
    }


    /**
     * @param Bucket $observer
     * @param        $redirect_uri
     *
     * @return \ILIAS\UI\Component\Legacy\Legacy
     */
    public function getUserInteractionContent(Bucket $observer, $redirect_uri)
    {
        $factory = $this->ui()->factory();
        $renderer = $this->ui()->renderer();
        $language = $this->lng();
        $persistence = $this->dic()->backgroundTasks()->persistence();
        if (!$observer->getCurrentTask() instanceof UserInteraction) {
            return $factory->legacy("");
        }
        /** @var UserInteraction $userInteraction */
        $userInteraction = $observer->getCurrentTask();
        $options = $userInteraction->getOptions($userInteraction->getInput());
        $buttons = array_map(function (UserInteraction\Option $option) use ($factory, $renderer, $observer, $persistence, $redirect_uri, $language) {
            $this->ctrl()
                 ->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::SELECTED_OPTION, $option->getValue());
            $this->ctrl()
                 ->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::OBSERVER_ID, $persistence->getBucketContainerId($observer));
            $this->addFromUrlToNextRequest($redirect_uri);

            return $renderer->render($factory->button()
                                             ->standard($language->txt($option->getLangVar()), $this->ctrl()
                                                                                                    ->getLinkTargetByClass([ ilBTControllerGUI::class ], ilBTControllerGUI::CMD_USER_INTERACTION)));
        }, $options);

        $options = implode(" ", $buttons);

        return $factory->legacy($options);
    }


    /**
     * @param \ILIAS\BackgroundTasks\Bucket $observer
     *
     * @return \ILIAS\UI\Component\Legacy\Legacy
     */
    protected function getProgressbar(Bucket $observer)
    {
        $percentage = $observer->getOverallPercentage();

        switch (true) {
            case ((int) $percentage === 100):
                $running = "";
                $content = $this->lng()->txt("completed");
                break;
            case ((int) $observer->getState() === State::USER_INTERACTION):
                $running = "";
                $content = $this->lng()->txt("waiting");
                break;
            default:
                $running = "active";
                $content = "{$percentage}%";
                break;
        }

        return $this->ui()->factory()->legacy(" <div class='progress'>
                    <div class='progress-bar progress-bar-striped {$running}' role='progressbar' aria-valuenow='{$percentage}'
                        aria-valuemin='0' aria-valuemax='100' style='width:{$percentage}%'>
                        {$content}
                    </div>
				</div> ");
    }


    protected function addButton(UserInteraction\Option $option, $redirect_uri, ilTemplate $bucket, Bucket $observer)
    {
        $r = $this->ui()->renderer();
        $f = $this->ui()->factory();
        $persistence = $this->dic()->backgroundTasks()->persistence();
        // Close Action
        $bucket->setCurrentBlock('close_button');

        $this->ctrl()
             ->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::OBSERVER_ID, $persistence->getBucketContainerId($observer));
        $this->addFromUrlToNextRequest($redirect_uri);

        switch ($option->getValue()) {
            case AbstractTask::MAIN_ABORT:
                $action = $this->ctrl()
                               ->getLinkTargetByClass([ ilBTControllerGUI::class ], ilBTControllerGUI::CMD_ABORT);
                break;
            case AbstractTask::MAIN_REMOVE:
                $action = $this->ctrl()
                               ->getLinkTargetByClass([ ilBTControllerGUI::class ], ilBTControllerGUI::CMD_REMOVE);
                break;
            default:
                $this->ctrl()
                     ->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::SELECTED_OPTION, $option->getValue());
                $action = $this->ctrl()
                               ->getLinkTargetByClass([ ilBTControllerGUI::class ], ilBTControllerGUI::CMD_USER_INTERACTION);
                break;
        }

        $label = $this->lng()->txt($option->getLangVar());

        $remove = $r->render($f->button()->standard($label, $action));

        $bucket->setVariable("CLOSE_BUTTON", $remove);
        $bucket->parseCurrentBlock();
    }


    /**
     * @param $redirect_uri
     */
    protected function addFromUrlToNextRequest($redirect_uri)
    {
        $this->ctrl()
             ->setParameterByClass(ilBTControllerGUI::class, ilBTControllerGUI::FROM_URL, ilBTControllerGUI::hash($redirect_uri));
    }
}
