<?php

use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
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
class ilBTPopOverGUI {

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
	public function __construct(Factory $uiFactory, Persistence $btPersistence, \ilLanguage $lng, ilCtrl $ctrl) {
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
	public function getPopOverContent($user_id, $redirect_uri, $replace_url = '') {
		$r = $this->ui()->renderer();
		$f = $this->ui()->factory();
		$persistence = $this->dic()->backgroundTasks()->persistence();

		$observer_ids = $this->btPersistence->getBucketIdsOfUser($user_id);
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
			if ($observer->getState() != State::USER_INTERACTION) {
				if ($observer->getLastHeartbeat() < (time() - $observer->getCurrentTask()
				                                                       ->getExpectedTimeOfTaksInSeconds())) {
					$bucket->setCurrentBlock('failed');
					$bucket->setVariable("ALERT", $this->lng()->txt('task_might_be_failed'));
					// Close Action
					$this->ctrl()
					     ->setParameterByClass(ilBTControllerGUI::class, "observer_id", $persistence->getBucketContainerId($observer));
					$this->ctrl()
					     ->setParameterByClass(ilBTControllerGUI::class, "from_url", urlencode($redirect_uri));
					$close_action = $this->ctrl()
					                     ->getLinkTargetByClass([ ilBTControllerGUI::class ], ilBTControllerGUI::CMD_QUIT);
					$remove = $r->render($f->button()
					                       ->close()
					                       ->withAdditionalOnLoadCode(function ($id) use ($close_action) {
						                       return "$($id).on('click', function() { 
						                            var url = '$close_action';
						                            var replacer = new RegExp('amp;', 'g');
                                                    url = url.replace(replacer, '');
						                            window.location=url
						                       });";
					                       }));
					$bucket->setVariable("CLOSE_BUTTON", $remove);
					$bucket->parseCurrentBlock();
				}
				$bucket->setVariable("CONTENT", $r->render($this->getDefaultCardContent($observer)));
			} else {
				$bucket->setVariable("CONTENT", $r->render($this->getProgressbar($observer)));
				$bucket->setVariable("INTERACTIONS", $r->render([
					$this->getUserInteractionContent($observer, $redirect_uri),
				]));
			}
			$bucket->setCurrentBlock("bucket");
			$bucket_title = $observer->getTitle() . ($observer->getState()
			                                         == State::SCHEDULED ? " ({$this->lng()->txt("scheduled")})" : "");
			$bucket->setVariable("BUCKET_TITLE", $bucket_title);
			if ($observer->getDescription()) {
				$bucket->setVariable("BUCKET_DESCRIPTION", $observer->getDescription());
			}
			$bucket->parseCurrentBlock();
		}
		$po_content->setVariable("CONTENT", $bucket->get());
		$uiElement = $f->legacy($po_content->get());

		return [ $uiElement ];
	}


	/**
	 * @param \ILIAS\BackgroundTasks\Bucket $observer
	 *
	 * @return \ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getDefaultCardContent(Bucket $observer) {
		$progressbar = $this->getProgressbar($observer);

		return $progressbar;
	}


	/**
	 * @param Bucket $observer
	 * @param        $redirect_uri
	 *
	 * @return \ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getUserInteractionContent(Bucket $observer, $redirect_uri) {

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
			     ->setParameterByClass(ilBTControllerGUI::class, "selected_option", $option->getValue());
			$this->ctrl()
			     ->setParameterByClass(ilBTControllerGUI::class, "observer_id", $persistence->getBucketContainerId($observer));
			$this->ctrl()
			     ->setParameterByClass(ilBTControllerGUI::class, "from_url", urlencode($redirect_uri));

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
	protected function getProgressbar(Bucket $observer) {
		$percentage = $observer->getOverallPercentage();

		switch (true) {
			case ((int)$percentage === 100):
				$running = "";
				$content = $this->lng()->txt("completed");
				break;
			case ((int)$observer->getState() === State::USER_INTERACTION):
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
}