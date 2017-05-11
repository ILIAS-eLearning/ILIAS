<?php
/**
 * Created by PhpStorm.
 * User: otruffer
 * Date: 05.05.17
 * Time: 10:14
 */
namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Implementation\Observer\State;

interface Persistence {

	/**
	 * @param Observer $observer The observer you want to save.
	 */
	public function saveObserverAndItsTasks(Observer $observer);


	/**
	 * @param int $user_id
	 *
	 * @return \int[] Returns an array of observer ids for the given user Id.
	 */
	public function getObserverIdsOfUser(int $user_id);


	/**
	 * @param $state State
	 *
	 * @return int[] Returns a list of observer ids for the given Observer State
	 */
	public function getObserverIdsByState($state);


	/**
	 * @param int $observer_id
	 *
	 * @return Observer
	 */
	public function loadObserver(int $observer_id);

	/**
	 * @param int[] $observer_ids
	 *
	 * @return Observer[]
	 */
	public function loadObservers($observer_ids);

	/**
	 * Deletes the Observer AND all its tasks and values.
	 *
	 * @param $observer_id int
	 *
	 * @return void
	 */
	public function deleteObserver($observer_id);

	/**
	 * Updates only the observer! Use this if e.g. the percentage or the current task changes.
	 *
	 * @param Observer $observer
	 */
	public function updateObserver(Observer $observer);
}