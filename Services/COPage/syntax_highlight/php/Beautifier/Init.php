<?php

/*

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

# Feel free to change the settings below.

# This should point to the root of your Beautifier install.
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
//$BEAUT_PATH = "./syntax_highlight/php";


# Changing bits after here is unwise.

// Start up the core modules.
require_once $BEAUT_PATH."/Beautifier/Core.php";

//$GLOBALS["BEAUT_PATH"] = $BEAUT_PATH;

?>
