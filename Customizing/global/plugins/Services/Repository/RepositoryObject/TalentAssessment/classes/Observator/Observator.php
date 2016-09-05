<?php

namespace CaT\Plugins\TalentAssessment\Observator;

class Observator {
	/**
	 * @var
	 */
	protected $firstname;

	/**
	 * @var
	 */
	protected $lastname;

	/**
	 * @var
	 */
	protected $login;

	/**
	 * @var
	 */
	protected $email;

	public function __construct($firstname, $lastname, $login, $email) {
		assert('is_string($firstname)');
		assert('is_string($lastname)');
		assert('is_string($login)');
		assert('is_string($email)');
	}

	public function getFirstname() {
		return $this->firstname;
	}

	public function getLastname() {
		return $this->lastname;
	}

	public function getFullname() {
		return $this->lastname.", ".$this->firstname;
	}

	public function getLogin() {
		return $this->login;
	}

	public function getEmail() {
		return $this->email;
	}
}