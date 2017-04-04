# Validations

This service abstract validations of values and provides some basic validations
that could be reused throughout the system.

A validation checks some supplied value for compliance with some restriction.
Validations MUST NOT modify the supplied value.

Having an interface to Validations allows to typehint on them and allows them
to be combined in structured ways. Understanding validation as a separate service
in the system with objects performing the validations makes it possible to build
a set of common validations used throughout the system. Having a known set of
validations makes it possible to perform at least some of the validations on
client side someday.
