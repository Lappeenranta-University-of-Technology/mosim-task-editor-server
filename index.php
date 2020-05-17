<?php
 include('common.php');
 if (!isset($_SESSION['showTaskID']))
 $_SESSION['showTaskID']=false;
?>
<!DOCTYPE html>
<html>
<title>MOSIM task list editor</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="w3.css">
<link rel="stylesheet" href="windows.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<script language="javascript" src="dragdrop.js"></script>
<link rel="stylesheet" href="styles.css">
<script>

 function removeStationDialog(stationid) {
  var w=document.getElementsByClassName("modalwindow")[0];
  w.dataset.stationid=stationid;
  w.className=w.className+" show";  
  windowUpdate('Delete confirmation','Are you sure you want to delete the current station with all the tasks?<br>This action is irreversable!',Array('Delete','Cancel'));
 }

 function windowShow(obj) {
  var w=document.getElementsByClassName("modalwindow")[0];	 
   if (obj!=null)
   {
    w.dataset.catid=obj.parentNode.dataset.id;
    w.lastElementChild.innerHTML=obj.parentNode.dataset.catname;
   }
  w.className=w.className+" show";
 }

 function windowUpload(obj) {
  	 
 }
 
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
 
 function windowOK(obj) {
  $.post("update.php",
  {
   action: "deleteStation",                                     
   stationid: obj.parentNode.dataset.stationid
  },
  function(data, status){ 	
   if (getTagValue(data,'result')=='OK')
   document.location.href='index.php';
   //windowCancel(obj);	 
   else
   windowUpdate('Error',getTagValue(data,'result'),Array('OK'));
  });  
 }
 
 function windowCancel(obj) {
  obj.parentNode.className=obj.parentNode.className.split(' show').join('');	 
 }
 
 function refreshTaskIDs() {
  var obj=document.getElementById('taskidshowhide');
  var j=[1];
  
  var tasklist=document.getElementById("tasklist");  
   for (var i=0; i<tasklist.children.length; i++)
	if (tasklist.children[i].hasAttribute('data-type'))
	{
	 if (tasklist.children[i].dataset.type=="taskitem")
	 {
	  if (obj.dataset.show=="1")   
      tasklist.children[i].children[2].children[0].innerHTML=
      "ID "+tasklist.children[i].dataset.id;	 
	  else
	  tasklist.children[i].children[2].children[0].innerHTML=
      "Task "+j[0];	 	
	  j[0]++;
	 }
	 if (tasklist.children[i].dataset.type=="assembly")
	 if (tasklist.children[i].dataset.id!=-1)	 
	 {	
      var branch=2;
      if (tasklist.children[i].dataset.id==0)
	  {
       j[parseInt(tasklist.children[i].dataset.level)+1]=1;                       
	   branch=3;
	  }
  
	  if (obj.dataset.show=="1")   
      tasklist.children[i].children[branch].children[0].innerHTML=
      "ID "+tasklist.children[i].dataset.assembly;	 
	  else
	  {
	   var task='Task ';
	    for (var m=0; m<tasklist.children[i].dataset.level; m++)
		task=task+(j[m]-1)+'.';
	  tasklist.children[i].children[branch].children[0].innerHTML=
      task+j[parseInt(tasklist.children[i].dataset.level)];
	  }	  
	  j[parseInt(tasklist.children[i].dataset.level)]++;
	 }
	} 
 }
 
 function showTaskID(obj) {	 
  if (obj.dataset.show=="1")
  {
   obj.dataset.show="0";
   obj.innerHTML="Show task IDs";
  }
  else
  {
   obj.dataset.show="1";
   obj.innerHTML="Show task numbers";
  }
  /*
  var j=[1];
  
  var tasklist=document.getElementById("tasklist");  
   for (var i=0; i<tasklist.children.length; i++)
	if (tasklist.children[i].hasAttribute('data-type'))
	{
	 if (tasklist.children[i].dataset.type=="taskitem")                          
	 {		
	  if (obj.dataset.show=="1")   
      tasklist.children[i].children[2].children[0].innerHTML=
      "ID "+tasklist.children[i].dataset.id;	 
	  else
	  tasklist.children[i].children[2].children[0].innerHTML=
      "Task "+j[0];	 	
	  j[0]++;
	 }
	 if (tasklist.children[i].dataset.type=="assembly")
	 if (tasklist.children[i].dataset.id!=-1)	 
	 {	
      var branch=2;
      if (tasklist.children[i].dataset.id==0)
	  {
       j[parseInt(tasklist.children[i].dataset.level)+1]=1;                       
	   branch=3;
	  }
  
	  if (obj.dataset.show=="1")   
      tasklist.children[i].children[branch].children[0].innerHTML=
      "ID "+tasklist.children[i].dataset.assembly;	 
	  else
	  {
	   var task='Task ';
	    for (var m=0; m<tasklist.children[i].dataset.level; m++)
		task=task+(j[m]-1)+'.';
	  tasklist.children[i].children[branch].children[0].innerHTML=
      task+j[parseInt(tasklist.children[i].dataset.level)];
	  }	  
	  j[parseInt(tasklist.children[i].dataset.level)]++;
	 }
	}*/
	refreshTaskIDs();
  $.post("update.php",
  {
   action: "taskIDNumberToggle",                                     
   value: obj.dataset.show
  },
  function(data, status){ 	

  });  	
 }
 
 function unFoldSubAssembly(obj) {
  var tasklist=document.getElementById("tasklist");
  
  if (obj.className=='fold')
  {
    if (obj.parentNode.hasAttribute('data-loaded'))
	{
	 var i=1;	
	  while (document.getElementById(obj.parentNode.id+"."+i)!=null)
	  {
	   document.getElementById(obj.parentNode.id+"."+i).style.display="none";	  
	   i++;	 
	  }
	  document.getElementById(obj.parentNode.id+".end").style.display="none";
	}	  
	obj.className='unfold';  
  }
  else
  {
	if (obj.parentNode.hasAttribute('data-loaded'))
	{
	 var i=1;	
	  while (document.getElementById(obj.parentNode.id+"."+i)!=null)
	  {
	   document.getElementById(obj.parentNode.id+"."+i).style.display="";	  
	   i++;	 
	  }
	  document.getElementById(obj.parentNode.id+".end").style.display="";
	  obj.className='fold';
	}
    else		
	{
	 $.post("update.php",
     {
      action: "getSubAssemblies",
      assembly: obj.parentNode.dataset.assembly
     },
      function(data, status){ 	
       if (getTagValue(data,'result')=='OK')
	   {   
        var level=parseInt(obj.parentNode.dataset.level);
		var b=document.createElement('div');
		b.id=obj.parentNode.id+".end";
		b.className="w3-container assemblybottom";
		b.dataset.type="assembly";
		b.dataset.assembly=obj.parentNode.dataset.assembly;
		b.dataset.level=level;
		b.dataset.id="-1";
		b.innerHTML='<div class="handle subassembly bottom"></div>';
		//TODO: on mouse down event
		if (obj.parentNode.nextElementSibling==null)
	    obj.parentNode.parentNode.appendChild(b);   
		else
        obj.parentNode.parentNode.insertBefore(b,obj.parentNode.nextElementSibling);   
		var mainid=obj.parentNode.dataset.order;
		var count = getTagValue(data,'count');                         
		data1=getTagValue(data,'data');
		if (count>0)
		for (var i=count-1; i>=0; i--)                                      
		{	
		 data2=getTagValue(data1,'task'+i);	
		 var item=document.createElement('div');
		 item.id=obj.parentNode.id+"." + ( i + 1 );                
		 item.className="w3-container assembly";
		 item.dataset.type="assembly";
		 item.dataset.level=level+1;
		 item.dataset.order=i+1;
		 item.dataset.assembly=obj.parentNode.dataset.assembly;
		 item.dataset.id=getTagValue(data2,'id');
		 var html='<div class="handle'+     
		 (getTagValue(data2,'tasktypeid')==0?' subassembly':'')+'"></div>';
		 
		 if (getTagValue(data2,'tasktypeid')==0)
	     html+='<div class="unfold" onclick="unFoldSubAssembly(this);"></div>';
	     html+="<h6 class=\"w3-text-teal time\"><i class=\"fa fa-hourglass fa-fw w3-margin-right\"></i>"+getTagValue(data2,'esttime')+"</h6>";
		 html+="<h5 onclick=\"clickSel(this);\" class=\"w3-opacity task\"><b>"+(getTagValue(data2,'showTaskID')?"ID "+getTagValue(data2,'id'):(getTagValue(data2,'tasktypeid')==0?'':"Task "+mainid+'.'+( i + 1 )))+"</b>";
		 if (getTagValue(data2,'tasktypeid')==0) //subassembly header/footer
		 {
	     html+="<span id=\"subassembly_"+getTagValue(data2,'stationid')+"\" onclick=\"clickTaskType(this);\" class=\"subassembly\" data-id=\""+getTagValue(data2,'stationid')+"\">"+getTagValue(data2,'tasktype')+"</span>";
		 html+="<span id=\"subassembly_"+getTagValue(data2,'stationid')+'_part'+"\" data-id=\""+getTagValue(data2,'partid')+"\" onclick=\"clickPart(this);\" style=\"background-image:url('icons/"+getTagValue(data2,'particon')+"');\" class=\"w3-tag w3-round tagicon part\">"+getTagValue(data2,'partname')+"</span>";
		 html+="<span id=\"subassembly_"+getTagValue(data2,'stationid')+'_position'+"\" onclick=\"clickPosition(this);\" data-id=\""+getTagValue(data2,'positionid')+"\" style=\"background-image:url('icons/"+getTagValue(data2,'toolicon')+"');\" class=\"w3-tag w3-round tagicon position\">"+getTagValue(data2,'positionname')+"</span>";
		 }
	     else		                             
		 {
	     html+="<span id=\"task_"+getTagValue(data2,'id')+'_operation' + "\" onclick=\"clickTaskType(this);\" style=\"background-image:url('icons/"+getTagValue(data2,'tticon')+"');\" class=\"w3-tag w3-teal w3-round tagicon operation\" data-id=\""+getTagValue(data2,'tasktypeid')+"\">"+getTagValue(data2,'tasktype')+"</span>";
		 html+="<span id=\"task_"+getTagValue(data2,'id')+'_part'+"\" data-id=\""+getTagValue(data2,'partid')+"\" onclick=\"clickPart(this);\" style=\"background-image:url('icons/"+getTagValue(data2,'particon')+"');\" class=\"w3-tag w3-round tagicon part\">"+getTagValue(data2,'partname')+"</span>";
		 html+="<span id=\"task_"+getTagValue(data2,'id')+'_tool' + "\" onclick=\"clickTool(this);\" data-id=\""+getTagValue(data2,'toolid')+"\" style=\"background-image:url('icons/"+getTagValue(data2,'toolicon')+"');\" class=\"w3-tag w3-round tagicon tools\">"+getTagValue(data2,'toolname')+"</span>";
		 }
         html+="</h5><p>"+getTagValue(data2,'description')+"</p>";
         item.innerHTML=html;

		 obj.parentNode.parentNode.insertBefore(item, obj.parentNode.nextElementSibling);
		 
		}
		obj.parentNode.dataset.loaded=1;		                                  
		obj.className='fold';   
		makeToolDraggable();
	   }
	   else
	   console.debug("Load Sub assemblies: "+getTagValue(data,'result'));	   
      });	
	}
	  
  }
 }
</script>

<style>
div.brackettop {
 background-position: left 3px, left bottom;
 background-repeat: no-repeat, no-repeat;
 background-size: 12px, 12px calc(100% - 48px);
 background-image: url('brackettop.png'), url('bracketstraight.png');
 margin-bottom: 0px;
 margin-top: 0px;
 background-origin: border-box;
}

div.bracketbottom {
 background-position: left calc(100% - 3px), left top;
 background-repeat: no-repeat, no-repeat;
 background-size: 12px, 12px calc(100% - 60px);
 background-image: url('bracketbottom.png'), url('bracketstraight.png');
 margin-bottom: 0px;
 margin-top: 0px;
 background-origin: border-box;
}

div.brackettopm {
 background-position: left 3px, left center, left calc(100% + 59px);
 background-repeat: no-repeat;
 background-size: 12px, 12px calc(100% - 90px), 12px;
 background-image: url('brackettop.png'), url('bracketstraight.png'), url('bracketmiddle.png');
 margin-bottom: 0px;
 margin-top: 0px;
 background-origin: border-box;
}

div.bracketbottomm {
 background-position: left calc(100% - 3px), left center, left -68px;
 background-repeat: no-repeat;
 background-size: 12px, 12px calc(100% - 118px), 12px;
 background-image: url('bracketbottom.png'), url('bracketstraight.png'), url('bracketmiddle.png');
 margin-bottom: 0px;
 margin-top: 0px;
 background-origin: border-box;
}

div.bracketmiddle {
 background-position: left top;
 background-repeat: repeat-y;
 background-size: 12px, 12px;
 background-image: url('bracketstraight.png');
 margin-bottom: 0px;
 margin-top: 0px;
 background-origin: border-box;
}

div.draggedtask {
 position: absolute;
 width: 100%;
 box-sizing: border-box;
 top: 182px;
 background-color: white;
 left: 0px;
 border: 1px solid chocolate;
 transform: scale(0.9);	
 z-index:2;
}

span.tagicon {
 padding-left: 30px;
 background-repeat: no-repeat;
 background-position: 5px center;
 background-size: 22px;
 margin-bottom: 5px;
 margin-left: 5px;
}
/*
span.tagicon.tools {
 background-image: url('tools-solid.svg');
}

span.tagicon.part {
 background-image: url('puzzle-piece-solid.svg');
}

span.tagicon.operation {
 background-image: url('cogs-solid.svg');
}
*/

div.w3-container.assembly {
 width: calc(100% - 20px);
 margin-left: 20px;
}

div.w3-container.assemblybottom {
 height: 20px;
}

div.handle.subassembly {
 width: 50%;
 left: 25%; 
}

div.handle.subassembly.bottom {
 border-radius: 0px 0px 5px 5px;
}

div.unfold, div.fold {
 position: absolute;
 border: 1px solid black;
 border-radius: 5px;
 width: 35px;
 height: 35px;
 right: 0px;	
 background-position: center center;
 background-size: contain;
 background-repeat: no-repeat;
 background-color: white;
 cursor: pointer;	
 transition: background-color 0.4s linear;
}

div.unfold:hover, div.fold:hover {
 background-color: bisque;	
}

div.unfold {
 background-image:url('expand.png');	
}

div.fold {
 background-image:url('fold.png');	
}

div.handle {
 position: absolute;
 width: 30%;
 height: 10px;
 background-color:
 cadetblue;
 left: calc(50% - 15%);
 cursor: pointer;
 border-radius: 5px 5px 0px 0px;	
}

div.handle.bottom {
 border-radius: 0px 0px 5px 5px;
}

div[data-type="taskitem"] textarea {
 width: 100%;
 min-height: 200px; 
}

div[data-type="taskitem"] > p > span {
 width: 25%;
 border-radius: 5px; 
}

div[data-type="taskitem"] > p > span.button:last-of-type {
 margin-left: 10px;	
}

div.modalwindow {
	height:250px;
}

div.modalwindow > div:first-child {
 padding: 20px;
 font-size: 18px;	
}

.modalbutton:nth-child(2) {
 left: calc(25% - 5px);
}

</style>

<?php
 $stationid=0;
 if (isset($_GET['station']))
  if (ctype_digit($_GET['station']))
  $stationid=$_GET['station'];
 loadStations();
 
 include('functions.php');
 
 function notBlankIcon($data)
 {
  return ($data==''?'':' style="background-image:url(\'icons/'.$data.'\');" ');
 }
 
 function loadTasks()
 {
  global $db, $stationid;
   
   $j=0;
/*   $sql='SELECT hlt.`id`, hlt.`stationid`, tt.id as tasktypeid, tt.name as `tasktype`, hlt.`sortorder`, hlt.toolid as toolid, hlt.partid as `partid`, p.name as `partname`, t.name as `toolname`, 0 as positionid, hlt.`positionname`, hlt.`description`, hlt.`esttime`, pc.icon as particon, tc.icon as toolicon, if(tt.icon=\'\',(SELECT icon FROM tasktypes WHERE id=tt.parent),tt.icon) as tticon FROM highleveltasks hlt, tools t, toolcat tc, tool_cat t_c, parts p, tasktypes tt, partcat pc, part_cat p_c WHERE t_c.tool=t.id and t_c.cat=tc.id and p_c.part=hlt.partid and p_c.cat=pc.id and tt.id=hlt.tasktype and p.id=hlt.partid and t.id=hlt.toolid and hlt.partid>0 and hlt.stationid='.$stationid.' '.*/
   $sql='SELECT hlt.`id`, hlt.`stationid`, tt.id as tasktypeid, tt.name as `tasktype`, hlt.`sortorder`, hlt.toolid as toolid, hlt.partid as `partid`, p.name as `partname`, t.name as `toolname`, 0 as positionid, hlt.`positionname`, hlt.`description`, hlt.`esttime`, '.
   '(SELECT pc.icon FROM partcat pc, part_cat p_c WHERE p_c.cat=pc.id and p_c.part=hlt.partid) as particon, '.
   '(SELECT tc.icon FROM toolcat tc, tool_cat t_c WHERE t_c.cat=tc.id and t_c.tool=hlt.toolid) as toolicon, '.
   'if(tt.icon=\'\',(SELECT icon FROM tasktypes WHERE id=tt.parent),tt.icon) as tticon '.
   'FROM highleveltasks hlt, tools t, parts p, tasktypes tt '.      
   'WHERE tt.id=hlt.tasktype and p.id=hlt.partid and t.id=hlt.toolid and hlt.stationid='.$stationid.' '.                                         
   'UNION ALL '.                                                                 
   'SELECT 0, s.id, 0, s.name, s.sortorder, 0, s.mainpart, p.name, \'\', pp.id, pp.name, \'\', \'00:00:00\', pc.icon, \'\', \'\' FROM stations s, parts p, positions pp, partcat pc, part_cat p_c WHERE s.mainpart=p.id and s.position=pp.id and pc.id=p_c.cat and p_c.part=s.mainpart and s.parent='.$stationid.' '.
   'UNION ALL '.
   'SELECT hlt.`id`, hlt.`stationid`, tt.id as tasktypeid, tt.name as `tasktype`, hlt.`sortorder`, hlt.toolid as toolid, CONCAT(\'S\',hlt.subpartid) as `partid`, p.name as `partname`, t.name as `toolname`, 0 as positionid, hlt.`positionname`, hlt.`description`, hlt.`esttime`, \'subassembly.png\' as particon, tc.icon as toolicon, if(tt.icon=\'\',(SELECT icon FROM tasktypes WHERE id=tt.parent),tt.icon) as tticon '.
   'FROM highleveltasks hlt, tools t, toolcat tc, tool_cat t_c, stations p, tasktypes tt WHERE t_c.tool=t.id and t_c.cat=tc.id and hlt.partid=0 and hlt.subpartid=p.id and tt.id=hlt.tasktype and t.id=hlt.toolid and hlt.stationid='.$stationid.' '.   
   '  ORDER BY sortorder, id';
   //TODO: Add display of first level subassembly headers

   if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	 $j=$j+1;	
	 echo '<div id="tool'.$j.'" data-level="0" data-order="'.$j.'" data-type="'.(($row['tasktypeid']==0)?'assembly" data-assembly="'.$row['stationid']:'taskitem').'" data-id="'.$row['id'].'" class="w3-container">';
	 echo '<div class="handle'.(($row['tasktypeid']==0)?' subassembly':'').'"></div>';
	 if ($row['tasktypeid']==0)
	 echo '<div class="unfold" onclick="unFoldSubAssembly(this);"></div>';
	 echo "<h6 class=\"w3-text-teal time\"><i class=\"fa fa-hourglass fa-fw w3-margin-right\"></i>".$row['esttime']."</h6>";
	                                         
     echo "<h5 onclick=\"clickSel(this);\" class=\"w3-opacity task\"><b>".
	 ($_SESSION['showTaskID']?"ID ".$row['id']:"Task $j")."</b>";
	 
	 if ($row['tasktypeid']==0) //subassembly header/footer
	 {
	 echo "<span id=\"subassembly_".$row['stationid']."\" onclick=\"clickTaskType(this);\" class=\"subassembly\" data-id=\"".$row['stationid']."\">".htmlentities($row['tasktype'])."</span>";
	 echo "<span id=\"subassembly_".$row['stationid'].'_part'."\" data-id=\"".$row['partid']."\" onclick=\"clickPart(this);\" ".notBlankIcon($row['particon'])."class=\"w3-tag w3-round tagicon part\">".htmlentities($row['partname'])."</span>";
	 echo "<span id=\"subassembly_".$row['stationid'].'_position'."\" onclick=\"clickPosition(this);\" data-id=\"".$row['positionid']."\" ".notBlankIcon($row['toolicon'])." class=\"w3-tag w3-round tagicon position\">".htmlentities($row['positionname'])."</span></h5>";
	 }
	 else		 //tasks
	 {
	 echo "<span id=\"task_".$row['id'].'_operation'."\" onclick=\"clickTaskType(this);\" ".notBlankIcon($row['tticon'])." class=\"w3-tag w3-teal w3-round tagicon operation\" data-id=\"".$row['tasktypeid']."\">".htmlentities($row['tasktype'])."</span>";
	 echo "<span id=\"task_".$row['id'].'_part'."\" data-id=\"".$row['partid']."\" onclick=\"clickPart(this);\" ".notBlankIcon($row['particon'])." class=\"w3-tag w3-round tagicon part\">".htmlentities($row['partname'])."</span>";                              
	 echo "<span id=\"task_".$row['id'].'_tool'."\" onclick=\"clickTool(this);\" data-id=\"".$row['toolid']."\" ".notBlankIcon($row['toolicon'])." class=\"w3-tag w3-round tagicon tools\">".htmlentities($row['toolname'])."</span></h5>";
     }
	 //TODO: add position id to the query
	 
     echo "<p>".str_replace("\n","<br>",htmlentities($row['description']))."</p>";
     //echo '<hr>';
     echo '</div>';
   }
 }
 
 function loadStations()
 {
  global $db, $stationid, $stations;
  $stations='';
  $projectid=$_SESSION['projectid'];
  $i=0;
   if ($result=$db->query('SELECT id, name, sortorder FROM stations WHERE projectid='.$projectid.' and parent=0 ORDER BY sortorder'))
	while ($row=$result->fetch_assoc())	
	{
	 $stations.='<option '.((($row['id']==$stationid) || (($stationid==0) && ($i==0)))?'selected="" ':'').'value="'.$row['id'].'">'.$row['name'].'</option>';	
	 if (($stationid==0) && ($i==0))
	 $stationid=$row['id'];	 
	 $i++;
	}	 
 }
 
 function insertSubPositions()
 {
  global $db;
  $sql='SELECT id, name, sortorder FROM positions WHERE language=\'mosim\' ORDER BY sortorder, id;';
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   echo '<option value="'.$row['id'].'">'.htmlentities($row['name']).'</option>';
 }
 
 function insertStations($selectCurrent=true)
 {
  global $stations;	 
  
  if ($selectCurrent)
  echo $stations;	  
  else
  echo str_ireplace('<option selected="" value=','<option value=',$stations);	  
 }	 
  
 function insertPartTypes()
 {
  global $db, $stationid;
  $selid=-2;
  $i=0;
/*   if ($result=$db->query('SELECT ifnull(p_c.cat,-2) as id, ifnull(pc.name,\'Uncategorized\') as name, max(pc.sortorder) as sortorder FROM `parts` p LEFT JOIN (part_cat p_c, partcat pc) ON (p_c.part=p.id and pc.id=p_c.cat and pc.language=\'mosim\') WHERE p.projectid=1 GROUP BY cat, name '.
   'UNION '.
   'SELECT id, name, sortorder FROM partcat where language=\'mosim\' ORDER BY sortorder'))
   */
   if ($result=$db->query('SELECT ifnull(pc.id,"-2") as id, ifnull(pc.name,\'Uncategorized\') as name, pc.sortorder FROM 
(SELECT p_c.cat from parts p LEFT JOIN part_cat p_c on (p.id=p_c.part) WHERE p.projectid='.$_SESSION['projectid'].' GROUP BY p_c.cat) catids 
LEFT JOIN partcat pc ON (catids.cat=pc.id) WHERE isnull(pc.projectid) or pc.projectid in (0,'.$_SESSION['projectid'].') ORDER BY sortorder;'))
	while ($row=$result->fetch_assoc())
	{
		if ($i==0)
		$selid=$row['id'];
		$i++;
		echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
	}
  if ($result=$db->query('SELECT count(*) as ile FROM `stations` WHERE parent='.$stationid.';'))
   if ($row=$result->fetch_assoc())
    if ($row['ile']>0)
	{
     echo '<option value="-1">Subassemblies</option>'; 		
	  if ($i==0)
	  $selid=-2;
	}
	return $selid;
 } 
 
 function insertToolTypes()
 {
  global $db;
   $selid=-2;
   $i=0;
   if ($result=$db->query('SELECT id, name, sortorder FROM toolcat WHERE language="mosim" UNION ALL (SELECT "-2", \'Uncategorized\', "-1" FROM tools t LEFT JOIN tool_cat t_c ON (t.id=t_c.tool) WHERE isnull(t_c.cat) or t_c.cat=0 LIMIT 1) ORDER BY sortorder, id'))
	   
	while ($row=$result->fetch_assoc())
	{
	 if ($i==0)
	 $selid=$row['id'];
	 $i++;
	 echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
	}
  return $selid;
 }
 
 function insertPositions()
 {
  global $positions;
   for ($i=0; $i<count($positions); $i++)
	echo '<option>'.$positions[$i].'</option>';   
 }
 
 function insertTypes()
 {
  global $db;  
   if ($result=$db->query('SELECT id, name, sortorder FROM `tasktypes` WHERE parent=0 and language="mosim"'))
	while ($row=$result->fetch_assoc())
	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';		 
 }
 
?>

<body class="w3-light-grey">
<div class="modalwindow"><div></div>
<div class="modalbutton" onclick="windowOK(this);">Delete</div>
<div class="modalbutton" onclick="windowCancel(this);">Cancel</div>
<div class="modaltoolbar">Warning</div>
</div>


<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Left Column -->
    <div class="w3-third">
    
      <div class="w3-white w3-text-grey w3-card-4">
        
		<?php include('header.php'); ?>
        
        <div class="w3-container">
        <?php include('menu.php'); ?>
		  <hr>
		  <p><i class="fa fa-gear fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#newstation">New station</a></p>
		  <hr>
          <p><i class="fa fa-gear fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#newtask">New task</a></p>
		  <p><i class="fa fa-gear fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#" onclick="editTask();">Edit task</a></p>
		  <p><i class="fa fa-copy fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#" onclick="cloneTask();">Duplicate task</a></p>
          <p><i class="fa fa-trash fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><span class="pointer" style="text-decoration: underline;" onclick="removeTask();">Remove task</span></p>
          <p><i class="fa fa-sort fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#movetoother">Move task to other station</a></p>
		  <hr>
		  <p><i class="fa fa-gear fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#newsubassembly">New subassembly</a></p>
		  <hr>
		  
		  <?php
		  echo '<p><i class="fa fa-eye fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#" id="taskidshowhide" onclick="showTaskID(this);" data-show="'.($_SESSION['showTaskID']?'1':'0').'">'.($_SESSION['showTaskID']?'Show task numbers':'Show task IDs').'</a></p>';
		  
		   if ($stationid>0)
		   echo '<hr>'.
		        '<p><i class="fa fa-gear fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="api.php?action=getTaskList&token=mosim2020-983456&station='.$stationid.'&format=XML">Export tasks to XML</a></p>';
		  ?>
		  
          <hr>
		  
          
		  <p id="newstation" class="w3-large"><b><i class="fa fa-gear fa-fw w3-margin-right w3-text-teal"></i>New station</b></p>
		  <p>Name: <input id="new_stationname" type="text" /></p>
		  <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="addStation();">Create station</span></p>
		  <p><i class="fa fa-trash fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><span class="pointer" style="text-decoration: underline;" onclick="removeStationDialog(<?php echo $stationid; ?>);">Remove current station</span></p>
		  
		  <hr>
		  
          <p id="newtask" class="w3-large"><b><i class="fa fa-gear fa-fw w3-margin-right w3-text-teal"></i>New task</b></p>
          <p>Operation type: <select id="new_type" data-sub="new_subtype" onchange="getSubTypes(event);"><?php insertTypes(); ?></select></p>
		  <p style="margin-left:10px;">Operation: <select id="new_subtype" onchange="getDefaultTool(event);"><?php insertSubTypes(); ?></select></p>
		  <p>Part type: <select id="new_parttype" data-sub="partselector" onchange="getSubParts(event);"><?php $selPartType=insertPartTypes(); ?></select></p>
          <p style="margin-left:10px;">Part: <select id="partselector">
		  <?php 
		   if ($selPartType==-2)
		   insertUncategorizedParts();
		   else
		   insertParts($selPartType); 
		  ?></select></p>
		  <p>Tool type: <select id="new_tooltype" data-sub="new_tool" onchange="getSubTools(event);"><?php $selToolType=insertToolTypes(); ?></select></p>
		  <p style="margin-left:10px;">Tool: <select id="new_tool">
		  <?php 
		   if ($selToolType==-2)
		   insertUncategorizedTools();
		   else
		   insertTools($selToolType); 
		  ?></select></p>
		  <p>Position: <!-- <select><?php insertPositions(); ?></select>--></p>
		  <p><div id="positions" class="position"><img style="display:none;" src="car1.jpg" width="100%" />
		  <div onclick="selectPosition(this);"></div>
		  <div onclick="selectPosition(this);"></div>
		  <div onclick="selectPosition(this);">1</div>
		  <div onclick="selectPosition(this);">2</div>
		  <div onclick="selectPosition(this);">3</div>
		  <div onclick="selectPosition(this);">4</div>
		  <div onclick="selectPosition(this);">5</div>
		  <div onclick="selectPosition(this);">6</div>
		  <div onclick="selectPosition(this);">7</div>
		  <div onclick="selectPosition(this);">8</div>
		  </div></p>
		  <p>Time estimate: <input id="new_time" onchange="timeEstimate(this);" type="text" placeholder="00:00:00" value="" /></p>
		  <p>Description: <textarea id="new_description" style="width:100%"></textarea></p>
          <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="addTask();">Add</span></p>
          <hr>

          <p id="movetoother" class="w3-large w3-text-theme"><b><i class="fa fa-sort fa-fw w3-margin-right w3-text-teal"></i>Move tasks to other station</b></p>
          <p>To station: <select id="tostation"><?php insertStations(false); ?></select></p>
		  <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="moveSelected();">Move selected</span></p>               

		  <hr>
		  
		  <p id="newsubassembly" class="w3-large w3-text-theme"><b><i class="fa fa-gear fa-fw w3-margin-right w3-text-teal"></i>Create subassembly</b></p>
          <p class="warning">Experimental - not fully supported yet</p>
		  <p>Name: <input type="text" id="new_subassemblyname" /></p>
		  <p>Main part type: <select id="newsub_parttype" data-sub="subpartselector" onchange="getSubParts(event);"><?php insertPartTypes(); ?></select></p>
          <p style="margin-left:10px;">Main part: <select id="subpartselector"><?php insertParts(); ?></select></p>
		  <p>Place: <select id="newsub_position"><?php insertSubPositions(); ?></select></p>
		  <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="addSubAssembly();">Create subassembly</span></p>               
        </div>
      </div>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
    
      <div id="tasklist" style="position:relative;" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-gear fa-fw w3-margin-right w3-xxlarge w3-text-teal"></i><span class="fa fa-angle-left w3-margin-right pointer" onclick="prevClick();"></span><select onchange="changeStation(this);" id="stations"><?php insertStations(); ?></select><span class="fa fa-angle-right w3-margin-left pointer" onclick="nextClick();"></span></h2>
		<?php
		loadTasks();
		?>
      </div>

    <!-- End Right Column -->
    </div>
    
  <!-- End Grid -->
  </div>
  
  <!-- End Page Container -->
</div>

<footer class="w3-container w3-teal w3-center w3-margin-top">
<!--  <p>Find me on social media.</p> -->
<!--  <i class="fa fa-facebook-official w3-hover-opacity"></i>
  <i class="fa fa-instagram w3-hover-opacity"></i>
  <i class="fa fa-snapchat w3-hover-opacity"></i>
  <i class="fa fa-pinterest-p w3-hover-opacity"></i>
  <i class="fa fa-twitter w3-hover-opacity"></i>
  <i class="fa fa-linkedin w3-hover-opacity"></i> -->
  <p>MOSIM ITEA project</p>
</footer>
<script>
partSelect(document.getElementById("partselector"));
makeToolDraggable();
//dragElement(document.getElementById("tool2"));
</script>
</body>
</html>