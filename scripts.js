  function getTagValue(html,tag)
  {
	a=html.indexOf('<'+tag+'>');
	b=html.indexOf('</'+tag+'>');
	return html.substr(a+tag.length+2,b-a-tag.length-2);
  }

 function foldTools(obj) {
	var catid=obj.parentNode.dataset.id;
	//var list=document.getElementById("toollist");
	var list=obj.parentNode.parentNode;
	for (var i=0; i<list.children.length; i++)
	 if ((list.children[i].tagName=='DIV') && (list.children[i].hasAttribute('data-cat')) && list.children[i].dataset.cat==catid)
	  if (obj.className=="")
      list.children[i].className="hidden";
      else
	  list.children[i].className="";	  
    if (obj.className=="")
    obj.className="folded";
    else
	obj.className="";
 }
 
 var MMUS = new function(){
 
 var fr = new FileReader();
 
 function onloadHandler(evt) {
	 document.location.reload();
 }
 
 function onprogressHandler(evt) {
	var percent = evt.loaded/evt.total*100;
	console.log('Upload progress: ' + percent + '%');
 }
 
 function MMUajax(fileInputBox,progressBar,start,chunkSize,fileID)
 {
			var chunknum=Math.ceil(start/chunkSize);
			 if (start<fr.result.byteLength)
			 {
				var chunk=[fr.result.slice(start,start+chunkSize)];
				var blob = new Blob(chunk, {type: fileInputBox.type});
				var formData = new FormData();
				formData.append('action','addMMU');
				formData.append('chunk',blob,fileInputBox.name);
				formData.append('chunknum',chunknum);
				formData.append('chunkend',start+chunkSize);
				formData.append('TotalSize',fr.result.byteLength);
				formData.append('fileID',fileID);
				$.ajax({
					type: "POST",
					url: "update.php",
					data: formData,
					processData: false,
					contentType: false,
					success: function(data, status){ 	
						var nextChunk=parseInt(getTagValue(data,'nextChunk'));
						var nextStart=parseInt(getTagValue(data,'nextStart'));
						fileID=getTagValue(data,'fileID');
						var progress=document.getElementById(progressBar);
						var progressText=(nextStart<=fr.result.byteLength?Math.ceil(nextStart*100/fr.result.byteLength):"100")+"%";
						progress.children[0].style.width=progressText;
						progress.children[1].innerHTML=progressText;
						console.log("NextChunk: "+nextChunk);
						console.log("NextStart: "+nextStart);
						console.log("Chunk size: "+chunkSize);
						if (nextStart<=fr.result.byteLength)
						MMUajax(fileInputBox,progressBar,nextStart,chunkSize,fileID);
						else //file upload complete
						{
						 if (getTagValue(data,'result')=='OK')
						 document.location.reload(); 
					     else
						 progress.children[1].innerHTML=getTagValue(data,'msg');
						}
						}
				});
			 }	 
 }
 
 function sendMMuChunk(fileInputBoxID,progressBar,start,chunkSize,fileID)
 {
	fileInputBox=document.getElementById(fileInputBoxID);
	if (fileInputBox.files.length==0)
		return false;
	
	//var fr=new FileReader();
	fileInputBox=fileInputBox.files[0];
	fr.onload=function(){ 
			MMUajax(fileInputBox,progressBar,start,chunkSize,fileID);
		}
	fr.readAsArrayBuffer(fileInputBox);
 }
 
 this.addMMU = function (fileInputBox,progressBar) {
	var chunkSize = 128*1024; //TODO: move to mmus.php or some global settings file
	var val=document.getElementById(fileInputBox);
	if (val.files.length>0)
	{
	val=val.files[0];
	fr.onload=function(){ 
			var progress=document.getElementById(progressBar);
			progress.className=progress.className.split("hide").join("").trim();
			progress.children[0].style.width="0%";
			progress.children[1].innerHTML="0%";
			sendMMuChunk(fileInputBox,progressBar,0,chunkSize,0);
	}
	fr.readAsArrayBuffer(val);
  }
 }
 
 }; //end of MMUS namespace
 
 function addPart(val) {
  val=document.getElementById(val).value;
  if (val!='')
   $.post("update.php",
    {
      action: "addPart",
	  name: val
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});  
 }
 
 function addPartCat(val) {
  val=document.getElementById(val).value;
  if (val!='')
   $.post("update.php",
    {
      action: "addPartCat",
	  name: val
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});  
 }
 
 function addTool(val) {
  val=document.getElementById(val).value;
  if (val!='')
   $.post("update.php",
    {
      action: "addTool",
	  name: val
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});  
 }
 
 function addToolCat(val) {
  val=document.getElementById(val).value;
  if (val!='')
   $.post("update.php",
    {
      action: "addToolCat",
	  name: val
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});  
 }
 
 function deleteToolCatYes(e) {
  e.stopPropagation();
  var action='delToolCat';
   if (e.target.parentNode.parentNode.parentNode.id=='partcatlist')
   action='delPartCat';	   
  var val=e.target.parentNode.parentNode.dataset.id;	 
  $.post("update.php",
    {
      action: action,
	  id: val
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
     else
	 e.target.parentNode.innerHTML="Error: Cannot delete entry";	 
	});   	 
 }
 
 function deleteStation(obj) {
  if (obj.className.indexOf("clicked")>-1)
  {
   obj.className="";	  
   obj.innerHTML="X";	  
  }
  else
  {
	obj.className="clicked";
    obj.innerHTML="Delete? <span onclick=\"deleteStationYes(event);\">Yes</span><span>Cancel</span>";	
  }
 }
 
 function deleteStationYes(e) {
  e.stopPropagation();
  var val=e.target.parentNode.parentNode.dataset.id;
  $.post("update.php",
    {
      action: 'delStation',
	  id: val
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='deleted')
     e.target.parentNode.parentNode.parentNode.removeChild(e.target.parentNode.parentNode);
     else
	 e.target.parentNode.innerHTML=getTagValue(data,'result');
	});
 }
 

 
 function deleteToolCat(obj) {
  if (obj.className.indexOf("clicked")>-1)
  {
   obj.className="";	  
   obj.innerHTML="X";	  
  }
  else
  {
	obj.className="clicked";
    obj.innerHTML="Delete? <span onclick=\"deleteToolCatYes(event);\">Yes</span><span>Cancel</span>";	
  }
 }
 
 function deleteToolYes(e) {
  e.stopPropagation();
  var action='delTool';
   if (e.target.parentNode.parentNode.parentNode.id=='partlist')
   action='delPart';	   
   if (e.target.parentNode.parentNode.parentNode.id=='mmulist')
   action='delMMU';
  var val=e.target.parentNode.parentNode.dataset.id;	 
  var cat=e.target.parentNode.parentNode.dataset.cat;	 
  $.post("update.php",
    {
      action: action,
	  id: val,
	  cat: cat
    },
    function(data, status){ 	
     if ((getTagValue(data,'result')=='OK') || (getTagValue(data,'result')=='deleted'))
     e.target.parentNode.parentNode.parentNode.removeChild(e.target.parentNode.parentNode);
     else
	 e.target.parentNode.innerHTML="Error: Cannot delete entry";	 
	});   	 
 }
 
 function deleteUserYes(e) {
   e.stopPropagation();
   var action="deactivateUser";
   if (e.target.parentNode.firstChild.dataset.toggleval==1)
   action="deleteUser";
   else
	   if (e.target.parentNode.parentNode.parentNode.dataset.enabled==0)
	   action="activateUser";
   $.post("update.php",
    {
      action: action,
	  id: e.target.parentNode.parentNode.parentNode.dataset.id
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='DEL-OK')
     e.target.parentNode.parentNode.parentNode.parentNode.removeChild(e.target.parentNode.parentNode.parentNode);
     else
      if (getTagValue(data,'result')=='DEACT-OK')
	  {
		e.target.parentNode.parentNode.parentNode.dataset.enabled="0";
		deleteUser(e.target.parentNode);
	  }
	  else
		  if (getTagValue(data,'result')=='EN-OK')
	      {
		   e.target.parentNode.parentNode.parentNode.dataset.enabled="1";
		   deleteUser(e.target.parentNode);
	      }
	      else
	      e.target.parentNode.innerHTML=getTagValue(data,'message');	 
	});   	 
 }
 
 function spanToggle(e,group) {
	e.stopPropagation();
	var values=e.target.dataset.toggle.split('/')
	values=values[group].split(',');
	var val=parseInt(e.target.dataset.toggleval);
	val++;
	 if (val>=values.length)
	 val=0;
   e.target.dataset.toggleval=val;
   e.target.innerHTML=values[val];
 }
 
 function deleteUser(obj) {
  if (obj.className.indexOf("clicked")>-1)
  {
   obj.parentNode.colSpan=1;
   obj.parentNode.nextSibling.style.display="";
   obj.parentNode.nextSibling.nextSibling.style.display="";
   obj.parentNode.nextSibling.nextSibling.nextSibling.style.display="";
   obj.className=obj.dataset.defclass;
   obj.innerHTML="X";	  
  }
  else
  {
	obj.parentNode.colSpan=4;
	obj.parentNode.nextSibling.style.display="none";
	obj.parentNode.nextSibling.nextSibling.style.display="none";
	obj.parentNode.nextSibling.nextSibling.nextSibling.style.display="none";
	obj.dataset.defclass=obj.className;  
	obj.className="clicked";
    obj.innerHTML='<span data-toggle="Activate,Delete/Deactivate,Delete" data-toggleval="0" onclick=spanToggle(event,this.parentNode.parentNode.parentNode.dataset.enabled);>'+(obj.parentNode.parentNode.dataset.enabled=="1"?'Dea':'A')+'ctivate</span>'+obj.parentNode.nextSibling.innerText+"? <span onclick=\"deleteUserYes(event);\">Yes</span><span>Cancel</span>";	
  }
 }
 
 function updateToolOrderAndCats(div,NewObject) {
 var neworder=[];
 var action='reorderTools';
  if (div.id=='partlist')
  action='reorderParts';
  if (div.id=='partstationlist')
  action='reorderPartsToStations';
 for (var i=0; i<div.children.length; i++)
  if ((div.children[i].tagName=='DIV') && (div.children[i].className.indexOf("category")==-1))
  if (div.children[i].dataset.cat==NewObject.dataset.cat) 
  neworder.push(
        String(div.children[i].dataset.id)+','+
		String(div.children[i].dataset.cat)+','+
		(div.children[i].hasAttribute('data-fromcat')?
		 div.children[i].dataset.fromcat:'-1')
		);
  NewObject.removeAttribute('data-fromcat');
		
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
 
 function cancelTool(obj) {
   obj.parentNode.parentNode.removeChild(obj.parentNode);
 }
 
 function copyTool(obj) {
  var obj1=obj;	 
  obj=obj.parentNode.parentNode;
  if (obj.className=="category")
  {
   var newObject=draggedObject.cloneNode(true);
   newObject.dataset.cat=obj.dataset.id;      
   newObject.lastChild.className="";
   obj.parentNode.insertBefore(newObject,obj.nextSibling);
  }
  else
  {
   var newObject=draggedObject.cloneNode(true);	  
   newObject.dataset.cat=obj.dataset.cat;	  
   newObject.lastChild.className="";
   obj.parentNode.insertBefore(newObject,obj);
  }	 
  var div=obj.parentNode;
  updateToolOrderAndCats(div,newObject);
  cancelTool(obj1); 
 }
 
 function moveTool(obj,direct) {
  var obj1=obj;	 
   if (!direct)
   obj=obj.parentNode.parentNode;
  if (obj.className=="category")
  {
   draggedObject.dataset.fromcat=draggedObject.dataset.cat;
   draggedObject.dataset.cat=obj.dataset.id;     
   draggedObject.lastChild.className="";   
   obj.parentNode.insertBefore(draggedObject,obj.nextSibling);
  }
  else
  {
   draggedObject.dataset.fromcat=draggedObject.dataset.cat;	  
   draggedObject.dataset.cat=obj.dataset.cat;	 
   draggedObject.lastChild.className="";   
   obj.parentNode.insertBefore(draggedObject,obj);
  }
  var div=obj.parentNode;  
  updateToolOrderAndCats(div,draggedObject);
  if (!direct)
  cancelTool(obj1);
 }
 
 function deleteTool(obj) {
  if (obj.className.indexOf("clicked")>-1)
  {
   obj.className="";	  
   obj.innerHTML="X";	  
  }
  else
  {
	obj.className="clicked";
    obj.innerHTML="Delete? <span onclick=\"deleteToolYes(event);\">Yes</span><span>Cancel</span>";	
  }
 }
 
 function setEnableMMU(obj) {
  var id=obj.parentNode.dataset.id;
  var projectid=obj.parentNode.dataset.project;
  var action=(obj.className=="check"?"disableMMU":"enableMMU");
   $.post("update.php",
    {
      action: action,
	  id: id,
	  projectid: projectid
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});   	 
 }
 
 function setDefaultPart(obj) {
  var id=obj.parentNode.dataset.id;
  var tcid=obj.parentNode.dataset.cat;
   $.post("update.php",
    {
      action: "setDefaultPartCat",
	  id: id,
	  catid: tcid
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});   	 
 }
 
 function setDefaultTool(obj) {
  var id=obj.parentNode.dataset.id;
  var tcid=obj.parentNode.dataset.cat;
   $.post("update.php",
    {
      action: "setDefaultToolCat",
	  id: id,
	  catid: tcid
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
     document.location.reload();		 
	});   	 
 }
 
 function moveSelected() {
  var tostation=document.getElementById("tostation").value;	 
  var fromstation=document.getElementById("stations").value;	 
  var tasks=document.getElementById("tasklist").children;
  var S='';
  var Subs='';
  var From='';
   for (var i=0; i<tasks.length; i++)
	if (tasks[i].tagName=="DIV") 
	{
     if ((tasks[i].dataset.type=="taskitem") &&
         (tasks[i].children[2].className.indexOf("selected")>-1))	
	{
	 S=S+tasks[i].dataset.id+',';
	 From+=fromstation+',';
	}
	 if ((tasks[i].dataset.type=="assembly") && (tasks[i].dataset.id!=-1) &&
         (tasks[i].children[2+(tasks[i].dataset.id==0)].className.indexOf("selected")>-1))
	{
		if (tasks[i].dataset.id==0)
		{
		 Subs=Subs+tasks[i].dataset.assembly+',';
		 From+=fromstation+',';
		}
		else
		{
		 S=S+tasks[i].dataset.id+',';
		 From+=tasks[i].dataset.assembly+',';
		}
	}
	}
   if (S.length>0)
   S=S.slice(0,-1);
   if (Subs.length>0)
   Subs=Subs.slice(0,-1);
   if (From.length>0)
   From=From.slice(0,-1);

   $.post("update.php",
    {
      action: "moveTask",
	  task_ids: S,
	  subassembly_id: Subs,
	  fromstation: From,
	  tostation: tostation
    },
    function(data, status){ 	
	 if (getTagValue(data,'result')=='OK')
	 {
      for (var i=tasks.length-1; i>=0; i--)
	   if ((tasks[i].tagName=="DIV") && (tasks[i].dataset.type=="taskitem"))
	    if (tasks[i].children[2].className.indexOf("selected")>-1)	
	    tasks[i].remove(); 	
	 }
	 else
	 {
	  windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
      windowShow(null);		 
	 }
	});
 }

 function removeTask() {
  var tasks=document.getElementById("tasklist").children;
  var S='';
   for (var i=tasks.length-1; i>=0; i--)
	if ((tasks[i].tagName=="DIV") && (tasks[i].dataset.type=="taskitem"))
	 if (tasks[i].children[2].className.indexOf("selected")>-1)	
	 S=S+tasks[i].dataset.id+',';
 
    if (S.length>0)
    S=S.slice(0,-1);		
 
    $.post("update.php",
    {
      action: "delTask",
	  task_ids: S
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
	 {		 
      for (var i=tasks.length-1; i>=0; i--)
	   if ((tasks[i].tagName=="DIV") && (tasks[i].dataset.type=="taskitem"))
	    if (tasks[i].children[2].className.indexOf("selected")>-1)	
	    tasks[i].remove();
	 }
	 else
	 {
	  windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
      windowShow(null);		 
	 }
	});
 }

 function removeUserFromProject(obj) {
   var projectid=obj.parentNode.parentNode.dataset.project;
   var userid=obj.parentNode.parentNode.dataset.id;
    $.post("update.php",
    {
      action: "removeUserFromProject",
	  userid: userid,
	  projectid: projectid
    },
    function(data, status){
		
     obj.parentNode.parentNode.parentNode.deleteRow(obj.parentNode.parentNode.rowIndex);
	});
 }
 
 function clearTags(obj) {
  for (var i=0; i<obj.parentNode.children.length; i++)
   if ((obj.parentNode.children[i]!=obj) && (obj.parentNode.children[i].tagName=='SPAN'))
   obj.parentNode.children[i].style.backgroundColor="";	  
 }
 
 function clickTaskType(obj) {
  if (obj.parentNode.className.indexOf('editmode')>-1)
    $.post("update.php",
    {
      action: "clickTaskType",
	  taskid: obj.parentNode.parentNode.dataset.id,
	  operationid: obj.dataset.id
    },
    function(data, status){	
	 if (getTagValue(data,'result')=='OK')
	 {
	  obj.id="task_"+obj.parentNode.parentNode.dataset.id+"_operation"; 
	  clearTags(obj);
	  obj.style.setProperty("background-color","orange","important");
      var spancat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_cat");
	  var selcat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_catval");
	  var spansubcat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_subcat");
	  var selsubcat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_subcatval");
	  spancat.innerHTML="Operation type:";
	  selcat.innerHTML=document.getElementById("new_type").innerHTML;
	  selcat.onchange=getSubTypes;
	  selcat.dataset.parent=obj.id;
	   if (getTagValue(data,'parent')==0)
	   selcat.value=obj.dataset.id;
       else
	   {
	    selcat.value=getTagValue(data,'parent');	   
	    selcat.dataset.id=obj.dataset.id;
	   }
      getSubTypes(selcat);
	  spansubcat.innerHTML="Operation:";
	 }
	});	 
 }
 
 function clickPart(obj) {
  var asmid = 0;	 
  if (obj.parentNode.parentNode.hasAttribute('data-assembly'))
  asmid=obj.parentNode.parentNode.dataset.assembly;
  var id=obj.parentNode.parentNode.dataset.id;
  var linkid="task_"+id;
   if ((asmid>0) && (id==0))
	   linkid="subasm_"+asmid;
  if (obj.parentNode.className.indexOf('editmode')>-1)	 
   $.post("update.php",
    {
      action: "clickPart",
	  assemblyid: asmid,
	  taskid: id,
	  partid: obj.dataset.id
    },
    function(data, status){
	 obj.id=linkid+"_part"; 	
	 clearTags(obj);
	 obj.style.setProperty("background-color","orange","important");
     var spancat=document.getElementById(linkid+"_cat");
	 var selcat=document.getElementById(linkid+"_catval");
	 var spansubcat=document.getElementById(linkid+"_subcat");
	 var selsubcat=document.getElementById(linkid+"_subcatval");
	 spancat.innerHTML="Part type:";
	 selcat.innerHTML=document.getElementById("new_parttype").innerHTML;
	 selcat.dataset.parent=obj.id;
	 document.getElementById(selcat.dataset.sub).dataset.parent=obj.id;
	 selcat.onchange=getSubParts;
	 spansubcat.innerHTML="Part:";
	 selsubcat.innerHTML='';
	   if (getTagValue(data,'parent')==0)
	   selcat.value=obj.dataset.id;
       else
	   {
	    selcat.value=getTagValue(data,'parent');	   
	    selcat.dataset.id=obj.dataset.id;
	   }
      getSubParts(selcat);
	});	 	 
 }
 
 function clickTool(obj) {
  if (obj.parentNode.className.indexOf('editmode')>-1)	 
   $.post("update.php",
    {
      action: "clickTool",
	  taskid: obj.parentNode.parentNode.dataset.id,
	  toolid: obj.dataset.id
    },
    function(data, status){
	 obj.id="task_"+obj.parentNode.parentNode.dataset.id+"_tool"; 	
	 clearTags(obj);
	 obj.style.setProperty("background-color","orange","important");
     var spancat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_cat");
	 var selcat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_catval");
	 var spansubcat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_subcat");
	 var selsubcat=document.getElementById("task_"+obj.parentNode.parentNode.dataset.id+"_subcatval");
	 spancat.innerHTML="Tool type:";
	 selcat.innerHTML=document.getElementById("new_tooltype").innerHTML;	
	 selcat.dataset.parent=obj.id;
	 selcat.onchange=getSubTools;
	 spansubcat.innerHTML="Tool:";
	 selsubcat.innerHTML='';
	  if (getTagValue(data,'parent')==0)
	   selcat.value=obj.dataset.id;
       else
	   {
	    selcat.value=getTagValue(data,'parent');	   
	    selcat.dataset.id=obj.dataset.id;
	   }
      getSubTools(selcat);
	});	 	 
 }
 
 function clickSel(obj) {
  if (obj.className.indexOf("editmode")>-1)
  return;	  
  if (obj.className.indexOf("selected")==-1)
  obj.className=obj.className+" selected";
  else	  
  obj.className=obj.className.replace(" selected","");	                  
 }
 
 function nextClick() {
  for (var i=0; i<document.getElementById("stations").options.length; i++)
   if (document.getElementById("stations").options[i].selected)
	 if (i+1<document.getElementById("stations").options.length)
	 {
	  document.getElementById("stations").index=document.getElementById("stations").options[i+1].selected=true;	 
	  changeStation(document.getElementById("stations"));
	  return;               
	 }                                
 }
 function prevClick() {
  for (var i=0; i<document.getElementById("stations").options.length; i++)
   if (document.getElementById("stations").options[i].selected)
	if (i>0)
	{
	 document.getElementById("stations").options[i-1].selected=true;
	 changeStation(document.getElementById("stations"));
	}
 }
 
 function selectPosition(obj)
 {
  if (obj.className.indexOf("selected")==-1)
  {
   obj.className=obj.className+" selected";
    for (var i=0; i<obj.parentNode.children.length; i++)
	 if (obj.parentNode.children[i]!=obj)
	 obj.parentNode.children[i].className=obj.parentNode.children[i].className.replace(" selected","");		
  }
  else	  
  obj.className=obj.className.replace(" selected","");
 }
 /*
 function partSelect(obj)
 {	 
  var p=document.getElementById("positions");	 
  if (obj.value==6)
  {
   p.children[0].src="car1.jpg";	 
   p.children[0].style.display="";   
   p.children[1].style.display="none";
   p.children[2].style.display="none";
   p.children[3].style.display="";
   p.children[4].style.display="";
   p.children[5].style.display="";
   p.children[6].style.display="";
   p.children[7].style.display="";
   p.children[8].style.display="";
   p.children[9].style.display="";
   p.children[10].style.display="";
  }	  
  else
   if (obj.value==0)
   {
	p.children[0].src="car.jpg";   
	p.children[0].style.display="";
	p.children[1].style.display="";
    p.children[2].style.display="";
    p.children[3].style.display="none";
    p.children[4].style.display="none";
    p.children[5].style.display="none";
    p.children[6].style.display="none";
    p.children[7].style.display="none";
    p.children[8].style.display="none";
    p.children[9].style.display="none";
    p.children[10].style.display="none";
   }
   else
   {
	p.children[0].style.display="none";   
	p.children[1].style.display="none";
    p.children[2].style.display="none";   
	p.children[3].style.display="none";
    p.children[4].style.display="none";
    p.children[5].style.display="none";
    p.children[6].style.display="none";
    p.children[7].style.display="none";
    p.children[8].style.display="none";
    p.children[9].style.display="none";
    p.children[10].style.display="none";   
   }
 }
 */
 function timeEstimate(obj) {
  var h=0, m=0, s=0;	 
  var allowed = '0123456789:';	 
  var S=obj.value;
   for (var i=S.length-1; i>=0; i--)
	if (allowed.indexOf(S[i])==-1)
	S=S.slice(0,i)+S.slice(i+1,S.length);
  var L=S.length;
  if (L>2)
  {
   if (S[L-3]!=':')
   S=S.slice(0,-2)+':'+S.slice(-2,L);
   L=S.length;
   if (L>5)
    if (S[L-6]!=':')
    S=S.slice(0,-5)+':'+S.slice(-5,L);
  }
  var j=0;
  for (var i=0; i<S.length; i++)
  {
   if (S[i]==':')
   j++;	   
   else	  
   switch (j) {
	 case 0: h=h*10+parseInt(S[i]);
     break;
     case 1: m=m*10+parseInt(S[i]);
     break;
     case 2: s=s*10+parseInt(S[i]);
     break;	 
   }	   
  }	  
   if (j==1)
   {
	 s=m;
     m=h;
     h=0;	 
   }	 
   if (j==0)
   {
	s=h;
    m=0;
    h=0;	
   }	   
   if (s>59)
   {
    m=m+(s-59);
	s=s-60
   }
   if (m>59)
   {
    h=h+(m-59);
	h=h-60;
   }
  S='';
   if (h>0)
	S=h+':';
   if (m>0)
   {
	 if (S=='')
     S=S+m+':';
     else
      if (m<10)
      S=S+'0'+m+':';		  
      else
	  S=S+m+':';
   }
   else
	if (S!='')
    S=S+'00:';
   if (S=='')
   {
	if (s>0)
    S=S+s;
   }
   else
	if (s<10)
    S=S+'0'+s;
    else
	S=S+s;	
  obj.value=S;
 }
 
 function addProject() {
  var pname = document.getElementById("new_projectname");
  var pdesc = document.getElementById("new_description");
  var perror= document.getElementById("new_projecterror");
   $.post("update.php",
    {
        action: "addProject",
		name: pname.value,
		desc: pdesc.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
	    else
		{
		 perror.innerHTML=getTagValue(data,'result');	
		 perror.style.display="inline";
		}
	});
 }
 
 function addStation(fieldname) {
  if ((fieldname==null) || (fieldname==""))
	  fieldname="new_stationname";
  var name=document.getElementById(fieldname);
    $.post("update.php",
    {
        action: "addStation",
		name: name.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
	    else
		{
		 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
         windowShow(null);	
		}
	});
 }
 
 function addSubAssembly() {
  var name=document.getElementById("new_subassemblyname");
  var mainpart=document.getElementById("subpartselector");
  var position=document.getElementById("newsub_position");
  var currentstation=document.getElementById("stations");
    $.post("update.php",
    {
        action: "addSubStation",
		parentstation: currentstation.value,
		name: name.value,
		mainpart: mainpart.value,
		position: position.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
	    else
		{
		 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
         windowShow(null);	
		}
	});	 
 }
 
 function addTask() {
  var subassemblies = [];	 
  var tasks=document.getElementById("tasklist");
   for (var i=0; i<tasks.children.length; i++)
    if (tasks.children[i].tagName=='DIV')
	 if (tasks.children[i].hasAttribute('data-type'))
      if ((tasks.children[i].dataset.type=="assembly") && (tasks.children[i].children.length>=4))
	   if ((tasks.children[i].children[3].className.indexOf('editmode')==-1) &&
          (tasks.children[i].children[3].className.indexOf('selected')>-1)) 
       subassemblies.push(i);
	  
  var ntype = document.getElementById("new_subtype");
  var npart = document.getElementById("partselector");
  var ntool = document.getElementById("new_tool");
  var ntime = document.getElementById("new_time");
  var ndesc = document.getElementById("new_description");
  var stationid = document.getElementById("stations").value;
   if (subassemblies.length==1)
   stationid=tasks.children[subassemblies[0]].dataset.assembly;
    //if more than two are selected show dialog to pick which one is important
	   
   $.post("update.php",
    {
        action: "addTask",
		stationid: stationid,
		type: ntype.value,
		part: npart.value,
		tool: ntool.value,
		position: "",
		time: ntime.value,
		desc: ndesc.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
	    else
        {
		 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
		 windowShow(null);
		}
	});
 }

 function cancelEdit(obj,e) {
  e.stopPropagation();	 
  var val=obj.parentNode.dataset.value;
  obj.parentNode.removeAttribute("data-value");
  obj.parentNode.innerHTML=val;
 }
 
 function editUserRoleOK(obj,e) {
  e.stopPropagation();	  
  var oldval=obj.parentNode.dataset.value;
  var val=obj.previousSibling.value;
  var userid=obj.parentNode.parentNode.dataset.id;
  var projectid=obj.parentNode.parentNode.dataset.project;
  $.post("update.php",
    {
        action: "editUserRole",
		userid: userid,
		projectid: projectid,
		newrole: val
    },
    function(data, status){
		obj.parentNode.removeAttribute("data-value");
		if (getTagValue(data,'result')=='OK')
         obj.parentNode.innerHTML=val;
		else
		 obj.parentNode.innerHTML=oldval;	
	});
 }
 
 function editUserRole(obj) {
  if (!("value" in obj.dataset))
  {
   obj.dataset.value=obj.innerHTML;
   var values = ['owner','editor','viewer','reviewer'];
   var options='';
    for (var i=0; i<values.length; i++)
    options+='<option '+(values[i]==obj.dataset.value?'selected="" ':'')+'value="'+values[i]+'">'+values[i]+'</option>';
   obj.innerHTML='<select>'+options+'</select><span class="w3-tag w3-teal w3-round button" onclick="editUserRoleOK(this,event);">OK</span><span class="w3-tag w3-teal w3-round button" onclick="cancelEdit(this,event);">X</span>';
  }
 }
 
 function toggleClass(obj,className) {
	if (obj.className==className)
		obj.className='';
	else
		obj.className=className;
 }
 
 function inarray(needle, haystack) {
	 for (var i=0; i<haystack.length; i++)
		 if (needle==haystack[i])
		 return true;
	return false;
 }
 
 function editUserAdminRole(obj) {
  if (!("value" in obj.dataset))
  {
   obj.dataset.value=obj.innerHTML;
   var values=obj.dataset.data.split(',');
   var options='';
    for (var i=0; i<roles.length; i++)
    options+='<span onclick="toggleClass(this,\'selected\')"'+(inarray(roles[i],values)?' class="selected"':'')+'>'+roles[i]+'</span>';
   obj.innerHTML=options+'<span class="w3-tag w3-teal w3-round button" onclick="editUserAdminRoleOK(this,event);">Save</span><span class="w3-tag w3-teal w3-round button" onclick="cancelEdit(this,event);">Cancel</span>';
  }
 } 
 
 function editUserAdminRoleOK(obj,e) {
	e.stopPropagation();
	var r=[];
	var newval='';
	for (var i=0; i<roles.length; i++)
	 if (obj.parentNode.children[i].className=='selected')
	 {
	  if (r.length>0)
	  newval+=', ';
	  newval+=roles[i];
	  r.push(roles[i]);
	 }
	var userid = obj.parentNode.parentNode.dataset.id;
	$.post("update.php",
    {
        action: "editUserAdminRole",
		userid: userid,
		newadminroles: r
    },
    function(data, status){
		obj.parentNode.removeAttribute("data-value");
		if (getTagValue(data,'result')=='OK')
		{
		 obj.parentNode.dataset.data=getTagValue(data,'data');
/*		  if (newval=='')
		  obj.parentNode.innerHTML='User';
		  else*/
          obj.parentNode.innerHTML=getTagValue(data,'dataf');//newval;
		}
		else
		{
		 obj.parentNode.innerHTML=obj.parentNode.dataset.value;	
		 obj.parentNode.removeAttribute("data-value");
		}
	});
 }
 
 function addUserToProject(obj) {
  var userlist=document.getElementsByClassName("users")[0];	 
    $.post("update.php",
    {
        action: "addUserToProject",
		userid: obj.dataset.id,
		projectid: userlist.dataset.project
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
        {
		 var newrow = userlist.insertRow();
         newrow.dataset.id=obj.dataset.id;
         newrow.dataset.project=userlist.dataset.project;
         newrow.innerHTML='<td><span class="w3-tag w3-teal w3-round button" onclick="removeUserFromProject(this);">X</span></td><td>'+obj.cells[0].innerHTML+'</td><td>'+obj.cells[1].innerHTML+'</td><td onclick="editUserRole(this);">editor</td>';
         obj.parentNode.deleteRow(obj.rowIndex);	
		}
		else
		{
		 obj.innerHTML='<td class="error" colspan=2>'+getTagValue(data,'result')+'</td>';
		}
	});	   
 }
 
 function searchUsers(obj,event,box) {
  box=document.getElementById(box);	 
  if (obj.value.length>=3)
   $.post("update.php",
    {
        action: "searchUser",
		search: obj.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
         box.innerHTML=getTagValue(data,'userlist');
		else
		 box.innerHTML='';	
	});	  
 }
 
 function addUser(box) {
  box=document.getElementById(box);
  var search=document.getElementsByClassName("searchbox")[0];
   if (search.className.indexOf("hidden")>-1)
   {
    search.className=search.className.split("hidden").join("");
   }
   else
   {
	   
   }
 }
 
 function changeProject(obj) {
  document.location.href='?project='+obj.value;	 
 }
 
 function changeStation(obj) {
  document.location.href='?station='+obj.value;	 
 }
 
 function cancelTaskEdit(e) {
  var task = e.target.parentNode.parentNode;
  
  $.post("update.php",
  {
    action: "getTask",
	taskid: task.dataset.id
  },
   function(data, status){
	if (getTagValue(data,'result')=='OK')
	{
	 task.children[1].removeChild(task.children[1].children[1]);
	 task.children[1].innerHTML+=getTagValue(data,'time');
	 task.children[3].innerHTML=getTagValue(data,'description');
	 document.getElementById('task_'+task.dataset.id+'_operation').dataset.id=getTagValue(data,'taskid');
	 document.getElementById('task_'+task.dataset.id+'_operation').innerHTML=getTagValue(data,'taskname');
	 document.getElementById('task_'+task.dataset.id+'_operation').style.backgroundColor="";
	 document.getElementById('task_'+task.dataset.id+'_part').dataset.id=getTagValue(data,'partid');
	 document.getElementById('task_'+task.dataset.id+'_part').innerHTML=getTagValue(data,'partname');
	 document.getElementById('task_'+task.dataset.id+'_part').style.backgroundColor="";
	 document.getElementById('task_'+task.dataset.id+'_tool').dataset.id=getTagValue(data,'toolid');
	 document.getElementById('task_'+task.dataset.id+'_tool').innerHTML=getTagValue(data,'toolname');
	 document.getElementById('task_'+task.dataset.id+'_tool').style.backgroundColor="";
	 task.children[2].className=task.children[2].className.split(' editmode').join('');          
	}
   });
 }
 
 function okTaskEdit(e) {
  var task = e.target.parentNode.parentNode;
  var operation = document.getElementById('task_'+task.dataset.id+'_operation');
  var part = document.getElementById('task_'+task.dataset.id+'_part');
  var tool = document.getElementById('task_'+task.dataset.id+'_tool');
  var desc = document.getElementById('task_'+task.dataset.id+'_desc');
  var time = document.getElementById('task_'+task.dataset.id+'_time');
  $.post("update.php",
  {
    action: "updateTask",
	taskid: task.dataset.id,
	operationid: operation.dataset.id,
	partid: part.dataset.id,
	toolid: tool.dataset.id,
	description: desc.value,
	time: time.value
  },
   function(data, status){
	if (getTagValue(data,'result')=='OK')
	{
	 task.children[1].removeChild(task.children[1].children[1]);
	 task.children[1].innerHTML+=getTagValue(data,'time');
	 task.children[3].innerHTML=desc.value;
	 operation.style.backgroundColor="";
	 part.style.backgroundColor="";
	 tool.style.backgroundColor="";
	 task.children[2].className=task.children[2].className.split(' editmode').join('');          
	}
	else
	{
	 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
     windowShow(null);	
	}
   });	 
 }
 
 function clickPosition(obj) {
  var id=obj.parentNode.parentNode.dataset.assembly;
  var positions=document.getElementById("newsub_position").innerHTML;
  document.getElementById("subasm_"+id+"_cat").innerHTML="Place:";
  document.getElementById("subasm_"+id+"_catval").innerHTML=positions;
  document.getElementById("subasm_"+id+"_catval").selectedIndex=0;
 }
 
 function editSubassembly(tasks, i) {
  var id=tasks.children[i].dataset.assembly;
  tasks.children[i].children[3].className=tasks.children[i].children[3].className.split(' selected').join('')+' editmode';
  var t=document.getElementById("subassembly_"+tasks.children[i].dataset.assembly).innerText;
  var d=document.createElement('INPUT');
  d.type="text";
  d.value=t;
  d.id='assembly_'+id+'_name';
  tasks.children[i].children[4].innerHTML='<span id="subasm_'+id+'_cat">Operation type:</span><select data-sub="subasm_'+id+'_subcatval" id="subasm_'+id+'_catval"></select><span id="subasm_'+id+'_subcat">Operation:</span><select id="subasm_'+id+'_subcatval"></select>';
  tasks.children[i].children[4].appendChild(d);
  var button=document.createElement('SPAN');
  button.innerHTML='OK';
  button.className="w3-tag w3-teal w3-round button";
  button.onclick=okTaskEdit;                                        
  tasks.children[i].children[4].appendChild(button);
  button=document.createElement('SPAN');
  button.innerHTML='Cancel';
  button.className="w3-tag w3-teal w3-round button";
  button.onclick=cancelTaskEdit;
  tasks.children[i].children[4].appendChild(button);
  tasks.children[i].children[3].children[2].click();	 
 }
 
 function editTaskInStation(tasks, i) {
  var htm=tasks.children[i].children[1].innerHTML;
  var pos=htm.indexOf('</i>');
   if (pos>-1)
   tasks.children[i].children[1].innerHTML=htm.substr(0,pos+4)+	 
   '<input id="task_'+tasks.children[i].dataset.id+'_time" type="text" value="'+htm.substr(pos+4)+'" />';
  tasks.children[i].children[2].className=tasks.children[i].children[2].className.split(' selected').join('')+' editmode';
  var t=tasks.children[i].children[3].innerText;
  var d=document.createElement('TEXTAREA');
  d.value=t;
  d.id='task_'+tasks.children[i].dataset.id+'_desc';
  tasks.children[i].children[3].innerHTML='<span id="task_'+tasks.children[i].dataset.id+'_cat">Operation type:</span><select data-sub="task_'+tasks.children[i].dataset.id+'_subcatval" id="task_'+tasks.children[i].dataset.id+'_catval"></select><span id="task_'+tasks.children[i].dataset.id+'_subcat">Operation:</span><select id="task_'+tasks.children[i].dataset.id+'_subcatval"></select>';
  tasks.children[i].children[3].appendChild(d);
  var button=document.createElement('SPAN');
  button.innerHTML='OK';
  button.className="w3-tag w3-teal w3-round button";
  button.onclick=okTaskEdit;                                        
  tasks.children[i].children[3].appendChild(button);
  button=document.createElement('SPAN');
  button.innerHTML='Cancel';
  button.className="w3-tag w3-teal w3-round button";
  button.onclick=cancelTaskEdit;
  tasks.children[i].children[3].appendChild(button);
  tasks.children[i].children[2].children[1].click();	 
 }
 
 function editTask() {
   var tasks=document.getElementById("tasklist");
   for (var i=0; i<tasks.children.length; i++)
    if (tasks.children[i].tagName=='DIV')
	 if (tasks.children[i].hasAttribute('data-type'))
	 {
      if ((tasks.children[i].dataset.type=="taskitem") ||
           ((tasks.children[i].dataset.type=="assembly") && (tasks.children[i].dataset.id>0)))    	  
	   if ((tasks.children[i].children[2].className.indexOf('editmode')==-1) &&
          (tasks.children[i].children[2].className.indexOf('selected')>-1)) 
	   editTaskInStation(tasks, i);
	  if ((tasks.children[i].dataset.type=="assembly") && (tasks.children[i].dataset.id==0))
	   if ((tasks.children[i].children[3].className.indexOf('editmode')==-1) &&
          (tasks.children[i].children[3].className.indexOf('selected')>-1))  
	   editSubassembly(tasks, i);	  
	 }
 }
 
 function cloneTask() {
   var tasks=document.getElementById("tasklist");
   var clonedTasks=[];
   var clonedI=[];
   for (var i=0; i<tasks.children.length; i++)
    if (tasks.children[i].tagName=='DIV')
	 if (tasks.children[i].hasAttribute('data-type'))
	 {
      if ((tasks.children[i].dataset.type=="taskitem") ||
           ((tasks.children[i].dataset.type=="assembly") && (tasks.children[i].dataset.id>0)))    	  
	   if ((tasks.children[i].children[2].className.indexOf('editmode')==-1) &&
          (tasks.children[i].children[2].className.indexOf('selected')>-1)) 
		  {
           var newObject=tasks.children[i].cloneNode(true);
           newObject.children[2].className=newObject.children[2].className.split(' selected').join('');
		   newObject.id=newObject.id+'-'+(tasks.children.length+1);
		   clonedTasks.push(newObject.dataset.id);
		   newObject.dataset.id='0';
		    if (i<tasks.children.length-1)
            tasks.insertBefore(newObject,tasks.children[i+1]);
		    else
			tasks.appendChild(newObject);
		   clonedI.push(i+1);
		  }
/*	  if ((tasks.children[i].dataset.type=="assembly") && (tasks.children[i].dataset.id==0))
	   if ((tasks.children[i].children[3].className.indexOf('editmode')==-1) &&
          (tasks.children[i].children[3].className.indexOf('selected')>-1))  
	   editSubassembly(tasks, i);	  */
	 }
	 
	 if (clonedTasks.length>0)
	  $.post("update.php",
      {
        action: "cloneTasks",
		tasks: clonedTasks
      },
      function(data, status){
		if (getTagValue(data,'result')=='OK')
		{
			var newIds=getTagValue(data,'ids').split(',');
			for (var i=0; i<newIds.length; i++)
			{
			 tasks.children[clonedI[i]].dataset.id=newIds[i];
			 if (newIds[i]==0)
			 document.location.reload();
			}
			refreshTaskIDs();
		}
		else
		document.location.reload();
		
		//TODO: Update id and numbering of all the tasks to avoid need for full page refresh
	  })
 } 
 
 function subTypesChange(e) {
  if (!(e instanceof Event))
  e.target=e;	  	 
  if (e.target.hasAttribute('data-parent'))
  {
    var plabel=document.getElementById(e.target.dataset.parent);
	if (e.target.selectedIndex>-1)
	{
	 plabel.innerHTML=e.target.options[e.target.selectedIndex].innerText;
	 plabel.dataset.id=e.target.value;
	}
  }
 }
 
 function getSubTypes(e) {
  if (!(e instanceof Event))
  e.target=e;	  
  var maintype = e.target.value;
  var subtypes = document.getElementById(e.target.dataset.sub);
  if (e.target.hasAttribute('data-parent'))
  {
   subtypes.dataset.parent=e.target.dataset.parent;  
   subtypes.onchange=subTypesChange;
  }
   $.post("update.php",
    {
        action: "getSubTypes",
		maintype: maintype
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
	 {
      subtypes.innerHTML=getTagValue(data,'response');
       if (e.target.hasAttribute('data-parent'))
	   {
		if (e.target.hasAttribute("data-id"))
		{	
	     for (var j=0; j<subtypes.options.length; j++)
		  if (subtypes.options[j].value==e.target.dataset.id)
		  {
           subtypes.value=e.target.dataset.id;
		   break;
		  }
		  subTypesChange(subtypes);
		}
	   }
	   else
	   if (subtypes.onchange!=null)
	   subtypes.onchange(subtypes);
	 }
	});
 }
 
 function getSubParts(e) {
  if (!(e instanceof Event))
  e.target=e;	 
  var maintype = e.target.value;
  var subtypes = document.getElementById(e.target.dataset.sub);
  var stationid = document.getElementById("stations").value;
  if (e.target.hasAttribute('data-parent'))
  {
   subtypes.dataset.parent=e.target.dataset.parent;  
   subtypes.onchange=subTypesChange;
  }
   $.post("update.php",
    {
        action: "getSubParts",
		maintype: maintype,
		stationid: stationid
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
	 {
      subtypes.innerHTML=getTagValue(data,'response');
	  if (e.target.hasAttribute('data-parent'))
	   {
		if (e.target.hasAttribute("data-id"))
		{	
	     for (var j=0; j<subtypes.options.length; j++)
		  if (subtypes.options[j].value==e.target.dataset.id)
		  {
           subtypes.value=e.target.dataset.id;
		   break;
		  }
		  subTypesChange(subtypes);
		}
	   }
	   
	   if (e.target.id=="new_parttype")
		partChange();
	
	   if (subtypes.hasAttribute('data-setvalue'))
	   {
			subtypes.value=subtypes.dataset.setvalue;
			subtypes.removeAttribute('data-setvalue');
	   }
	 }
 
	});
 }
 
 function getSubTools(e) {
  if (!(e instanceof Event))
  e.target=e;	  
  var maintype = e.target.value;
  var subtypes = document.getElementById(e.target.dataset.sub);
  if (e.target.hasAttribute('data-parent'))
  {
   subtypes.dataset.parent=e.target.dataset.parent;  
   subtypes.onchange=subTypesChange;
  }
   $.post("update.php",
    {
        action: "getSubTools",
		maintype: maintype
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
	 {
      subtypes.innerHTML=getTagValue(data,'response');
	  if (e.target.hasAttribute('data-parent'))
	   {
		if (e.target.hasAttribute("data-id"))
		{	
	     for (var j=0; j<subtypes.options.length; j++)
		  if (subtypes.options[j].value==e.target.dataset.id)
		  {
           subtypes.value=e.target.dataset.id;
		   break;
		  }
		  subTypesChange(subtypes);
		}
	   }
	 }
	});
 }
 
 function getDefaultTool(obj) {
  var tooltypes=document.getElementById("new_tooltype");
  var subtypes=document.getElementById("new_subtype");
  var parttypes=document.getElementById("new_parttype");
  var toolselector=document.getElementById("new_tool");
  var partselector=document.getElementById("partselector");
	if (subtypes.options[subtypes.options.selectedIndex].dataset.defaulttool==-1)
	{
	 tooltypes.style.display='none';
	 toolselector.style.display='none';
	}
	else
	{
	 tooltypes.style.display='';
	 toolselector.style.display='';
	 if (subtypes.options[subtypes.options.selectedIndex].dataset.defaulttool!=0)
	 {
      tooltypes.value=subtypes.options[subtypes.options.selectedIndex].dataset.defaulttool;
	  tooltypes.onchange(tooltypes);
	 }
	}
	
	 if (subtypes.options[subtypes.options.selectedIndex].dataset.defaultpart==-1)
	 {
	  parttypes.style.display='none';
	  partselector.style.display='none';
	 }
	 else
	 {
	  parttypes.style.display='';
	  partselector.style.display='';
	   if (subtypes.options[subtypes.options.selectedIndex].hasAttribute('data-onlyparts'))
	   {
		var parts=subtypes.options[subtypes.options.selectedIndex].dataset.onlyparts.split(",")
		 if (parts.length==1)
		 parttypes.style.display='none';
		 if (parts.indexOf(subtypes.options[subtypes.options.selectedIndex].dataset.defaultpart)==-1)
		 subtypes.options[subtypes.options.selectedIndex].dataset.defaultpart=parts[0];
	   }
		if (subtypes.options[subtypes.options.selectedIndex].dataset.defaultpart!=0)
		{
		  parttypes.value=subtypes.options[subtypes.options.selectedIndex].dataset.defaultpart;
		  parttypes.onchange(parttypes);
		}
	 }
 }
 
 //windows
 function windowUpdate(title,content,buttons) {
  var w=document.getElementsByClassName("modalwindow")[0];
  w.firstChild.innerHTML=content;
  w.lastElementChild.innerHTML=title;
  
  if (buttons.length==1)
  {
   w.children[2].innerHTML=buttons[0];	  	  
   if (w.children[1].className.indexOf("single")==-1)
   w.children[1].className=w.children[1].className+" single";
   if (w.children[2].className.indexOf("single")==-1)
   w.children[2].className=w.children[2].className+" single";
  }
  else
  {
	w.children[1].innerHTML=buttons[0];	  	    
	w.children[2].innerHTML=buttons[1];	    
	w.children[1].className=w.children[1].className.split(' single').join('');  
	w.children[2].className=w.children[2].className.split(' single').join('');  
  }
 }
 