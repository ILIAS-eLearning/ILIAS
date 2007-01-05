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
 
?><html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>
      SCORM 2004 Player DEMO
    </title>
	<link type="text/css" href="templates/css/player.css" rel="stylesheet"/>
	<link type="text/css" href="templates/css/admin.css" rel="stylesheet"/>
	<script type="text/javascript" src="scripts/remoting.js" charset="ISO-8859-1"></script>
	<script type="text/javascript" src="scripts/scormapi.js" charset="ISO-8859-1"></script>
	<script type="text/javascript" src="player.php?call=cp&amp;packageId=<?=$_REQUEST['packageId']?>" charset="UTF-8"></script>
	<script type="text/javascript" src="player.php?call=cmi&amp;packageId=<?=$_REQUEST['packageId']?>" charset="UTF-8"></script>
	<script type="text/javascript" src="scripts/gui.js" charset="ISO-8859-1"></script>
	<script type="text/javascript" src="scripts/sequencing.js" charset="ISO-8859-1"></script>
	<script type="text/javascript" src="scripts/player.js" charset="ISO-8859-1"></script>	
	<base target="frmResource" />
	</head>
	<body>

		<table border="0" width="100%" height="96%" cellpadding="0" cellspacing="0">
			<caption>
				SCORM 2004 Player DEMO
			</caption>
			<tr height="28">
				<td>
					<a target="_self" href="#" class="btn" id="btnStart">Start</a> 
					<a target="_self" href="#" class="btn" id="btnResumeAll">ResumeAll</a>
				</td>
				<td id="tdControls" valign="middle" align="right">
					<div style="float: left"> 
						<a target="_self" href="#" class="btn" id="btnBackward">Backward</a>
						<a target="_self" href="#" class="btn" id="btnForward">Forward</a>
						|
						<a target="_self" href="#" class="btn" id="btnExit">Exit</a>
						<a target="_self" href="#" class="btn" id="btnExitAll">ExitAll</a>
						|
						<a target="_self" href="#" class="btn" id="btnAbandon">Abandon</a>
						<a target="_self" href="#" class="btn" id="btnAbandonAll">AbandonAll</a>
						|
						<a target="_self" href="#" class="btn" id="btnSuspendAll">SuspendAll</a>
					</div>
					<div style="float: right"> 
						<a target="_self" href="#" class="btn" id="btnPrevious">&lt; Previous</a>
						<a target="_self" href="#" class="btn" id="btnContinue">Continue &gt;</a>
						</div>
				</td>
			</tr>
			<tr>
				<td width="30%" valign="top" align="left">
					<table width="100%" height="95%>
						<tr height="28">
							<td id="tdItems">
								<select style="width: 100%" id="listView"></select>
							</td>
						</tr>
						<tr height="42%">
							<td >
								<div style="overflow: scroll; overflow-x: hidden; width: 100%; height: 100%" id="treeView"></div>
							</td>
						</tr>
						<tr height="42%">
							<td >
								<div style="overflow: scroll; overflow-x: hidden; width: 100%; height: 100%" id="reportView"></div>
							</td>
						</tr>
						<tr height="86">
							<td >
								<div style="overflow: scroll; overflow-y: hidden; width: 100%; height: 100%" >
								
									<div id="stripView"><div>
								</div>
							</td>
						</tr>
						<tr height="28">
							<td>
								Sequencing Log<br/>
								<select style="font: 12px monospace;" id="seqlog" size="6">
								</select>
							</td>
						</tr>
					</table>				
				</td>
				<td id="tdResource" width="70%" >
				</td>
			</tr>
		</table>
			
	</body>

</html>

