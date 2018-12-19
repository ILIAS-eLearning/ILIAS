# Input Handling in the UI-Framework

The model used for implementing inputs in the UI-framework is build by four basic
blocks:

* A *field* defines, which visual input elements a user can see, which constraints
  are put on those fields and which values developers on the server side retrieve
  from these inputs. *fields* can also be arranged to *groups* which allow to place
  constraints on a collection of such fields. Such *groups* may also alter the visual 
  appearance of *fields*.
* A *container* defines, which means of submitting the forms are used and how
  the fields are displayed together.
* A *constraint* puts some restriction on the values supplied by the user.
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

The form then is rendered and displayed like every other UI-component. The user
fills in the fields and submits the form back to the server in the defined way.

The developer passes the request retrieved from the user to the form. The form
internally uses the constraints and transformations put on the fields to evaluate
if the inputs of the users are corrected and what the result is. Depending on
the result, the developer can either choose to do further processing on the valid
result or display the form again to the client, now showing the problems with the
input.

## Fields

A [input field](src/UI/Component/Input/Field/Input.php) in the model can be thought of
as two things, glued together:

* The look of the field, which is defined by the renderer belonging to the field
  in the same way as this is defined for other UI components. Most of the methods
  in the `Input`-interface account to that part of the model.
* The content of the field, which is roughly the thing the user supplied to the
  input field, but in a more abstract way.

Since the first part is similar to other UI components, we focus on the second
part. The `field`-model allows developers to talk about the input of users
without knowing the actual value the user supplied, which together with
`Transformation` and `Constraint` is a powerful abstraction to compose forms.

Think of the input as a box containing a value, but do not have the key to the
box, so you cannot open it to inspect the value:

```
*---*
| ? |
*---*
```

You still have the possibility to modify that value by applying a transformation
to it via `withAdditionalTransformation`:

```
*---*                                      *------*
| ? |->withAdditionalTransformation(f) =>  | f(?) |
*---*                                      *------*
```

The box now contains another value, i.e. the (still unknown) value one would
get after applying the known transformation to the previous (unknown) value.
It is of course possible to stack multiple transformation onto each other.

One also could define constraints the value needs to define via `withAdditionalConstraint`.
The constraints are the interleaved with the transformations in the order
they were defined:

```
*---*
| ? |->withAdditionalTransformation(f)->withAdditionalContraint(c)->withAdditionalTransformation(g)
*---*

   *--------------*
=> | g(c(f( ? ))) |
   *--------------*
```

The facility to fill in the yet unknown value `?` with client input then belongs
to the container which will be explained in the following.

This model allows for easily building new inputs from existing ones by adding new
constraints or transformations to them. Since the data processing is bound to the
fields, it can be exchanged with them and makes it easy to share components in the
system. This will allow for fields that contain tightly defined or even complex
datastructures without the burden on the users of the field to know about the
details of the data retrieval from the client.

## Containers

An input container defines the means how the data entered by the user reaches the
server and how the post processing of the values from the client is performed. It
may also define visual appearance of the inputs. Two instances of containers that
will be created are the standard form, known and loved by every ILIAS user, and the
filter. Since these tasks may be rather diverse depending on the type of container,
there is no common interface for containers and a general description of their
tasks is hard.

Instead of describing general container responsibilities we thus walk through
the abstract [Form class](src/UI/Implementation/Component/Input/Container/Form/Form.php).

First thing to note is that the class implements the `NameSource` interface.
Developers do not need to assign HTML-side `name`s to the input fields to make
the fields composable. This task is done automatically at the moment the form is
constructed. The implementation of `NameSource` in `getNewName` is rather simple. 

Second thing to notice is that Form internally uses an input group. This done
for code sharing. The things that the form adds on top of the group are the
naming and the actual request handling.

The request handling is performed in `withRequest`. The HTTP-Request is checked
for general sanity and the data in POST is extracted. That data then is simply
passed on two the input group which takes care of the further processing together
with the fields contained in it.

The developer in turn can retrieve that data via `getData` which uses the input
group again to get hold onto the data.

Other types of container might use other mechanisms for data submission. A filter
e.g. will likely be commiting its content via query parameters in the URL to make
the results of the query cachable and maintain HTTP-semantics. Another type of
form might submit its contents asynchronously.

## How to add a new Input

Inputs in the UI-Framework are meant to be extended by new inovative form of enabling
inputs by the user. To ease the definition of new inputs, we propose several examples
in this tutorial walking throught the steps of adding new inputs one by one.

### Example 1, Basic numeric field Input
This example describes how the basic numeric input was added.

#### Step 1, define the interface
As with all UI-Elements, the first step should be to define the interface in the
respective [factory](src/UI/Component/Input/Field/Factory.php) class and the
[interface](src/UI/Component/Input/Field/numeric.php) of the input itself. It is
very possible the interface of your new input just extend the existing basic
interface of inputs without adding any new specialities. This interface MUST be
discussed in the JF.

#### Step 2, Design necessary default constraints and transformation
You may need new constraints or validation you may want to offer to the ILIAS
core the enable other developers to profit from those. For our new numeric input,
we propsed the "[isNumeric](src/Validation/Constraints/IsNumeric.php)" constraint, 
which will be quite handy for our new input.

#### Step 3, Write tests
Next you should write your tests for the new input (e.g. see [numeric input](tests/UI/Component/Input/Field/NumericInputTest.php)), 
constraints (see [isNumeric](tests/Validation/Constraints/StandardConstraintsTest.php))
and transformation.

#### Step 4, Implement the inputs
Implement the input (e.g. see "[numeric](src/UI/Implementation/Component/Input/Field/Numeric.php)",
you may attach your new constraint in the constructor if needed. Also, extend
the renderer with the logic of [rendering](src/UI/Implementation/Component/Input/Field/Renderer.php)
your component. You probably also need a new template (e.g. see [tpl.numeric.html](src/UI/templates/default/Input/tpl.numeric.html)).

#### Step 5, Propose an example
Finally do not forget to implement an [example](src/UI/examples/Input/Field/Numeric/numeric_inputs.php)
showing ot power of your new component.

### Example 2, Group Field Input
The steps of adding a new input group are almost the same as adding a new input with
the exception that you need to extend the group interface and class instead of the
basic input. Note that this input group also extends input. You may therefore also
attach transformations and validations as needed.

### Example 3, Container Input
TBD, see the form as example for such a container.
