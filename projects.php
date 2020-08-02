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
<link rel="stylesheet" href="w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<link rel="stylesheet" href="styles.css">
<script>

 function editProjectName(obj,projectid) {
	if (obj.hasAttribute('data-saved'))
	return;
	obj.dataset.saved=obj.nextSibling.innerHTML;
	obj.nextSibling.innerHTML='<input style="display: block;" type="text" />'+
	'<br><span class="w3-tag w3-teal w3-round button" onclick="saveProjectName(this,'+projectid+');">Save changes</span>'+
	'<span class="w3-tag w3-teal w3-round button" style="margin-left:20px;" onclick="cancelProjectDescription(this);">Cancel</span>';
	obj.nextSibling.firstChild.value=obj.dataset.saved;
	obj.nextSibling.firstChild.focus();
 }

 function editProjectDescription(obj,projectid) {
	if (obj.hasAttribute('data-saved'))
	return;
	obj.dataset.saved=obj.nextSibling.innerHTML;
	obj.nextSibling.innerHTML='<textarea style="width:100%; height:300px;"></textarea>'+
	'<br><span class="w3-tag w3-teal w3-round button" onclick="saveProjectDescription(this,'+projectid+');">Save changes</span>'+
	'<span class="w3-tag w3-teal w3-round button" style="margin-left:20px;" onclick="cancelProjectDescription(this);">Cancel</span>';
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
</script>

<style>
 input[type="text"] {width:100%}
 p#new_projecterror {color: red; display:none;}
 table.users {
	width:100%;	
	margin-bottom:20px;
 }
 table.users th {
    border: 1px solid #009688;
    background-color: #009688;
	color: white;
	padding: 2px;
 }
 table.users td {
    border: 1px solid gainsboro;
	background-color: gainsboro;
	padding: 5px;
	cursor: pointer;
 }
 table.users td > select {
    width: calc(100% - 85px);
 }
 table.users td > span {
	width: 40px;  
	padding: 5px;
    margin-left: 2px;
 }
 table.users td:first-child {
    width: 55px;	 
 }
 table.users.results td:first-child {
   width: auto;	 
 }
 input.hidden {display: none; width: 0px !important;}
 input.searchbox {transition: width 1s linear; width: 50%; margin-right:5px;}
 
 table.results tr {
   cursor: pointer;	 
 }
 
 table.results tr td.error {
   color: red;	 
 }
</style>


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
       echo '<tr data-project="'.$_SESSION['projectid'].'" data-id="'.$row['id'].'"><td><span class="w3-tag w3-teal w3-round button" onclick="removeUserFromProject(this);">X</span></td><td>'.                       htmlentities($row['name']).'</td><td>'.htmlentities($row['email']).'</td><td onclick="editUserRole(this);">'.htmlentities($row['role']).'</td></tr>';
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
        <div class="w3-container">
		  <?php include('menu.php'); ?> 
          <hr>

          <p id="newproject" class="w3-large"><b><i class="fa fa-gear fa-fw w3-margin-right w3-text-teal"></i>New project</b></p>
		  <p id="new_projecterror"></p>
          <p>Name: <input type="text" id="new_projectname"/></p>
		  <p>Description: <textarea id="new_description" style="width:100%"></textarea></p>
          <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="addProject();">Create</span></p>
          <br>
        </div>
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
    
      <div id="tasklist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-gear fa-fw w3-margin-right w3-xxlarge w3-text-teal"></i><span class="fa fa-angle-left w3-margin-right pointer" onclick="prevClick();"></span><select onchange="changeProject(this);" id="stations"><?php loadProjects(); ?></select><span class="fa fa-angle-right w3-margin-left pointer" onclick="nextClick();"></span></h2>
		<?php
		loadProjectDetails();
		?>
		<p><input type="text" class="searchbox hidden" onKeyPress="searchUsers(this,event,'searchUsers');" /><span class="w3-tag w3-teal w3-round button" onclick="addUser('searchUsers');">Add user</span>
	    <div id="searchUsers"></div>
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
</body>
</html>
