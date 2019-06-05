<?php

require __DIR__ . '/../../../../libs/composer/vendor/autoload.php';

//require '/../../../../libs/composer/vendor/arangodb-php/lib/ArangoDBClient/autoloader.php';
\ArangoDBClient\Autoloader::init();

/*
class AddCourseMemberServiceTest extends \PHPUnit_Framework_TestCase
{
	private $service;

	public function setUp()
	{
		$this->service = new AddCourseMemberServiceTest(new InMemoryPostRepository());
	}
	// ...
}*/


//TODO
/*
$mongo_manager = new  MongoDB\Driver\Manager("mongodb://localhost:27017");
$collection = new \MongoDB\Collection($mongo_manager, "ilias", "course", array());
*/
use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Command\addCourseMemberToCourseCommand;

$command_bus = new CommandBusBuilder();

$command_bus->handle(new addCourseMemberToCourseCommand(2,56));




$test = new CourseRepository(new InMemoryEventStore(),new CourseProjection($collection));

print_r($test->get(3)->getRecordedEvents());
print_r($test->get(3)->hasChanges());

print_r($collection->findOne(['course_id' => '2']));

print_r($test->get(3));



/**
 * Fully qualified class name of an object, without a leading backslash
 * @param object|string $object
 * @return string
 */
function fqcn($object)
{
	if (is_string($object)) {
		return str_replace('.', '\\', $object);
	}
	if (is_object($object)) {
		return trim(get_class($object), '\\');
	}
	throw new \InvalidArgumentException(sprintf("Expected an object or a string, got %s", gettype($object)));
}
/**
 * Canonical class name of an object, of the form "My.Namespace.MyClass"
 * @param object|string $object
 * @return string
 */
function canonical($object)
{
	return str_replace('\\', '.', fqcn($object));
}
/**
 * Underscored and lowercased class name of an object, of the form "my.mamespace.my_class"
 * @param object|string $object
 * @return string
 */
function underscore($object)
{
	return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', canonical($object)));
}
/**
 * The class name of an object, without the namespace
 * @param object|string $object
 * @return string
 */
function short($object)
{
	$parts = explode('\\', fqcn($object));
	return end($parts);
}
/**
 * Returns an array of CONSTANT_NAME => contents for a given class
 * @param string $className
 * @return string[]
 */
function constants($className)
{
	return (new \ReflectionClass($className))->getConstants();
}