# I should...

* write primitives for all HTML input types
* clean up _collect and expose it in consumer interface.
* clean up Value-class

# I could...

* Find a possibility how errors during function execution could be displayed
  so i could catch an error thrown by Date::__construct in the example in
  README.md.
* Put more parts of the actual interface into the consumer interface and 
  document them
* Find a nicer syntax for $foo->cmb(..)->cmb(..)->...
* create a composer package
* write proper documentation for my classes
* write tests for all classes and constructs
* let html_tag take a flexible number of HTMLs and concat them itself
* elaborate representation of input to make it read only and take `$_FILES` into
  account
* introduce a possibility to output erros from function calls
* introduce namespacing
    - this seems to be a mess
    - call_user_func does not play well
    - introduces much noise
    - maybe not the right fit for the functionality i intent?
