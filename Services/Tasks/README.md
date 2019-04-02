#Task Service

## Derived Tasks

If your component wants to add entries to the taksks list of a user it must do the following.

Add an entry in the `$provider` array under `Services/Tasks/DerivedTasks/classes/class.ilLearningHistoryProviderFactory.php`. It is planned to switch this to a future service discovery concept.

The class provided at this location MUST implement `Services/Tasks/DerivedTasks/interfaces/interface.ilDerivedTaskProviderFactory.php`. The constructor of this class MUST accept an instance of `ilTaskService` as the first argument.


Method `getProviders()` must return an array of objects that implement the `Services/Tasks/DerivedTasks/interfaces/interface.ilDerivedTaskProvider.php` interface

In this interface the method `getTasks($user_id)` must return all tasks fo a user. To create the entries in the `ilDerivedTask[]` array a factory provided by the service should be used.

```
$tasks[] = $this->derived()->factory()->task($title, $ref_id,
	$deadline, $starting_time);
```

# JF Decisions

12 Nov 2018

- General introduction of the service and derived task interface
- https://docu.ilias.de/goto_docu_wiki_wpage_4910_1357.html