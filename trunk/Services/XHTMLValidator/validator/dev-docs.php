<pre>

<?php
include './validator.inc';

// print reflection
reflectionClass::export('validator');
echo '</pre>';

//print source code
highlight_file('./validator.inc');

?>
