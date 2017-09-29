# Input Handling in the UI-Framework

The model used for implementing inputs in the UI-framework is build by five basic
blocks:

* A *field* defines, which visual input elements a user can see, which constrains
  are put on those fields and which values developers on the server side retrive
  from these inputs.
* A *container*  defines, which means of submitting the forms are used and how
  the fields are displayed together.
* A *constraints* puts some restriction on the values supplied by the user.
  Constraints can also be used independently from the UI-framework, as they are
  [implemented in their own library](src/Validation/README.md).
* A *transformation* defines, how a value supplied by the user should be processed.
  Like constraints, transformations are [implemented in their own library](src/Transformation/README.md)
  and can thus be used independently from the UI-framework.


To create a form, the developer uses fields from the UI-framework. She may then
enrich them with constraints and transformations to adopt the general input
elements to the case at hand. She may also group the fields in various ways.
She then uses a container for the fields, e.g. a property form, to define what
the general appearance of the inputs is and how they should be transmitted from
the client to the server. She may also bind a final transformation to the whole
form, to tie all inputs together.

The form than is rendered and displayed like every other UI-component. The user
fills in the fields and submits the form back to the server in the defined way.

The developer passes the request retreived from the user to the form. The form
internally uses the constraints and transformations put on the fields to evaluate
if the inputs of the users are corrected and what the result is. Depending on
the result, the developer can either choose to do further processing on the valid
result or display the form again to the client, now showing the problems with the
input.
