
var editor = null;
function initEditor(TextAreaName) 
{
  editor = new HTMLArea(TextAreaName);

  var cfg = editor.config; // this is the default configuration

    cfg.registerButton(
	{
      id        : "ilias-str",
      tooltip   : "Strong",
      image     : "./htmlarea/images/ed_format_bold.gif",
      textMode  : false,
      action    : function(editor, id) 
	  			  {
                    editor.surroundHTML('<span class="ilc_Strong">', '</span>');
                  }
    });

    cfg.registerButton(
	{
      id        : "ilias-emp",
      tooltip   : "Italic",
      image     : "./htmlarea/images/ed_format_italic.gif",
      textMode  : false,
      action    : function(editor, id)
	  			  {
                    editor.surroundHTML('<span class="ilc_Emph">', '</span>');
                  }
    });

    cfg.registerButton(
	{
      id        : "ilias-com",
      tooltip   : "Kommentar",
      image     : "./htmlarea/images/ed_com.gif",
      textMode  : false,
      action    : function(editor, id) 
	  			  {
                    editor.surroundHTML('<span class="ilc_Comment">', '</span>');
                  }
    });

    cfg.registerButton(
	{
      id        : "ilias-quot",
      tooltip   : "Quotation",
      image     : "./htmlarea/images/ed_quot.gif",
      textMode  : false,
      action    : function(editor, id) 	
	  			  {
                    editor.surroundHTML('<span class="ilc_Quotation">', '</span>');
                  }
    });
    
    cfg.registerButton(
	{
      id        : "ilias-code",
      tooltip   : "Code",
      image     : "./htmlarea/images/ed_code.gif",
      textMode  : false,
      action    : function(editor, id) 
	  			  {
                    editor.surroundHTML('<code>', '</code>');
                  }
    });

	cfg.registerButton(
	{
      id        : "ilias-fn",
      tooltip   : "Footnote",
      image     : "./htmlarea/images/ed_footnote.gif",
      textMode  : false,
      action    : function(editor, id) 
	  			  {
                    buttonFootnote();
                  }
    });

	cfg.registerButton(
	{
      id        : "ilias-xtl",
      tooltip   : "External link",
      image     : "./htmlarea/images/ed_xtl.gif",
      textMode  : false,
      action    : function(editor, id) 
	  			  {
                    buttonExternalLink();
                  }
    });
	cfg.registerButton(
	{
      id        : "ilias-itl",
      tooltip   : "Internal link",
      image     : "./htmlarea/images/ed_itl.gif",
      textMode  : false,
      action    : function(editor, id) 
	  			  {
                    buttonInternalLink();
                  }
    });
	
  	cfg.pageStyle = ".footnote { color:0000FF; } ";
	cfg.pageStyle += ".ilc_Quotation {color: rgb(165, 42, 42); font-style: italic;} ";
	cfg.pageStyle += ".ilc_Comment {color: rgb(0, 128, 0);} ";
	cfg.pageStyle += ".ilc_Strong {font-weight: bold;} ";
	cfg.pageStyle += ".ilc_Emph {font-style: italic;} ";
	cfg.pageStyle += ".iliasxln {color: 0000FF;text-decoration:underline;}";
	
	//cfg.statusBar = false;
	
	cfg.toolbar = [
		[ 
		  "ilias-str", "ilias-emp", "ilias-com", "ilias-quot", "ilias-code", "separator", 
          
		  
		  "ilias-fn", "ilias-xtl", "ilias-itl",
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



function replaceAll(heystack, needle, newneedle) 
{
	while (heystack.indexOf(needle)!=-1) 
	{
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

function buttonFootnote() 
{
	w = window.open("lm_edit.php?cmd=popup&ptype=footnote","footnote","width=500,height=450,resizable=yes");
	setTimeout("w.focus()",500);
}

function buttonExternalLink() 
{
	w = window.open("lm_edit.php?cmd=popup&ptype=xtl","xtl","width=500,height=450,resizable=yes");
	setTimeout("w.focus()",500);
}	
	
function buttonInternalLink() 
{
	w = window.open("lm_edit.php?cmd=popup&ptype=itl","itl","width=600,height=800,resizable=yes");
	setTimeout("w.focus()",500);
}	
	
