# Refinery

The `Refinery` library is used to unify the way input is
processed by the ILIAS project.

**Table of Contents**
- [Refinery](#refinery)
  * [Factory](#factory)
    + [Groups](#groups)
      - [To](#to)
      - [In](#in)
        * [series](#series)
        * [parallel](#parallel)
  * [Libraries](#libraries)
    + [Transformation](#transformation)
    + [Validation](#validation)

## Factory

The factory of the refinery interface can create
an implementation of two very different [groups](#groups)

Checkout the [examples](/src/Refinery/examples) to
see how these library can be used.

### Groups

The different groups are used to validate and/or transform
the input given to the certain transformation.

#### to

The `to` group consists of combined validations and transformations
for primitive data types that establish a baseline for further constraints
and more complex transformations.

To learn more about transformations checkout the
[README about Transformations](/src/Refinery/Transformation/README.md).

The transformations of this group are very strict, which means
that there are several type checks before the transformation is
executed.

#### in

The `in` group is a group with a dict of `Transformations`
as parameters that define the content at the indices.

There are currently two different strategies supported by this group.

##### series

The transformation `series` takes an array of transformations and
performs them one after another on the result of the previous transformation.

##### parallel

The transformation `parallel` takes an array of transformations and
performs each on the input value to form a tuple of the results.

## Libraries

Many of these libraries have their own descriptions.

### Transformation

[README for Transformation Library](/src/Refinery/Transformation/README.md)

### Validation

[README for Validation Library](/src/Refinery/Validation/README.md)

