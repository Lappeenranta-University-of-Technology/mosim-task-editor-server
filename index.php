<?php
 include 'common.php';
 if (!isset($_SESSION['showTaskID']))
 $_SESSION['showTaskID']=false;
?>
<!DOCTYPE html>
<html>
<title>MOSIM task list editor</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/windows.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<script language="javascript" src="dragdrop.js"></script>
<!-- Babylon.js -->
<script src="https://preview.babylonjs.com/babylon.js"></script>
<script src="https://preview.babylonjs.com/loaders/babylonjs.loaders.min.js"></script>
<script src="https://code.jquery.com/pep/0.4.3/pep.js"></script>
<script src="babylon.stlFileLoader.min.js"></script>
<!-- End of Babylon.js -->
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="css/index.css">
<script>
 
 function removeStationDialog(stationid) {
  var w=document.getElementById("msgwindow");
  w.dataset.stationid=stationid;
  w.className=w.className+" show";  
  windowUpdate('Delete confirmation','Are you sure you want to delete the current station with all the tasks?<br>This action is irreversable!',Array('Delete','Cancel'));
 }

 function windowShow(obj) {
  var w=document.getElementById("msgwindow");	 
   if (obj!=null)
   {
    w.dataset.catid=obj.parentNode.dataset.id;
    w.lastElementChild.innerHTML=obj.parentNode.dataset.catname;
   }
  w.className=w.className+" show";
 }

 function windowUpload(obj) {
  	 
 }
 
 function showAddTaskWindow() {
	var w=document.getElementById("newtaskwindow");
	if (w.className.indexOf(" show")==-1)
	{
		w.className=w.className+" show";
		partChange();
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
 
 function partChange() {
	 var p=document.getElementById('partselector');
	 loadPart3D(p.value,"pyTxMNWiTCQLK6rkTiocd486spTm33");
	 //var i=document.getElementById('new_partpreview');
	 //i.style.backgroundImage='url(\'image.php?part='+p.value+'\')';
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
	     html+="<h6 class=\"iconback time\"><i class=\"fa fa-hourglass fa-fw w3-margin-right\"></i>"+getTagValue(data2,'esttime')+"</h6>";
		 html+="<h5 onclick=\"clickSel(this);\" class=\"w3-opacity task\"><b>"+(getTagValue(data2,'showTaskID')?"ID "+getTagValue(data2,'id'):(getTagValue(data2,'tasktypeid')==0?'':"Task "+mainid+'.'+( i + 1 )))+"</b>";
		 if (getTagValue(data2,'tasktypeid')==0) //subassembly header/footer
		 {
	     html+="<span id=\"subassembly_"+getTagValue(data2,'stationid')+"\" onclick=\"clickTaskType(this);\" class=\"subassembly\" data-id=\""+getTagValue(data2,'stationid')+"\">"+getTagValue(data2,'tasktype')+"</span>";
		 html+="<span id=\"subassembly_"+getTagValue(data2,'stationid')+'_part'+"\" data-id=\""+getTagValue(data2,'partid')+"\" onclick=\"clickPart(this);\" style=\"background-image:url('icons/"+getTagValue(data2,'particon')+"');\" class=\"w3-tag w3-round tagicon part\">"+getTagValue(data2,'partname')+"</span>";
		 html+="<span id=\"subassembly_"+getTagValue(data2,'stationid')+'_position'+"\" onclick=\"clickPosition(this);\" data-id=\""+getTagValue(data2,'positionid')+"\" style=\"background-image:url('icons/"+getTagValue(data2,'toolicon')+"');\" class=\"w3-tag w3-round tagicon position\">"+getTagValue(data2,'positionname')+"</span>";
		 }
	     else
		 {
	     html+="<span id=\"task_"+getTagValue(data2,'id')+'_operation' + "\" onclick=\"clickTaskType(this);\" style=\"background-image:url('icons/"+getTagValue(data2,'tticon')+"');\" class=\"w3-tag w3-round tagicon operation\" data-id=\""+getTagValue(data2,'tasktypeid')+"\">"+getTagValue(data2,'tasktype')+"</span>";
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
   '(SELECT pc.icon FROM partcat pc, part_cat p_c WHERE p_c.cat=pc.id and p_c.part=hlt.partid LIMIT 1) as particon, '.
   '(SELECT tc.icon FROM toolcat tc, tool_cat t_c WHERE t_c.cat=tc.id and t_c.tool=hlt.toolid LIMIT 1) as toolicon, '.
   'if(tt.icon=\'\',(SELECT icon FROM tasktypes WHERE id=tt.parent LIMIT 1),tt.icon) as tticon '.
   'FROM highleveltasks hlt, tools t, parts p, tasktypes tt '.      
   'WHERE tt.id=hlt.tasktype and p.id=hlt.partid and t.id=hlt.toolid and hlt.stationid='.$stationid.' '.                                         
   'UNION ALL '.
   'SELECT 0, s.id, 0, s.name, s.sortorder, 0, s.mainpart, p.name, \'\', pp.id, pp.name, \'\', \'00:00:00\', \'subassembly.png\' as icon, \'\', \'\' FROM stations s, stations p, positions pp WHERE s.main=\'station\' and s.mainpart=p.id and s.position=pp.id and s.parent='.$stationid.' '.
   'UNION ALL '.
   'SELECT 0, s.id, 0, s.name, s.sortorder, 0, s.mainpart, p.name, \'\', pp.id, pp.name, \'\', \'00:00:00\', (SELECT icon FROM partcat pc, part_cat p_c WHERE pc.id=p_c.cat and p_c.part=s.mainpart LIMIT 1) as icon, \'\', \'\' FROM stations s, parts p, positions pp WHERE s.main=\'part\' and s.mainpart=p.id and s.position=pp.id and s.parent='.$stationid.' '.
   'UNION ALL '.
   'SELECT hlt.`id`, hlt.`stationid`, tt.id as tasktypeid, tt.name as `tasktype`, hlt.`sortorder`, hlt.toolid as toolid, CONCAT(\'S\',hlt.subpartid) as `partid`, p.name as `partname`, t.name as `toolname`, 0 as positionid, hlt.`positionname`, hlt.`description`, hlt.`esttime`, \'subassembly.png\' as particon, tc.icon as toolicon, if(tt.icon=\'\',(SELECT icon FROM tasktypes WHERE id=tt.parent LIMIT 1),tt.icon) as tticon '.
   'FROM highleveltasks hlt, tools t, toolcat tc, tool_cat t_c, stations p, tasktypes tt WHERE t_c.tool=t.id and t_c.cat=tc.id and hlt.partid=0 and hlt.subpartid=p.id and tt.id=hlt.tasktype and t.id=hlt.toolid and hlt.stationid='.$stationid.' '.   
   '  ORDER BY sortorder, id';
   //TODO: Add display of first level subassembly headers
   //TODO: Check why LIMIT 1 is needed in all the subqueries, they should be constrained enough not to produce more than one result.

   
   if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	 $j=$j+1;	
	 echo '<div id="tool'.$j.'" data-level="0" data-order="'.$j.'" data-type="'.(($row['tasktypeid']==0)?'assembly" data-assembly="'.$row['stationid']:'taskitem').'" data-id="'.$row['id'].'" class="w3-container">';
	 echo '<div class="handle'.(($row['tasktypeid']==0)?' subassembly':'').'"></div>';
	 if ($row['tasktypeid']==0)
	 echo '<div class="unfold" onclick="unFoldSubAssembly(this);"></div>';
	 echo "<h6 class=\"iconback time\"><i class=\"fa fa-hourglass fa-fw w3-margin-right\"></i>".$row['esttime']."</h6>";
	                                         
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
	 echo "<span id=\"task_".$row['id'].'_operation'."\" onclick=\"clickTaskType(this);\" ".notBlankIcon($row['tticon'])." class=\"w3-tag w3-round tagicon operation\" data-id=\"".$row['tasktypeid']."\">".htmlentities($row['tasktype'])."</span>";
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
 
 function insertStations($selectCurrent=true)
 {
  global $stations;
  
  if ($selectCurrent)
  echo $stations;
  else
  echo str_ireplace('<option selected="" value=','<option value=',$stations);	  
 }	 
  /*
 function insertPartTypes()
 {
  global $db, $stationid;
  $selid=-2;
  $i=0;
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
 } */
 
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
<!-- First modal window is the general message window -->
<div id="msgwindow" class="modalwindow"><div></div>
<div class="modalbutton" onclick="windowOK(this);">Delete</div>
<div class="modalbutton" onclick="windowCancel(this);">Cancel</div>
<div class="modaltoolbar">Warning</div>
</div>

<div id="newtaskwindow" class="modalwindow"><div>
<canvas id="renderCanvas" touch-action="none"></canvas><!-- touch-action="none" for best results from PEP -->
<div id="new_partpreview" style="background-image: url('car.jpg'); display:none;"></div>
<p><span>Operation type:</span><select id="new_type" data-sub="new_subtype" onchange="getSubTypes(event);"><?php insertTypes(); ?></select></p>
<p><span>Operation:</span><select id="new_subtype" onchange="getDefaultTool(event);"><?php insertSubTypes(); ?></select></p>
<p><span>Part type:</span><select id="new_parttype" data-sub="partselector" onchange="getSubParts(event);"><?php $selPartType=insertPartTypes($stationid); ?></select></p>
<p><span>Part:</span><select id="partselector" onchange="partChange();">
  <?php 
   if ($selPartType==-2)
   insertUncategorizedParts();
   else
   insertParts($selPartType); 
  ?></select></p>
  <p><span>Tool type:</span><select id="new_tooltype" data-sub="new_tool" onchange="getSubTools(event);"><?php $selToolType=insertToolTypes(); ?></select></p>
<p><span>Tool:</span><select id="new_tool">
 <?php 
   if ($selToolType==-2)
   insertUncategorizedTools();
   else
   insertTools($selToolType); 
  ?></select></p>
<p><span>Time estimate:</span><input id="new_time" onchange="timeEstimate(this);" type="text" placeholder="00:00:00" value="" /></p>
<p><span>Description:</span><textarea id="new_description" style="width:100%"></textarea></p>
</div>
<div class="modalbutton" onclick="addTask(); windowCancel(this);">Add</div>
<div class="modalbutton" onclick="windowCancel(this);">Cancel</div>
<div class="modaltoolbar">New task</div>
</div>
<script>
<?php include('babylon.js'); ?>
</script>
<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Left Column -->
    <div class="w3-third">
    
      <div class="w3-white w3-text-grey w3-card-4">
        
		<?php include('header.php'); ?>
        
        <div class="w3-container menuitems">
        <?php include('menu.php'); ?>
		</div>
		<div class="w3-container">
		  <hr />
		  <p><i class="fa fa-gear fa-fw w3-margin-right w3-large iconback pointer"></i><a href="#newstation">New station</a></p>
		  <hr>
          <p><i class="fa fa-gear fa-fw w3-margin-right w3-large iconback pointer"></i><a<span class="pointer" onclick="showAddTaskWindow();">New task</span></p>
		  <p><i class="fa fa-gear fa-fw w3-margin-right w3-large iconback pointer"></i><a href="#" onclick="editTask();">Edit task</a></p>
		  <p><i class="fa fa-copy fa-fw w3-margin-right w3-large iconback pointer"></i><a href="#" onclick="cloneTask();">Duplicate task</a></p>
          <p><i class="fa fa-trash fa-fw w3-margin-right w3-large iconback pointer"></i><span class="pointer" onclick="removeTask();">Remove task</span></p>
          <p><i class="fa fa-sort fa-fw w3-margin-right w3-large iconback pointer"></i><a href="#movetoother">Move task to other station</a></p>
		  <hr>
		  <p><i class="fa fa-gear fa-fw w3-margin-right w3-large iconback pointer"></i><a href="#newsubassembly">New subassembly</a></p>
		  <hr>
		  
		  <?php
		  echo '<p><i class="fa fa-eye fa-fw w3-margin-right w3-large iconback pointer"></i><a href="#" id="taskidshowhide" onclick="showTaskID(this);" data-show="'.($_SESSION['showTaskID']?'1':'0').'">'.($_SESSION['showTaskID']?'Show task numbers':'Show task IDs').'</a></p>';
		  
		   if ($stationid>0)
		   echo '<hr>'.
		        '<p><i class="fa fa-gear fa-fw w3-margin-right w3-large iconback pointer"></i><a href="api.php?action=getTaskList&station='.$stationid.'&format=XML">Export tasks to XML</a></p>';
		  ?>
		  
          <hr>
		  
          
		  <p id="newstation" class="w3-large"><b><i class="fa fa-gear fa-fw w3-margin-right iconback"></i>New station</b></p>
		  <p>Name: <input id="new_stationname" type="text" /></p>
		  <p style="text-align: center"><span class="w3-tag w3-round button" onclick="addStation();">Create station</span></p>
		  <p><i class="fa fa-trash fa-fw w3-margin-right w3-large iconback pointer"></i><span class="pointer" onclick="removeStationDialog(<?php echo $stationid; ?>);">Remove current station</span></p>
		  
		  <hr>

          <p id="movetoother" class="w3-large w3-text-theme"><b><i class="fa fa-sort fa-fw w3-margin-right iconback"></i>Move tasks to other station</b></p>
          <p>To station: <select id="tostation"><?php insertStations(false); ?></select></p>
		  <p style="text-align: center"><span class="w3-tag w3-round button" onclick="moveSelected();">Move selected</span></p>               

		  <hr>
		  
		  <p id="newsubassembly" class="w3-large w3-text-theme"><b><i class="fa fa-gear fa-fw w3-margin-right iconback"></i>Create subassembly</b></p>
          <p class="warning">Experimental - not fully supported yet</p>
		  <p>Name: <input type="text" id="new_subassemblyname" /></p>
		  <p>Main part type: <select id="newsub_parttype" data-sub="subpartselector" onchange="getSubParts(event);"><?php insertPartTypes($stationid); ?></select></p>
          <p style="margin-left:10px;">Main part: <select id="subpartselector"><?php insertParts(); ?></select></p>
		  <p>Place: <select id="newsub_position"><?php insertSubPositions(); ?></select></p>
		  <p style="text-align: center"><span class="w3-tag w3-round button" onclick="addSubAssembly();">Create subassembly</span></p>               
        </div>
      </div>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
      <div class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 id="projectname"><?php projectName(); ?></h2>
	  </div>
      <div id="tasklist" style="position:relative;" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-gear fa-fw w3-margin-right w3-xxlarge iconback"></i><span class="fa fa-angle-left w3-margin-right pointer" onclick="prevClick();"></span><select onchange="changeStation(this);" id="stations"><?php insertStations(); ?></select><span class="fa fa-angle-right w3-margin-left pointer" onclick="nextClick();"></span></h2>
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

<footer class="w3-container banner w3-center w3-margin-top">
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
//partSelect(document.getElementById("partselector"));
makeToolDraggable();
//dragElement(document.getElementById("tool2"));
</script>
</body>
</html>
