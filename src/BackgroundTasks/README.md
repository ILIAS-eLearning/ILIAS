# Background Tasks for ILIAS

This namespace provides a small framework for creating ILIAS specific tasks that can be executed 
synchronized and unsynchronized.

Overview and Glossary for this namespace:


| Name | Description | Example |
|------|-------------|---------|
| Value | A value contains some serializable value that can be used as input or output of a task. A Value is a PHP-Class that implements Value. | IntegerValue, FilePathValue |
| Type | Values have a type. Tasks have input types and output types. Those are lended from the Types namespace. They are used to check for compatibility during task composition | ListType(SingleType(IntegerValue)), SingleType(ListValue) |
| Task | A task is an operation that is executed and can be combined with other tasks | DownloadFile, ZipFolders |
| Job |  A Task, which can be run without any interaction with the user, such as zipping files or just collecting some data | ZipFolders |
| UserInteraction | Is a task that needs the user's input | DownloadFile |
| Option | Describes an option that the user can choose in a user interaction | DownloadFileOption, DiscardFileOption |
| Bucket | Contains a composition of tasks and meta information about the tasks (name, current task, percentage, user id, etc.) | BasicBucket |
| BucketMeta | Contains the meta information of a composition of tasks. This is a lightweight version of Bucket that does NOT contain the tasks itself but only its Meta | BasicBucketMeta |
| ExceptionHandler | Describes the behaviour what happens if a task goes wrong. | Delete the Task, Don't delete the task, show message or don't show a message |
| Observer | Can be given to a TaskManager and will be triggered for each action that happens during task execution | PersistingObserver (writes the state of the bucket into the database on each step). NonPersistingObserver (writes the state into the bucket object but does NOT persist it into the database). |
| Persistence | Saves and loads buckets and its tasks into the database | BasicPersistence |
| TaskManager | Can run a task or continue a task that is in the state UserInteraction | BasicTaskManager, AsyncTaskManager |


Task Definition
---------------
If you want to define your own Tasks the only thing you have to do is to implement the right 
interface, resp. to extend the right abstract class.

For defining a new value implement the interface Value. The easiest way to do so is to extend 
ILIAS\BackgroundTasks\Implementation\Values\AbstractValue.
  
Same goes for implementing a new Job: Implement the interface Job or extend AbstractJob. A very 
simple example can be found here: BackgroundTasks/Implementation/Tasks/PlusJob.php.

Implementing a new UserInteraction has a very similar approach to the implementing a new job. The 
only difference is that you have to supply possible Options a user can choose and react accordingly. 
See an example here: BackgroundTasks/Implementation/Tasks/DownloadInteger.php

Important: Your constructor for all three cases cannot take any arguments besides the Services that 
are contained in the $DIC. The Services will be injected if correctly type hinted.

```php
[...]
public function __constrcuct(ilDBInterface $ilDB) {
	$this->db = $ilDB;
}
[...]
```

Task Composition & Scheduling
---------------
If you have defined the tasks you need you can combine them, put them in a bucket and schedule the 
bucket. Whether the task is executed in the same request or is put in the background will depend 
on the ILIAS configuration.

```php
global $DIC;

$factory = $DIC->backgroundTasks()->taskFactory();
$taskManager = $DIC->backgroundTasks()->taskManager();

// We create a bucket that will be scheduled and set the user that should observe the bucket.
$bucket = new BasicBucket();
$bucket->setUserId($DIC->user()->getId());

// Combine the tasks. This will create a task that looks like this: (1 + 1) + (1 + 2). 
$a = $factory->createTask(PlusJob::class, [1, 1]); 
// Note the integer 1 will automatically be wrapped in a IntegerValue class. All scalars can be 
// wrapped automatically.
// The above is the same as:
// $a = $factory->createTask(PlusJob::class, [new IntegerValue(1), new IntegerValue(1)]);
$b = $factory->createTask(PlusJob::class, [1, 2]);
$c = $factory->createTask(PlusJob::class, [$a, $b]);

// The last task is a user interaction that allows the user to download the result calculated above.
/** @var DownloadInteger $userInteraction */
$userInteraction = $factory->createTask(DownloadInteger::class, [$c]);

// We put the combined task into the bucket and add some description
$bucket->setTask($userInteraction);
$bucket->setTitle("Some calculation.");
$bucket->setDescription("We calculate 5!");

// We schedule the task.
$taskManager->run($bucket);

// We redirect somewhere.
$this->ctrl->redirect($this, "showContent");
```


Task Execution
---------------
 Usually you do NOT need to execute the task yourself but only need to use the run method of the 
 TaskManager and let the task manager decide on whether to run the task synchroniously or 
 asynchroniously.
 
 If you really want to force to execute a task directly you can use:
 ```php
$factory = $DIC->backgroundTasks()->taskFactory();
// We need to construct the synchronous task manager. The task manager in the $DIC may be 
// asynchronous.
$taskManager = new BasicTaskManager($DIC->backgroundTasks()->persistence());
$persistence = $DIC->backgroundTasks()->persistence();

$task = $factory->createTask(PlusJob::class, [1, 1]); 
$observer = new MockObserver();

$value = $taskManager->executeTask($task, $observer);
echo $value->getValue(); // will echo 2.
```
 
