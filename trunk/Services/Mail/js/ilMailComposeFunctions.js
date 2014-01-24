// inserts placeholder at current coursor position
function insertTextIntoTextField(text, obj_id)
{
    if (text && obj_id)
    {
		var objTextField = document.getElementById(obj_id);
		
		if (document.selection)
		{
        	objTextField.focus();
            sel = document.selection.createRange();
            sel.text = text;
        }
        else if (objTextField.selectionStart || objTextField.selectionStart == '0')
        {
            var startPos = objTextField.selectionStart;
            var endPos = objTextField.selectionEnd;
            var TextFieldValue = objTextField.value;

            objTextField.value = TextFieldValue.substring(0, startPos) +
	    				text +
					TextFieldValue.substring(endPos, TextFieldValue.length);
        }
        else
        {
            objTextField.value += text;
        }
    }
}

// removes ',' at the ending of recipients textfield
function getStripCommaCallback(obj)
{
	return function ()
	{
		var val = obj.value.replace(/^\s+/, '').replace(/\s+$/, '');
		var stripcount = 0;
		var i;
		for (i = 0; i < val.length && val.charAt(val.length - i - 1) == ','; i++)
			stripcount++;
		obj.value = val.substr(0, val.length - stripcount);
	}
}

// initializes textfields for comma stripping on leaving recipients textfields
il.Util.addOnLoad(
	function()
	{
		var ar = ['rcp_to', 'rcp_cc', 'rcp_bcc'];
		for(var i = 0; i < ar.length; i++)
		{
			var obj = document.getElementById(ar[i]); 
			if (obj)
			{
				obj.onblur = getStripCommaCallback(document.getElementById(ar[i]));
			}
		}
	}
);
