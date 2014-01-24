<?php
/* 
   +----------------------------------------------------------------------+
   | HTML/XML Validator                                                   |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 Nuno Lopes                                   |
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or        |
   | modify it under the terms of the GNU Lesser General Public           |
   | License as published by the Free Software Foundation; either         |
   | version 2.1 of the License, or (at your option) any later version.   |
   |                                                                      |
   | This program is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
   | Lesser General Public License for more details.                      |
   |                                                                      |
   | You should have received a copy of the GNU Lesser General Public     |
   | License along with this library; if not, write to the Free Software  |
   | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA             |
   | 02111-1307  USA.                                                     |
   +----------------------------------------------------------------------+
   |                     http://validator.aborla.net/                     |
   +----------------------------------------------------------------------+

vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:

     $Id$
*/

ini_set('default_charset', 'UTF-8');

include './include.inc';
include './local/en.inc';

common_header('Translators');

$files = scandir('./local');
$files = array_splice($files, 2); // remove '.' and '..'
$skip  = array('CVS'=>1, 'en.inc'=>1, 'convert.php'=>1);

// fetch the english revision
preg_match('/\$'.'Revision: 1\.(\d+)/', file_get_contents('./local/en.inc'), $data);
$revision = $data[1];

echo <<< HTML
<p>&nbsp;</p>
<p>This page is used to keep track of the current translations and their maintainers and revisions.
 Therefore we thank all the translators listed below for their work!</p>
<p>If you would like to become the maintainer of the translation of a language, please download the
 <a href="/local/en.inc">english file</a> and translate it (please encode the file in UTF-8). Then, send the file
 and your details (name, site, e-mail) to our <a href="mailto:htmlchecker-devl@lists.sourceforge.net">mailling list</a>.</p>

<p>English Revision: $revision</p>
<table border='1'>
 <tr>
  <td>Language</td>
  <td>EN Revision</td>
  <td>Revision</td>
  <td>Translator</td>
  <td>E-mail</td>
  <td>Site</td>
 </tr>

HTML;

// iterate through the translated files
foreach ($files as $file) {

    if (isset($skip[$file])) continue;

    $lang = explode('.', $file);
    $lang = $lang[0];

    $file   = file_get_contents("./local/$file");
    $rev    = $email = $site = $name = '';
    $en_rev = 0;

    if (preg_match('/\$'.'Revision: 1\.(\d+)/',  $file, $data))
        $rev = $data[1];

    if (preg_match('/EN-Revision: 1\.(\d+)/', $file, $data))
        $en_rev = $data[1];

    if(preg_match('/Translation:\s*([^(\r\n]+)\s*(\(.+\))?/',$file, $data)) {
        $name  = rtrim($data[1]);

        if (isset($data[2]) && preg_match_all('/\(([^)]+)\)/', $data[2], $contacts)) {

            foreach($contacts[1] as $contact) {
                if(strpos($contact, '@'))
                    $email = str_replace(array('@', '.'), array(' -at- ', ' ! '), $contact);
                else
                    $site  = $contact;
            }
        }
    }

    $diff = $revision - $en_rev;
    $lang = "{$langs[$lang]}  ($lang)";

    if ($diff == 0)
        $trclass = 'ok';

    else {
        $lang = "<a href='http://cvs.sourceforge.net/viewcvs.py/htmlchecker/validator/local/en.inc?tr1=1.$en_rev&amp;tr2=1.$revision&amp;r1=text&amp;r2=text&amp;diff_format=u'>$lang</a>";

        if ($diff <= 5)
            $trclass = 'old';
        else
            $trclass = 'critical';
    }

    $site  = $site ? "<a href='$site'>$site</a>" : '&nbsp;';
    $email = $email ? $email : '&nbsp;';

    echo <<< HTML
 <tr class="$trclass">
  <td>$lang</td>
  <td>$en_rev</td>
  <td>$rev</td>
  <td>$name</td>
  <td>$email</td>
  <td>$site</td>
 </tr>

HTML;
}
?>

</table>

<p>&nbsp;</p>

<div class="centered"><table border="1">
 <tr>
  <td>Legend</td>
 </tr>
 <tr class="ok">
  <td>The translation is up-to-date!</td>
 </tr>
 <tr class="old">
  <td>The translation is a bit old (revisions &lt;= 5)</td>
 </tr>
 <tr class="critical">
  <td>The translation is highly outdated. Please update ASAP!</td>
 </tr>
</table></div>

<?php
common_footer();
?>
