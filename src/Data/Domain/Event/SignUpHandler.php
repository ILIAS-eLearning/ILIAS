<?php

interface EventSignUpHandler
{
	public function __construct();

	public function handle ($command):void;
}