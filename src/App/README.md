# Bibliography
* https://de.slideshare.net/_leopro_/clean-architecture-with-ddd-layering-in-php-35793127
* https://stefanoalletti.wordpress.com/2018/08/10/cqrs-is-easy-with-symfony-4-and-his-messanger-component/
* https://www.fabian-keller.de/blog/domain-driven-design-with-symfony-a-folder-structure
* https://github.com/msgphp/msgphp
* https://www.rabbitmq.com/tutorials/tutorial-one-php.html
* https://www.heise.de/developer/artikel/CQRS-neues-Architekturprinzip-zur-Trennung-von-Befehlen-und-Abfragen-1797489.html?seite=all
* https://beberlei.de/2012/08/18/oop_business_applications__command_query_responsibility_seggregation.html

#Install AMPQ
https://www.rabbitmq.com/download.html
https://www.rabbitmq.com/tutorials/tutorial-one-php.html

#Weshalb
* DB-Schicht extrem einfach austauschbar.
* sämtliche Aktionen könnten geloggt werden da jede Aktion eindeutig und einmalig!
* Jede Aktion wird über einen Message-Bus geschickt. Dort könnte man beispielsweise per Plugin beliebig reinhooken!


#ToDo
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
Funktioniert nicht: 
$crs_entity_repository = new CourseEntityRepository($entityManager);
$crs_repository = new CourseRepository($crs_entity_repository);
$crs_object = $crs_repository->find(ilObject::_lookupObjectId($_GET['ref_id']));
$crs_member_via_course = [];
foreach ($crs_object->getMembers() as $crs_members) {
$crs_member_via_course[] = $crs_members->getUser()->getLastname();
}