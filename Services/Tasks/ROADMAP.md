# Roadmap

## Short Term

...

## Mid Term

### Derived Tasks: Get rid of $provider array

The `$provider` array under `Services/Tasks/DerivedTasks/classes/class.ilLearningHistoryProviderFactory.php` should be removed by a general collector/provider/consumer pattern.

### Derived Tasks: Remove Task

It should be possible to remove tasks manually from the list, even if they are not fullfilled. This will need an ID concept and a repository to store which tasks have been removed.

## Long Term

...
