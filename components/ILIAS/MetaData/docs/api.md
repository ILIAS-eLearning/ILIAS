# Metadata API

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

The `Metadata` component offers an API with which the [Learning Object Metadata
(LOM)](lom_structure.md) of ILIAS objects can be read out, processed,
and manipulated. It can be obtained from the `DIC` via the method
`learningObjectMetadata`.

In the following, we will explain what functionality the API offers,
and how it can be used.

## Contents

1. [`read`](#read)
2. [`search`](#search)
3. [`manipulate`](#manipulate)
4. [`derive`](#derive)
5. [`deleteAll`](#deleteall)
6. [`paths`](#paths)
7. [`dataHelper`](#datahelper)
8. [`copyrightHelper`](#copyrighthelper)

## `read`

With `read`, one can read out the LOM of a specific ILIAS object.

When calling `read`, the object whose metadata one wants to read out
needs to be identified by a triple of IDs as explained [here](identifying_objects.md).
Optionally, one can in addition specify metadata elements via a [path](#paths).
In this case, not the whole metadata set is read out, but only the
elements on the path. If the path ends at an element that has
sub-elements, reading continues recursively.

> Beware that when restricting `read` to a path, filters on the path
are ignored. Further, if the path contains steps to super elements,
it is only followed down to the first element that the path returns
to (see [here](#paths) for details).

`read` returns a `Reader`, which can then be used to access the
values of different elements in the (partial) set, selected via [paths](#paths).
These values are returned as data objects, containing the actual value
as a string, and its data type (see [here](lom_structure.md) for details
on the data types in LOM). The order in which these values are returned
is consistent. To further process the values, see the [data
helper](#datahelper).

Note that the `Reader` returns null data objects for elements not
carrying any data, and when requesting the `firstData` of an element
that does not exist at all in the set of the current ILIAS object.

### Examples

To read out the title of a Course with `obj_id` 380, call `read` with
the [appropriate IDs](identifying_objects.md), and then `firstData`
with the right [path](#paths). Since this returns a data object, one
then has to extract the actual value with `value`:

````
$reader = $lom->read(380, 380, 'crs');
$title = $reader->firstData($lom->paths()->title())->value();
````

As mentioned above, `firstData` will return a null data object if the
requested element does not exist.

To read out all instances of an element, use `allData` instead of
`firstData`. For example, to read out all keywords of a chapter with
`obj_id` 2 in a Learning Module with `obj_id` 325:

````
$reader = $lom->read(325, 2, 'st');
$keywords = [];
foreach ($reader->allData($lom->paths()->keywords()) as $keyword_data) {
    $keywords[] = $keyword_data->value();
}
````

Note that the values of data the reader provides are (depending on
their type) in LOM-internal formats, which need to be transformed
before they can be used properly. See the [data helper](#datahelper)
for more details. For example, if one wants to have the typical
learning time of the Learning Module from above in a user-readable
format:

````
$reader = $lom->read(325, 325, 'lm');
$learning_time_data = $reader->firstData($lom->paths()->firstTypicalLearningTime());
$learning_time = $lom->dataHelper()->makePresentable($learning_time_data);
````

Lastly, it is possible to limit how much of the LOM is read out initially
via a path. For example, if one only wants to read out `title`, `descriptions`
and `keywords` (which are all subelements of `general`), the following will
avoid a few unnecessary queries to the database:

````
$path_to_general = $lom->paths()->custom()->withNextStep('general')->get();
$reader = $lom->read(380, 380, 'crs', $path_to_general);
$title = $reader->firstData($lom->paths()->title())->value();
````

See [here](#paths) for details on custom paths.

## `search`

`search` is used to find objects whose LOM matches some user-defined
specifications. When calling `search`, a `Searcher` is
returned. In the `Searcher`, search `Clauses` and `Filters` can be
assembled, and using them a search can be performed. In the search
results, objects are identified by a triple of ID as explained [here](identifying_objects.md).

`Clauses` can be obtained in the `ClauseFactory` available through the
`Searcher`. Basic `Clauses` consist of a [path](#paths) to a LOM
element, a search `Mode`, and a value to search for. Additionally, the
`Mode` can be negated. Searching with just the basic `Clause` then
finds all objects whose LOM sets have at least one element specified
by the path, and whose value fulfills the condition given by search `Mode`
and value (or does not fulfill the condition, in the case
of the `Mode` being negated).

The search does take into account path filters, with the exception
of those of type `INDEX`. For more information on paths, see [here](#paths).

>The search is not built to check the (non-)existence of elements
which do not carry any value. Searches of this nature will not give
accurate or reliable results.

Multiple `Clauses` can be joined logically into a single `Clause`
using `AND` or `OR` `Operators`. These joined `Clauses` then can further
be joined, such that arbitrarily complicated search criteria can be
assembled from the basic `Clauses`.

Further, `Clauses` can be negated. Negating a basic `Clause` will then
lead to a search that finds objects with LOM sets that  have **no**
elements that fulfill the conditions. Note that this can lead to different
results than negating the `Mode` of the basic `Clause`! Negating joined
`Clauses` will negate the whole assembled logical statement.

Searches will return a `RessourceID` object for each search result,
and each `RessourceID` identifies an object in ILIAS by a [triple of IDs](identifying_objects.md).

The `Searcher` can also generate `Filters`. These `Filters` can be passed
to the `execute` method in the `Searcher` in addition to a clause to restrict
the objects the search will return. Each `Filter` object carries the
same [triple of IDs](identifying_objects.md) as is returned by the search. Each ID can
either be a specific value, or a `Placeholder`. Using `Placeholders`, the
filter can be configured to allow either any value for an ID, or only allow
values that match the value of one of the other IDs. Multiple values in
the same filter are joined with a logical AND, and multiple filters in the same
search with a logical OR.

Finally, a limit and offset can also be applied to the search. Both parameters
can be set independently of each other. The order of results returned by
the search is consistent, they are ordered by their IDs. 

>The search was built to be versatile, and is as such not particulary
well optimized for any specific task. If you have a use case for the
search that performs especially poorly, feel free to report that
in the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a
possible improvement to the search via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

### Examples

To find all objects in ILIAS that have a keyword with value 'Great Content',
build an according basic `Clause` and pass it to `Searcher::search` with
limit and offset set to `null`:

````
$clause = $lom->search()->getClauseFactory()->getBasicClause(
    $lom->paths()->keywords(),
    Mode::EQUALS,
    'Great Content'
);

$results = $lom->search()->execute($clause, null, null);
````

By negating the search `Mode` in the basic `Clause` one can find e.g.
all objects in ILIAS that have a keyword that does not start with 'Great':

````
$clause = $lom->search()->getClauseFactory()->getBasicClause(
    $lom->paths()->keywords(),
    Mode::STARTS_WITH,
    'Great',
    true
);

$results = $lom->search()->execute($clause, null, null);
````

On the other hand, by negating the whole basic `Clause` one can find
e.g. all objects that have no keyword ending in 'Content'. Note that 
because a LOM set can have multiple keywords, this is different to
searching for all objects that have a keyword that does **not** end with
content! If an object has a keyword 'Great Content' and a keyword
'Great Styling', it would be returned in the second search but not in
the first.

````
$clause_factory = $lom->search()->getClauseFactory();
$clause = $clause_factory->getBasicClause(
    $lom->paths()->keywords(),
    Mode::ENDS_WITH,
    'Content'
);
$clause = $clause_factory->getNegatedClause($clause);

$results = $lom->search()->execute($clause, null, null);
````

Joining `Clauses` allows for more specific searches. The following finds
all objects with a keyword containing 'great', and with an author
'Dr. Doom':

````
$clause_factory = $lom->search()->getClauseFactory();
$keyword_clause = $clause_factory->getBasicClause(
    $lom->paths()->keywords(),
    Mode::CONTAINS,
    'great'
);
$author_clause = $clause_factory->getBasicClause(
    $lom->paths()->authors(),
    Mode::EQUALS,
    'Dr. Doom'
);
$clause = $clause_factory->getJoinedClauses(
    Operator::AND,
    $keyword_clause,
    $author_clause
);

$results = $lom->search()->execute($clause, null, null);
````

Joining clauses even further, one can find e.g. all objects with a
keyword containing 'great', and with an author 'Dr. Doom' or
'Dr. House':

````
$clause_factory = $lom->search()->getClauseFactory();
$keyword_clause = $clause_factory->getBasicClause(
    $lom->paths()->keywords(),
    Mode::CONTAINS,
    'great'
);
$doom_clause = $clause_factory->getBasicClause(
    $lom->paths()->authors(),
    Mode::EQUALS,
    'Dr. Doom'
);
$house_clause = $clause_factory->getBasicClause(
    $lom->paths()->authors(),
    Mode::EQUALS,
    'Dr. House'
);
$clause = $clause_factory->getJoinedClauses(
    Operator::AND,
    $keyword_clause,
    $clause_factory->getJoinedClauses(
        Operator::OR,
        $doom_clause,
        $house_clause
    )
);

$results = $lom->search()->execute($clause, null, null);
````

To find specifically all Courses with keyword 'Great Content', `Filters`
can be used to only search for objects which have `'crs'` as the type in
their [triple of IDs](identifying_objects.md). Since the other two parameters do not need
to be restricted, one can set them to `Placeholder::ANY`:

````
$clause = $lom->search()->getClauseFactory()->getBasicClause(
    $lom->paths()->keywords(),
    Mode::EQUALS,
    'Great Content'
);
$filter = $lom->search()->getFilter(
    Placeholder::ANY,
    Placeholder::ANY,
    'crs'
);

$results = $lom->search()->execute($clause, null, null, $filter);
````

Analogously, one can also search for a specific repository object and
its subobjects. To only find e.g. the Learning Module with `obj_id` 123
and its chapters and pages:

````
$filter = $lom->search()->getFilter(
    123,
    Placeholder::ANY,
    Placeholder::ANY
);

$results = $lom->search()->execute($clause, null, null, $filter);
````

Multiple filters are joined with a logical OR, so to search in two different
Learning Modules with `obj_id`s 123 and 456 simultaneously:

````
$filter_123 = $lom->search()->getFilter(
    123,
    Placeholder::ANY,
    Placeholder::ANY
);
$filter_456 = $lom->search()->getFilter(
    456,
    Placeholder::ANY,
    Placeholder::ANY
);

$results = $lom->search()->execute(
    $clause,
    null,
    null,
    $filter_123,
    $filter_456
);
````

`Filters` can also be used to only search repository objects, and not
their sub-objects. To this end, use placeholders to set the `sub_id` equal
to the `obj_id`:

````
$filter = $lom->search()->getFilter(
    Placeholder::ANY,
    Placeholder::OBJ_ID,
    Placeholder::ANY
);

$results = $lom->search()->execute($clause, null, null, $filter);
````

Alternatively, one can also filter for objects with `sub_id` equal to 0.
See [here](identifying_objects.md) for information on how repository objects are indentified
in the `Metadata` component.

Lastly, limit and offset can be used to e.g. sequentially search through
objects. The following will first fetch the first five results, then the
next five, and lastly all remaining results:

````
$first_five_results = $lom->search()->execute($clause, 5, null);
$next_five_results = $lom->search()->execute($clause, 5, 5);
$remaining_results = $lom->search()->execute($clause, null, 10);
````

## `manipulate`

With `manipulate`, one can edit an ILIAS object's LOM by deleting
elements, changing their value or adding new elements.

When calling `manipulate`, the object in question needs to be identified
by a triple of IDs as explained [here](identifying_objects.md). A `Manipulator` is returned.

The `Manipulator` offers a few `prepare` methods, with which the changes
one wants to make to the metadata can be collected. Upon calling
`execute`, all changes registered to the `Manipulator` are carried
out simultaneously.

- `prepareCreateOrUpdate`: The elements selected by a [path](#paths)
are set to be updated with the provided values. The order of LOM elements
is consistent, and the values are applied to the elements in order. If
there are less elements than provided values, new elements will be set
to be created according to the path to hold the leftover values.<br>
If one of the provided values is not valid for the data type of the
selected elements, or if it is not possible to add enough elements to the
LOM set to fit all values, an exception will be thrown (either by this method
or when calling `execute`). Make sure that you are not trying to give
multiple values to unique elements (see [here](lom_structure.md) for details).<br>
For further details on how the `Manipulator` works see [here](manipulator.md).
- `prepareForceCreate`: This behaves identically to the above, but will
always create new elements, and never update existing ones.<br>
The warning given above goes double here; if not enough of the requested
elements can be created, an exception will be thrown. We recommend only using
this method over `prepareCreateOrUpdate` when absolutely necessary, and
only for non-unique elements.
- `prepareDelete`: All elements selected by a [path](#paths) are set to
be deleted. All their sub-elements are recursively deleted as well.

### Examples

To update the title in the LOM metadata of a Course with `obj_id`
380, call `manipulate` with the [appropriate IDs](identifying_objects.md),
then `prepareCreateOrUpdate` with the right [path](#paths), and
finally `execute`:

````
$lom->manipulate(380, 380, 'crs')
    ->prepareCreateOrUpdate($lom->paths()->title(), 'new title')
    ->execute();
````

Note that adding a second value to `prepareCreateOrUpdate` would lead
to an exception. The manipulator would try to create a second `title` element
to hold the additional value, but this is not possible since `title`
is unique.

Where non-unique elements are concerned, arbitrarily many of them can
be manipulated at once.  For example, update the first two authors of
a chapter with `obj_id` 2 in a Learning Module with `obj_id` 325:

````
$lom->manipulate(325, 2, 'st')
    ->prepareCreateOrUpdate(
        $lom->paths()->authors(),
        'new first author',
        'new second author'
    )
    ->execute();
````

If only one or no authors exist, new ones will be added, and if more
than two authors exist the others will be left alone.

Sometimes it might be preferrable to just add an instance of an element,
without needing to consider what is already there. For this, `prepareForceCreate`
can be used. For example, if one wants to add an author to the end of
the list:

````
$lom->manipulate(325, 2, 'st')
    ->prepareForceCreate($lom->paths()->authors(), 'last author')
    ->execute();
````

Note that when creating or updating, the provided values need to be
in the correct format for the element that the path points to (see
[here](lom_structure.md) for details). The [data helper](#datahelper)
offers some methods to make this easier. For example, when one wants
to set the typical learning time of the Learning Module from above
to 3 hours:

````
$lom->manipulate(325, 325, 'lm')
    ->prepareCreateOrUpdate(
        $lom->paths()->firstTypicalLearningTime(),
        $lom->dataHelper()->durationFromIntegers(
            null,
            null,
            null,
            3,
            null,
            null
        )
    )
    ->execute();
````

Deleting elements has none of the subtleties discussed above. It
simply deletes all elements the path points to. For example, the
following with delete all authors of the chapter in the Learning
Module:

````
$lom->manipulate(325, 2, 'st')
    ->prepareDelete($lom->paths()->authors())
    ->execute();
````

If multiple actions need to be performed, they can be collected and
executed simultaneously. For example, this will update both
the title and the first description:

````
$lom->manipulate(380, 380, 'crs')
    ->prepareCreateOrUpdate($lom->paths()->title(), 'title')
    ->prepareCreateOrUpdate($lom->paths()->descriptions(), 'description')
    ->execute();
````

and this will completely replace the list of authors by deleting
the exisiting ones, and adding new authors:

````
$lom->manipulate(325, 2, 'st')
    ->prepareDelete($lom->paths()->authors())
    ->prepareForceCreate(
        $lom->paths()->authors(),
        'first new author',
        'second new author'
    )
    ->execute();
````

Further [below](#paths) we will explain that one can very granularly
control which elements will be selected by using custom paths. The
manipulator will try its best to respect the paths given
to it. For example, the following will update the first keywords that
is in English:

````
$path_to_english_keywords = $lom->paths()
                                ->custom()
                                ->withNextStep('general')
                                ->withNextStep('keyword')
                                ->withNextStep('language')
                                ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'en')
                                ->withNextStepToSuperElement()
                                ->withNextStep('string')
                                ->get();

$lom->manipulate(380, 380, 'crs')
    ->prepareCreateOrUpdate($path_to_english_keywords, 'keyword')
    ->execute();
````

Keywords with a different language (or without one) will be ignored,
and if no keyword with language English exists, it will be created
(including the `language` element).

Note that index (and ID) filters are respected when updating existing
elements, but not when creating new ones, as the manipulator has no
control over the index (or ID) of newly created elements.

The above is especially useful for updating vocabulary values, as
the validity of their value depends on their source. Currently, ILIAS
only supports the basic `LOMv1.0` vocabularies, but this might change
in the future. For example, to update the `structure` element:

````
$path_to_structure = $lom->paths()
                         ->custom()
                         ->withNextStep('general')
                         ->withNextStep('structure')
                         ->withNextStep('source')
                         ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'LOMv1.0')
                         ->withNextStepToSuperElement()
                         ->withNextStep('value')
                         ->get();

$lom->manipulate(380, 380, 'crs')
    ->prepareCreateOrUpdate($path_to_structure, 'atomic')
    ->execute();
````

The custom path ensures, that even when the `structure` element does
not already exist, it will be created with the right source.

## `derive`

`derive` can be used to derive a LOM set for a target from that of a
source. This encompasses copying between ILIAS objects and creating
a LOM set for an object from basic properties, depending on the chosen
type of source and target.

In the future, XML might be supported as source and target to also allow
import and export of LOM sets via the API.

When calling `derive`, a `SourceSelector` is returned. There,
either an ILIAS object can be identified as a source by a triple of 
IDs as explained [here](identifying_objects.md), or a LOM set can be created from basic
fields. A `Derivator` is returned, where an object can be chosen
analogously as the target.

When a target is chosen, the `Derivator` reads out the LOM set from the
source, and writes it to the target. Currently, the two use cases
are:

- **Creation:** When the target is an ILIAS object and title, description
and language are given as the source, a new LOM set is created for the
object containing the given data. Note that description and language are
optional, but title is not: if an empty title is given, it is replaced by
a placeholder. Any previous LOM of the target object is deleted before
copying.
- **Copying:** When both source and target are ILIAS objects, the `Derivator` creates
a LOM set for the target by copying the LOM of the source. Any previous
LOM of the target object is deleted before copying.

### Examples

To create a new LOM set for a course with `obj_id` 380, pass its title,
description and language as basic properties, and choose the course as
the target:

````
$lom->derive()
    ->fromBasicProperties(
        'title',
        'description',
        'en'
    )
    ->forObject(380, 380, 'crs');
````

To copy the LOM of a chapter with `obj_id` 2 in a Learning Module with
`obj_id` 325 to the course, choose those objects as target and source
with the appropriate IDs:

````
$lom->derive()
    ->fromObject(325, 2, 'st')
    ->forObject(380, 380, 'crs');
````

## `deleteAll`

`deleteAll` simply deletes all LOM of an ILIAS object, the object being
by identified by a triple of IDs as explained [here](identifying_objects.md).

Note that consistency of the LOM set is not checked before deletion,
all occurences of the given object will be scrubbed indiscriminately
from the LOM tables.

### Examples

To delete all LOM of a Course with `obj_id` 380, call `deleteAll` with
the [appropriate IDs](identifying_objects.md):

````
$lom->deleteAll(380, 380, 'crs');
````

or for a chapter with `obj_id` 2 in a Learning Module with `obj_id` 325:

````
$lom->deleteAll(325, 2, 'st');
````

## `paths`

Elements in LOM can not to be identified by name alone, but rather by
the path to them through the set from the root element. See [here](lom_structure.md)
for more details. Under `path`, one can find a collection of paths to
commonly used elements, as well as a `Builder` to create custom paths.

Paths consist of steps from the root element of LOM down to the element
one wants to specify. Elements are referred to by their name according
to the [LOM Standard](lom_structure.md). Note that the start of the path
is always implied to be the root, there is no need to specify `'lom'` as
the first step.

If one wants to select only a subset of the elements of that name with
a step (e.g. if one does not need all occurences of a non-unique element,
or one only wants to select an element if it fulfills a certain condition),
one can attach one or multiple filters to a step. Filters will be explained
in more detail below.

Lastly, steps can also lead to the super-elements of the current elements.
This is useful if one only wants to select elements that contain certain
sub-elements. Especially in combination with filters, this makes paths a
powerful tool for working with e.g. the `Reader` and `Manipulator`. See the
examples for possible ways to make use of this.

### Filters

There are three types of filters:

- `'id'`: Filters elements by their ID from the `Metadata`
tables. This is primarily used internally, the API does not expose
these IDs.
- `'index`: Filters elements by their index in order, starting from 0.
The ordering of elements is consistent. Non-integer or negative values
will select the last element.
- `'data'`: Filters elements by the value of their data. 

Adding multiple values to a single filter will filter for either value.
Multiple filters added to the same step are applied in the order they
were added. This is important especially for index filters, as elements
that were filtered out by a previous filter will not count towards the
index.

### Examples

The following gives the path to the title:

````
$lom->paths()
    ->custom()
    ->withNextStep('general')
    ->withNextStep('title')
    ->withNextStep('string')
    ->get();
````

Note that it does not stop at the element `title`, since that element
consists  not only of the `string`, but can also contain a `language` sub-element.
Many elements work similarly, often one needs to go one step
further than one would think to get to the data-carrying element. If
in doubt, consult the [LOM Standard](lom_structure.md).

If one wants to select only some instances of an element, filters are
the tool of choice:

````
// to the first contributor
$lom->paths()
    ->custom()
    ->withNextStep('lifeCycle')
    ->withNextStep('contribute')
    ->withNextStep('entity')
    ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
    ->get();
    
// to the first three contributors
$lom->paths()
    ->custom()
    ->withNextStep('lifeCycle')
    ->withNextStep('contribute')
    ->withNextStep('entity')
    ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0', '1', '2')
    ->get();
    
// to all contributors in the first contribute element
$lom->paths()
    ->custom()
    ->withNextStep('lifeCycle')
    ->withNextStep('contribute')
    ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
    ->withNextStep('entity')
    ->get();
    
// to the first contributor with value 'Tim'
$lom->paths()
    ->custom()
    ->withNextStep('lifeCycle')
    ->withNextStep('contribute')
    ->withNextStep('entity')
    ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'Tim')
    ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
    ->get();
    
// to the first contributor, but only if it has the value 'Tim' or 'Tom'
$lom->paths()
    ->custom()
    ->withNextStep('lifeCycle')
    ->withNextStep('contribute')
    ->withNextStep('entity')
    ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
    ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'Tim', 'Tom')
    ->get();
````

Note that one can have very close control over which elements the
path points to, depending on where one adds filters and in which order.

Similarly powerful is `withNextStepToSuperElement`: if used properly,
it can also act as a sort of filter, checking for the existence of 
particular sub-elements. For example, the following path leads to
the languages of keywords that have a string:

````
$lom->paths()
    ->custom()
    ->withNextStep('general')
    ->withNextStep('keyword')
    ->withNextStep('string')
    ->withNextStepToSuperElement()
    ->withNextStep('language')
    ->get();
````

This can then also be combined with data and index filters. The following
path points only to keywords in English:

````
$lom->paths()
    ->custom()
    ->withNextStep('general')
    ->withNextStep('keyword')
    ->withNextStep('language')
    ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'en')
    ->withNextStepToSuperElement()
    ->withNextStep('string')
    ->get();
````

## `dataHelper`

`dataHelper` is used to transform the data-values of LOM elements from
various LOM-internal formats into something more useful.

`makePresentable` returns the value of a data-object as something
that can be shown to the user: vocabulary values and languages will be
translated to the user's language, the user's preferred format will
be applied to datetimes, etc.

`datetimeToObject` and `datetimeFromObject` transfrom between the
LOM-internal datetime format and `DateTimeImmutable` objects. Note
that ILIAS discards the time part of datetimes in LOM.

`durationFromIntegers` and `durationToArray` transform between the
LOM-internal duration format, and the separate integers for years,
months, days, hours, minutes, seconds (in this order). Note that
there is a difference between a field not being filled and being 
filled with 0. In the former case, null is used instead of an integer.

`durationToSeconds` transforms a LOM-duration to seconds.
This is only a rough estimate, as LOM-durations do not have a start
date, so e.g.  each month is treated as 30 days.

`getAllLanguages` returns all languages that are valid in LOM in ILIAS
(see also [here](lom_structure.md#language-lang)) as pairs of value and
label. The value is what should be actually written into LOM, and the
label can be presented as is to the user.

## `copyrightHelper`

The LOM of an object also contains its copyright information. It 
can be found in `rights > description > string`. The information
might be in that element in plain text when it was manually written
there, but the element might also just contain an identifier referencing
one of the preset copyright licences on the installation (See
[here](copyrights.md) for details). Information about the licence
is then not contained directly in the LOM, and needs to be looked
up elsewhere. To make this process easier, as well as working with
copyright in general, `copyrightHelper`, offers a collection of helpful resources.

With `isCopyrightSelectionActive` one can find out whether copyright
selection is actually activated on the installation. If it is not,
all copyright is treated like manually entered.

`hasPresetCopyright`, `readPresetCopyright`, and `readCustomCopyright`
can be used to read out the copyright information of an object.
All three methods require a `Reader` for that object, see [above](#read).
If copyright selection is active, the copyright information
can either have been entered manually, or selected from the preset
copyright licences. `hasPresetCopyright` decides between the two
cases, and `readPresetCopyright` or `readCustomCopyright` then 
allow reading out the copyright respectively.

`readPresetCopyright` returns a `Copyright` object, which contains
further information about the preset copyright licence. It also
offers representations of the licence ready to be shown in the UI,
either as UI Components, or in reduced form as a string. Whenever
possible, the former should be used.

Note that if copyright selection is active, objects with no copyright
information in their LOM are treated like they are under the default
preset copyright licence. Conversely, if copyright selection is inactive,
all objects are treated like they have no copyright information.
The respective LOM fields can however still be accessed via the
[reader](#read).

`prepareCreateOrUpdateOfCopyrightFromPreset` and `prepareCreateOrUpdateOfCustomCopyright`
can be used in conjunction with a `Manipulator` (see [above](#manipulate))
to set the copyright information of an object. In the former, the identifier of the to-be-selected
preset copyright is required, in the latter the copyright information
can be given freely in the form of a string.

`getAllCopyrightPresets` and `getNonOutdatedCopyrightPresets` can be
used to find out which preset copyright licences are available on
the installation. Outdated licences should not be offered for selection
for new objects, but should not be disregarded for objects where they
were selected previously. When copyright selection is not active, these
methods will not return anything.

Lastly, `getCopyrightSearchClause` returns a search clause to be used
in a `Searcher` (see [above](#search)) to find objects for which one the given preset
copyright licences is selected. The licences are given via their
identifier. When copyright selection is active, searching with `getCopyrightSearchClause`
for the default copyright licence also yields objects without any
copyright information in their LOM.

### Examples

The copyright information of a Course with `obj_id` 380 can be
rendered for display in the GUI as follows:

````
$copyright_helper = $lom->copyrightHelper();
$reader = $lom->read(380, 380, 'crs');

$copyright = '';
if ($copyright_helper->hasPresetCopyright($reader)) {
    $copyright = $ui_renderer->render(
        $copyright_helper->readPresetCopyright($reader)->presentAsUIComponents()
    );
} else {
    $copyright = $copyright_helper->readCustomCopyright($reader);
}
````

When custom copyright information should not be displayed, this can
be shortened. `readPresetCopyright` returns a null object when no
preset copyright can be found, which plays nice with the UI rendering:

````
$copyright_helper = $lom->copyrightHelper();
$reader = $lom->read(380, 380, 'crs');

$copyright = $ui_renderer->render(
    $copyright_helper->readPresetCopyright($reader)->presentAsUIComponents()
);
````

When compiling a list of all available preset copyright licences,
e.g. for selection in a dropdown, one can proceed as follows:

````
if ($copyright_helper->isCopyrightSelectionActive()) {
    $copyrights_for_dropdown = [];
    foreach ($copyright_helper->getNonOutdatedCopyrightPresets() as $copyright) {
        $copyrights_for_dropdown[$copyright->identifier()] = $copyright->title();
    }
    $inputs['copyright'] = $ui_factory->input()->field()->select(
        'Copyright',
        $copyrights_for_dropdown
    );
}
````

If copyright selection is not active, the dropdown should 
not be offered at all, and outdated licences should not be offered
in the dropdown.

To then write the selected copyright to the object's LOM, use 
`prepareCreateOrUpdateOfCopyrightFromPreset`:

````
if ($copyright_helper->isCopyrightSelectionActive()) {
    $copyright_helper->prepareCreateOrUpdateOfCopyrightFromPreset(
        $lom->manipulate(380, 380, 'crs'),
        $data['copyright']
    )->execute();
}
````

`getCopyrightSearchClause` can for example be used together with
`getAllCopyrightPresets` to find all Media Objects with an outdated licence:

````
$copyright_helper = $lom->copyrightHelper();
$searcher = $lom->search();

$outdated_identifiers = [];
foreach ($copyright_helper->getAllCopyrightPresets() as $copyright) {
    if  (!$copyright->isOutdated()) {
        continue;
    }
    $outdated_identifiers[] = $copyright->identifier();
}

$results = [];
if (!empty($outdated_identifiers)) {
    $results = $searcher->execute(
        $copyright_helper->getCopyrightSearchClause(...$outdated_identifiers),
        null,
        null,
        $searcher->getFilter(Placeholder::ANY, Placeholder::ANY, 'mob')
    );
}
````

For more details on searching, see [above](#search).
