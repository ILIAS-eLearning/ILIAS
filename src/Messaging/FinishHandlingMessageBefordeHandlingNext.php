<?php
namespace ILIAS\Messaging

use ILIAS\Messaging\Contract\Command;

class FinischHandlingMessageBefordeHandlingNextMiddleware extends FinishesHandlingMessageBeforeHandlingNextAdapter implements CommandHandlerMiddleware {

}