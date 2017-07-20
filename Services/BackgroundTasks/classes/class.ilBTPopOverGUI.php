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
		$this->ctrl = $ctrl;
	}


	/**
	 * Get the content for the popover as ui element. DOES NOT DO ANY PERMISSION CHECKS.
	 *
	 * @param int  $user_id
	 * @param null $redirect_uri
	 *
	 * @return \ILIAS\UI\Component\Component[]
	 */
	public function getPopOverContent($user_id, $redirect_uri) {
		assert(is_int($user_id));

		global $DIC;

		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();
		$persistence = $DIC->backgroundTasks()->persistence();

		$observer_ids = $this->btPersistence->getBucketIdsOfUser($user_id);
		$observers = $this->btPersistence->loadBuckets($observer_ids);

		$metas = $persistence->getBucketMetaOfUser($DIC->user()->getId());
		$numberOfUserInteractions = count(array_filter($metas, function (BucketMeta $meta) {
			return $meta->getState() == State::USER_INTERACTION;
		}));

		$template = new ilTemplate("tpl.popover_content.html", true, true, "Services/BackgroundTasks");
		$template->setVariable("BACKGROUND_TASKS_TOTAL", count($metas));
		$template->setVariable("BACKGROUND_TASKS_USER_INTERACTION", $numberOfUserInteractions);

		// TODO implement the content with UI-Service components
		foreach ($observers as $observer) {
			if ($observer->getState() != State::USER_INTERACTION) {
				$content = $this->getDefaultCardContent($observer);
			} else {
				$content = $this->getUserInteractionContent($observer, $redirect_uri);
			}
			$template->setCurrentBlock("bucket");
			$bucket_title = $observer->getTitle() . ($observer->getState()
			                                         == State::SCHEDULED ? " ({$this->lng->txt("scheduled")})" : "");
			$template->setVariable("BUCKET_TITLE", $bucket_title);
			$template->setVariable("BUCKET_CONTENT", $renderer->render($content));
			$template->parseCurrentBlock();
		}
		$uiElement = $factory->legacy($template->get());

		return [ $uiElement ];
	}


	public function getDefaultCardContent(Bucket $observer) {
		global $DIC;
		$running = $observer->getState() == State::RUNNING;

		$overallPercentage = $observer->getOverallPercentage();

		return $DIC->ui()->factory()->legacy(" <div class=\"progress\">
                    <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"{$overallPercentage}\"
                        aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:{$overallPercentage}%\">
                        {$overallPercentage}%
                    </div>
				</div> ");
	}


	/**
	 * @param Bucket $observer
	 * @param        $redirect_uri
	 *
	 * @return Descriptive|null
	 */
	public function getUserInteractionContent(Bucket $observer, $redirect_uri) {
		global $DIC;
		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
		$persistence = $DIC->backgroundTasks()->persistence();
		if (!$observer->getCurrentTask() instanceof UserInteraction) {
			return null;
		}
		/** @var UserInteraction $userInteraction */
		$userInteraction = $observer->getCurrentTask();
		$options = $userInteraction->getOptions($userInteraction->getInput());
		$buttons = array_map(function (UserInteraction\Option $option) use ($factory, $renderer, $observer, $persistence, $redirect_uri) {

			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "selected_option", $option->getValue());
			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "observer_id", $persistence->getBucketContainerId($observer));
			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "from_url", urlencode($redirect_uri));

			return $renderer->render($factory->button()->standard($option->getLangVar(), $this->ctrl->getLinkTargetByClass([ ilBTControllerGUI::class ], "userInteraction")));
		}, $options);

		$options = implode(" ", $buttons);

		return $factory->legacy($options);
	}
}