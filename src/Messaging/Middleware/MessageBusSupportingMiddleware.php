<?php

namespace ILIAS\Messaging\Middleware;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware as SBusMessageBusSupportingMiddleware;

class MessageBusSupportingMiddleware extends MessageBusSupportingMiddlewareDecorator {

	public function __construct() {
		parent::__construct(new SBusMessageBusSupportingMiddleware());
	}
}