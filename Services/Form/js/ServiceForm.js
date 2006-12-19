function ilFormAddEvent(func){
  if (!document.getElementById | !document.getElementsByTagName) return
  var oldonload=window.onload
  if (typeof window.onload != 'function') {window.onload=func}
  else {window.onload=function() {oldonload(); func()}}
}

ilFormAddEvent(ilFormHideAll)


function ilFormHideAll(){
  var obj,nextspan,anchor,content

  // get all spans
  obj=document.getElementsByTagName('span')

  // run through them
  for (var i=0;i<obj.length;i++){

    // if it has a class of helpLink
    if(/ilFormHelpLink/.test(obj[i].className)){

      // get the adjacent span
      nextspan=obj[i].nextSibling
      while(nextspan.nodeType!=1) nextspan=nextspan.nextSibling

       // hide it
      nextspan.style.display='none'

      //create a new link
      anchor=document.createElement('a')

      // copy original helpLink text and add attributes
      //content=document.createTextNode(obj[i].firstChild.nodeValue)
	  nnode = obj[i].firstChild.cloneNode(false);
      //anchor.appendChild(content)
	  anchor.appendChild(nnode)
      anchor.href='#help'
      //anchor.title='Click to show help'
      anchor.className=obj[i].className
      anchor.nextspan=nextspan
      anchor.onclick=function(){ilFormShowHide(this.nextspan);ilFormChangeTitle(this);return false}

      // replace span with created link
      obj[i].replaceChild(anchor,obj[i].firstChild)
    }
  }
}

// used to flip helpLink title
function ilFormChangeTitle(obj){
  //if(obj)
  //  obj.title = obj.title== 'Click to show help' ? 'Click to hide help' : 'Click to show help'
}

// used to flip the display property
function ilFormShowHide(obj){
  if(obj)
    obj.style.display = obj.style.display=='none' ? 'inline' : 'none'
}


