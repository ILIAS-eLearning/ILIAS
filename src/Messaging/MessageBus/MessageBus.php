<?php

namespace ILIAS\Messaging\MessageBus;

interface MessageBus
{
	/**
	 * @param object $message
	 * @return void
	 */
	public function handle($message);
}