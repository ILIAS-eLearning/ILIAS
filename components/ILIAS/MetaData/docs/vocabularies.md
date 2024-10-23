# Vocabularies

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

To make LOM of objects in ILIAS more useful, some LOM elements come with
pre-defined vocabularies to restrict the values of those elements to a
fixed list. Some of those vocabularies are defined in the LOM standard, but
it is also possible to configure the available vocabularies on an installation
in the [Administration](#administration).

Vocabularies consist of a list of values, optionally with labels. The values
are what is actually persisted and written to XML exports of the metadata,
and labels are human-readable translations of the values displayed in the UI.

## Handling of foreign values

It cannot always be ensured that all LOM for every object in ILIAS conforms
to the configured vocabularies. LOM elements might acquire foreign values e.g.
when an object is imported, or when vocabularies are deleted.

To make sure that no data is lost, these foreign values are never ignored
or deleted. When editing the LOM of an object in the full
editor, these values are flagged as coming from an unknown vocabulary.

## Administration

In the 'Vocabularies'-tab of the Metadata Administration, an overview of
all currently installed vocabularies and their current configuration 
is shown. Further, the option to [import](#import) additional vocabularies
from a file is offered.

The vocabularies are categorized in four types, according to
which LOM elements they apply to and where they come from:

- **Standard**: preset vocabularies defined in the LOM standard, see
  [below](#standard-lom-vocabularies) for details.
- **Copyright**: [preset copyrights](copyrights.md) are treated like a
  vocabulary, see [below](#copyright) for details.
- **Controlled Selection**: previously imported vocabularies applied
  to elements of type [vocabulary value](lom_structure.md#vocabulary-source-vocab_source).
- **Controlled Text**: previously imported vocabularies applied
  to elements of type [string](lom_structure.md#string-string).

### Active

Inactive vocabularies are only shown in the Administration, but are
ignored everywhere else: their values are not shown in the selection
lists in the full LOM editor, and if one of their values is already
selected, it is flagged as coming from an unknown vocabulary.
In addtion, values from an inactive vocabulary are shown as is in the
UI (both in the LOM editor and elsewhere), and not translated to their
labels.

### Custom Input Allowed

'Custom Input Allowed' is not applicable to vocabularies of type 'Standard'
or 'Controlled Selection', but only to vocabularies for elements
of type [string](lom_structure.md#string-string). For these elements,
the full LOM editor usually allows free text input.

When vocabularies are active for such elements, the editor also offers
a corresponding selection list, by default in addition to
the free text input. However, when at least one of the vocabularies of
such an element disallows custom inputs, **only** the selection list is
offered.

### Actions

In the 'Vocabualries' Administration, actions are offered to change the
vocabularies' properties, to delete them, and to show their full list
of values. Not all actions are offered for all vocabularies: of course,
a vocabulary can only be activated if it is inactive, and can only be set
to allow custom inputs if it currently disallows them (and vice versa).
There are also further, type-specific restrictions summarized in the
table below.

|                           |   Standard    | Copyright | Controlled Selection | Controlled Text |
|:--------------------------|:-------------:|:---------:|:--------------------:|:---------------:|
| **Delete**                |      no       |    no     |    maybe **(*)**     |       yes       |
| **Deactivate**            | maybe **(*)** |    no     |    maybe **(*)**     |       yes       |
| **Activate**              |      yes      |    no     |         yes          |       yes       |
| **Disallow Custom Input** |      no       |    no     |          no          |       yes       |
| **Allow Custom Input**    |      no       |    no     |          no          |       yes       |
| **Show All Values**       |      yes      |    yes    |         yes          |       yes       |

**(*)**: The action is only available if there is at least one other active vocabulary for the same element.

### Import

Additional vocabularies can be imported from XML files. These files must
be in a specific format, for example:

````
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>educational</step>
            <step>difficulty</step>
            <step>value</step>
        </pathToElement>
    </appliesTo>
    <source>Test</source>
    <values>
        <value label="Hellish">hell</value>
        <value label="Absolutely Brutal">brutal</value>
        <value label="Soul-Crushing">no_soul</value>
    </values>
</vocabulary>
````

The file above will add a vocabulary for the element `educational > difficulty > value`,
with source `Test` and three labelled values. The label-attributes are optional,
and can be omitted when the values are already human-readable.

Before a vocabulary is created, a validation is performed to make sure
that the vocabulary conforms to the restrictions laid out [below](#controlled-vocabularies).

Some vocabularies only apply when a different LOM element than the one
the vocabulary applies to carries a certain value. Such a relationship
can be expressed with an optional `condition` XML child-element in `appliesTo`.
See below for a vocabulary for `lifeCycle > contribute > entity` where 
the associated `contribute > role > value` carries the value `publisher`.
Note that this can only be applied to specific elements, and then only with
specific conditions, see [here](#applicable-elements) for details.

````
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>lifeCycle</step>
            <step>contribute</step>
            <step>entity</step>
        </pathToElement>
        <condition value="publisher">
            <pathToElement>
                <stepToSuper/>
                <step>role</step>
                <step>value</step>
            </pathToElement>
        </condition>
    </appliesTo>
    <source>Test</source>
    <values>
        <value>publisher1</value>
        <value>publisher2</value>
        <value>publisher3</value>
        <value>publisher4</value>
    </values>
</vocabulary>
````

For a vocabulary for `technical > requirement > orComposite > name > value`
where the associated `orComposite > type > value` carries the value `operating system`
(and analogously for the value `browser`), the `appliesTo` XML element
would be:

````
<appliesTo>
    <pathToElement>
        <step>technical</step>
        <step>requirement</step>
        <step>orComposite</step>
        <step>name</step>
        <step>value</step>
    </pathToElement>
    <condition value="operating system">
        <pathToElement>
            <stepToSuper/>
            <stepToSuper/>
            <step>type</step>
            <step>value</step>
        </pathToElement>
    </condition>
</appliesTo>
````

## Controlled Vocabularies

Vocabularies of type 'Controlled Text' and 'Controlled Selection' are
those that have previously been [imported](#import) on the installation.
Which of the two types they belong to depends on the LOM element they
apply to, see [below](#applicable-elements) for details.

Generally, any list of values is permissible for such a vocabulary, and every
value can optionally have a label. If a value does not have a label, the 
value itself is displayed in the UI. However, a few restrictions apply:
a vocabulary cannot contain no values, and multiple vocabularies of type
'Controlled Text' or 'Controlled Selection' for the same LOM element cannot
contain the same value. Further, the source of these vocabularies
cannot be `LOMv1.0`.

#### Applicable Elements

Controlled vocabularies can be applied to almost all elements of type
[vocabulary value](lom_structure.md#vocabulary-source-vocab_source),
and a selection of elements of type [string](lom_structure.md#string-string),
for a full list see below.

Excluded of the vocabulary value elements are `lifeCycle > contribute > role > value`,
`metaMetadata > contribute > role > value` and `technical > requirement > orComposite > type > value`,
in order to exclude misconfigurations.

Type vocabulary value:

- `general > structure > value`
- `general > aggregationLevel > value`
- `lifeCycle > status > value`
- `technical > requirement > orComposite > name > value` where
  `orComposite > type > value` is `operating system`
- `technical > requirement > orComposite > name > value` where
  `orComposite > type > value` is `browser`
- `educational > interactivityType > value`
- `educational > learningResourceType > value`
- `educational > interactivityLevel > value`
- `educational > semanticDensity > value`
- `educational > intendedEndUserRole > value`
- `educational > context > value`
- `educational > difficulty > value`
- `rights > cost > value`
- `rights > copyrightAndOtherRestrictions > value`
- `relation > kind > value`
- `classification > purpose > value`

Type string:

- `general > coverage > string`
- `general > identifier > catalog`
- `lifeCycle > contribute > entity` where
  `contribute > role > value` is `publisher`
- `metaMetadata > identifier > catalog`
- `metaMetadata > metadataSchema`
- `technical > otherPlatformRequirements > string`
- `technical > format`
- `educational > typicalAgeRange > string`
- `classification > keyword > string`
- `classification > taxonPath > source > string`
- `classification > taxonPath > taxon > entry > string`

## Copyright

If ['Copyright Selection'](copyrights.md) is activated, the available
copyrights are treated as a vocabulary for the element
`rights > description > string`. Internal identifiers for the copyright
presets are used as the values, and their titles as labels. The source
of this vocabulary is `ILIAS`.

## Standard LOM Vocabularies

All elements of type [vocabulary value](lom_structure.md#vocabulary-source-vocab_source)
have a preset vocabulary specified by the LOM standard. The source of
these vocabularies is `LOMv1.0`, listed below are their values.

The labels for these values are language-specific, and can be changed
in the Language Administration.

#### general > structure > value

````
atomic, collection, networked, hierarchical, linear
````

#### general > aggregationLevel > value

````
1, 2, 3, 4
````

#### lifeCycle > status > value

````
draft, final, revised, unavailable
````

#### lifeCycle > contribute > role > value

````
author, publisher, unknown, initiator, terminator, editor,
graphical designer, technical implementer, content provider,
technical validator, educational validator, script writer,
instructional designer, subject matter expert
````

#### metaMetadata > contribute > role > value

````
creator, validator
````

#### technical > requirement > orComposite > type > value

````
operating system, browser
````

#### technical > requirement > orComposite > name > value

Permissible values of the `name` element depend on the
value of  the `type` element in the same `orComposite`.
If the `type` element has the value `operating system`,
then permissible values are

````
pc-dos, ms-windows, macos, unix, multi-os, none
````

and if it has the value `browser`

````
any, netscape communicator, ms-internet explorer, opera, amaya
````

#### educational > interactivityType > value

````
active, expositive, mixed
````

#### educational > learningResourceType > value

````
exercise, simulation, questionnaire, diagram, figure, graph, index,
slide, table, narrative text, exam, experiment, problem statement,
self assessment, lecture
````

#### educational > interactivityLevel > value

````
very low, low, medium, high, very high
````

#### educational > semanticDensity > value

````
very low, low, medium, high, very high
````

#### educational > intendedEndUserRole > value

````
teacher, author, learner, manager
````

#### educational > context > value

````
school, higher education, training, other
````

#### educational > difficulty > value

````
very easy, easy, medium, difficult, very difficult
````

#### rights > cost > value

````
yes, no
````

#### rights > copyrightAndOtherRestrictions > value

````
yes, no
````

#### relation > kind > value

````
ispartof, haspart, isversionof, hasversion, isformatof, hasformat,
references, isreferencedby, isbasedon, isbasisfor, requires,
isrequiredby
````

#### classification > purpose > value

````
discipline, idea, prerequisite, educational objective,
accessibility restrictions, educational level, skill level,
security level, competency
````