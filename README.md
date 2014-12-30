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

## Functions as values, currying

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
# Create a function object from an ordinary PHP function, you need to
# give its arity and name.
$explode = _function(2, "explode");

# For the lib to work, we need to treat functions and values the same,
# so we need to lift ordinary values to our abstraction.
$delim = _value(" ");
$string = _value("foo bar");

# We could apply the function once to the delim, creating a new function.
$explodeBySpace = $explode->apply($delim);

# We could apply the function to the string to create the final result:
$res = $explodeBySpace->apply($string);

# Since the value is still wrapped, we need to unwrap it.
$unwrapped = $res->get();

print_r($unwrapped);
```

