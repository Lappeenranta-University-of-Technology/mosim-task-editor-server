<?php
 include('common.php');
  
  if (isset($_GET['project']) && ctype_digit($_GET['project']))
  {
   if ($result=$db->query('SELECT count(*) as ile FROM userroles ur WHERE ur.projectid='.$_GET['project'].' and ur.userid='.$_SESSION['userid'].';'))
	 if ($row=$result->fetch_assoc())
	  if ($row['ile']==1)
	  {
	   $_SESSION['projectid']=$_GET['project'];
	   $db->query('UPDATE users SET lastprojectid='.$_GET['project'].' WHERE id='.$_SESSION['userid'].';');
	  }
  }

  if ($_SESSION['projectid']==0) 
   if ($result=$db->query('SELECT projectid FROM userroles WHERE  userid='.$_SESSION['userid'].';'))
    if ($row=$result->fetch_assoc())
    $_SESSION['projectid']=$row['projectid'];
  
?>
<!DOCTYPE html>
<html>
<title>MOSIM task list editor - projects</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/projects.css">
<script>

 function editProjectName(obj,projectid) {
	if (obj.hasAttribute('data-saved'))
	return;
	obj.dataset.saved=obj.nextSibling.innerHTML;
	obj.nextSibling.innerHTML='<input style="display: block;" type="text" />'+
	'<br><span class="w3-tag w3-round button" onclick="saveProjectName(this,'+projectid+');">Save changes</span>'+
	'<span class="w3-tag w3-round button" style="margin-left:20px;" onclick="cancelProjectDescription(this);">Cancel</span>';
	obj.nextSibling.firstChild.value=obj.dataset.saved;
	obj.nextSibling.firstChild.focus();
 }

 function editProjectDescription(obj,projectid) {
	if (obj.hasAttribute('data-saved'))
	return;
	obj.dataset.saved=obj.nextSibling.innerHTML;
	obj.nextSibling.innerHTML='<textarea style="width:100%; height:300px;"></textarea>'+
	'<br><span class="w3-tag w3-round button" onclick="saveProjectDescription(this,'+projectid+');">Save changes</span>'+
	'<span class="w3-tag w3-round button" style="margin-left:20px;" onclick="cancelProjectDescription(this);">Cancel</span>';
	obj.nextSibling.firstChild.innerHTML=obj.dataset.saved;
	obj.nextSibling.firstChild.focus();
	 
 }
 
 function saveProjectName(obj,projectid) {
	$.post("update.php",
    {
      action: 'editProjectName',
	  projectid: projectid,
	  name: obj.parentNode.firstChild.value
    },
    function(data, status){ 	
	 if (data.indexOf('<result>OK</result>')>-1)
	 obj.parentNode.innerHTML=obj.parentNode.firstChild.value;
	 else
     cancelProjectDescription(obj);
	});	 
	
 }
 
 function saveProjectDescription(obj,projectid) {
	$.post("update.php",
    {
      action: 'editProjectDescription',
	  projectid: projectid,
	  description: obj.parentNode.firstChild.value
    },
    function(data, status){ 	
	 if (data.indexOf('<result>OK</result>')>-1)
	 obj.parentNode.innerHTML=obj.parentNode.firstChild.value;
	 else
     cancelProjectDescription(obj);
	});	 
	
 }
 
 function cancelProjectDescription(obj) {
	 var data=obj.parentNode.previousSibling.dataset.saved;
	 obj.parentNode.previousSibling.removeAttribute('data-saved');
	 obj.parentNode.innerHTML=data;
 }
 
 function cloneProject(newName,outputBox) {
	 var outBox=document.getElementById(outputBox);
	 var cloneList = {tools:document.getElementById('param_tools').checked,
					toolCats:document.getElementById('param_toolscats').checked,
					parts:document.getElementById('param_parts').checked,
					partCats:document.getElementById('param_partscats').checked,
					avatars:document.getElementById('param_avatars').checked,
					avatarsParams:document.getElementById('param_avatarparam').checked,
					stations:document.getElementById('param_stations').checked,
					tasks:document.getElementById('param_tasks').checked,
					users:document.getElementById('param_users').checked};
	 
	$.post("update.php",
	{
	  action: 'duplicateProject',
	  id: document.getElementById("projects").value,
	  cloneList: cloneList,
	  cloneName: document.getElementById(newName).value
	},
	function(data, status){
		outBox.innerHTML=data;
	});	 
	 
 }
 
 function backupProject(desc,outputBox) {
	 
 }
 
 function projectActions(group, obj) {
	 //obtaining container boxes
	 projectName=document.getElementById("projects");
	 projectName=projectName.children[projectName.selectedIndex].text; //TODO: name escaping
	 params=document.getElementById(group+"params");
	 group=document.getElementById(group);
	 //modifying selection of the item
	 obj.className="chosen";
	 //unselecting all other options
	  for (i=0; i<group.children.length; i++)
		  if (group.children[i]!=obj.parentNode)
			  group.children[i].firstChild.className="";
	//common actions
	 actions="<br><input type=\"checkbox\" id=\"param_tools\" checked /><label for=\"param_tools\">Tools</label>"+
			 "<br><input type=\"checkbox\" id=\"param_toolscats\" checked /><label for=\"param_toolscats\">Tools' categories</label>"+
	         "<br><input type=\"checkbox\" id=\"param_parts\" checked /><label for=\"param_parts\">Parts</label>"+
			 "<br><input type=\"checkbox\" id=\"param_partscats\" checked /><label for=\"param_partscats\">Parts' categories</label>"+
			 "<br><input type=\"checkbox\" id=\"param_stations\" checked /><label for=\"param_stations\">Stations</label>"+
			 "<br><input type=\"checkbox\" id=\"param_avatars\" checked /><label for=\"param_avatars\">Avatars</label>"+
			 "<br><input type=\"checkbox\" id=\"param_avatarparam\" checked /><label for=\"param_avatarparam\">Avatar paramerers</label>"+
			 "<br><input type=\"checkbox\" id=\"param_tasks\" checked /><label for=\"param_tasks\">Task lists (require stations and parts)</label>"+
			 "<br><input type=\"checkbox\" id=\"param_users\" checked /><label for=\"param_users\">User lists</label>"; //TODO: add user password export options, encrypted project package
	//displaying parameters relevant to specific actions
	 if (obj.dataset.action=="clone")
	 params.innerHTML="Clone project name: <input id=\"clone_name\" type=\"text\" value=\""+projectName+" - clone\"/><p>Content to clone:"+actions+"</p><p>"+
	 "<span class=\"w3-tag w3-round button\" onclick=\"cloneProject('clone_name','"+params.id+"');\">Create duplicate</span></p>";
	 else
	 if (obj.dataset.action=="backup")
	  params.innerHTML="Backup remarks: <textarea id=\"backup_desc\" style=\"width:100%;\"></textarea><p>Content to backup:"+actions+"</p><p>"+
	 "<span class=\"w3-tag w3-round button\" onclick=\"backupProject('backup_desc','"+params.id+"');\">Create backup</span></p>";
	 else
	 if (obj.dataset.action=="export")
	  params.innerHTML="Content to export:"+actions+"</p><p>"+
	 "<span class=\"w3-tag w3-round button\" onclick=\"backupProject('backup_desc','"+params.id+"');\">Export</span></p>";
	 else
		 params.innerHTML=obj.innerHTML+" action is not yet implemented.";
 }
 
</script>

<?php
 $stationid=1;
 if (isset($_GET['station']))
  if (ctype_digit($_GET['station']))
  $stationid=$_GET['station'];
 
 include('functions.php');
 
 function loadProjects()
 {
  global $db, $stationid;
	 
   if ($result=$db->query('SELECT p.id, p.name FROM projects p, userroles ur WHERE ur.userid='.$_SESSION['userid'].' and ur.projectid=p.id order by p.name;'))
   while ($row=$result->fetch_assoc())
   {
	echo '<option '.($row['id']==$_SESSION['projectid']?'selected="" ':'').'value="'.$row['id'].'">'.$row['name'].'</option>';	 
   }
 }
 
 function loadProjectDetails()
 {
  global $db;	 
  $sql='SELECT p.id, p.name, p.description, p.date FROM projects p, userroles ur WHERE ur.userid='.$_SESSION['userid'].' and ur.projectid='.$_SESSION['projectid'].' and ur.projectid=p.id';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
    {
	echo '<p><span style="cursor:pointer;" onclick="editProjectName(this,'.$_SESSION['projectid'].');">Name:</span><span style="margin-left:20px; font-weight:bold;">'.htmlentities($row['name']).'</span></p>';         
	 echo '<p style="cursor:pointer;" onclick="editProjectDescription(this,'.$_SESSION['projectid'].');">Description:	</p><p style="white-space: pre;">'.htmlentities($row['description']).'</p>';         
	 echo '<p>Users:</p><table class="users" data-project="'.$_SESSION['projectid'].'"><tr><th colspan="2">Name</th><th>E-mail</th><th>Role</th></tr>';
	 $sql='SELECT u.id, u.name, u.email, ur.role FROM `userroles` ur, `users` u WHERE u.id=ur.userid and ur.projectid='.$_SESSION['projectid'].' and u.enabled=1';                                                           
	  if ($result=$db->query($sql))
	   while ($row=$result->fetch_assoc())
       echo '<tr data-project="'.$_SESSION['projectid'].'" data-id="'.$row['id'].'"><td><span class="w3-tag w3-round button" onclick="removeUserFromProject(this);">X</span></td><td>'.                       htmlentities($row['name']).'</td><td>'.htmlentities($row['email']).'</td><td onclick="editUserRole(this);">'.htmlentities($row['role']).'</td></tr>';
     echo '</table>';
	}		
 }
 
 function insertStations($selectCurrent=true, $projectid=1)
 {
  global $db, $stationid;
  
   if ($result=$db->query('SELECT id, name, sortorder FROM stations WHERE projectid='.$projectid.' ORDER BY sortorder'))
	while ($row=$result->fetch_assoc())
	echo '<option '.($selectCurrent && ($row['id']==$stationid)?'selected="" ':'').'value="'.$row['id'].'">'.$row['name'].'</option>';	
 }	 
  /*
 function insertPartTypes()
 {
  global $db;
   if ($result=$db->query('SELECT id, name FROM partcat ORDER BY sortorder, name ASC'))
	while ($row=$result->fetch_assoc())
	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
 } 
 */
 function insertToolTypes()
 {
  global $db;
   if ($result=$db->query('SELECT id, name, sortorder FROM toolcat WHERE language="mosim" order by sortorder, id'))
	while ($row=$result->fetch_assoc())
	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
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
          <hr>

          <p id="newproject" class="w3-large"><b><i class="fa fa-gear fa-fw w3-margin-right iconback"></i>New project</b></p>
		  <p id="new_projecterror"></p>
          <p>Name: <input type="text" id="new_projectname"/></p>
		  <p>Description: <textarea id="new_description" style="width:100%"></textarea></p>
          <p style="text-align: center"><span class="w3-tag w3-round button" onclick="addProject();">Create</span></p>
          <br>
        </div>
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
    
      <div id="tasklist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-gear fa-fw w3-margin-right w3-xxlarge iconback"></i><span class="fa fa-angle-left w3-margin-right pointer" onclick="prevClick();"></span><select onchange="changeProject(this);" id="projects"><?php loadProjects(); ?></select><span class="fa fa-angle-right w3-margin-left pointer" onclick="nextClick();"></span></h2>
		<?php
		loadProjectDetails();
		?>
		<p><input type="text" class="searchbox hidden" onKeyPress="searchUsers(this,event,'searchUsers');" /><span class="w3-tag w3-round button" onclick="addUser('searchUsers');">Add user</span>
	    <div id="searchUsers"></div>
      </div>
	  
	  <div id="tasklist" class="w3-container w3-card w3-white w3-margin-bottom">
	  <h2><i class="fa fa-gear fa-fw w3-margin-right w3-xxlarge iconback"></i>Project actions</h2>
	  <ul id="projectactions">
	  <li><span onclick="projectActions('projectactions', this);" data-action="clone">Duplicate</span>
	  <li><span onclick="projectActions('projectactions', this);" data-action="backup">Create backup</span>
	  <li><span onclick="projectActions('projectactions', this);" data-action="restore">Restore from backup</span>
	  <li><span onclick="projectActions('projectactions', this);" data-action="export">Export</span>
	  <li><span onclick="projectActions('projectactions', this);" data-action="import">Import</span>
	  </ul>
	  <div id="projectactionsparams"></div>
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
</body>
</html>
