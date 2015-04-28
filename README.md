[![Build Status](https://travis-ci.org/lechimp-p/php-formlets.svg?branch=master)](https://travis-ci.org/lechimp-p/php-formlets)
[![Scrutinizer](https://scrutinizer-ci.com/g/lechimp-p/php-formlets/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/php-formlets)
[![Coverage](https://scrutinizer-ci.com/g/lechimp-p/php-formlets/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/php-formlets)


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

*This code is released under the [MIT License](LICENSE.md)*

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

// We propably use a better autoloader in a real setting.
require_once("tests/autoloader.php");

use Lechimp\Formlets\Formlets as F;

// Create a function object from an ordinary PHP function. Since explode takes
// two mandatory and one optional parameter, we have to explicitly tell how many
// optional parameters we want to have. 
$explode = F::fun("explode", 2);

// For the lib to work, we need to treat functions and values the same,
// so we need to lift ordinary values to our abstraction.
$delim = F::val(" ");
$string = F::val("foo bar");

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

$throws = F::fun(function ($foo) {
    throw new Exception("I knew this would happen.");
    return $foo;
});

$throwsAndCatches = $throws->catchAndReify("Exception");

$res = $throwsAndCatches->apply(F::val("But it won't..."));
echo "This will state my hindsight:\n";
echo ($res->isError()?$res->error():$res->get())."\n";
?>
```

The whole machinery should be working in an immutable style (except for HTML 
representation), that is creating new values instead of modifying old. The 
same goes for the rest of the stuff. The related classes and functions could 
be found at the section starting with the class Value in formlets.php.

## Form(let)s as Applicative Functors

The building blog of our forms are called formlets, and they behave according
to an abstraction called applicative functor. We'll try to understand both
alongside, there's enough stuff about the abstract concept on the net.

A formlet is a thing that encapsulates two things, a renderer and a collector,
in a highly composable way. The renderer is the entity that creates HTML output,
while the collector is responsible for collecting values from the user input.
The most simple formlet thus is a formlet that renders to nothing and 'collects'
a constant value, regardless of the user input.

```php
<?php
// Really not too interesting, but we need to use our value abstraction.
$boringFormlet = F::inject(F::val("Hello World!"));

// Since functions are values as well, we could construct a formlet containing 
// a function.
$functionFormlet = F::inject($explodeBySpace);
?>
```

After creating some reusable formlets, we can turn a formlet into an actual
form that could be rendered, is able to yield a result and abstracts away some
plumbing.

```php
<?php

// We need to specify an id for the form and an action target.
$form = F::form("boring", "www.example.com", $boringFormlet); 

// We initialize the form with an empty input array. One could
// use init without args to use $_POST as input.
$form->init(array());

// Renderer does nothing
echo "This will show \"No content\":\n";
echo ("<form method=\"post\" action=\"www.example.com\"></form>" == $form->html() 
     ? "No content\n"
     : "Oh, that's not pure...\n"
     );

// The form has a constant result as expected.
echo "This will show \"Hello World!\":\n";
echo $form->result()."\n";

?>
```

A formlet is a functor, since in some sense every formlet contains a value. And 
we can map over that value, that is apply a function to that value inside the 
formlet, yielding a new formlet containing the result of the function call.

```php
<?php
// The function to map over the formlet is called mapCollector, since it leaves
// the builder untouched. Maybe at some point a mapRenderer might come in handy
// too...
$containsArrayFormlet = $boringFormlet->map($explodeBySpace);

$form = F::form("contains_array", "www.example.com", $containsArrayFormlet); 
$form->init(array());

echo "Array containing \"Hello\" and \"World!\":\n";
print_r($form->result());
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
$exploded = F::inject($explode)
                ->cmb(F::inject($delim))
                ->cmb(F::inject($string))
                ;

$form = F::form("explode", "www.example.com", $exploded);
$form->init(array());

echo "Array containing \"foo\" and \"bar\":\n";
print_r($form->result());
?>
```

To do checks on inputs, one can use the `satisfies` method, to attach a predicate
to a formlet. As the other operations, this creates a new formlet, so the old 
one could be reused. When the value in the formlet fails the predicate, there is
an error in the form.

```php
<?php

// fun also supports defining some fixed arguments.
$containsHello = F::fun("preg_match", 2, array("/.*hello.*/i"));

// Append the predicate to a formlet. If its not truthy for the value in the 
// formlet, an error value with the given message will be collected.
$withPred = F::inject(F::val("Hi there."))
                ->satisfies($containsHello, "You should say hello.");

$form = F::form("with_pred", "www.example.com", $withPred);
$form->init(array());

echo "This will be stating, what you should say:\n";
echo ($form->wasSuccessfull() ? $form->result() : $form->error())."\n";

?>
```

## Primitives and Application

Now you need to see, how this stuff works out. I'll show you an example how one 
create a form using some of the primitives that are provided in the consumer
interface to the library.

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
        if (!is_int($y) || !is_int($m) || !is_int($d)) {
            throw new Exception("Expected int's as input.");
        }

        if ($m < 1 || $m > 12) {
            throw new Exception("Invalid month: '%m'.");
        }

        if ($d > 31) {
            throw new Exception("Invalid day: '%d'.");
        }

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

// Our constructor function. We want to catch Exceptions since the constructor
// of the class could throw. In the real world we would be more specific
// on the type of exception we want to catch.
$mkDate = F::fun(function ($y, $m, $d) {
        return new _Date($y, $m, $d);
})
->catchAndReify("Exception")
;

function inRange($l, $r) {
    return F::fun(function($value) use ($l, $r) {
        return $value >= $l && $value <= $r;
    });
}
?>
```

After the boilerplate, we start with the interesting stuff, that is actually
constructing a form from the two primitives. We start by creating some basic
input elements we'll need from the only input element i provide atm, the 
`text_input`.

```php
<?php

// First we create an integer input from a text input by map intval over the
// string input after checking it is indeed an integer. We also want to
// display the errors.
$int_formlet = F::with_errors(F::text_input())
                ->satisfies(F::fun("is_numeric"), "No integer.")
                ->map(F::fun("intval", 1))
                ;

// From the integer input we'll create a month and day input by doing further
// checks on the input. Make sure you understand, that none of these touches
// the int_formlet, but rather creates new objects.
$month_formlet = $int_formlet
    ->satisfies(inRange(1,12), "Month must have value between 1 and 12.")
    ;
$day_formlet = $int_formlet
    ->satisfies(inRange(1,31), "Day must have value between 1 and 31.")
    ;
?>
```

Next we'll be combining these basic inputs to a more complex input that could
be used to define a date. We also use the other primitive i have implemented 
atm, that is `text`, which renders a static string and collects nothing. To 
compose the formlets to our date formlet, we use the `cmb` (for combine) method,
shown above, through the convenience function `formlet`. We plumb the stuff with 
$mkDate to get a formlet that creates us a date object.

```php
<?php
// We use a convenience function here to not have cmb that often.
$date_formlet = F::formlet(
                    F::inject($mkDate),
                        F::text("\n\t"), // for readability on cli only
                    F::with_label("Year: ", $int_formlet),
                        F::text("\n\t"), // for readability on cli only
                    F::with_label("Month: ", $month_formlet),
                        F::text("\n\t"), // for readability on cli only
                    F::with_label("Day: ", $day_formlet),
                        F::text("\n") // for readability on cli only
                );
?>
```

That's it. Since we never modify existing objects, the stuff above could be
completely reused and combined to even more complex formlets. E.g. one could
use the date formlet twice to create a period formlet. Now lets try it out:

```php
<?php
// You got that step, right?
$form = F::form("date", "www.example.com", $date_formlet);
$form->init(array());

// First look at the rendering:
echo "This will show some date input in HTML representation:\n";
echo $form->html()."\n\n";

// Then lets look at the collected values. Since we don't actually POST the 
// form, we need to mock up some input. 
$mock_post1 = array( "date_input_0" => "2014"
                   , "date_input_1" => "12"
                   , "date_input_2" => "24"
                   );

// We could use init without parameters if we wanted to use $_POST as input.
$form->init($mock_post1);

echo "This will show a date of christmas eve:\n";
echo $form->result()->toISO()."\n\n";


// To see how errors will show up in the formlets, lets try the same with faulty
// input:
$mock_post2 = array( "date_input_0" => "2014"
                   , "date_input_1" => "12"
                   , "date_input_2" => "32" // that would make a long month
                   );

$form->init($mock_post2);

echo "This will tell why creation of date object did not work:\n";
echo ($form->wasSuccessfull() ? $form->result()->toISO() : $form->error())."\n\n";

// So there's something wrong, and we most likely want to reprompt the user with 
// the form, stating the problem.
echo "This will show some HTML of the formlet with error messages:\n";
echo $form->html();

?>
```
