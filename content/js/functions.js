var stopHigh = false;
/**
*   On mouse over highlight the current block with colored border.
*/
function doMouseOver(id) 
{
	
	if(stopHigh) return;
	stopHigh=true;
	overId = id;
	setTimeout("stopHigh=false",10);
	
    if (document.getElementById) 
	{
        obj = document.getElementById(id);
        obj.style.border="solid red 1px";
    } 
	else if(document.layers)
	{
        obj = eval("document."+id); 
        obj.border="solid red 1px";
    }
}

/**
*   On mouse out turn highligh of current border off
*/
function doMouseOut(id, dotted) 
{
	if (id!=overId) return;
	stopHigh = false;
    if (document.getElementById) 
	{
        obj = document.getElementById(id);
		if(dotted == true)
		{
			obj.style.border="dotted gray 1px";
		}
		else
		{
			obj.style.border="0px";
		}
    } else if(document.layers)
	{
        obj = eval("document."+id); 
        obj.border="solid gray 1px";
    }
}

var dragDropShow = false;
var mouseIsDown = false;
var Mposx = 0;
var Mposy = 0;

var dragId = "";
var overId = "";

function doMouseDown(id) 
{
	//dd.elements.contextmenu.hide();
	
	if (!mouseIsDown) dragId = id;
	
	oldMposx = Mposx;
	oldMposy = Mposy;
	mouseIsDown = true;
}

function beginDrag() 
{
	dd.elements.dragdropsymbol.show();
	dragDropShow = true;
	moveDragDropSymbol();
}

var cmd1 = "";
var cmd2 = "";
var cmd3 = "";
var cmd4 = "";

function callBeforeAfterAction(setCmd3) 
{
	cmd3 = setCmd3;
	doActionForm(cmd1, cmd2, cmd3, cmd4);
}

function doMouseUp(id) 
{
	if (dragDropShow) 
	{
		// mousebutton released over new object. call moveafter
		//alert(dragId+" - "+overId);
		DID = overId.substr(7);
		OID = dragId.substr(7);
		if (DID != OID) 
		{ 
			doCloseContextMenuCounter = 20;
			openedMenu = "movebeforeaftermenu";
			dd.elements.movebeforeaftermenu.moveTo(Mposx,Mposy);
			dd.elements.movebeforeaftermenu.show();
			cmd1 = 'cmd[exec_'+OID+']';
			cmd2 = 'command'+OID;
			cmd3 = 'moveAfter';
			cmd4 = DID;
			//doActionForm('cmd[exec_'+OID+']','command'+OID+'', 'moveAfter', DID);
		}
	}
	dragId = "";
	mouseIsDown = false;
	dragDropShow = false;
	dd.elements.dragdropsymbol.hide();
	dd.elements.dragdropsymbol.moveTo(-1000,-1000);
	setTimeout("dragDropShow = false",500);
	
}

function moveDragDropSymbol() 
{
	
	if (dragDropShow) 
	{
		dd.elements.dragdropsymbol.moveTo(Mposx+5,Mposy-5);
	}
	//setTimeout("moveDragDropSymbol()",100);
}
//setTimeout("moveDragDropSymbol()",1000);

/**
*   on Click show context-menu at mouse-position
*/

var menuBlocked = false;
function nextMenuClick() {
	menuBlocked = false;
}

var openedMenu="";
function doMouseClick(e,id,ctype) 
{
	// dies ist nï¿½tig, weil wenn zwei Layer ï¿½bereinander liegen von beiden ein Event ausgelï¿½st wird.
	// Jetzt wird aber bei einem Klick das Menï¿½ geblockt fï¿½r einen halbe sekunde.
	if(menuBlocked) return;
	menuBlocked = true;
	setTimeout("nextMenuClick()",500);
	
	if (!e) var e = window.event;
	
	clickcmdid = id.substr(7);
	
	if (e.pageX || e.pageY)
	{
		Mposx = e.pageX;
		Mposy = e.pageY;
	}
	else if (e.clientX || e.clientY)
	{
		Mposx = e.clientX + document.body.scrollLeft;
		Mposy = e.clientY + document.body.scrollTop;
	}
	if (!dragDropShow) 
	{
	
		if (openedMenu!="") 
		{
			dd.elements[openedMenu].hide();
			openedMenu = "";
		} 
		else 
		{
			dd.elements["contextmenu_"+clickcmdid].moveTo(Mposx,Mposy-10);
			dd.elements["contextmenu_"+clickcmdid].show();
			openedMenu = "contextmenu_"+clickcmdid;
		}
		doCloseContextMenuCounter=20;
	}
}

/**
*   on MouseOut of context-menu hide context-menu 
*/
var doCloseContextMenuCounter = -1;
function doCloseContextMenu() 
{
	if (doCloseContextMenuCounter>-1) 
	{
		doCloseContextMenuCounter--;
		if(doCloseContextMenuCounter==0) 
		{
			if(openedMenu!="") 
			{
				dd.elements[openedMenu].hide();
				openedMenu = "";
			}
			doCloseContextMenuCounter=-1;
		}
	}
	setTimeout("doCloseContextMenu()",100);
}
setTimeout("doCloseContextMenu()",200);

var clickcmdid = 0;
function doActionForm(cmd, command, value, target)    
{
    doCloseContextMenuCounter = 2;
    if(cmd=="cmd[exec]") 
	{
        cmd = "cmd[exec_"+clickcmdid+"]";
    }
    
    if (command=="command") 
	{
        command += clickcmdid;
    }
    
	if (value=="delete") 
	{
		if(!confirm("wirklich löschen ?")) 
		{
			menuBlocked = true;
			setTimeout("nextMenuClick()",500);
			return;
		}
		menuBlocked = true;
		setTimeout("nextMenuClick()",500);
	}
	
	//alert(target+" - "+command+" - "+value+" - "+cmd);
	
    html = "<form name=cmform id=cmform method=post action='"+actionUrl+"'>";
    html += "<input type=hidden name='target[]' value='"+target+"'>";
    html += "<input type=hidden name='"+command+"' value='"+value+"'>";
    html += "<input type=hidden name='"+cmd+"' value='Ok'>";
    html += "</form>";

    dd.elements.actionForm.write(html);
    //alert(html);
    obj = document.getElementById("cmform");
    obj.submit();
    
}

function M_in(cell) 
{
    cell.style.cursor='pointer';
    cell.bgColor='gray';
    doCloseContextMenuCounter=-1;
}
function M_out(cell) 
{
    cell.bgColor='';
    doCloseContextMenuCounter=5;
}

var oldMposx = -1;
var oldMposy = -1;    
function followmouse1(e) 
{

    if (!e) var e = window.event;
    
    if (e.pageX || e.pageY)
	{
		Mposx = e.pageX;
		Mposy = e.pageY;
	}
	else if (e.clientX || e.clientY)
	{
		Mposx = e.clientX + document.body.scrollLeft;
		Mposy = e.clientY + document.body.scrollTop;
	}
    
    if (mouseIsDown) 
	{
        
        if ( Math.sqrt((Mposx-oldMposx)*(Mposx-oldMposx) + (Mposy-oldMposy)*(Mposy-oldMposy)) > 4 ) 
		{
            beginDrag();
        }
        
    }
    
    moveDragDropSymbol();
    
}

function doKeyDown(e) 
{
    if (!e) var e = window.event;
    kc = e.keyCode;
    kc = kc*1;
    if(kc==17) 
	{
        dd.elements.contextmenu.hide();
        oldMposx = Mposx;
        oldMposy = Mposy;
        mouseIsDown = true;        
    }
}

function doKeyUp(e) 
{
    if (!e) var e = window.event;
    kc = e.keyCode;

    kc = kc*1;
    if(kc==17) 
	{
        mouseIsDown = false;
        dd.elements.dragdropsymbol.hide();
        dd.elements.dragdropsymbol.moveTo(-1000,-1000);
        setTimeout("dragDropShow = false",500);
    }
}

document.onmousemove=followmouse1;
//document.onkeydown=doKeyDown;
//document.onkeyup=doKeyUp;


