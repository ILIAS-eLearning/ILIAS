<?php
namespace ILIAS\App\Infrastructure\Broker;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class WorkerSender
{
	/* ... SOME OTHER CODE HERE ... */

	/**
	 * Sends an invoice generation task to the workers
	 *
	 * @param int $invoiceNum
	 */
	public function execute($invoiceNum)
	{
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('hello', false, true, false, false);

		$msg = new AMQPMessage($invoiceNum,
			array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
		);
		$channel->basic_publish($msg, '', 'invoice_queue');


		$channel->close();
		$connection->close();
	}
}