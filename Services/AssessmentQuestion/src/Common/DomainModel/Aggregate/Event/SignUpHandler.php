<?php

/**
 * Interface SignUpHandler
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface SignUpHandler
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