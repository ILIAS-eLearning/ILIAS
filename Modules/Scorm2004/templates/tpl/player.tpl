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
 *  
 */ 
 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>SCORM 2004 Player DEMO</title>
		<link type="text/css" href="templates/css/player.css" rel="stylesheet"/>
		<link type="text/css" href="templates/css/admin.css" rel="stylesheet"/>
		<base target="frmResource" />
	</head>
	<body>
	
		<table id="introTable" border="0" cellpadding="0" cellspacing="0" summary="">
			<tr>
				<td style="text-align: center; vertical-align: middle;">
					<noscript class="warning">JAVASCRIPT NEEDED</noscript>
					<div class="warning" style="display: none">PAGE CSS NEEDED</div>
					<img src="templates/img/intro.gif" alt=""/>
					<br/>
					<span id="introLabel"></span>
				</td>
			</tr>
		</table>
		
		<table id="mainTable" border="0" cellpadding="0" cellspacing="0" summary="" width="100%" >
			<caption id="mainTitle">
				SCORM 2004 Player DEMO 
			</caption>
			<tbody>
			<tr>
				<td width="30%" height="56">
					<select style="width: 100%" id="listView"></select>
				</td>
				<td width="70%" id="tdControls" valign="middle" align="right" style="text-align: right; line-height: 1.7;" height="56">
					<a target="_self" href="#" class="btn" id="navStart">Start</a> 
					<a target="_self" href="#" class="btn" id="navResumeAll">ResumeAll</a>
					
					<a style="display: none" target="_self" href="#" class="btn" id="navBackward">Backward</a>
					<a style="display: none" target="_self" href="#" class="btn" id="navForward">Forward</a>
					
					<a target="_self" href="#" class="btn" id="navExit">Exit</a>
					<a target="_self" href="#" class="btn" id="navExitAll">ExitAll</a>
					
					<a target="_self" href="#" class="btn" id="navAbandon">Abandon</a>
					<a target="_self" href="#" class="btn" id="navAbandonAll">AbandonAll</a>
					
					<a target="_self" href="#" class="btn" id="navSuspendAll">SuspendAll</a>
					
					<a target="_self" href="#" class="btn" id="navPrevious">&lt; Previous</a>
					<a target="_self" href="#" class="btn" id="navContinue">Continue &gt;</a>
			</td>
			</tr>
			<tr height="540">
				<td width="30%" valign="top" align="left">
					<table summary="" style="width: 100%; height: 100%;">
						<tr>
							<td >
								<div style="overflow: scroll; overflow-x: hidden; width: 100%; height: 100%" id="treeView"></div>
							</td>
						</tr>
					</table>				
				</td>
				<td id="tdResource" width="70%">
				</td>			
			</tr>
			</tbody>
		</table>
		
		<? if (true || $_REQUEST['debug']) { ?>
		<!-- begin debug  -->
		<br/>
		<form name="debug" action="" onsubmit="return false;" >
		<table id="debugTable" border="0" cellpadding="0" cellspacing="0" summary="">
			<caption>Debug Information</caption>
			<tr>
				<td width="30%">
					Sequencing Log<br/>
					<select style="font: 12px monospace;" id="seqlog" size="28"></select>
				</td>
				<td width="70%">
					<table>
					<thead>
					<tr >
						<th>Web Content	</th>
						<td>
							<a class="btn" href="#" id="btnWebContent">
								<? print($_SERVER['PATH_INFO'] . '?' . $_SERVER['QUERY_STRING']); ?>
							</a>
							<label align="right"><input type="checkbox" onclick="chkWebContent_click(this.checked)" id="chkWebContent"/> Auto Show</label>
							<a class="btn" href="#" id="btnCMIDataShow">CMI Data Show</a>
							<a class="btn" href="#" id="btnCMIDataSave">CMI Data Save</a>
						</tr>
					<tr >
						<th>API Log</th>
						<td>
							<select id="apilog" size="6"></select>
						</td>
						</tr>
					</thead>
					<tbody>
					
					<tr >
						<td>
						</td>
						<td style="line-height: 1.7"><br/>
							<a class="btn" href="#" id="apiInitialize">Initialize</a>
							<a class="btn" href="#" id="apiCommit">Commit</a>
							<a class="btn" href="#" id="apiTerminate">Terminate</a>
							<a class="btn" href="#" id="apiGetValue">GetValue</a>
							<a class="btn" href="#" id="apiSetValue">SetValue</a>
							<a class="btn" href="#" id="apiGetDiagnostic">GetDiagnostic</a>
							<a class="btn" href="#" id="apiGetErrorString">GetErrorString</a>
							<a class="btn" href="#" id="apiGetLastError">GetLastError</a>
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
							<textarea id="cmidata" name="cmidata" cols="40" rows="7"></textarea>
						</td>
						</tr>
					<tr >
						<th>CMI Save</th>
						<td>
							<textarea id="cmisave" name="cmisave" cols="40" rows="7"></textarea>
						</td>
						</tr>
							</tbody>
						</table>
					</td>
				</tr>

			</table>
		</form>
		<!-- end debug  -->
		<? } // end debug ?>
		
		
		<!-- Load scripts after rest of page is already visible (instead of DEFER) -->
		<script type="text/javascript" src="scripts/remoting.js" charset="ISO-8859-1"></script>
		<script type="text/javascript" src="scripts/scormapi.js" charset="ISO-8859-1"></script>
		<script type="text/javascript" src="scripts/scormapi-1.3.js" charset="ISO-8859-1"></script>
		<script type="text/javascript" src="scripts/gui.js" charset="ISO-8859-1"></script>
		<script type="text/javascript" src="scripts/sequencing.js" charset="ISO-8859-1"></script>
		<script type="text/javascript" src="scripts/cmicache.js" charset="ISO-8859-1"></script>
		<script type="text/javascript" src="scripts/player.js" charset="ISO-8859-1"></script>
		<!--
			Later on load all static js in one step (if compressed may load in 4 to 5 sec on 56k Modem)  
			<script type="text/javascript" src="scripts/static.php" charset="ISO-8859-1"></script>		 
		-->
		<script type="text/javascript">
			var player = new Player(
				<?=json_encode($config)?>, 
				new Gui(<?=json_encode($gui)?>, <?=json_encode($langstrings)?>)
			);
		</script>
	</body>
</html>

