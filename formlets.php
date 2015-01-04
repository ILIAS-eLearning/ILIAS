<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This is an attempt to a PHP implementation of the idea of formlets [1].
 * General idea is to have an abstract and composable representation of forms, 
 * called Formlets, that can be transformed to a concrete Builder and 
 * Collector. 
 * While the Builder is responsible for creating an HTML representation of a 
 * Formlet, the Collector is responsible for collecting inputs of the user.
 *
 * The PHP implementations turns out to be a little more complex, since stuff 
 * like currying and functions as values is not as handy as in functional 
 * languages.
 *
 * [1] http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf
 *     The Essence of Form Abstraction (Cooper, Lindley, wadler, Yallop)
 */

require_once("formlets/values.php");
require_once("formlets/html.php");
require_once("formlets/builders.php");
require_once("formlets/collectors.php");
require_once("formlets/namesource.php");
require_once("formlets/base.php");
require_once("formlets/formlets.php");
require_once("formlets/renderers.php");
require_once("formlets/helpers.php");

?>
