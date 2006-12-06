This is the README file for PHPlot
Last updated for PHPlot-5.0rc3 on 2006-11-13
The project home page is http://sourceforge.net/projects/phplot/
-----------------------------------------------------------------------------

OVERVIEW:

PHPlot is a PHP class for creating scientific and business charts.

The release documentation contains only summary information. For more
complete information, refer to the PHPlot Reference Manual available on the
Sourceforge project web site.


CONTENTS:

   ChangeLog  . . . . . . . . . . . Lists all changes to the sources
   LICENSE.GPL  . . . . . . . . . . License file
   LICENSE.PHP_3_0  . . . . . . . . License file
   NEWS.txt . . . . . . . . . . . . Highlights changes in releases
   README.txt   . . . . . . . . . . This file
   phplot.php   . . . . . . . . . . The main PHPlot source file
   phplot_data.php  . . . . . . . . Auxiliary and extended functions
   rgb.inc.php  . . . . . . . . . . Optional extended color table


REQUIREMENTS:

You need a recent version of PHP: 4.3.0 or higher. You are advised to use
the latest stable release of PHP 4. (Testing with PHP 5 is not complete
at this time.)

You need the GD extension to PHP either built in to PHP or loaded as a
module. Refer to the PHP documentation for more information - see the
Image Functions chapter in the PHP Manual.

If you want to display PHPlot charts on a web site, you need a PHP-enabled
web server. You can also use the PHP CLI interface without a web server.

If you want to use TrueType fonts on your charts, you need to have TrueType
support in GD, and some TrueType font files. PHPlot does not include any
TrueType font files. By default, PHPlot uses a simple built-in font.


INSTALLATION:

Unpack the distribution. (If you are reading this file, you have probably
already done that.)

Installation of PHPlot simply involves copying three script files somewhere
your PHP application scripts will be able to find them. The scripts are:
     phplot.php
     phplot_data.php
     rgb.inc.php
(Only phplot.php is necessary for most graphs.)
Make sure the protections on these files allow the web server to read them.

The ideal place is a directory outside your web server document area,
and on your PHP include path. You can add to the include path in the PHP
configuration file; consult the PHP manual for details.



TESTING:

You can test your installation by creating the following two files somewhere
in your web document area. First, the HTML file:

------------ simpleplot.html ----------------------------
<html>
<head>
<title>Hello, PHPlot!</title>
</head>
<body>
<h1>PHPlot Test</h1>
<img src="simpleplot.php">
</body>
</html>
---------------------------------------------------------

Second, in the same directory, the image file producing PHP script file.
Depending on where you installed phplot.php, you may need to specify a path
in the 'require' line below.

------------ simpleplot.php -----------------------------
<?php
require 'phplot.php';
$plot =& new PHPlot();
$data = array(array('', 0, 0), array('', 1, 9));
$plot->SetDataValues($data);
$plot->SetDataType('data-data');
$plot->DrawGraph();
---------------------------------------------------------

Access the URL to 'simpleplot.html' in your web browser. If you see a
simple graph, you have successfully installed PHPlot. If you see no
graph, check your web server error log for more information.


COPYRIGHT and LICENSE:

PHPlot is Copyright (C) 1998-2006 Afan Ottenheimer

This is distributed with NO WARRANTY and under the terms of the GNU GPL
and PHP licenses. If you use it - a cookie or some credit would be nice.

You can get a copy of the GNU GPL at http://www.gnu.org/copyleft/gpl.html
You can get a copy of the PHP License at http://www.php.net/license.html

See http://www.sourceforge.net/projects/phplot/ for the latest changes.
