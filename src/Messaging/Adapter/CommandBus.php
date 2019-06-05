<?php

namespace ILIAS\Messaging\Adapter;

use ILIAS\Messaging\Adapter\SimpleBus\Command\CommandBus as CommandBusAdapter;
use ILIAS\Messaging\Contract\Command\CommandBus as CommandBusContract;

class CommandBus extends CommandBusAdapter implements CommandBusContract {

}