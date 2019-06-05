<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Command;

use ILIAS\Messaging\Contract\Command\CommandHandler;
use ArangoDBClient;

use ArangoDBClient\Autoloader;
use ArangoDBClient\Collection;
use ArangoDBClient\CollectionHandler;
use ArangoDBClient\DocumentHandler;
use ArangoDBClient\Document;

class addCourseMemberToCourseCommandHandler implements CommandHandler {

	/**
	 * @var CourseRepository
	 */
	private $course_repository;


	public function __construct() {
		//require "/var/www/ilias/libs/composer/vendor" . DIRECTORY_SEPARATOR . 'autoload.php';

		$connectionOptions =array(
			// server endpoint to connect to
			\ArangoDBClient\ConnectionOptions::OPTION_ENDPOINT => 'tcp://127.0.0.1:8529',
			// authorization type to use (currently supported: 'Basic')
			\ArangoDBClient\ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
			// user for basic authorization
			\ArangoDBClient\ConnectionOptions::OPTION_AUTH_USER => 'root',
			// password for basic authorization
			\ArangoDBClient\ConnectionOptions::OPTION_AUTH_PASSWD => 'root',
			// connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
			\ArangoDBClient\ConnectionOptions::OPTION_CONNECTION => 'Close',
			// connect timeout in seconds
			\ArangoDBClient\ConnectionOptions::OPTION_TIMEOUT => 3,
			// whether or not to reconnect when a keep-alive connection has timed out on server
			\ArangoDBClient\ConnectionOptions::OPTION_RECONNECT => true,
			// optionally create new collections when inserting documents
			\ArangoDBClient\ConnectionOptions::OPTION_CREATE => true,
			// optionally create new collections when inserting documents
			\ArangoDBClient\ConnectionOptions::OPTION_UPDATE_POLICY => \ArangoDBClient\UpdatePolicy::LAST,
		);

		// open connection
		$connection = new \ArangoDBClient\Connection($connectionOptions);

		ArangoDBClient\Autoloader::init();
		//	127.0.0.1:8529
		//$db =  $connection->setDatabase('ilias');


		$collectionName = "Elements";
		$collection = new Collection($collectionName);
		$collectionHandler = new CollectionHandler($connection);
		if ($collectionHandler->has($collectionName)) {
			$collectionHandler->drop($collectionName);
		}
		$collectionId = $collectionHandler->create($collection);
		// Add new documents
		$documentHandler = new DocumentHandler($connection);
		$document1 = new Document();
		$document1->set("now", date('Y-m-d H:i:s'));
		$document2 = new Document();
		$document2->set("created", ['during' => ['VilniusPHP', 'event']]);
		$documentId1 = $documentHandler->save($collectionName, $document1);
		$documentId2 = $documentHandler->save($collectionName, $document2);
		// Read them all
		$documents = $collectionHandler->all($collectionId);
		foreach ($documents as $document) {
			/** @var $document Document */
			echo '<h2>' . htmlspecialchars($document->getInternalId()) . '</h2>';
			echo "<div><b>Revision:</b> {$document->getRevision()}</b>";
			echo '<pre>' . json_encode($document->getAll(), JSON_PRETTY_PRINT) . '</pre>';
		}

		/*$this->course_repository = \ArangoDBClient\Edge\createFromArrayWithContext($data, $options) : \ArangoDBClient\Edge
		//TODO
		/*
	$mongo_manager = new  MongoDB\Driver\Manager("mongodb://localhost:27017");

	$collection = new MongoDB\Collection($mongo_manager, "ilias", "course", array());


	$this->course_repository = new InMemoryCourseRepository(new InMemoryEventStore(),new CourseProjection($collection));
		*/
	}


	/**
	 * @param addCourseMemberToCourseCommand $command
	 */
	public function handle($command) {
		$course = $this->course_repository->get($command->course_id);

		$course->addCourseMember($command->user_id);
		$this->course_repository->add($course);
	}
}
