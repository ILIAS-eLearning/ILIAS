# php-formlets

**Create highly composable forms in PHP. Implementation of ideas from "The 
Essence of Form Abstraction" from Cooper, Lindley, Wadler and Yallop.**

Writing up formulars is a big part in the every days business of PHP-developers.
To ease this work, one would like to abstract the formulars in a way that makes
them composable. This is the implementation of an attempt to this problem grown 
from functional programming and [elaborated scientifically](http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf). 

It could be usefull for educational purpose, since it implements some interesting
concepts. I'm also interested in real world applications of the code, but i'm
not quite sure, weather the code is really ready for that atm. The concepts used 
in the implementation might feel somehow strange to PHPers anyway (and others as 
well), so i will start with some explanation of the concepts and how one could
use them for a framework to create forms. Hf.

*This README.md is also a literate PHP-file.*

## Functions as Values, Currying

To understand the first important ingredient of the formlet abstraction, we need
to look at functions in a different way then we are used to from PHP. 

The functions we need for this abstraction could be used as ordinary values, that 
is they can be stored in a variable, being passed around or used as an argument 
to another function. Functions in PHP in opposite are not that volatile, mostly
you call them by their name. You could off course use some PHP magic like 
$function_name() to come a bit closer to the afformentioned property of functions
and PHPs callable aims at this direction. 

The functions in our abstraction also all need to have an arity (that is amount
of arguments) of one. How to do something like explode(" ", $foo) then, you 
might wonder. Easy. Since functions are ordinary values in our abstraction, you
just create a function that takes the delimiter and returns a function that
splits a string at the delimiter. That also means you could call a function 
partially. PHP functions are rather different. You always call them at once.

```php
<?php
$TEST_MODE = false;
require_once("formlets.php");

// Create a function object from an ordinary PHP function, you need to
// give its arity and name.
$explode = _function(2, "explode");

// For the lib to work, we need to treat functions and values the same,
// so we need to lift ordinary values to our abstraction.
$delim = _value(" ");
$string = _value("foo bar");

// We could apply the function once to the delim, creating a new function.
$explodeBySpace = $explode->apply($delim);

// We could apply the function to the string to create the final result:
$res = $explodeBySpace->apply($string);

// Since the value is still wrapped, we need to unwrap it.
$unwrapped = $res->get();

echo "Array containing \"foo\" and \"bar\":\n";
print_r($unwrapped);
?>
```

The function evaluation works lazy, that is it only calculates the value when 
it is really needed the first time. In our case that is the moment we call 
`$res->get()`. You will be safe in terms of the result of function applications 
if you only use functions without sideeffects like writing or reading global 
stuff. When using functions with sideeffects, the result might be suprising.

For the later use with the formlets, values can be erroneous to catch an 
exception from the underlying PHP function and turn it into an error value, 
one can use `catchAndReify` to create a new function value.

```php
<?php
function throws($foo) {
    throw new Exception("I knew this would happen.");
    return $foo;
}

$throws = _function(1, "throws");
$throwsAndCatches = $throws->catchAndReify("Exception");

$res = $throwsAndCatches->apply(_value("But it won't..."));
echo "This will state my hindsight:\n";
echo ($res->isError()?$res->error():$res->get())."\n";
?>
```

The whole machinery should be working in an immutable style, that is creating
new values instead of modifying old. The same goes for the rest of the stuff.
The related classes and functions could be found at the section starting with 
the class Value in formlets.php.

## Form(let)s as Applicative Functors

The building blog of our forms are called formlets, and they behave according
to an abstraction called applicative functor. We'll try to understand both
alongside, there's enough stuff about the abstract concept on the net.

A formlet is a thing that encapsulates two things, a renderer and a collector,
in a highly composable way. The renderer is the entity that creates HTML output,
while the collector is responsible for collecting values from the user input.
The most simple formlet (the 'pure' one) thus is a formlet that renders to nothing
and 'collects' a constant value, regardless of the user input.

```php
<?php
// Really not too interesting, but we need to use our value abstraction.
$boringFormlet = _pure(_value("Hello World!"));

// Since functions are values as well, we could construct a formlet containing 
// a function.
$functionFormlet = _pure($explodeBySpace);
?>
```

The created entities could be used to build concrete renderers and collectors.
To do that while maintain composability, we need a source that creates unique
names in a reproducible way. To avoid complex sideeffects, the name source
can only be instantiated once and needs to be passed around explicitly. This
is a point i'm currently thinking about how to handle best.

```php
<?php
// The one and only (might not be that way always):
$name_source = NameSource::instantiate();

// Very boring renderer and collector.
$repr = $boringFormlet->build($name_source);

// Renderer does nothing
echo "This will show \"No output\":\n";
echo ("" == $repr["renderer"]->render()?"No output\n":"Oh, that's not pure...\n");

// The collector 'collects' a constant value, wrapped in our value representation.
echo "This will show \"Hello World!\":\n";
echo $repr["collector"]->collect(array())->get()."\n";

// We need to update the name source:
$name_source = $repr["name_source"]; 
?>
```

A formlet is a functor, since in some sense every formlet contains a value. And 
we can map over that value, that is apply a function to that value inside the 
formlet, yielding a new formlet containing the result of the function call.

```php
<?php
// The function to map over the formlet is called mapCollector, since it leaves
// the renderer untouched. Maybe at some point a mapRenderer might come in handy
// too...
$containsArrayFormlet = $boringFormlet->mapCollector($explodeBySpace);

$repr = $containsArrayFormlet->build($name_source);
$name_source = $repr["name_source"];

echo "Array containing \"Hello\" and \"World!\":\n";
print_r($repr["collector"]->collect(array())->get());
?>
```

A formlet is applicative, since it provides an interesting way of combining two 
formlets into a new one. If you have two formlets, one collecting a function
and rendering some output, and the other collecting a value and rendering some
other output, you can combine them to a new formlet, 'collecting' the function 
applied to the value and rendering the concatenated outputs of the other formlets.

```php
<?php
// Will be a lot more interesting when you see formlets that actually take some 
// input.
$exploded = _pure($explode)
                ->cmb(_pure($delim))
                ->cmb(_pure($string))
                ;

$repr = $exploded->build($name_source);
$name_source = $repr["name_source"];

echo "Array containing \"foo\" and \"bar\":\n";
print_r($repr["collector"]->collect(array())->get());
?>
```

To do checks on inputs, one can use the `satisfies` method, to attach a predicate
to a formlet. As the other operations, this creates a new formlet, so the old 
one could be reused. When the value in the formlet fails the predicate, `collect`
returns an error value.

```php
<?php
// _function also supports defining some fixed arguments.
$containsHello = _function(1, "preg_match", array("/.*hello.*/i"));

// Append the predicate to a formlet. If its not truthy for the value in the 
// formlet, an error value with the given message will be collected.
$withPred = _pure(_value("Hi there."))
                ->satisfies($containsHello, "You should say hello.");

$repr = $withPred->build($name_source);
$name_source = $repr["name_source"];

$res = $repr["collector"]->collect(array());
echo "This will be stating, what you should say:\n";
echo ($res->isError()?$res->error():$res->get())."\n";

?>
```

## Primitives and Application

Now you need to see, how this stuff works out. I won't explain how to implement
new primitives for forms, since atm i only implemented two of them by myself.
So that'll be left for later. I rather show you an example how one could use 
the primitives to construct an input for a date. Their names are `_text_input` and
`_static`.

First we'll write our own (and very dump) date class. We won't be doing this in
*The Real World*, i guess, but here we'll do it to see how it works more easily.
We'll also need some boilerplate to make everything work out nicely. If this 
stuff would ever be used, i would expect a lot of this be going into a the 
library for reuse.

```php
<?php

// Maybe next time we'll use a Wheel as example.
class _Date {
    public function __construct($y, $m, $d) {
        guardIsInt($y);
        guardIsInt($m);
        guardIsInt($d);

        if ($m === 2 && $d > 29) {
            throw new Exception("Month is 2 but day is $d.");
        }
        if (in_array($m, array(4,6,9,11)) && $d > 30) {
            throw new Exception("Month is $m but day is $d.");
        }

        $this->y = $y;
        $this->m = $m;
        $this->d = $d;
    }

    public function toISO() {
        return $this->y."-".$this->m."-".$this->d;
    }
}

// PHPy function
function mkDate($y, $m, $d) {
    return new _Date($y, $m, $d);
}

// Our type of function, we want to catch Exceptions since the constructor
// of the class could throw. In the real world we would be more specific
// on the type of exception we want to catch.
$mkDate = _function(3, "mkDate")
            ->catchAndReify("Exception")
            ;

function inRange($l, $r, $value) {
    return $value >= $l && $value <= $r;
}

function _inRange($l, $r) {
    return _function(1, "inRange", array($l, $r));
}
?>
```

After the boilerplate, we start with the interesting stuff, that is actually
constructing a form from the two primitives. We start by creating some basic
input elements we'll need from the only input element i provide atm, the 
`_text_input`.

```php
<?php
// First we create an integer input from a text input by map intval over the
// string input after checking it is indeed an integer.
$int_formlet = _text_input()
                ->satisfies(_function(1, "is_numeric"), "No integer.")
                ->mapCollector(_function(1, "intval"))
                ;

// From the integer input we'll create a month and day input by doing further
// checks on the input. Make sure you understand, that none of these touches
// the int_formlet, but rather creates new objects.
$month_formlet = $int_formlet
    ->satisfies(_inRange(1,12), "Month must have value between 1 and 12.")
    ;
$day_formlet = $int_formlet
    ->satisfies(_inRange(1,31), "Day must have value between 1 and 31.")
    ;
?>
```

Next well be combining these basic inputs to a more complex input that could
be used to define a date. We also use the other primitive i have implemented 
atm, that is `_static` which renders a static text and collects nothing. To 
compose the formlets to our date formlet, we use the combine function, shown
above. We plumb the stuff with mkDate to get a formlet, that creates us a 
date object.

```php
<?php
// Written in odd notation to see what's going on...
$date_formlet = _pure(  $mkDate             )
                ->cmb(  _static("Year: ")   )
                ->cmb(  $int_formlet        )
                ->cmb(  _static("Month: ")  )
                ->cmb(  $month_formlet      )
                ->cmb(  _static("Day: ")    )
                ->cmb(  $day_formlet        )
                ;
?>
```

That's it. Since we never modify existing objects, the stuff above could be
completely reused and combined to even more complex formlets. E.g. one could
use two date formlets to create a period formlet. Now lets try it out:

```php
<?php
// You got that step, right?
$repr = $date_formlet->build($name_source);
$name_source = $repr["name_source"];

// First look at the rendering:
echo "This will show some date input in HTML representation:\n";
echo $repr["renderer"]->render()."\n";

// Then lets look at the collected values. Since we don't actually POST the 
// form, we need to mock up some input. This would be completely opaque when 
// using render and then collect the results from $_POST.
$mock_post1 = array( "input0" => "2014"
                   , "input1" => "12"
                   , "input2" => "24"
                   );

$res = $repr["collector"]->collect($mock_post1);
echo "This will show a date of christmas eve:\n";
echo $res->get()->toISO()."\n";


// To see how errors will show up in the formlets, lets try the same with faulty
// input:
$mock_post2 = array( "input0" => "2014"
                   , "input1" => "12"
                   , "input2" => "32" // that would make a long month
                   );

$res = $repr["collector"]->collect($mock_post2);
echo "This will tell why creation of date object did not work:\n";
echo ($res->isError()?$res->error():$res->get()->toISO())."\n";

// So there's something wrong, and we most likely want to reprompt the user with 
// the form, stating the problem.

// We need to turn the retreived value into a representation for rendering ...
$renderDict = new RenderDict($res);

// ... and call another render function on the renderer with said dict.
echo "This will show some HTML of the formlet with error messages:\n";
echo $repr["renderer"]->renderValues($renderDict)."\n";

?>
```

## Outlook

Atm this is a proof of concept, and i'm thinking about if and how to turn it 
into a really usable library. So i'm highly interested in suggestions on and
reviews of my code. There's plenty of stuff that should or could be done i 
already know about:

* Write tests that proof the properties of the implementations i use for 
  functions as values, applicative functors and atoms for the formlets
  machinery.
* Implement some usefull primitives and maybe provide some readymade functions
  as values for common checks and instantiations. 
* Write tests to proof properties of the primitives.
* Document how new primitives could be implemented and provide tests for self
  made primitives.
* Think about using some other representation for HTML then strings, so the 
  renderer output could be processed further.
* Think about the name source and how a formlet is actually turned into an HTML
  form.

