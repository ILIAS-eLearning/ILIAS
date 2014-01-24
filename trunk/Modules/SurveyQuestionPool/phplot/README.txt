This is the README file for PHPlot
Last updated for PHPlot-5.0.5 on 2008-01-13
The project web site is http://sourceforge.net/projects/phplot/
The project home page is http://phplot.sourceforge.net/
-----------------------------------------------------------------------------

OVERVIEW:

PHPlot is a PHP class for creating scientific and business charts.

The release documentation contains only summary information. For more
complete information, download the PHPlot Reference Manual from the
Sourceforge project web site. You can also view the manual online at
http://phplot.sourceforge.net

For important changes in this release, see the NEWS.txt file.


CONTENTS:

   ChangeLog  . . . . . . . . . . . Lists changes to the sources
   LICENSE.GPL  . . . . . . . . . . License file
   LICENSE.PHP_3_0  . . . . . . . . License file
   NEWS.txt . . . . . . . . . . . . Highlights changes in releases
   README.txt   . . . . . . . . . . This file
   phplot.php   . . . . . . . . . . The main PHPlot source file
   phplot_data.php  . . . . . . . . Auxiliary and extended functions
   rgb.inc.php  . . . . . . . . . . Optional extended color table


REQUIREMENTS:

You need a recent version of PHP5, and you are advised to use the latest
stable release.  This version of PHPlot has been tested with PHP-5.2.5.
(PHPlot-5.0.5 does not support PHP4, since the PHP group has discontinued
support for PHP4.)

You need the GD extension to PHP either built in to PHP or loaded as a
module. Refer to the PHP documentation for more information - see the
Image Functions chapter in the PHP Manual. We test PHPlot only with the
PHP-supported, bundled GD library.

If you want to display PHPlot charts on a web site, you need a PHP-enabled
web server. You can also use the PHP CLI interface without a web server.

PHPlot supports TrueType fonts, but does not include any TrueType font
files.  If you want to use TrueType fonts on your charts, you need to have
TrueType support in GD, and some TrueType font files.  By default, PHPlot
uses a simple font built-in to the GD library.


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


KNOWN ISSUES:

Here are some of the problems we know about in PHPlot. See the bug tracker
on the PHPlot project web site for more information.

#1795969 The automatic range calculation for Y values needs to be rewritten.  
  This is especially a problem with small offset ranges (e.g. Y=[999:1001]).
  You can use SetPlotAreaWorld to set a specific range instead.

#1605558 Wide/Custom dashed lines don't work well
  This is partially a GD issue, partially PHPlot's fault.

#1795972 and #1795971: Default data colors and default point shapes need to
  be improved.

Tick interval calculations should try for intervals of 1, 2, or 5 times a
power of 10.


If you think you found a problem with PHPlot, or want to ask questions or
provide feedback, please use the Help forum at
     http://sourceforge.net/projects/phplot/
If you are relatively sure you have found a bug, you can report it on the
bug tracker at the same web site.



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
$plot = new PHPlot();
$data = array(array('', 0, 0), array('', 1, 9));
$plot->SetDataValues($data);
$plot->SetDataType('data-data');
$plot->DrawGraph();
---------------------------------------------------------

Access the URL to 'simpleplot.html' in your web browser. If you see a
simple graph, you have successfully installed PHPlot. If you see no
graph, check your web server error log for more information.


COPYRIGHT and LICENSE:

PHPlot is Copyright (C) 1998-2008 Afan Ottenheimer

This is distributed with NO WARRANTY and under the terms of the GNU GPL
and PHP licenses. If you use it - a cookie or some credit would be nice.

You can get a copy of the GNU GPL at http://www.gnu.org/copyleft/gpl.html
You can get a copy of the PHP License at http://www.php.net/license.html

See http://sourceforge.net/projects/phplot/ for the latest information.
