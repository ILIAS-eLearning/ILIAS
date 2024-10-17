# The Structure of LOM

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

Metadata of objects in ILIAS follow the Learning Object Metadata
(LOM) standard (for the most part, see [here](#specific-to-ilias) for
diversions). The standard will not be reproduced here in full, 
this documentation is restricted to providing information useful for
working with LOM in ILIAS.

LOM consists of a nested set of elements, forming the structure
illustrated below. The elements in the set can not be  uniquely
identified by name alone, but by their position in the set: they need
to be selected by the path to them from the root element `lom`.

All elements but the root element `lom` are optional, meaning that in
any given LOM set they might not occur at all. Elements marked
with `*` are unique, at their position in the set they may not occur more
than once. Of non-unique elements, arbitrarily many instances can
occur. The leaves of the set carry data of different types, which
will be explained below.

````
lom*
├── general*
│   ├── identifier
│   │   ├── catalog* (string)
│   │   └── entry* (string)
│   ├── title*
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── language (lang)
│   ├── description
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── keyword
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── coverage
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── structure*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   └── aggregationLevel*
│       ├── source* (vocab_source)
│       └── value* (vocab_value)
├── lifeCycle*
│   ├── version*
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── status*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   └── contribute
│       ├── role*
│       │   ├── source* (vocab_source)
│       │   └── value* (vocab_value)
│       ├── entity (string)
│       └── date*
│           ├── dateTime* (datetime)
│           └── description*
│               ├── string* (string)
│               └── language* (lang)
├── metaMetadata*
│   ├── identifier
│   │   ├── catalog* (string)
│   │   └── entry* (string)
│   ├── contribute
│   │   ├── role*
│   │   │   ├── source* (vocab_source)
│   │   │   └── value* (vocab_value)
│   │   ├── entity (string)
│   │   └── date*
│   │       ├── dateTime* (datetime)
│   │       └── description*
│   │           ├── string* (string)
│   │           └── language* (lang)
│   ├── metadataSchema (string)
│   └── language* (lang)
├── technical*
│   ├── format (string)
│   ├── size* (non_neg_int)
│   ├── location (string)
│   ├── requirement
│   │   └── orComposite
│   │       ├── type*
│   │       │   ├── source* (vocab_source)
│   │       │   └── value* (vocab_value)
│   │       ├── name*
│   │       │   ├── source* (vocab_source)
│   │       │   └── value* (vocab_value)
│   │       ├── minimumVersion* (string)
│   │       └── maximumVersion* (string)
│   ├── installationRemarks*
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── otherPlatformRequirements*
│   │   ├── string* (string)
│   │   └── language* (lang)
│   └── duration*
│       ├── duration* (duration)
│       └── description*
│           ├── string* (string)
│           └── language* (lang)
├── educational
│   ├── interactivityType*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── learningResourceType
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── interactivityLevel*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── semanticDensity*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── intendedEndUserRole
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── context
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── typicalAgeRange
│   │   ├── string* (string)
│   │   └── language* (lang)
│   ├── difficulty*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── typicalLearningTime*
│   │   ├── duration* (duration)
│   │   └── description*
│   │       ├── string* (string)
│   │       └── language* (lang)
│   ├── description
│   │   ├── string* (string)
│   │   └── language* (lang)
│   └── language (lang)
├── rights*
│   ├── cost*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   ├── copyrightAndOtherRestrictions*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   └── description*
│       ├── string* (string)
│       └── language* (lang)
├── relation
│   ├── kind*
│   │   ├── source* (vocab_source)
│   │   └── value* (vocab_value)
│   └── resource*
│       ├── identifier
│       │   ├── catalog* (string)
│       │   └── entry* (string)
│       └── description
│           ├── string* (string)
│           └── language* (lang)
├── annotation
│   ├── entity* (string)
│   ├── date*
│   │   ├── dateTime* (datetime)
│   │   └── description*
│   │       ├── string* (string)
│   │       └── language* (lang)
│   └── description*
│       ├── string* (string)
│       └── language* (lang)
└── classification
    ├── purpose*
    │   ├── source* (vocab_source)
    │   └── value* (vocab_value)
    ├── taxonPath
    │   ├── source*
    │   │   ├── string* (string)
    │   │   └── language* (lang)
    │   └── taxon*
    │       ├── id* (string)
    │       └── entry*
    │           ├── string* (string)
    │           └── language* (lang)
    ├── description*
    │   ├── string* (string)
    │   └── language* (lang)
    └── keyword
        ├── string* (string)
        └── language* (lang)
````

## Data Types

### String (`string`)

String-data can have as its value any string, except an empty one.

### Language (`lang`)

Language-data can can have as its value any of the following two-character
codes, including `xx` as a stand-in for the token `none`.

````
aa, ab, af, am, ar, as, ay, az, ba, be, bg, bh, bi, bn, bo, br, ca,
co, cs, cy, da, de, dz, el, en, eo, es, et, eu, fa, fi, fj, fo, fr,
fy, ga, gd, gl, gn, gu, ha, he, hi, hr, hu, hy, ia, ie, ik, id, is,
it, iu, ja, jv, ka, kk, kl, km, kn, ko, ks, ku, ky, la, ln, lo, lt,
lv, mg, mi, mk, ml, mn, mo, mr, ms, mt, my, na, ne, nl, no, oc, om,
or, pa, pl, ps, pt, qu, rm, rn, ro, ru, rw, sa, sd, sg, sh, si, sk,
sl, sm, sn, so, sq, sr, ss, st, su, sv, sw, ta, te, tg, th, ti, tk,
tl, tn, to, tr, ts, tt, tw, ug, uk, ur, uz, vi, vo, wo, xh, yi, yo,
za, zh, zu, xx
````

Note that ILIAS is more restrictive than the LOM standard, as the
standard also allows for example three-character codes, or adding
hyphen-separated suffixes to the base codes.

### Vocabulary Value (`vocab_value`)

The value of vocabulary value-data should be set to one of the values in a
vocabulary, pre-defined for each element with this data type separately.
These vocabularies can be configured in the ILIAS Administration, for more
information see [here](vocabularies.md).

Note that elements with other values than those from the element-specific
vocabularies are not treated as invalid by ILIAS. This is to avoid
information being lost on import/export between installations with 
differently configured vocabularies, or when re-configuring vocabularies.

### Vocabulary Source (`vocab_source`)

Vocabulary source-data should be set to the source of the vocabulary
that the associate `value` element is from. For more information see
[here](vocabularies.md). As described [above](#vocabulary-source-vocab_source),
elements with values not conforming to this are not treated as
invalid in ILIAS.

### Datetime (`datetime`)

The value of datetime-data follows a specific format, simplified in
ILIAS to `YYYY-MM-DD`, since ILIAS only processes the date portion
of datetimes.

### Non-Negative Integer (`non_neg_int`)

Non-negative integer-data can have as their value any combination of
any number of digits.

### Duration (`duration`)

The value of datetime-data consists of up to six non-negative integers,
arranged in a specific format:

- the number of years, appended by `Y`
- the number of months, appended by `M`
- the number of days, appended by `D`
- the number of hours, appended by `H`
- the number of minutes, appended by `M`
- the number of seconds, appended by `S`

All of these fields are optional. If any of them are present, the 
value is prepended by `P`, and if any of the last three are present
they are prepended by `T`, e.g. `P12Y55MT35M`, `P0D`, or `PT9999S`.

Note that the LOM standard also allows for fractions of seconds,
but this is not implemented in ILIAS.

## Further Restrictions

### From the Standard

If at least one element `metaMetadata > metadataSchema` is present,
it must have the value `LOMv1.0`, meaning the first occurence of 
this element cannot be edited or deleted directly, and comes pre-filled
(it can however be deleted by deleting `metaMetadata`).

### Specific to ILIAS

Since the value of the element `general > title > string` is
synchronized with the  title of the ILIAS object the LOM set belongs
to, neither it nor its super-elements can be deleted.

The sub-elements of the first `general > identifier` are fixed and can
neither be edited nor deleted, along with their super-element. The
`catalog` sub-element is always set to `ILIAS` and the `entry` to
`il_{Installation ID}_{Object Type}_{Object ID}`. `Object ID` refers
here to either the `sub_id` if it is non-zero, and otherwise to the
`obj_id` of the object as defined [here](identifying_objects.md).

The LOM standard allows any elements of type `LangString`, meaning those
with a tupel `string` and `language` as sub-elements, to have arbitrarily
many such tupels. This is currently not implemented in ILIAS.

Further, some elements denoted above as having data type `string` are
more restrictive in the LOM standard. For example, `technical > format`
should be restricted to MIME types (or the token `non-digital`), and
`contribute > entity` should be restricted to representation of vCards.
Likewise, these restrictions are currently not implemented in ILIAS.
