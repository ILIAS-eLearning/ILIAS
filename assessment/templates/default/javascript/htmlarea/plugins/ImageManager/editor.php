<?
/**
 * The PHP Image Editor user interface.
 * @author $Author$
 * @version $Id$
 * @package ImageManager
 */

require_once('config.inc.php');
require_once('Classes/ImageManager.php');
require_once('Classes/ImageEditor.php');

$manager = new ImageManager($IMConfig);
$editor = new ImageEditor($manager);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
	<title></title>
	<link href="assets/editor.css" rel="stylesheet" type="text/css" />	
<script type="text/javascript" src="assets/slider.js"></script>
<script type="text/javascript" src="assets/popup.js"></script>
<script type="text/javascript">
/*<![CDATA[*/
	window.resizeTo(673, 531);
	if(window.opener)
		I18N = window.opener.I18N;
/*]]>*/
</script>
<script type="text/javascript" src="assets/editor.js"></script>
</head>

<body>
<div id="indicator">
<img src="img/spacer.gif" id="indicator_image" height="20" width="20" alt="" />
</div>
<div id="tools">
	<div id="tools_crop" style="display:none;">
		<div id="tool_inputs">
			<label for="cx">Start X:</label><input type="text" id="cx"  class="textInput" onchange="updateMarker('crop')"/>
			<label for="cy">Start Y:</label><input type="text" id="cy" class="textInput" onchange="updateMarker('crop')"/>
			<label for="cw">Width:</label><input type="text" id="cw" class="textInput" onchange="updateMarker('crop')"/>
			<label for="ch">Height:</label><input type="text" id="ch" class="textInput" onchange="updateMarker('crop')"/>
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
		</div>	
		<a href="javascript: editor.doSubmit('crop');" class="buttons" title="OK"><img src="img/btn_ok.gif" height="30" width="30" alt="OK" /></a>
		<a href="javascript: editor.reset();" class="buttons" title="Cancel"><img src="img/btn_cancel.gif" height="30" width="30" alt="Cancel" /></a>		
	</div>	
	<div id="tools_scale" style="display:none;">
		<div id="tool_inputs">
			<label for="sw">Width:</label><input type="text" id="sw" class="textInput" onchange="checkConstrains('width')"/>
			<a href="javascript:toggleConstraints();" title="Lock"><img src="img/islocked2.gif" id="scaleConstImg" height="14" width="8" alt="Lock" class="div" /></a><label for="sh">Height:</label>
			<input type="text" id="sh" class="textInput" onchange="checkConstrains('height')"/>
			<input type="checkbox" id="constProp" value="1" checked="checked" onclick="toggleConstraints()"/>
			<label for="constProp">Constrain Proportions</label>
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
		</div>	
		<a href="javascript: editor.doSubmit('scale');" class="buttons" title="OK"><img src="img/btn_ok.gif" height="30" width="30" alt="OK" /></a>
		<a href="javascript: editor.reset();" class="buttons" title="Cancle"><img src="img/btn_cancel.gif" height="30" width="30" alt="Cancel" /></a>		
	</div>	
	<div id="tools_rotate" style="display:none;">
		<div id="tool_inputs">
			<select id="flip" name="flip" style="margin-left: 10px; vertical-align: middle;">
              <option selected>Flip Image</option>
              <option>-----------------</option>
              <option value="hoz">Flip Horizontal</option>
              <option value="ver">Flip Virtical</option>
         </select>
			<select name="rotate" onchange="rotatePreset(this)" style="margin-left: 20px; vertical-align: middle;">
              <option selected>Rotate Image</option>
              <option>-----------------</option>

              <option value="180">Rotate 180 &deg;</option>
              <option value="90">Rotate 90 &deg; CW</option>
              <option value="-90">Rotate 90 &deg; CCW</option>
         </select>
			<label for="ra">Angle:</label><input type="text" id="ra" class="textInput" />
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
		</div>	
		<a href="javascript: editor.doSubmit('rotate');" class="buttons" title="OK"><img src="img/btn_ok.gif" height="30" width="30" alt="OK" /></a>
		<a href="javascript: editor.reset();" class="buttons" title="Cancle"><img src="img/btn_cancel.gif" height="30" width="30" alt="Cancel" /></a>		
	</div>		
	<div id="tools_measure" style="display:none;">
		<div id="tool_inputs">
			<label>X:</label><input type="text" class="measureStats" id="sx" />
			<label>Y:</label><input type="text" class="measureStats" id="sy" />
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
			<label>W:</label><input type="text" class="measureStats" id="mw" />
			<label>H:</label><input type="text" class="measureStats" id="mh" />
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
			<label>A:</label><input type="text" class="measureStats" id="ma" />		
			<label>D:</label><input type="text" class="measureStats" id="md" />		
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
			<button type="button" onclick="editor.reset();" >Clear</button>
		</div>	
	</div>
	<div id="tools_save" style="display:none;">
		<div id="tool_inputs">
			<label for="save_filename">Filename:</label><input type="text" id="save_filename" value="<? echo $editor->getDefaultSaveFile();?>"/>
			<select name="format" id="save_format" style="margin-left: 10px; vertical-align: middle;" onchange="updateFormat(this)">
            <option value="" selected>Image Format</option>
            <option value="">---------------------</option>
            <option value="jpeg,85">JPEG High</option>
            <option value="jpeg,60">JPEG Medium</option>
            <option value="jpeg,35">JPEG Low</option>
            <option value="png">PNG</option>
			<? if($editor->isGDGIFAble() != -1) { ?>
            <option value="gif">GIF</option>
			<? } ?>
         </select>
			<label>Quality:</label>
			<table style="display: inline; vertical-align: middle;" cellpadding="0" cellspacing="0">
				<tr>
				<td>
					<div id="slidercasing"> 
				<div id="slidertrack" style="width:100px"><img src="img/spacer.gif" width="1" height="1" border="0" alt="track"></div>
            <div id="sliderbar" style="left:85px" onmousedown="captureStart();"><img src="img/spacer.gif" width="1" height="1" border="0" alt="track"></div>
			</div>	
				</td>
				</tr>
			</table>				
			<input type="text" id="quality" onchange="updateSlider(this.value)" style="width: 2em;" value="85"/>
			<img src="img/div.gif" height="30" width="2" class="div" alt="|" />
		</div>	
		<a href="javascript: editor.doSubmit('save');" class="buttons" title="OK"><img src="img/btn_ok.gif" height="30" width="30" alt="OK" /></a>
		<a href="javascript: editor.reset();" class="buttons" title="Cancel"><img src="img/btn_cancel.gif" height="30" width="30" alt="Cancel" /></a>		
	</div>	
</div>
<div id="toolbar">
<a href="javascript:toggle('crop')" id="icon_crop" title="Crop"><img src="img/crop.gif" height="20" width="20" alt="Crop" /><span>Crop</span></a>
<a href="javascript:toggle('scale')" id="icon_scale" title="Resize"><img src="img/scale.gif" height="20" width="20" alt="Resize" /><span>Resize</span></a>
<a href="javascript:toggle('rotate')" id="icon_rotate" title="Rotate"><img src="img/rotate.gif" height="20" width="20" alt="Rotate" /><span>Rotate</span></a>
<a href="javascript:toggle('measure')" id="icon_measure" title="Measure"><img src="img/measure.gif" height="20" width="20" alt="Measure" /><span>Measure</span></a>
<a href="javascript: toggleMarker();" title="Marker"><img id="markerImg" src="img/t_black.gif" height="20" width="20" alt="Marker" /><span>Marker</span></a>
<a href="javascript:toggle('save')" id="icon_save" title="Save"><img src="img/save.gif" height="20" width="20" alt="Save" /><span>Save</span></a>
</div>
<div id="contents">
<iframe src="editorFrame.php?img=<? if(isset($_GET['img'])) echo rawurlencode($_GET['img']); ?>" name="editor" id="editor"  scrolling="auto" title="Image Editor" frameborder="0"></iframe>
</div>
<div id="bottom"></div>
</body>
</html>
