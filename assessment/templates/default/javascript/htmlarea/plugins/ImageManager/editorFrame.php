<?

/**
 * The frame that contains the image to be edited.
 * @author $Author$
 * @version $Id$
 * @package ImageManager
 */

require_once('config.inc.php');
require_once('Classes/ImageManager.php');
require_once('Classes/ImageEditor.php');

$manager = new ImageManager($IMConfig);
$editor = new ImageEditor($manager);
$imageInfo = $editor->processImage();

?>

<html>
<head>
	<title></title>
<link href="assets/editorFrame.css" rel="stylesheet" type="text/css" />	
<script type="text/javascript" src="assets/wz_jsgraphics.js"></script>
<script type="text/javascript" src="assets/EditorContent.js"></script>
<script type="text/javascript">
if(window.top)
	I18N = window.top.I18N;

function i18n(str) {
	if(I18N)
		return (I18N[str] || str);
	else
		return str;
};
	
	var mode = "<? echo $editor->getAction(); ?>" //crop, scale, measure

var currentImageFile = "<? if(count($imageInfo)>0) echo rawurlencode($imageInfo['file']); ?>";

<? if ($editor->isFileSaved() == 1) { ?>
	alert(i18n('File saved.'));
<? } else if ($editor->isFileSaved() == -1) { ?>
	alert(i18n('File was not saved.'));
<? } ?>

</script>
<script type="text/javascript" src="assets/editorFrame.js"></script>
</head>

<body>
<div id="status"></div>
<div id="ant" class="selection" style="visibility:hidden"><img src="img/spacer.gif" width="0" height="0" border="0" alt="" id="cropContent"></div>
<? if ($editor->isGDEditable() == -1) { ?>
	<div style="text-align:center; padding:10px;"><span class="error">GIF format is not supported, image editing not supported.</span></div>
<? } ?>
<table height="100%" width="100%">
	<tr>
		<td>
<? if(count($imageInfo) > 0 && is_file($imageInfo['fullpath'])) { ?>
	<span id="imgCanvas" class="crop"><img src="<? echo $imageInfo['src']; ?>" <? echo $imageInfo['dimensions']; ?> alt="" id="theImage" name="theImage"></span>
<? } else { ?>
	<span class="error">No Image Available</span>
<? } ?>
		</td>
	</tr>
</table>
</body>
</html>
