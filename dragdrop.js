draggedObject = null;

function startDrag(e) {
  draggedObject=e.target;	
}

function dragOver(e) {
 e.preventDefault();
  if (e.target.className=="category")
  e.target.style.borderBottom="20px solid white";	  
  else	  
  e.target.style.borderTop="20px solid white";
}

function dragDrop(e) {
 e.preventDefault();

 e.target.style.borderTop="";
 e.target.style.borderBottom="";

 var div=e.target.parentNode;
 var action='reorderToolCat';
  if (div.id=='partcatlist')
  action='reorderPartCat';
 var dropid=e.target.dataset.id;
 var dragid=draggedObject.dataset.id;
 e.target.parentNode.insertBefore(draggedObject,e.target);
 var neworder=[];
 for (var i=0; i<div.children.length; i++)
  if (div.children[i].tagName=='DIV') 	 
  neworder.push(div.children[i].dataset.id);	 
 
    $.post("update.php",
    {
      action: action,
	  neworder: neworder
    },
    function(data, status){ 	
     //if (getTagValue(data,'result')=='OK')
	  	 
     //document.location.reload();		 
	});  
}

function copyMoveDialog(e) {
 var a=document.createElement("SPAN");
 a.className="dialog";
 a.innerHTML='<span onclick="copyTool(this);">Copy</span><span onclick="moveTool(this,false);">Move</span><span onclick="cancelTool(this);">Cancel</span>';
 e.target.insertBefore(a,e.target.lastChild);	
}

function dragDropTool(e) {
 e.preventDefault();
 e.target.style.borderTop="";
 e.target.style.borderBottom="";
 
 var div=e.target.parentNode;
 var dropid=e.target.dataset.id;
 var dragid=draggedObject.dataset.id;
 
  if (e.target.className=="category")
  {
   if (e.target.dataset.id!=draggedObject.dataset.cat)
   copyMoveDialog(e);
   else
   moveTool(e.target,true);	   
  }
   else
	if (e.target.dataset.cat!=draggedObject.dataset.cat)   
	copyMoveDialog(e);
    else
	moveTool(e.target,true);
}

function dragExit(e) {
 e.preventDefault();
 e.target.style.borderTop="";
 e.target.style.borderBottom="";
}


function makeDraggable(containerid) {
 var obj=document.getElementById(containerid);
  for (var i=0; i<obj.children.length; i++) 
  {
   if (obj.children[i].tagName=='DIV')
   {
    obj.children[i].draggable=true;
	obj.children[i].ondragstart=startDrag;	
	obj.children[i].ondragover=dragOver;
	obj.children[i].ondrop=dragDrop;
	obj.children[i].ondragexit=dragExit;
   }
  }
}

//---------------------------------------------------------------

function makeDraggableTool(containerid) {
 var obj=document.getElementById(containerid);
  for (var i=0; i<obj.children.length; i++) 
   if (obj.children[i].tagName=='DIV')
   {
	if (obj.children[i].className.indexOf('category')==-1) //tool
    {
     obj.children[i].draggable=true;
	 obj.children[i].ondragstart=startDrag;	
	}
	 obj.children[i].ondragover=dragOver;
	 obj.children[i].ondrop=dragDropTool;
	 obj.children[i].ondragexit=dragExit;
   }
}

function toolMouseOver(e) {
// if (document.getElementsByClassName('draggedtask').length==1)
// this.style.backgroundColor="yellow";	 
// this.style.marginTop="30px";	
}

function toolMouseOut(e) {
 //this.style.marginTop="";
 //this.style.backgroundColor="";
}

function makeToolDraggable() {
 var tasks=document.getElementById("tasklist");
  for (var i=0; i<tasks.children.length; i++)
   if (tasks.children[i].tagName=='DIV')
	if (tasks.children[i].hasAttribute('data-type'))
     if ((tasks.children[i].dataset.type=="taskitem") ||
         (tasks.children[i].dataset.type=="assembly"))
	 {
	  tasks.children[i].onmouseover=toolMouseOver;
      tasks.children[i].onmouseout=toolMouseOut;	  
	  dragElement(tasks.children[i]);
	 }		 
}

function dragElement(elmnt) {
  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0, yscroll = 0, ypos = 0;
  var ditem;
  var tasks=document.getElementById("tasklist");
  var beforeOrAfter = 0;
  var dropitem = null;
  if (elmnt.firstChild.className.indexOf('handle')>-1) {
    // if present, the header is where you move the DIV from:
    elmnt.firstChild.onmousedown = dragMouseDown;
  } else {
    // otherwise, move the DIV from anywhere inside the DIV:
    elmnt.onmousedown = dragMouseDown;
	elmnt.onmouseup = dragMouseUp;
  }

  function dragMouseUp(e) {
	if (this.className.indexOf('draggedtask')>-1)		
	this.className=String(this.className).split(' draggedtask').join('');
  }
  
  function dragMouseDown(e) {
    e = e || window.event;
    e.preventDefault();
	if (e.button>0)
	return;	
	// get the mouse cursor position at startup:
	pos3 = e.clientX;
    pos4 = e.clientY;
	
	yscroll = -window.scrollY;
	ypos=elmnt.offsetTop-5-pos4+55;
	//console.debug(String(pos4));
	//console.debug("Offset" + String(elmnt.offsetTop));
	elmnt.style.top = (elmnt.offsetTop-5)+"px";
	if (e.target.parentNode.className.indexOf('draggedtask')==-1)
	{
	 ditem=e.target.parentNode.cloneNode(true);
	 ditem.style.opacity="0.3";
     tasks.insertBefore(ditem,e.target.parentNode);	 
	 e.target.parentNode.className=e.target.parentNode.className+' draggedtask';
	}
    
	document.onmouseup = closeDragElement;
    // call a function whenever the cursor moves:
    document.onmousemove = elementDrag;
  }

  function elementDrag(e) {
    e = e || window.event;
    e.preventDefault();
    // calculate the new cursor position:
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    pos3 = e.clientX;
    pos4 = e.clientY;

	elmnt.style.top = (-ypos+pos4+window.scrollY - yscroll)+"px";

	 for (i=0; i<tasks.children.length; i++)
	  if (tasks.children[i].tagName=='DIV')
	   if (tasks.children[i].hasAttribute('data-type'))
        if ((tasks.children[i].dataset.type=="taskitem") ||	 
	        (tasks.children[i].dataset.type=="assembly"))
		 {
          if ((tasks.children[i].offsetTop<elmnt.offsetTop) &&
             (tasks.children[i].offsetTop+tasks.children[i].offsetHeight>elmnt.offsetTop)) 	  
			 {
			  if ((tasks.children[i].offsetTop+tasks.children[i].offsetHeight/2)>elmnt.offsetTop)
			  {
			  tasks.children[i].style.borderTop="2px dashed black";
			  tasks.children[i].style.borderBottom="2px dashed transparent";
			  beforeOrAfter=0;
			  }
		      else
			  {
			  tasks.children[i].style.borderTop="2px dashed transparent";	  
			  tasks.children[i].style.borderBottom="2px dashed black";
			  beforeOrAfter=1;
			  }
			  dropitem=tasks.children[i];
			 }
			 else
			 {
			 tasks.children[i].style.borderTop="";
			 tasks.children[i].style.borderBottom="";
			 }
		 }
  }

  function saveTaskListOrder() {
	var station=document.getElementById("stations").value;
	var neworder=[];  
	for (i=0; i<tasks.children.length; i++)
	  if (tasks.children[i].tagName=='DIV')
	   if (tasks.children[i].hasAttribute('data-type'))
	   {
        if (tasks.children[i].dataset.type=="taskitem")	 
		neworder.push(tasks.children[i].dataset.id+";"+station+";"+tasks.children[i].dataset.level);	
	    if ((tasks.children[i].dataset.type=="assembly") && 
		    (tasks.children[i].dataset.id!=-1)) //except the closing bracket
		neworder.push(tasks.children[i].dataset.id+";"+tasks.children[i].dataset.assembly+";"+tasks.children[i].dataset.level);
	   }
	$.post("update.php",
    {
        action: "reorderTaskList",
		neworder: neworder
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
     console.debug("Saved new task list order");
     else
	 console.debug("Error in new task list order saving");	 
	});
  }
  
  function closeDragElement() {
    // stop moving when mouse button is released:
	ditem.style.opacity="";
	dropitem.style.borderTop="";
	dropitem.style.borderBottom="";
    elmnt.parentNode.removeChild(elmnt);
	dragElement(ditem);		
	 if (beforeOrAfter==0)
	 ditem.parentNode.insertBefore(ditem,dropitem);
     else
	  if (dropitem.nextElementSibling==null)
	  ditem.parentNode.appendChild(ditem);	  
      else		  
	  ditem.parentNode.insertBefore(ditem,dropitem.nextElementSibling);	 
    ditem.className=dropitem.className;
	ditem.dataset.type=dropitem.dataset.type;
	ditem.dataset.level=dropitem.dataset.level;
	 if (dropitem.hasAttribute("data-assembly"))
	 {
	  ditem.dataset.assembly=dropitem.dataset.assembly;
      	  
	 }		 
    document.onmouseup = null;
    document.onmousemove = null;
	saveTaskListOrder();
  }
}
