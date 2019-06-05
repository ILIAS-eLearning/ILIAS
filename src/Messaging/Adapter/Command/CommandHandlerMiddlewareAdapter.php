<?php
namespace ILIAS\Messaging\Adapter\Command;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware as SimpleBusMessageBusMiddleware;
use ILIAS\Messaging\Contract\Command\CommandHandlerMiddleware as CommandHandlerMiddlewareContract;

interface CommandHandlerMiddlewareAdapter extends SimpleBusMessageBusMiddleware, CommandHandlerMiddlewareContract {

}