# I should...

* write proper documentation for my classes
* write tests for all classes and constructs
* write a proper form abstraction that hides plumbing like name source
* clean up the still messy handling of origins in ErrorValues:
    - many special error handling cases in FunctionValue
    - what does "origin" really mean atm? seems to be unclear
    - how does this relate to function calls? whats their origin?
    - maybe whole model of errors is wrong? maybe some kind of log during
      evaluation would serve the intended purpose better?

# I could...

* elaborate representation of input to make it read only and take `$_FILES` into
  account
* let html_tag take a flexible number of HTMLs and concat them itself
* introduce a possibility to output erros from function calls
