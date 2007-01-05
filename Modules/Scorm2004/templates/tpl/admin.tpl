<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * Copyright (c) 2005-2007 Alfred Kohnert.
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 */

/**
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 */ 

?><html>
<head>
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
	function btnPlay_click() 
	{
		var a = document.getElementsByName("packageId");
		var i = getSelectedIndex(a);
		if (i>=0) 
		{
			open("player.php?call=player&packageId=" + a[i].value);
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
			f.submit();
		} else {
			alert("No file selected");
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
<input class="button" name="submit[removePackage]" type="submit" 
	value="Remove" title="Removes the package completely, tracking data will be lost"/>
<input class="button" type="button" 
	value="Play" title="" onclick="btnPlay_click()"/>
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
<td colspan="2"><hr>
Add New Package</td>
</tr>
<tr>
<td>Title</td>
<td><input class="text" name="title" type="text" disabled="disabled" 
	value="will be set to MD5 hash of uploaded file"/>
</td>
</tr>
<tr>
<td>ID</td>
<td><input class="text" name="id" type="text" value="100" disabled="disabled" /></td>
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
	<input class="button" name="submit[uploadAndImport]" type="button" value="Upload &amp; Import"
		onclick="return uploadAndImport_click(this.form)"
		/>
</td>
</tr>

</table>
</form>

</body>
</html>
