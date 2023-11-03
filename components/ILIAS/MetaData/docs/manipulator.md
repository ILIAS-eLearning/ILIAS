# Manipulator: Technical Details

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

In this documentation we describe how exactly the `Manipulator` used
in the [API](api.md) manipulates LOM sets via the method
`prepareCreateOrUpdate` (and relatedly `prepareForceCreate`).

The main mechanism with which the `Manipulator` decides how the proceed
is by checking whether various paths are 'complete' on the to-be-manipulated
set, meaning whether there is at least one instance of the element
that the path points to in the set.

Consider `prepareCreateOrUpdate` being called with a path *p* and *n*
string values. First, if the path contains any steps to super-elements,
it is decomposed: sub-paths of *p* which begin and end at the same
element are separated from the 'clean' path. These sub-paths are in
the following treated essentially as filters for their start/end-elements,
filtering out those whose sub-paths are not complete.

Then, it is checked whether *p* is complete, and if yes how many
instances of the element it points to are found:

1) complete, *m* endpoints with *n* < *m*:
   - The values are inserted into the first *n* endpoints.

2) complete, *m* endpoints with 0 < *m* < *n*:
   - The first *m* values are inserted into the endpoints.
   - The remaining *n - m* values are handled as in option 3.

3) incomplete, no endpoints:
   - 'Loose ends' are identified, meaning sub-paths of *p* beginning at
   the root that are complete.
   - Elements are appended to (at most *n*) loose ends until the original
   path *p* is completed, and the values are inserted in order into
   the newly created endpoints.
   - If values are remaining, an 'insertion point' is identified by
   navigating backwards along the clean path to the super-element of
   the first non-unique element.
   - The remaining values are inserted from the insertion point, analogous
   to the finishing of loose ends described above.

`prepareForceCreate` acts exactly like `prepareCreateOrUpdate`, but
options 1 and 2 are ignored.
