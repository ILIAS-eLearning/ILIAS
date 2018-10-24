==========
Maphilight
==========
.. image:: https://img.shields.io/cdnjs/v/maphilight.svg
  :alt: CDNJS 
  :target: https://cdnjs.com/libraries/maphilight

Maphilight is a jQuery plugin that adds visual hilights to image maps.

It provides a single jQuery function: ``$('.foo').maphilight()``

In IE VML is used. In other browsers canvas is used. Maphilight has been
tested in Firefox, IE, Safari, Chrome, and Opera.

Documentation is included in the ``docs`` directory, or can be found
at http://davidlynch.org/js/maphilight/docs/

Development
-----------

If you want to make changes to Maphilight, check out the repository and
then do:

``> npm install``

Before submitting a pull request, make sure you've run

``> grunt lint``

and fixed any errors it reports.

To regenerate the minified version, you can run

``> grunt``
