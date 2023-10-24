# Learning Progress

Note: This documentation may not be complete, but the points documented should (still) be correct. Reports of missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contributions via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories)
are greatly appreciated.

## Core concepts

The learning progress service in ILIAS consists of 3 core concepts:

- Change Event
- (Learning Progress) Status
- (Learning Progress) Marks

The other components like (object) statistics and session statistics which can be found in *Administration > Statistics and Learning Progress* are not part of this how-to.

### Change Event

Not to be confused with ILIAS event handling, the change event service keeps track of user activity in ILIAS repository objects. There are read and write events. The learning progress only utilizes **read events** which are recorded / updated for each "click" inside a repository object. Write events are mostly restricted to changes in object settings or new repository objects and are the basis for the "changed inside" messages in the repository object lists.

Read events mostly consist of 2 figures which are tracked for each repository object and user:

- read count (or "requests")
- spent seconds (or "time spent")

> For every "click" or request ILIAS calculates the time which has passed since the last request and depending on the administration setting "Max. Time Between Requests" will
>- add the time to the "spent seconds" figure of the current object if the time passed is **below** the threshold
>- increment the "read count" figure of the current object and ignore the time passed if it is **above** the threshold
> Both figures will also be added to all (current) parent objects.

*DB: "change_event"*

### LP status

The learning progress status is designed to make the different repository object types comparable regarding user activity and its result. There are several different ways a LP status is calculated - called LP modes - and those can be configured for most object types which support the learning progress feature.

A learning progress status has 4 possible values:

- "not attempted": the user has no recorded activity
- "in progress": the user has recorded activity but no result yet
- "completed": the user has completed the object
- "failed": the user has failed the object

Please keep in mind that "object" is not limited to repository object here but can also mean SCO, learning module chapter, learning objective and so on.

Each repository object type supports different modes of learning progress, e.g.

- Manual by Tutor
- Automatic by Collection of Objects
- Manual by Learner
- Test finished
and so on.

> Currently the learning progress does not support any notion of a final status. At anytime a user LP status may change for an object. On changing the learning progress mode for a repository object - which is always possible - all LP status for existing users are re-calculated.

The LP status calculation (including the optional percentage) has to be triggered by a repository object each time "something" changed that might result in a LP status change. That "something" depends on the current LP mode for that object. The most simple example would be the 1st read event or 1st click of a user inside an object which (most of the time) results in a status change from "not attempted" to "in progress".

See:

- `ilLearningProgress::_tracProgress()`
- `ilLPStatusWrapper::_updateStatus()`

Do **NOT** use `ilLPStatusWrapper::_refreshStatus()`, which will re-calculate the LP status for the complete object. Please refer to the `ilObjectLP::resetLPDataFor*`-methods on how to remove LP data properly. Each repository object type that supports learning progress has its own "connector" class which extends `ilObjectLP`.
 
Hint: there is a `LPStatus*`-class for each LP *mode*, do not get confused by this.

*DB: "ut_lp_marks" (status, status_changed, status_dirty, percentage)*

### Collections

A collection consists of 1-n sub-objects which can be repository objects, SCOs, learning modules or learning objectives. There is no way to discern this in the DB, it depends on the LP mode of the parent object.
The LP status of a collection is determined by the status of its sub-objects (and their optional groupings). Every time a LP status changes for an object every parent collection is updated accordingly. This can lead to chains of updates for nested collections.

*DB: "ut_lp_collections"*

### LP marks

This directly corresponds to the "edit"-form in the LP for single users. It is mostly used for "Manual by Tutor"-mode where the "completed"-flag translates to LP status "completed".

*DB: "ut_lp_marks" (completed, mark, u_comment)*

## Misc

The complex LP DB queries can currently all be found in `ilTrQuery`. This might change.

Do not call any `ilLPStatus*`-class directly, use `ilLPStatusWrapper`.

The LP status is a mixture of progress and success information. Those 2 concepts are kept separate in SCORM. We are aware of this, but in ILIAS the focus for the learning progress design is to keep the LP status consistent and comparable for all object types.

## Figures in LP statistics

- Access/Time Spent
  - For each "click" ILIAS measures the time which passed since the last "click". if it is below the threshold (administration > lp > "max. time between requests") it will be counted as the same request and the time difference will be added to "Time spent" (for the current object). If is bigger than the threshold the Access Number is incremented and the time difference is ignored.
  - Things are completely different for SCORM modules though, as the player is more or less an external black box (which does the access/time spent handling by itself).
  - Please keep in mind that access number and time spent of sub-items, e.g. objects in courses, will be added to their parent (in the LP statistics). Furthermore if you move sub-items to a different parent, the numbers might not add up at all.
- Percentage
  - This is decided by the specific test LP mode. "Test passed" seems to use points as basis (not number of questions).
- Last Status Change
  - "last access" is the last "click"/action in an object, "last status change" means the point in time when the LP status changed for the last time, e.g. from "in progress" to "completed".
- Total Time Online
  - This is the time spent logged into ILIAS regardless of object or context. The access/time spent logic also applies here (see above). We might discuss the presentation of this in the near future, currently we would favour to include it in the user management and remove it from the LP statistics.
- Last Login
  - This is the datetime of the last login whereas "last access" is updated on each "click" (and for each object).
- Working Time
  - This is test-specific and is not supplied by the learning progress.

## Permissions

- read_learning_progress: read learning progress **of other users**
  - This gives access to the LP data of others users in the LP statistics in the repository
    If a user can see his/her own LP status is determined by the administration setting "Accesible Personal Learning Progress".
- edit_learning_progress
  - This allows to edit the LP settings of a repository object and edit the LP data of object "members": comment, mark, completed.
- "See learning progress overview of other users" (Administration > LP)
  - This gives access to the tab "Users" in Personal Desktop > LP. Due to performance reasons we cannot use object permissions to determine the access.
