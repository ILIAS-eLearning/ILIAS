<?php

namespace ILIAS\Messaging\Adapter\SimpleBus\Command;

use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware as SimpleBusMessageBusSupportingMiddleware;

class CommandBusWithMiddlewareSupportAdapter extends SimpleBusMessageBusSupportingMiddleware
{

}