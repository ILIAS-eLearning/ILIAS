font-family is only "Open Sans". (old values like "Open Sans Light" are not workink any more)

font-style possible values are normal or italic

Attributed values for font-weight are:
300 (OpenSans-Light)
400 or normal (OpenSans-Regular)
600 (OpenSans-Semibold)
700 or bold (OpenSans-Bold)
800 (OpenSans-Extrabold)


CSS possible values for font-weight are: normal | bold | bolder | lighter | 100 | 200 | 300 | 400 | 500 | 600 | 700 | 800 | 900 | inherit
Other attributed values will act as follow:
100, 200 like 300
500 like 400 (normal)
900 like 800

The value "lighter" sets one value less than the parent element (cannot be inherited, the computed value is inherited)
The value "bolder" sets one value more as the parent element (cannot be inherited, the computed value is inherited)
The value "inherit" does what we think it should do! (same value as parent element)