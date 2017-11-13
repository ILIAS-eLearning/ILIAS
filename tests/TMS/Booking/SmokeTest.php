<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Booking;

class DummyStep implements Booking\Step {
	public function getLabel() {}
	public function getDescription() {}
	public function getPriority() {}
	public function appendToStepForm(\ilPropertyFormGUI $form) {}
	public function isApplicableFor($usr_id) {}
	public function getData(\ilPropertyFormGUI $form) {}
	public function appendToOverviewForm($data, \ilPropertyFormGUI $form) {}
	public function	processStep($crs_id, $usr_id, $data) {}
	public function entity() {}
	public function addDataToForm(\ilPropertyFormGUI $form, $data) {}
}

class DummyPlayer extends Booking\Player {
	public function getForm() {
		throw new \LogicException("Mock me!");
	}
	protected function txt($id) {
		throw new \LogicException("Mock me!");
	}
	protected function redirectToPreviousLocation($message, $success) {
		throw new \LogicException("Mock me!");
	}
	protected function getPlayerTitle() {
		throw new \LogicException("Mock me!");
	}
	protected function getOverViewDescription() {
		throw new \LogicException("Mock me!");
	}
	protected function getConfirmButtonLabel() {
		throw new \LogicException("Mock me!");
	}
}

class TMS_Booking_SmokeTest extends PHPUnit_Framework_TestCase {
	public function test_instantiateStep() {
		$step = new DummyStep();

		$this->assertInstanceOf(Booking\Step::class, $step);
	}

	public function test_instantiatePlayer() {
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new DummyPlayer([], 0, 0, $db);

		$this->assertInstanceOf(Booking\Player::class, $player);
	}
}
