/**
*   On mouse over highlight the current block with colored border.
*/
function doMouseOver(id) {
    if (document.getElementById) {
        obj = document.getElementById(id);
        obj.style.border="solid red 1px";
    } else if(document.layers){
        obj = eval("document."+id); 
        obj.border="solid red 1px";
    }
}

/**
*   On mouse out turn highligh of current border off
*/
function doMouseOut(id) {
    if (document.getElementById) {
        obj = document.getElementById(id);
        obj.style.border="dotted gray 1px";
    } else if(document.layers){
        obj = eval("document."+id); 
        obj.border="solid gray 1px";
    }
}

/**
*   on Click show context-menu at mouse-position
*/
function doMouseClick(e,id) {
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
    
    dd.elements.contextmenu.moveTo(Mposx,Mposy-10);
    dd.elements.contextmenu.show();
    doCloseContextMenuCounter=20;
}

/**
*   on MouseOut of context-menu hide context-menu 
*/
var doCloseContextMenuCounter = -1;
function doCloseContextMenu() {
    if (doCloseContextMenuCounter>-1) {
        doCloseContextMenuCounter--;
        if(doCloseContextMenuCounter==0) {
            dd.elements.contextmenu.hide();
            doCloseContextMenuCounter=-1;
        }
    }
    setTimeout("doCloseContextMenu()",100);
}
setTimeout("doCloseContextMenu()",200);

var clickcmdid = 0;
function doActionForm(cmd,command,value) {
    
    if(cmd=="cmd[exec]") {
        cmd = "cmd[exec_"+clickcmdid+"]";
    }
    
    if (command=="command") {
        command += clickcmdid;
    }
    
    html = "<form name=cmform id=cmform method=post action='"+actionUrl+"'>";
          
    
    html += "<input type=hidden name='"+command+"' value='"+value+"'>";
    html += "<input type=hidden name='"+cmd+"' value='Ok'>";
    html += "</form>";

    dd.elements.actionForm.write(html);
    //alert(html);
    obj = document.getElementById("cmform");
    obj.submit();
    
}

function M_in(cell) {
    cell.style.cursor='pointer';
    cell.bgColor='gray';
    doCloseContextMenuCounter=-1;
}
function M_out(cell) {
    cell.bgColor='';
    doCloseContextMenuCounter=5;
}

