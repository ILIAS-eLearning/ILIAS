<?php

namespace ILIAS\Messaging\Adapter\SimpleBus\Command;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware as SimpleBusMessageBusMiddleware;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware as CommandHandlerMiddlewareContract;


abstract class CommandHandlerMiddlewareAdapter implements SimpleBusMessageBusMiddleware, CommandHandlerMiddlewareContract {


}