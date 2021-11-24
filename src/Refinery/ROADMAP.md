# Roadmap

The refinery library is used to process input data.

This file aims to serve a basic overview of the planned and
already implemented Features.
This file should be updated regulary to avoid collision with
other development processes and give anybody a fair overview
of the planned features of this service.

## Already done

* First version with new groups to transform input data.
* Added `in` and `to` groups based on the concept of
  `docs/documentation/input-processing.md`
* Move `Transforamtion` and `Validation` libraries to new `Refinery` library
* Move `Transformation` and `Validation` library to the top level.
* Create new groups in the refinery with the transformation and constraints
  of `Transformation` and `Validation`.
* Implementation of `kindlyTo` groups based on the concept of
  `docs/documentation/input-processing.md`.
* Replace type hints and return types in doc-strings by real type hints and
  return-types

## Mid Term

* Unify the concepts of `Transformation` and `Constraints` and remove remaining
  unused classes.
* Implement tree based XHTML Transformation for `MakeClickable` transformation
