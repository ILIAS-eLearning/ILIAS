<?php

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

	/** @var Factory  */
	protected $uiFactory;
	/** @var  Persistence */
	protected $btPersistence;
	/** @var \ilLanguage  */
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
	 *
	 * @param null $redirect_uri
	 *
	 * @return \ILIAS\UI\Component\Component[]
	 */
	public function getPopOverContent($user_id, $redirect_uri = null) {
		assert(is_int($user_id));
		$observer_ids = $this->btPersistence->getBucketIdsOfUser($user_id);
		$observers = $this->btPersistence->loadBuckets($observer_ids);

		$cards = [];
		foreach ($observers as $observer) {
			if($observer->getState() != State::USER_INTERACTION) {
				$content = $this->getDefaultCardContent($observer);
			} else {
				$content = $this->getUserInteractionContent($observer, $redirect_uri);
			}
			$cards[] = $this->uiFactory->card($observer->getTitle())->withSections([$content]);
		}

		return [$this->uiFactory->deck($cards)];
	}

	public function getDefaultCardContent(Bucket $observer) {
		global $DIC;
		$running = $observer->getState() == State::RUNNING;
		return $this->uiFactory->listing()->descriptive(
			[
				"State" => $this->translateState($observer->getState(), $this->lng),
				"Percentage" => $DIC->ui()->factory()->progressbar($observer->getOverallPercentage(), $running)
			]
		);
	}

	public function getUserInteractionContent(Bucket $observer, $redirect_uri) {
		global $DIC;
		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
		$persistence = $DIC->backgroundTasks()->persistence();
		if (!$observer->getCurrentTask() instanceof UserInteraction)
			return "";
		$redirect_uri = $redirect_uri?$redirect_uri:$this->full_url($_SERVER);
		/** @var UserInteraction $userInteraction */
		$userInteraction = $observer->getCurrentTask();
		$options = $userInteraction->getOptions($userInteraction->getInput());
		$buttons = array_map(function (UserInteraction\Option $option) use ($factory, $renderer, $observer, $persistence, $redirect_uri) {

			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "selected_option", $option->getValue());
			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "observer_id", $persistence->getBucketContainerId($observer));
			$this->ctrl->setParameterByClass(ilBTControllerGUI::class, "from_url", urlencode($redirect_uri));
			return $renderer->render($factory->button()->standard($option->getLangVar(), $this->ctrl->getLinkTargetByClass([ilBTControllerGUI::class], "userInteraction")));

		}, $options);

		$options = implode(" ", $buttons);
		return $this->uiFactory->listing()->descriptive(
			[
				"State" => $this->translateState($observer->getState(), $this->lng),
				"Options" => $options
			]
		);
	}

	protected function url_origin( $s, $use_forwarded_host = false )
	{
		$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp       = strtolower( $s['SERVER_PROTOCOL'] );
		$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port     = $s['SERVER_PORT'];
		$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
		$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
		return $protocol . '://' . $host;
	}

	public function full_url( $s, $use_forwarded_host = false )
	{
		return $this->url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
	}
}