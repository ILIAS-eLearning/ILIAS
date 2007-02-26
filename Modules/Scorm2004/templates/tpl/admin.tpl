<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert 
 */ 

?><html>
	<head>
   	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>SCORM 2004 Admin DEMO</title>
		<link type="text/css" rel="stylesheet" href="templates/css/admin.css"/> 
	</head>
	<body>
<?
/**
 * Finally write HTML body and footer 
 * and start with messages samples in request processing 
 */
if (isset($msg)) 
{
	print('<div class="msg">' . 
		(is_array($msg) ? implode('<br>', $msg) : $msg) . 
	'</div>');
}
?>

<form id="packagesForm" action="" 
	method="post" onsubmit="return packagesForm_submit();">
<script>
	function getSelectedIndex(arr) 
	{
		for (var i=arr.length-1; i>-1; i-=1)
		{
			if (arr[i].checked) break;
		}
		return i;
	}
	function packagesForm_submit() 
	{
		var a = document.getElementsByName("packageId");
		var i = getSelectedIndex(a);
		if (i==-1) 
		{
			alert('No package selected');
			return false;
		}
	}
	function btnPlay_click(mode) 
	{
		var a = document.getElementsByName("packageId");
		var i = getSelectedIndex(a);
		if (i>=0) 
		{
			open("player.php?call=player&packageId=" + a[i].value + "&debug=" + mode);
		}
		else
		{
			alert('No package selected');
		}
	}
	function uploadAndImport_click(f) 
	{
		if (f.packagedata.value) {
			f.enctype = "multipart/form-data";
			f.onsubmit = null;
			return true;
		} else {
			alert("No file selected");
			return false;
		}
	}
</script>
<table align="center">
<caption>Packages</caption>
<tr>
<td></td>
<td>
<fieldset>
<?
/**
 * Read the list of packages from DB to select commands on them.
 * Shows you how to use database class.
 */
foreach ($packages as $p) {
	print(
		'<input id="id' . $p['obj_id'] . '" type="radio" name="packageId" value="' . 
		$p['obj_id'] . 
		'"' . ($p['obj_id']==$_REQUEST['packageId'] ? ' checked="checked"' : '') . '><label for="id' . $p['obj_id'] . '">' . 
		$p['obj_id'] . ' [' . $p['identifier'] . ']' .  
		'</label><br/>' . NEWLINE
	);
}
?>
</select>
</td>
</tr>
<tr>
<td colspan="2" align="center">
<input class="button" type="button" 
	value="Refresh" title="Refreshes this page" onclick="location.href=location.href"/>
<input class="button" name="submit[removePackage]" type="submit" 
	value="Remove" title="Removes the package completely, tracking data will be lost"/>
<input class="button" type="button" 
	value="Play" title="" onclick="btnPlay_click(0)"/>
<input class="button" type="button" 
	value="Play (Debug)" title="" onclick="btnPlay_click(1)"/>
<br/>
<input class="button" name="submit[exportXML]" type="submit" 
	value="Export XML" title="Gives you the internal XML representation"/>
<input class="button" name="submit[exportManifest]" type="submit" 
	value="Export Manifest" title="Creates a IMS Manifest from database representation"/>
<br/>
<input class="button" name="submit[exportZIP]" type="submit" 
	value="Export ZIP" title="Gives you the original uploaded ZIP"/>
<input class="button" name="submit[exportPackage]" type="submit" 
	value="Export Package" title="Creates new package from database representation"/>
</td>
</tr>



<tr>
	<td colspan="2">
		<hr>
		Add New Package
	</td>
</tr>
<tr>
	<td>Title</td>
	<td>
		<input class="text" name="title" type="text" disabled="disabled" 
			value="will be set to MD5 hash of uploaded file"/>
	</td>
</tr>
<tr>
	<td>ID</td>
	<td><input class="text" name="id" type="text" value="<?print(IL_OP_PACKAGE_ID)?>" disabled="disabled" /></td>
</tr>
<tr>
	<td>File</td>
	<td><input type="hidden" name="MAX_FILE_SIZE" value="2000000" title="1 MB" />
		<input class="text" name="packagedata" type="file" />
		Max upload size is 2 MB
	</td>
</tr>
<tr>
	<td></td>
	<td>
		<input class="button" name="submit[uploadAndImport]" type="submit" value="Upload &amp; Import"
		onclick="return uploadAndImport_click(this.form)"
		/>
	</td>
</tr>


<tr>
<td colspan="2"><hr>
Package Settings</td>
</tr>
<tr>
	<td>Online</td>
	<td>
		<input name="online" type="checkbox" disabled="disabled" value="true" checked/>
	</td>
</tr>
<tr>
	<td>API Adapter</td>
	<td>
		<select name="api_adapter">
			<option disabled value="API">API</option>
			<option selected value="API_1484_11">API_1484_11</option>
		</select>
	</td>
</tr>
<tr>
	<td>API Prefix</td>
	<td>
		<select name="api_func_prefix">
			<option disabled value="LMS">LMS</option>
			<option selected value=""></option>
		</select> 
	</td>
</tr>
<tr>
	<td>Credit</td>
	<td>
		<select name="credit">
			<option selected value="credit">yes</option>
			<option disabled value="no_credit">no</option>
		</select> 
	</td>
</tr>
<tr>
	<td>Default Lesson Mode</td>
	<td>
		<select name="default_lesson_mode">
			<option selected value="normal">normal</option>
			<option disabled value="browse">browse</option>
		</select> 
	</td>
</tr>
<tr>
	<td>Auto Review</td>
	<td>
		<select name="auto_review">
			<option selected value="y">yes</option>
			<option disabled value="n">no</option>
		</select> 
	</td>
</tr>
<tr>
	<td>Type</td>
	<td>
		<select name="type">
			<option disabled value="scorm">SCORM 1.2</option>
			<option selected value="scorm2004">SCORM 2004</option>
			<option disabled value="aicc">AICC</option>
			<option disabled value="hacp">HACP</option>
		</select>
	</td>
</tr>
<tr>
	<td>Persistance</td>
	<td>
		<input disabled name="persistPreviousAttempts" type="checkbox" value="true" checked/> Persist Previous Attempts
	</td>
</tr>
<tr>
	<td>Auto Start</td>
	<td>
		<input disabled name="autoStart" type="checkbox" value="true" checked/> 
	</td>
</tr>
<tr>
	<td>New Window Name</td>
	<td>
		<input type="text" class="small-text" size=16 name="WindowName" value="" disabled> 
	</td>
</tr>
<tr>
	<td>New Window Params</td>
	<td>
		<input type="text" class="text" size=6 name="WindowParams" value="width=80%,height=80%,align=right,vertical=10%,resizable=yes" disabled> 
	</td>
</tr>
<tr>
	<td>Navigation</td>
	<td>
		<select name="Navigation">
			<option selected value="top">top</option>
			<option disabled value="bottom">bottom</option>
		</select>
	</td>
</tr>
<tr>
	<td>DropDownList</td>
	<td>
		<select name="DropDownList">
			<option disabled value="">none</option>
			<option selected value="left">left</option>
			<option disabled value="right">right</option>
		</select>
	</td>
</tr>
<tr>
	<td>TreeView</td>
	<td>
		<select name="TreeView">
			<option disabled value="">none</option>
			<option selected value="left">left</option>
			<option disabled value="right">right</option>
		</select>
	</td>
</tr>
<tr>
	<td>StripView</td>
	<td>
		<select name="StripView">
			<option disabled value="">none</option>
			<option selected value="top">top</option>
			<option disabled value="bottom">bottom</option>
		</select>
	</td>
</tr>
<tr>
	<td>ReportList</td>
	<td>
		<select name="ReportList">
			<option disabled value="">none</option>
			<option selected value="left">left</option>
			<option disabled value="right">right</option>
		</select>
	</td>
</tr>
<tr>
	<td>Left Column</td>
	<td>
		<input type="text" class="small-text" size=6 name="WidthLeft" value="30%" disabled> 
	</td>
</tr>
<tr>
	<td>Center Column</td>
	<td>
		<input type="text" class="small-text" size=6 name="WidthCenter" value="70%" disabled>
	</td>
</tr>
<tr>
	<td>Right Column</td>
	<td>
		<input type="text" class="small-text" size=6 name="WidthRight" value="0%" disabled>
	</td>
</tr>
<tr>
	<td>Top Row</td>
	<td>
		<input type="text" class="small-text" size=6 name="WidthLeft" value="56" disabled> 
	</td>
</tr>
<tr>
	<td>Center Row</td>
	<td>
		<input type="text" class="small-text" size=6 name="WidthCenter" value="*" disabled>
	</td>
</tr>
<tr>
	<td>Bottom Row</td>
	<td>
		<input type="text" class="small-text" size=6 name="WidthRight" value="0%" disabled>
	</td>
</tr>
<tr>
	<td>Interval</td>
	<td>
		<input type="text" class="small-text" size=6 name="storeMaxTime" value="60" disabled> Auto persist after # sec
	</td>
</tr>
<tr>
	<td>MaxSize</td>
	<td>
		<input type="text" class="small-text" size=6 name="storeMaxSize" value="2" disabled> Auto persist if buffer exeeds # items
	</td>
</tr>
<tr>
	<td></td>
	<td>
		<input class="button" name="submit[update]" type="submit" value="Update Settings"
			onclick="return update(this.form)"
		/>
	</td>
</tr>

</table>
</form>


<div style="max-height: inherit; white-space: pre" class="msg">
ILIAS SCORM 2004

PRELIMINARY EDITION 
This is work in progress and therefore incomplete and buggy ... 

Contact: alfred.kohnert@bigfoot.com

Prequisites
- PHP 5.1
- Extensions php_pdo, php_pdo_sqlite, php_json, php_xsl must be activated in PHP.  
- You need write access for web user to this module directory.
- The zip/unzip executables must be accessable in environmental path 
  (or modify settings in common.php).

Ilias Integration
Currently SCORM 2004 is not integrated in anyway. It does not use any ILIAS
modules, classes, data or settings. The server side processes you see are only
mockups for getting the client side player into development.  

Restrictions
- Currently we support only one package (with fixed package id of "100").
- Currently we support only one scorm user (with fixed user id of "50").

</div>

</body>
</html>
