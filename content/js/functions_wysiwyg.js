
var editor = null;
function initEditor(TextAreaName) {
  editor = new HTMLArea(TextAreaName);

  var cfg = editor.config; // this is the default configuration

    cfg.registerButton({
      id        : "ilias-str",
      tooltip   : "Strong",
      image     : "./htmlarea/images/ed_format_bold.gif",
      textMode  : false,
      action    : function(editor, id) {
                    editor.surroundHTML('<span class="iliasstrong">', '</span>');
                  }
    });

    cfg.registerButton({
      id        : "ilias-emp",
      tooltip   : "Italic",
      image     : "./htmlarea/images/ed_format_italic.gif",
      textMode  : false,
      action    : function(editor, id) {
                    editor.surroundHTML('<span class="iliasemp">', '</span>');
                  }
    });

    cfg.registerButton({
      id        : "ilias-com",
      tooltip   : "Kommentar",
      image     : "./htmlarea/images/ed_com.gif",
      textMode  : false,
      action    : function(editor, id) {
                    editor.surroundHTML('<span class="iliascom">', '</span>');
                  }
    });

    cfg.registerButton({
      id        : "ilias-quot",
      tooltip   : "Quotation",
      image     : "./htmlarea/images/ed_quot.gif",
      textMode  : false,
      action    : function(editor, id) {
                    editor.surroundHTML('<span class="iliasquot">', '</span>');
                  }
    });
    
    cfg.registerButton({
      id        : "ilias-code",
      tooltip   : "Code",
      image     : "./htmlarea/images/ed_code.gif",
      textMode  : false,
      action    : function(editor, id) {
                    editor.surroundHTML('<code>', '</code>');
                  }
    });

	cfg.registerButton({
      id        : "ilias-fn",
      tooltip   : "Footnote",
      image     : "./htmlarea/images/ed_footnote.gif",
      textMode  : false,
      action    : function(editor, id) {
                    buttonFootnote();
                  }
    });

  	cfg.pageStyle = ".footnote { color:0000FF; } .iliasquot {color: rgb(165, 42, 42); font-style: italic;} .iliascom {color: rgb(0, 128, 0);} .iliasstrong {font-weight: bold;} .iliasemp {font-style: italic;} ";
	cfg.statusBar = false;
	
	cfg.toolbar = [
		[ 
		  "ilias-str", "ilias-emp", "ilias-com", "ilias-quot", "ilias-code", "separator", 
          
		  
		  "ilias-fn", 
		  "separator",
		  "copy", "cut", "paste", "space", "undo", "redo", 
		  
          ],

	];

    
/*
"insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
	cfg.toolbar = [
		[ "fontname", "space",
		  "fontsize", "space",
		  "formatblock", "space",
		  "bold", "italic", "underline", "strikethrough", "separator",
		  "subscript", "superscript", "separator",
		  "copy", "cut", "paste", "space", "undo", "redo" ],

		[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
		  "lefttoright", "righttoleft", "separator",
		  "insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
		  "forecolor", "hilitecolor", "separator",
		  "inserthorizontalrule", "createlink", "insertimage", "inserttable", "htmlmode", "separator",
		  "popupeditor", "separator", "showhelp", "about" ]
	];

*/    
    
  editor.config = cfg;
  
  // comment the following two lines to see how customization works
  editor.generate();
  return false;

}



	function replaceAll(heystack, needle, newneedle) {
		while (heystack.indexOf(needle)!=-1) {
			heystack = heystack.replace(needle, newneedle);
		}
		return(heystack);
	}

	var fussnoten = new Array();
	var fnCount = 0;
	
	function addFussnote(Tb)
	{
		fussnoten[fnCount] = Tb;
		fnCount++;
	}
	
	function buttonFootnote() {
	
		w = window.open("","footnote","width=450,height=350,resizable=yes");
		setTimeout("w.focus()",500);
		
		html = "<html><body>";
		html += "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />";
		html += "<b>Fussnoten / Footnotes</b><ul>";
		
		for (i=0;i<fussnoten.length;i++) {
		    j=i+1;
			html += "["+j+"]&nbsp;"+fussnoten[i]+"<br>"; 
		}
		html += "</ul>";
		
		html += "<form name='form'>";
		html += "<hr size=1>";
		html += "Neue Fussnote eintragen:<br>";
		html += "<input type='text' size=30 style='width:100%' name='fn'>";
		html += "<p>";
		html += "<table cellspacing=0 cellpadding=0 width=100%><tr>";
		html += "<td><input type='button' value='Fussnote eintragen' onClick=\"opener.addFussnote(document.form.fn.value);opener.editor.surroundHTML('<span class=&quot;footnote&quot; value=&quot;'+document.form.fn.value+'&quot;>['+opener.fnCount+']</span>','');window.close();\"></td>";
		html += "<td align=right><input type='button' value='Fenster schliessen' onClick='window.close();'></td>";
		html += "</tr></table>";
		html += "";
		html += "";
		html += "";
		html += "</form>";
		
		html += "</body></html>";
		
		w.document.open();
		w.document.write(html);
		w.document.close();
		
		//editor.surroundHTML('[fn]', '[/fn]');
	}
	
