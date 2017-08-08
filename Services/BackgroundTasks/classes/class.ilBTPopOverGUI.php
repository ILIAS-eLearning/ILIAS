<?php

use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\UI\StateTranslator;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\UI\Component\Listing\Descriptive;
use ILIAS\UI\Factory;

require_once("./Services/BackgroundTasks/classes/StateTranslator.php");

class ilBTPopOverGUI {

	use StateTranslator;
	/** @var Factory */
	protected $uiFactory;
	/** @var  Persistence */
	protected $btPersistence;
	/** @var \ilLanguage */
	protected $lng;
	/** @var  ilCtrl */
	protected $ctrl;


	public function __construct(Factory $uiFactory, Persistence $btPersistence, \ilLanguage $lng, ilCtrl $ctrl) {
		$this->uiFactory = $uiFactory;
		$this->btPersistence = $btPersistence;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('background_tasks');
		$this->ctrl = $ctrl;
	}


	/**
	 * Get the content for the popover as ui element. DOES NOT DO ANY PERMISSION CHECKS.
	 *
	 * @param int $user_id
	 * @param null $redirect_uri
	 *
	 * @return \ILIAS\UI\Component\Component[]
	 */
	public function getPopOverContent($user_id, $redirect_uri, $replace_url = '') {
		global $DIC;

		$r = $DIC->ui()->renderer();
		$f = $DIC->ui()->factory();
		$persistence = $DIC->backgroundTasks()->persistence();

		$observer_ids = $this->btPersistence->getBucketIdsOfUser($user_id);
		$observers = $this->btPersistence->loadBuckets($observer_ids);

		$metas = $persistence->getBucketMetaOfUser($DIC->user()->getId());
		$user_inter = count(array_filter($metas, function (BucketMeta $meta) {
			return $meta->getState() == State::USER_INTERACTION;
		}));

		$po_content = new ilTemplate("tpl.popover_content.html", true, true, "Services/BackgroundTasks");
		$po_content->setVariable("BACKGROUND_TASKS_TOTAL", count($metas));
		$po_content->setVariable("BACKGROUND_TASKS_USER_INTERACTION", $user_inter);

		$bucket = new ilTemplate("tpl.bucket.html", true, true, "Services/BackgroundTasks");

		foreach ($observers as $observer) {
			if ($observer->getState() != State::USER_INTERACTION) {
				$bucket->setVariable("CONTENT", $r->render($this->getDefaultCardContent($observer)));
			} else {
				$bucket->setVariable("INTERACTIONS", $r->render([
					$this->getProgressbar($observer),
					$this->getUserInteractionContent($observer, $redirect_uri)
				]));
			}
			$bucket->setCurrentBlock("bucket");
			$bucket_title = $observer->getTitle() . ($observer->getState()
			== State::SCHEDULED ? " ({$this->lng->txt("scheduled")})" : "");
			$bucket->setVariable("BUCKET_TITLE", $bucket_title);
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
		return $this->getProgressbar($observer);
	}


	/**
	 * @param Bucket $observer
	 * @param        $redirect_uri
	 *
	 * @return \ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getUserInteractionContent(Bucket $observer, $redirect_uri) {
		global $DIC;
		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
		$language = $this->lng;
		$persistence = $DIC->backgroundTasks()->persistence();
		if (!$observer->getCurrentTask() instanceof UserInteraction) {
			return $factory->legacy("");
		}
		/** @var UserInteraction $userInteraction */
		$userInteraction = $observer->getCurrentTask();
		$options = $userInteraction->getOptions($userInteraction->getInput());
		$buttons = array_map(function (UserInteraction\Option $option) use ($factory, $renderer, $observer, $persistence, $redirect_uri, $language) {

			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "selected_option", $option->getValue());
			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "observer_id", $persistence->getBucketContainerId($observer));
			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "from_url", urlencode($redirect_uri));

			return $renderer->render($factory->button()->standard($language->txt($option->getLangVar()), $this->ctrl->getLinkTargetByClass([ ilBTControllerGUI::class ], "userInteraction")));
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
		global $DIC;
		$percentage = $observer->getOverallPercentage();
		if ($observer->getState() == State::USER_INTERACTION || $percentage === 100) {
			$content = $this->lng->txt("completed");
		}else {
			$content = "{$percentage}%";
		}

		return $DIC->ui()->factory()->legacy(" <div class='progress'>
                    <div class='progress-bar progress-bar-striped' role='progressbar' aria-valuenow='{$percentage}'
                        aria-valuemin='0' aria-valuemax='100' style='width:{$percentage}%'>
                        {$content}
                    </div>
				</div> ");
	}
}