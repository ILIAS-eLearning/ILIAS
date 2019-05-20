<?php
require_once "/var/www/ilias/src/Modules/Course/Domain/Entity/ObjectData.php";
require_once "/var/www/ilias/src/Modules/Course/Domain/Entity/Courses.php";
require_once "/var/www/ilias/src/Modules/Course/Domain/Entity/CourseMember.php";

require_once "/var/www/ilias/src/Modules/Course/Domain/Entity/ObjMembers.php";

require_once "/var/www/ilias/src/Modules/User/Domain/Entity/User.php";
require_once "bootstrap.php";
/*
$object_data = new ObjectData();
$object_data->setTitle("sdf");

$entityManager->persist($object_data);

// actually executes the queries (i.e. the INSERT query)
$entityManager->flush();*/

/*
$object_data_repository = $entityManager->getRepository('ILIAS\Modules\Course\Domain\Entity\CourseMember');
$objects = $object_data_repository->findAll();
*/




foreach ($objects as $object) {
	/**
	 * @var ILIAS\Modules\Course\Domain\Entity\CourseMember $object
	 */

	echo $object->getUser()->getLastname();

	/*print_r($object);exit;
	echo sprintf("-%s\n", $object->getTitle());*/
}