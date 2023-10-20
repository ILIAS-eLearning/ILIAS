# Metadata API

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

`Services\Metadata` offers an API with which the [Learning Object Metadata
(LOM)](lom_structure.md) of ILIAS objects can be read out, processed,
and manipulated. It can be obtained from the `DIC` via the method
`learningObjectMetadata`.

The API offers four different sub-services. In the following, we will
explain what they offer and how they can be used.

## `read`

With `read`, one can read out the LOM of a specific ILIAS object.

When calling `read`, the object whose metadata one wants to read out
needs to be identified by a triple of IDs as explained [here](identifying_objects.md).
Optionally, one can also specify metadata elements via a [path](#paths).
In this case, not the whole metadata set is read out, but only the
elements on the path along with all sub-elements of its last element.

`read` returns a `Reader` object, which can then be used to access the
values of different elements in the (partial) set, selected via [paths](#paths).
These values are returned as data objects, containing the actual value
as a string, and its data type (see [here](lom_structure.md) for details
on the data types in LOM). The order in which these values are returned
is consistent. To further process the values, see the [data
helper](#datahelper).

Note that the `Reader` returns null data objects for elements not
carrying any data according to the [LOM standard](lom_structure.md), and when 
requesting the `firstData` of an element that does not exist at all
in the set of the current ILIAS object.

### Examples

To read out the title of a course with `obj_id` 380, call `read` with
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

## `manipulate`

With `manipulate`, one can edit an ILIAS object's LOM by deleting
elements, changing their value or adding new ones.

When calling `manipulate`, an object needs to be identified by a triple
of IDs as explained [here](identifying_objects.md). A `Manipulator`
object is returned.

The `Manipulator` offers a few `prepare` methods, with which changes
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
LOM set to fit all values, an error will be thrown (either by this method
or by `execute`). Make sure that you are not trying to give multiple 
values to unique elements (see [here](lom_structure.md) for details).<br>
For further details on how the `Manipulator` works see [here](manipulator.md).
- `prepareForceCreate`: This behaves identically to the above, but will
always create new elements, and never update existing ones.<br>
The warning given above goes double here; if not enough of the requested
elements can be created, an error will be thrown. We recommend only using
this method over `prepareCreateOrUpdate` when absolutely necessary, and
if at all possible only for non-unique elements.
- `prepareDelete`: All elements selected by a [path](#paths) are set to
be deleted, along with their sub-elements.

### Examples

To update the title in the LOM metadata of a course with `obj_id`
380, call `manipulate` with the [appropriate IDs](identifying_objects.md),
then `prepareCreateOrUpdate` with the right [path](#paths), and
finally `execute`:

````
$lom->manipulate(380, 380, 'crs')
    ->prepareCreateOrUpdate($lom->paths()->title(), 'new title')
    ->execute();
````

Note that adding a second value to `prepareCreateOrUpdate` would lead
to an error. The manipulator would try to create a second `title` element
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

Lastly, steps can also lead to the super-elements (or parent) of
the current elements. This is useful if one only wants to select elements
that contain certain sub-elements. Especially in combination with filters,
this makes paths a powerful tool for working with the `Reader` and
`Manipulator`. See the examples for possible ways to make use of this.

#### Filters

There are three types of filters:

- `'id'`: Filters elements by their ID from the `Services\Metadata`
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
consists  not only of the `string`, can also contain a `language` sub-element.
Many elements work similarly, often times one needs to go one step
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
various LOM-internal formats into more useful forms.

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

Lastly, `durationToSeconds` transforms a LOM-duration to seconds.
This is only a rough estimate, as LOM-durations do not have a start
date, so e.g.  each month is treated as 30 days.
