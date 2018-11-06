<?php

declare(strict_types=1);

/**
 * EventListener for LSO
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilLearningSequenceAppEventListener
{

	public static function handleEvent($component, $event, $parameter)
	{
		switch ($component) {
			case "Services/Tracking":
				switch ($event) {
					case "updateStatus":
						self::onServiceTrackingUpdateStatus($parameter);
						break;
				}
				break;
			case "Services/Object":
				switch ($event) {
					case "beforeDeletion":
						self::onObjectDeletion($parameter);
						break;
				}
				break;
			default:
				throw new ilException(
					"ilLearningSequenceAppEventListener::handleEvent: ".
					"Won't handle events of '$component'."
				);
		}
	}

	private static function onServiceTrackingUpdateStatus($parameter)
	{
		$handler = new ilLSLPEventHandler(self::getIlTree(), self::getIlLPStatusWrapper());
		$handler->updateLPForChildEvent($parameter);
	}

	private static function onObjectDeletion($parameter)
	{
		$handler = new ilLSEventHandler(self::getIlTree());
		$handler->handleObjectDeletion($parameter);
	}


	protected static function getIlTree(): ilTree
	{
		global $DIC;
		return $DIC['tree'];
	}

	protected static function getIlLPStatusWrapper(): ilLPStatusWrapper
	{
		return new ilLPStatusWrapper();
	}

}
