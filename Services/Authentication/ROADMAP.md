# Roadmap

## Short Term

## Mid Term
- Split AuthType and Ordering into seperate fields. Currently there is the mixer of int and int_int, which make typing difficult.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
- Fix overall structure. There are several services dealing with diffent auth methods, but all also have dependent code inside the 
  authentication service. This should be split up into decouple the code.