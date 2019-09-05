<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\CallableObjective;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;


/**
 * This encapsulate one database update step which is a method on some
 * ilDatabaseUpdateSteps-object.
 *
 * This is a close collaborator of ilDatabaseUpdateSteps and should most probably
 * not be used standalone.
 */
class ilDatabaseUpdateStep extends CallableObjective {
	/**
 	 * @var	string
	 */
	protected $class_name;

	/**
	 * @var string
	 */
	protected $method_name;

	public function __construct(
		ilDatabaseUpdateSteps $parent,
		string $method_name,
		Objective ...$preconditions
	) {
		$this->class_name = get_class($parent);
		$this->method_name = $method_name;
		parent::__construct(
			function (Environment $env) use ($parent, $method_name) {
				$db = $env->getResource(Environment::RESOURCE_DATABASE);
				call_user_func([$parent, $method_name], $db); 
			},
			"Database update step {$this->class_name}::{$this->method_name}",
			false,
			...$preconditions
		);
	}


	/**
	 * @inheritdocs 
	 */
	public function getHash() : string {
		return hash(
			"sha256",
			$this->class_name."::".$this->method_name
		);
	}
}
