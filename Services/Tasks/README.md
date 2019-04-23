#Task Service API

## Derived Tasks

If your component wants to add entries to the taksks list of a user it must do the following.

Add an entry in the `$provider` array under `Services/Tasks/DerivedTasks/classes/class.ilLearningHistoryProviderFactory.php`. It is planned to switch this to a future general collector/provider/consumer pattern.

The class provided at this location MUST implement `Services/Tasks/DerivedTasks/interfaces/interface.ilDerivedTaskProviderFactory.php`. The constructor of this class MUST accept an instance of `ilTaskService` as the first argument.


Method `getProviders()` must return an array of objects that implement the `Services/Tasks/DerivedTasks/interfaces/interface.ilDerivedTaskProvider.php` interface

In this interface the method `getTasks($user_id)` must return all tasks fo a user. To create the entries in the `ilDerivedTask[]` array a factory provided by the service should be used.

```
$tasks[] = $this->derived()->factory()->task($title, $ref_id,
	$deadline, $starting_time);
```

### Custom Links

The title of a task will be linked with the repository object
by using `\ilLink::_getStaticLink`, if the task provides a valid `ref_id`.
In case a concrete `\ilDerivedTaskProvider` would like to define a custom URL for
it's tasks, you can use `\ilDerivedTask::withUrl` to retrieve a task with an URL
passed as method argument.

```php
$task = $task->withUrl('...');
``` 

# JF Decisions

12 Nov 2018

- General introduction of the service and derived task interface
- https://docu.ilias.de/goto_docu_wiki_wpage_4910_1357.html

# Metrics

## ILIAS 6.0.0 alpha, 4 Apr 2019

```
> phpmd Services/Tasks/ text codesize
clean

> phploc Services/Tasks

Directories                                          3
Files                                               20

Size
  Lines of Code (LOC)                             1282
  Comment Lines of Code (CLOC)                     443 (34.56%)
  Non-Comment Lines of Code (NCLOC)                839 (65.44%)
  Logical Lines of Code (LLOC)                     261 (20.36%)
    Classes                                        251 (96.17%)
      Average Class Length                          13
        Minimum Class Length                         1
        Maximum Class Length                        54
      Average Method Length                          3
        Minimum Method Length                        0
        Maximum Method Length                       29
    Functions                                        0 (0.00%)
      Average Function Length                        0
    Not in classes or functions                     10 (3.83%)

Cyclomatic Complexity
  Average Complexity per LLOC                     0.10
  Average Complexity per Class                    2.42
    Minimum Class Complexity                      1.00
    Maximum Class Complexity                     11.00
  Average Complexity per Method                   1.46
    Minimum Method Complexity                     1.00
    Maximum Method Complexity                     9.00

Dependencies
  Global Accesses                                    5
    Global Constants                                 0 (0.00%)
    Global Variables                                 4 (80.00%)
    Super-Global Variables                           1 (20.00%)
  Attribute Accesses                                85
    Non-Static                                      84 (98.82%)
    Static                                           1 (1.18%)
  Method Calls                                     222
    Non-Static                                     204 (91.89%)
    Static                                          18 (8.11%)

Structure
  Namespaces                                         0
  Interfaces                                         2
  Traits                                             0
  Classes                                           17
    Abstract Classes                                 0 (0.00%)
    Concrete Classes                                17 (100.00%)
  Methods                                           62
    Scope
      Non-Static Methods                            60 (96.77%)
      Static Methods                                 2 (3.23%)
    Visibility
      Public Methods                                59 (95.16%)
      Non-Public Methods                             3 (4.84%)
  Functions                                          2
    Named Functions                                  0 (0.00%)
    Anonymous Functions                              2 (100.00%)
  Constants                                          0
    Global Constants                                 0 (0.00%)
    Class Constants                                  0 (0.00%)
```
