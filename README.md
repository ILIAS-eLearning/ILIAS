# php-formlets

**Create highly composable forms in PHP. Implementation of ideas from "The 
Essence of Form Abstraction" from Cooper, Lindley, Wadler and Yallop**

Writing up formulars is a big part in the every days business of PHP-developers.
To ease this work, one would like to abstract the formulars in a way that makes
them composable. This is the implementation of an attempt to this problems grown 
from functional programming and [elaborated scientifically](http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf). 

It could be usefull for educational purpose, since it implements some interesting
concepts. I'm also interested in real world applications of the code, but i'm
not quite sure, weather the code is really ready for that atm. The concepts used 
in the implementation might feel somehow strange to PHPers anyway (and others as 
well), so i will start with some explanation of the concepts and how one could
use them with my library to create forms. Hf.

## Functions as Values, Currying

First concept we need to understand for the formlets abstraction, we need to look
at functions in a different way then we are used to from PHP. 

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
$res->get(). You will be safe in terms of the result of function applications 
if you only use functions without sideeffects like writing or reading global 
stuff. When using functions with sideeffects, the result might be suprising.

For the later use with the formlets, the functions and values can be erroneous
and have an origin. The related classes and functions could be found at the
section starting with the class Value in formlets.php.

## Form(let)s as Applicative Functors

The building blog of our form are called formlets, and they behave according
to an abstraction called applicative functor. We'll try to understand both
alongside, there's enough stuff about the abstract concept on the net.

A formlet is a thing that encapsulates two things, a renderer and a collector,
in a highly composable way. The renderer is the entity that creates HTML output,
while the collector is responsible for collecting values from the user input.
The most simple formlet (the 'pure' one) thus is a formlet the renders to nothing
and 'collects' a constant value, regardless of the user-input.

```php
<?php
// Really not to interesting, but we need to use our value abstraction.
$boringFormlet = _pure(_value("Hello World!"));

// Since functions are values as well, we could construct a formlet containing 
// a function.
$functionFormlet = _pure($explodeBySpace);
?>
```

The created entities could be used to build concrete renderers and collectors.
To do that and maintain maintainability, we need a source that creates unique
names in an reproducible way. To avoid complex sideeffects, the name source
can only be instantiated once and needs to be passed around explicitly. This
is also a point i'm currently thinking about how to handle best.

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
echo $repr["collector"]->collect(array())->get();

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
// to...
$containsArrayFormlet = $boringFormlet->mapCollector($explodeBySpace);

$repr = $containsArrayFormlet->build($name_source);
$name_source = $repr["name_source"];

echo "Array containing \"Hello\" and \"World!\":\n";
print_r($repr["collector"]->collect(array())->get());
?>
```

It is also applicative, since it provides an interesting way of combining two 
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
