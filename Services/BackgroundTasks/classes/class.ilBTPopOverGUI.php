<?php

use ILIAS\BackgroundTasks\Implementation\UI\StateTranslator;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\UI\Component\Listing\Descriptive;
use ILIAS\UI\Factory;

class ilBTPopOverGUI {

	use StateTranslator;

	/** @var Factory  */
	protected $uiFactory;
	/** @var  Persistence */
	protected $btPersistence;
	/** @var \ilLanguage  */
	protected $lng;

	public function __construct(Factory $uiFactory, Persistence $btPersistence, \ilLanguage $lng) {
		$this->uiFactory = $uiFactory;
		$this->btPersistence = $btPersistence;
		$this->lng = $lng;
	}


	/**
	 * Get the content for the popover as ui element. DOES NOT DO ANY PERMISSION CHECKS.
	 *
	 * @param int $user_id
	 *
	 * @return \ILIAS\UI\Component\Deck\Deck
	 */
	public function getPopOverContent(int $user_id) {
		$observer_ids = $this->btPersistence->getObserverIdsOfUser($user_id);
		$observers = $this->btPersistence->loadObservers($observer_ids);

		$taskInfos = array_map(function(Observer $observer){
			return $this->uiFactory->listing()->descriptive(
				[
					"State" => $this->translateState($observer->getState(), $this->lng),
					"Percentage" => $observer->getPercentage()
				]
			);
		}, $observers);

		$cards = array_map(function(Descriptive $taskInfo) {
			return $this->uiFactory->card("Some Observer")->withSections([$taskInfo]);
		}, $taskInfos);

		return $this->uiFactory->deck($cards);

	}
}