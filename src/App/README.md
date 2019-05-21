#Intention
For a long time I'm thinking about how we can create a more up-to-date architectural basis for ILIAS. We have more and more requirements regarding the interaction of components. 

The conversation with Richard Kless last week brought me to this final idea worked out here.

With this pull request I would like to point out a possible solution for the following problems. I'm shure it's not completed. I would be very happy if we discuss this together and adjust it continuously.

Problems
1. We have more and more use cases where we have to build views and interactions across several ILIAS Modules.
2. In ILIAS we have not a clear concept how a maintainer has to build the structure within his module. For Example 
2.1 it is a difficult task for a maintainer if he like to build an enrolment from his component to a course.
2.2 it is a diffcult taks for a maintainer if he like to show a table with certificates or course enromlents.
...
3. In ILIAS it is a extremly difficult task if I would like to understand which use cases a component covers.
4. We have more an more use cases where we should log changes in different modules. E.G. 
4.1 Who has made the permission changes on a ILIAS object
4.2 Who and when has enrolled a user to course
...
5. Sometime we need a little customizing of a workflow - but if there is no event. It's really a pain.
6. We have difficulties to find an train new developers for ILIAS because it's hard to learn how development in ILIAS works.
7. We can not refactore ILIAS in one step and we need a concpet to make this step by step.
8. Many of the current proccesses are hard to test.

#Proposal
1. We introduce the principle of cqrs, DDD and Event Sourcing. I d'like to explain it with an example!
2. We use parts of frame works which are etablished and make fun. We use them so, that we can them change if the will no longer be maintained once.
3. In cqrs the reading and writing side will be splitted. At least for the reading side I would be very pleased to use symfony/doctrine in several use cases.
4. We make this change step by step. It is possible to make this also step by step within a single ILIAS Module.


##Explanation / Example
Use Case: 
1. Displaying the members of a course.
2. Add a member to a course.

Software Structure

- src
	- App
		- CoreApp
			- Course
				- Domain
					- Comand
					- Entity
					- Repository
					- Service
				- Infrastrucure
					- Command Handler
					- Repository
						- Doctrine
						- SQL
						- ...
					- Resources
						- Doctrine
							- Entity
			- Big ILIAS Modul
				- Part 1
					- Domain
						- Comand
						- ...
					- Infrastrucure
						- Command Handler
						- ...	
				- Part 2
					- Domain
					
						
###Course
####Domain

####Entity
```
<?php

namespace ILIAS\App\CoreApp\Course\Domain\Entity;

/**
 * Course
 */
class Course
{
    /**
     * @var CourseAdmin[]
     */
    private $admins = [];

    /**
     * @var CourseMember[]
     */
    private $members = [];

    /**
     * @var int
     */
     private $objId;

    /**
     * @var string|null
     */
    private $title;
    
    //TODO construct
```

```
<?php
   
   namespace ILIAS\App\CoreApp\Course\Domain\Entity;
   
   use ILIAS\App\CoreApp\User\Domain\Entity\User;
   
   /**
    * CourseMember
    */
   class CourseMember
   {
   	/**
   	 * @var Course
   	 */
   	protected $course;
   
   	/**
   	 * @var User
   	 */
   	protected $user;
   	/**
   	 * @var int
   	 */
   	private $objId;
``` 
  	
#####Repository
```  
<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class CourseReadonlyRepository implements ReadOnlyRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;
	
	public function __construct(Repository $repository) {
    		$this->repository = $repository;
    	}
	...
``` 

```  
<?php
namespace ILIAS\App\CoreApp\Course\Domain\Repository;

use ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember;
use ILIAS\App\Infrasctrutre\Repository\Repository;

class CourseMemberReadonlyRepository implements ReadOnlyRepository
{
	/**
	 * @var Repository
	 */
	protected $repository;

	public function __construct(Repository $repository) {
		$this->repository = $repository;
	}

	public function find(): CourseMember
	{
		//TODO
	}

	/**
	 * @param int $obj_id
	 *
	 * @return CourseMember[]
	 */
	public function findAllByObjId(int $obj_id): array
	{
		return $this->repository->doFindByFields(['objId' => $obj_id]);
	}
}
``` 

#####Service
Example see Member

####Infrastructure
#####CommandHandler
Example see Member
#####Repository
######Doctrine
```
<?php
namespace ILIAS\App\CoreApp\Course\Infrastructure\Repository\Doctrine;

use ILIAS\App\CoreApp\Course\Domain\Repository\CourseMemberReadonlyRepository;
use ILIAS\App\Infrasctrutre\Persistence\Doctrine\AbstractDoctrineRepository;
use ILIAS\App\Infrasctrutre\Persistence\Doctrine\DoctrineEntityManager;

class CourseMemberEntityRepository extends AbstractDoctrineRepository {

	/**
	 * CourseMemberEntityRepository constructor.
	 *
	 * @param DoctrineEntityManager $em
	 */
	public function __construct($em)
	{
		/**
		 * @var DoctrineEntityManager
		 */
		$doc = $em->visit($this->getRepositoryXmlMetaDataConfiguration());

		parent::__construct($doc->getEntityManager(), 'ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember');
	}
```

#Beispielumsetzung
Services/Membership/classes/class.ilMembershipGUI.php
#####Resources
######Entity
```
<?xml version="1.0" encoding="utf-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
  <entity name="ILIAS\App\CoreApp\Course\Domain\Entity\CourseAdmin" table="obj_members" inheritance-type="SINGLE_TABLE">

    <id name="objId" type="integer" column="obj_id"/>
    <id name="usrId" type="integer" column="usr_id"/>

    <discriminator-column name="admin" type="smallint"/>
    <discriminator-map>
      <discriminator-mapping value="1" class="ILIAS\App\CoreApp\Course\Domain\Entity\CourseAdmin" />
    </discriminator-map>

    <many-to-one field="course" target-entity="Course">
      <join-column name="obj_id" referenced-column-name="obj_id" />
    </many-to-one>

    <one-to-one field="user" target-entity="ILIAS\App\CoreApp\User\Domain\Entity\User">
      <join-column name="usr_id" referenced-column-name="usr_id" />
    </one-to-one>

    <field name="blocked" type="boolean" column="blocked" nullable="false">
      <options>
        <option name="default">0</option>
      </options>
    </field>
    <field name="notification" type="boolean" column="notification" nullable="false">
      <options>
        <option name="default">0</option>
      </options>
    </field>
```    
###Member
####Domain
#####Command
```
<?php

namespace ILIAS\App\CoreApp\Member\Domain\Command;

class AddCourseMemberToCourseCommand {

	/**
	 * @var int
	 */
	private $obj_id;
	/**
	 * @var int
	 */
	private $user_id;


	public function __construct(int $obj_id, int $user_id) {
		$this->obj_id = $obj_id;
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->obj_id;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}
}
```
#####Entity
Example see course
#####Repository
Example see course
#####Service
```
<?php

namespace ILIAS\App\CoreApp\Course\Domain\Service;
use ILIAS\App\Domain\Service\WriteonlyService;
use ILIAS\App\CoreApp\Course\Domain\Command\AddMemberCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class CourseWriteonlyService implements WriteonlyService
{
	/** @var MessageBusInterface  */
	private $messageBus;

	public function __construct(
		MessageBusInterface $messageBus
	) {
		$this->messageBus = $messageBus;
	}

	public function addMember(int $obj_id,int $user_id)
	{
		$this->messageBus->dispatch(
			new AddMemberCommand($obj_id,$user_id)
		);
	}
}
```
####Infrastructure
####CommandHandler
```
<?php

namespace ILIAS\App\CoreApp\Member\Infrastructure\CommandHandler;

use ILIAS\App\CoreApp\Member\Domain\Repository\MemberWriteonlyRepository;
use ILIAS\App\CoreApp\Member\Domain\Command\AddCourseMemberToCourseCommand;

class AddCourseMemberToCourseCommandHandler
{
	/**
	 * @var  MemberWriteonlyRepository
	 */
	private $course_repository;

	public function __construct(MemberWriteonlyRepository $member_writeonly_repository)
	{
		$this->member_writeonly_repository = $member_writeonly_repository;
	}

	/**
	 * @param AddCourseMemberToCourseCommand $add_course_member_to_course_command
	 *
	 */
	public function __invoke(AddCourseMemberToCourseCommand $add_member_command)
	{
		$this->member_writeonly_repository->addParticipant($add_member_command->getObjId(),$add_member_command->getUserId());
	}
}
```
####Repository
Example see course
####Resources
Example see course


#ToDo
###zu PRüfen
Das deckt sich mit dem Entwurfsmuster CQS (Command Query Separation), das von Bertrand Meijer erdacht wurde. Es besagt, dass jede Funktion eines Objekts entweder als Command oder als Query entworfen sein soll.
###Validation Folder
https://medium.com/@developeruldeserviciu/ddd-usually-means-at-least-3-layers-application-services-domain-service-and-infrastructure-967e80403615
Event-Folder
Exception filder
###Kursrepositories aufteilen in Read und Write (siehe Member)
###folgendes ist zu kompliziert:
'$member_entity_repository = new MemberEntityRepository(DoctrineEntityManage'
'$bus = new MessageBus([                                                    
'		new CommandHandlerMessageMiddleware(new HandlersLocator($member_entity_repository)'                                                                      
'	$course_service = new MemberWriteonlyService($bus);'                       
' 	$course_service->addMember(ilObject::_lookupObjectId($_GET['ref_id']),292);'

###zusätzliches Beispiel mit MySQL-Repositories umsetzen.
###Events im Sinne von Erfolgsmeldungen ebenfalls im cqrs stil?
###Readme pro App
###Einfaches REST?
###CourseRepository->getMembers()



$crs_entity_repository = new CourseEntityRepository($entityManager);
$crs_repository = new CourseRepository($crs_entity_repository);
$crs_object = $crs_repository->find(ilObject::_lookupObjectId($_GET['ref_id']));
$crs_member_via_course = [];
foreach ($crs_object->getMembers() as $crs_members) {
$crs_member_via_course[] = $crs_members->getUser()->getLastname();
}

# Bibliography
* https://de.slideshare.net/_leopro_/clean-architecture-with-ddd-layering-in-php-35793127
* https://stefanoalletti.wordpress.com/2018/08/10/cqrs-is-easy-with-symfony-4-and-his-messanger-component/
* https://www.fabian-keller.de/blog/domain-driven-design-with-symfony-a-folder-structure
* https://github.com/msgphp/msgphp
* https://www.rabbitmq.com/tutorials/tutorial-one-php.html
* https://www.heise.de/developer/artikel/CQRS-neues-Architekturprinzip-zur-Trennung-von-Befehlen-und-Abfragen-1797489.html?seite=all
* https://beberlei.de/2012/08/18/oop_business_applications__command_query_responsibility_seggregation.html




#Weshalb
* DB-Schicht extrem einfach austauschbar.
* sämtliche Aktionen könnten geloggt werden da jede Aktion eindeutig und einmalig!
* Jede Aktion wird über einen Message-Bus geschickt. Dort könnte man beispielsweise per Plugin beliebig reinhooken!


#Install AMPQ
https://www.rabbitmq.com/download.html
https://www.rabbitmq.com/tutorials/tutorial-one-php.html