/**
 * Javascript used by the editorFrame.php, it basically initializes the frame.
 * @author $Author$
 * @version $Id$
 * @package ImageManager
 */

var topDoc = window.top.document;

var t_cx = topDoc.getElementById('cx');
var t_cy = topDoc.getElementById('cy');
var t_cw = topDoc.getElementById('cw');
var t_ch = topDoc.getElementById('ch');

var m_sx = topDoc.getElementById('sx');
var m_sy = topDoc.getElementById('sy');
var m_w = topDoc.getElementById('mw');
var m_h = topDoc.getElementById('mh');
var m_a = topDoc.getElementById('ma');
var m_d = topDoc.getElementById('md');

var s_sw = topDoc.getElementById('sw');
var s_sh = topDoc.getElementById('sh');

var r_ra = topDoc.getElementById('ra');

var pattern = "img/2x2.gif";

function doSubmit(action)
{
    if (action == 'crop')
    {
        var url = "editorFrame.php?img="+currentImageFile+"&action=crop&params="+parseInt(t_cx.value)+','+parseInt(t_cy.value)+','+ parseInt(t_cw.value)+','+parseInt(t_ch.value);

        //alert(url);
        location.href = url;

        //location.reload();
    }   
    else if (action == 'scale')
    {
        var url = "editorFrame.php?img="+currentImageFile+"&action=scale&params="+parseInt(s_sw.value)+','+parseInt(s_sh.value);
        //alert(url);
        location.href = url;
        
    }
    else if (action == 'rotate')
    {
        var flip = topDoc.getElementById('flip');

        if(flip.value == 'hoz' || flip.value == 'ver') 
            location.href = "editorFrame.php?img="+currentImageFile+"&action=flip&params="+flip.value;
        else if (isNaN(parseFloat(r_ra.value))==false)
            location.href = "editorFrame.php?img="+currentImageFile+"&action=rotate&params="+parseFloat(r_ra.value);
    }
    else if(action == 'save') {
        var s_file = topDoc.getElementById('save_filename');
        var s_format = topDoc.getElementById('save_format');
        var s_quality = topDoc.getElementById('quality');

        var format = s_format.value.split(",");
        if(s_file.value.length <= 0) 
		{
            alert(i18n('Please enter a filename to save.'));
        }
        else
        {
            var filename = encodeURI(s_file.value);
            var quality = parseInt(s_quality.value);
            var url = "editorFrame.php?img="+currentImageFile+"&action=save&params="+format[0]+","+quality+"&file="+filename;
            //alert(url);
            location.href = url;
        }
    }
}


function addEvent(obj, evType, fn)
{ 
	if (obj.addEventListener) { obj.addEventListener(evType, fn, true); return true; } 
	else if (obj.attachEvent) {  var r = obj.attachEvent("on"+evType, fn);  return r;  } 
	else {  return false; } 
} 

var jg_doc

init = function()
{
	jg_doc = new jsGraphics("imgCanvas"); // draw directly into document
	jg_doc.setColor("#000000"); // black

	initEditor();
}

addEvent(window, 'load', init);

