# Roadmap

## Short Term
Fix leftovers from Review:
- Remove usage of $_GET / $_POST / $_REQUEST / $_SESSION: 
  The review found 22 usages of $_REQUEST, 7 usages of $_GET, 30 usages of $_SESSION and 34 usages of $_POST.

## Mid Term
- Split AuthType and Ordering into seperate fields. Currently there is the mixer of int and int_int, which make typing difficult.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
- Fix overall structure. There are several services dealing with diffent auth methods, but all also have dependent code inside the 
  authentication service. This should be split up into decouple the code.