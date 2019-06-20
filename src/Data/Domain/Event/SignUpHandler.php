<?php

/**
 * Interface EventSignUpHandler
 */
interface EventSignUpHandler
{
	/**
	 * EventSignUpHandler constructor.
	 */
	public function __construct();

	/**
	 * @param $command
	 */
	public function handle ($command):void;
}