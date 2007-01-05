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
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 */

/**
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Frontend for demonstration of current state of ILIAS SCORM 2004 
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id: $
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *  
 */ 
 

/* THIS IS FOR DEBUGGING PURPOSES ONLY */


$basehref = dirname($_SERVER['SCRIPT_NAME']) . '/';

?><html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>SCO</title>
	<link type="text/css" rel="stylesheet" href="<?print($basehref)?>templates/css/admin.css"/> 

<script type="text/javascript">

window.onload = function () { // replace later by Attach Event 
	document.onclick = function (e) {
		var apiname = 'API_1484_11';
		var target = e ? e.target : event.srcElement;
		if (target.tagName === "A" && target.className === "btn") {
			if (typeof window[target.id + '_onclick'] === "function")
			{
				return window[target.id + '_onclick']();
			}
			var api = parent[apiname];
			if (!api) {
				alert(apiname + " not found");
				return false; 
			}  
			var btn = target.id.substr(3);
			var f = document.forms[0];
			if (api && typeof(api[btn])==="function") {
				f.cmireturn.value = api[btn](f.cmielement.value, f.cmivalue.value);
				f.cmidiagnostic.value = api.GetDiagnostic();
				f.cmierror.value = api.GetLastError();
				f.cmidiagnostic.value = api.GetErrorString();
			} else {
				alert(['not found', btn])
			} 
			return false;
		}
	}
	var chkWebContent = document.getElementById("chkWebContent");
	chkWebContent.checked = document.cookie && document.cookie.indexOf("true")!=-1;
	if (chkWebContent.checked) btnWebContent_onclick(); 
}

function btnWebContent_onclick()
{
	var elm = document.getElementById('btnWebContent');
	var frm = document.getElementById('frmWebContent');
	if (!frm) 
	{
		frm = document.body.appendChild(document.createElement("IFRAME")); 
		frm.id = "frmWebContent";
		frm.width = "100%";
		frm.height = "600";
		frm.src = elm.innerHTML.indexOf(":")===-1 ? "<?print($basehref)?>" + elm.innerHTML : elm.innerHTML;
	}
	else 
	{
		frm.style.display = frm.style.display == 'none' ? '' : 'none'; 	
	}
	document.getElementsByTagName("TBODY")[0].style.display = frm.style.display == 'none' ? '' : 'none';
}

function chkWebContent_click(newState) 
{
	parent.document.cookie = newState;
	if (newState) btnWebContent_onclick();
}

</script>
  </head>
  <body>

<form action="" onsubmit="return false;" >

<table width="100%" >
<caption>SCO Dummy Implementation (API_1484_11 GUI)</caption>
<thead>
<tr >
	<th>Web Content	</th>
	<td>
		<a class="btn" href="#" id="btnWebContent">
			<? print($_SERVER['PATH_INFO'] . '?' . $_SERVER['QUERY_STRING']); ?>
		</a>
		<label align="right"><input type="checkbox" onclick="chkWebContent_click(this.checked)" id="chkWebContent"/> Auto Show</label>
	</tr>
<tr >
	<th>API Log</th>
	<td>
		<select id="apilog" size="3">
		</select>
	</td>
	</tr>
</thead>
<tbody>

<tr >
	<td>
	</td>
	<td ><br/>
		<a class="btn" href="#" id="btnInitialize">Initialize</a>
		<a class="btn" href="#" id="btnCommit">Commit</a>
		<a class="btn" href="#" id="btnTerminate">Terminate</a>
		<a class="btn" href="#" id="btnGetValue">GetValue</a>
		<a class="btn" href="#" id="btnSetValue">SetValue</a>
		<a class="btn" href="#" id="btnGetDiagnostic">GetDiagnostic</a>
		<a class="btn" href="#" id="btnGetErrorString">GetErrorString</a>
		<a class="btn" href="#" id="btnGetLastError">GetLastError</a>
	</td>
</tr>

<tr >
	<th>CMI Element	</th>
	<td><input class="text" type="text" name="cmielement"/>	</td>
	</tr>

<tr >
	<th>CMI Value	</th>
	<td><input class="text" type="text" name="cmivalue"/>	</td>
	</tr>

<tr >
	<th>CMI Return	</th>
	<td><input class="text" type="text" name="cmireturn"/>	</td>
	</tr>

<tr >
	<th>CMI Error	</th>
	<td><input class="text" type="text" name="cmierror"/>	</td>
	</tr>

<tr >
	<th>CMI Diagnostic	</th>
	<td><input class="text" type="text" name="cmidiagnostic"/>	</td>
	</tr>

<tr >
	<th>CMI Data</th>
	<td>
		<select name="cmidata" size="10">
		</select>
	</td>
	</tr>
</tbody>
<tfoot>
</tfoot>
</table>
</form>

  </body>
</html>
