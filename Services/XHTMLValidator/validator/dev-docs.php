<pre>

<?php
// patch-begin: Information disclosure
// Remove this patch to use the documentation
exit();
// patch-end: Information disclosure
include './validator.inc';

// print reflection
reflectionClass::export('validator');
echo '</pre>';

//print source code
highlight_file('./validator.inc');

?>
