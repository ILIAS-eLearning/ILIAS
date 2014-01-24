<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
/*
	Script to mimimze all JS sources for the RTE into one file
	@author Hendrik Holtmann <holtmann@mac.com>
	
	This software is provided "AS IS," without a warranty of any kind.  ALL 
	EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING 
	ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE 
	OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL Co-Lab Hub AND ITS LICENSORS 
	SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF 
	USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO 
	EVENT WILL ADL Co-Lab Hub OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE, 
	PROFIT OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL, 
	INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE 
	THEORY OF LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE 
	SOFTWARE, EVEN IF ADL Co-Lab Hub HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH 
	DAMAGES.
*/

	require_once "JSMin_lib.php";

	//location of the RTE-script-files
	$location = "../scripts";
	
	//list all scripts that are needed for the RTE
	$mandatory_scripts = array( "sequencer/ADLAuxiliaryResource.js",
	 						    "sequencer/ADLDuration.js",
	 							"sequencer/ADLLaunch.js",
	 							"sequencer/ADLObjStatus.js",
								"sequencer/ADLSeqUtilities.js",
								"sequencer/ADLSequencer.js",
								"sequencer/ADLTOC.js",
								"sequencer/ADLTracking.js",
								"sequencer/ADLValidRequests.js",
								"sequencer/Basics.js",
								"sequencer/SeqActivity.js",
								"sequencer/SeqActivityTree.js",
								"sequencer/SeqCondition.js",
								"sequencer/SeqConditionSet.js",
								"sequencer/SeqNavRequest.js",
								"sequencer/SeqObjective.js",
								"sequencer/SeqObjectiveMap.js",
								"sequencer/SeqObjectiveTracking.js",
								"sequencer/SeqRollupRule.js",
								"sequencer/SeqRollupRuleset.js",
								"sequencer/SeqRule.js",
								"sequencer/SeqRuleset.js",
								"rtemain/main.js",
								"rtemain/rte.js");
  
	
	//minimize all scripts
	foreach ($mandatory_scripts as $file) {
		$inp = file_get_contents($location."/".$file);
		$jsMin = new JSMin($inp, false);
		$jsMin->minify();
		$outjsmin[] = $jsMin->out;
		$out[] = $inp;
	}
	$timestamp = time();
	$f_time=date("YndHis",$timestamp);
	$comment="// Build: $f_time \n";
	$outjsmin = implode("", $outjsmin);
	$out = implode("", $out);
	$outjsmin=$comment.$outjsmin;
	$out=$comment.$out;
	$filenamemin="../scripts/buildrte/rte-min.js";
	$filename="../scripts/buildrte/rte.js";
	file_put_contents($filenamemin, $outjsmin);
	file_put_contents($filename, $out);
?>