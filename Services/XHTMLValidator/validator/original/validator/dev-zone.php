<?php
ini_set('default_charset', 'UTF-8');

require './include.inc';
require './local/en.inc';

common_header('Dev Zone');
?>

<p>
This program is open-source and is licensed under the <a href="/lgpl.txt">LGPL license</a>.
It is powered by PHP 5 and HTML tidy.
</p>

<p>Our current TODO list includes:</p>
<ul>
<li>Localize the messages to a lot more languages</li>
<li>Implement a link checker bot</li>
<li>Implement a CSS validator and repairer (we need to find out if there is some library usable)</li>
<li>Help the HTML tidy project to localize their messages (with our translators database)</li>
</ul>

<p>
We would like also to receive any feedback about improvements that could be done (providing
patch or not), security bugs (very important to us), interface suggestions, etc.. All the
feedback should go into our <a href="http://sourceforge.net/tracker/?group_id=143995">tracker</a>.
</p>

<p>
The <a href="http://sourceforge.net/project/showfiles.php?group_id=143995">source code</a>
is freely available for downloading.
</p>

<p>
If you are interested in translating this program or you are a mainter of a current translation,
please check the <a href="/translators.php">Translators page</a>.
</p>

<p>
The <a href="http://sourceforge.net/projects/htmlchecker/">project facilities</a> are hosted at
<a href="http://sourceforge.net"><img src="http://sourceforge.net/sflogo.php?group_id=143995&amp;type=1" width="88" height="31" alt="SourceForge.net" /></a>
and the PHP 5 hosting is provided by <a href="http://www.aborla.net">Aborla.net</a>.
</p>

<p style="text-align: right">
<a href="http://sourceforge.net/donate/index.php?group_id=143995"><img src="http://images.sourceforge.net/images/project-support.jpg" width="88" height="32" alt="Support this Project" /> </a> 
</p>

<?php common_footer(); ?>
