<?php
 include('common.php');
  //is this below needed? - direct copy from projects.php
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
<title>MOSIM task list editor settings</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/settings.css">

<script>
 <?php echo "\r\nroles=".json_encode(loadAllowedRoles()).";\r\n"; ?>

 function saveUserData() {
	var username=document.getElementById('username').value;
	var useremail=document.getElementById('useremail').value;
	var userpasscurrent=document.getElementById('userpasscurent').value;
	var userpassnew=document.getElementById('userpass').value;
	var userpassre=document.getElementById('userpassre').value;
	var responsebox=document.getElementById('responseMessage');
	$.post("update.php",
    {
      action: "updateUser",
	  name: username,
	  email: useremail,
	  passcurrent: userpasscurrent,
	  passnew: userpassnew
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		responsebox.className='error';
		else
		responsebox.className='';
     responsebox.innerHTML=getTagValue(data,'message');
	});  
 }
 
 function createUser() {
	var username=document.getElementById('newusername');
	var useremail=document.getElementById('newuseremail');
	var userpassnew=document.getElementById('newuserpass');
	var userpassrenew=document.getElementById('newuserpassre');
	var responsebox=document.getElementById('responseMessage2');
	$.post("update.php",
    {
      action: "addUser",
	  name: username.value,
	  email: useremail.value,
	  passnew: userpassnew.value
    },
    function(data, status){ 	
     if (getTagValue(data,'result')=='OK')
	 {
		responsebox.className='';
		responsebox.innerHTML='User '+username.value+' has been successfully created.';
		username.value='';
		useremail.value='';
		userpassnew.value='';
		userpassrenew.value='';
	 }
	 else
	 {
		responsebox.className='error';
		responsebox.innerHTML=getTagValue(data,'message');
	 }
	});  
 }
</script>

<?php
 $stationid=1;
 if (isset($_GET['station']))
  if (ctype_digit($_GET['station']))
  $stationid=$_GET['station'];
 
 include('functions.php');
 
 function loadAllowedRoles()
 {
  global $db, $serverdatabase;
  $sql='SELECT `COLUMN_TYPE` as roles FROM `information_schema`.`COLUMNS` '.
       'WHERE `TABLE_SCHEMA` = \''.$serverdatabase.'\' '.
       'AND `TABLE_NAME`   = \'adminroles\' '.
       'AND `COLUMN_NAME`  = \'role\'; ';
	   if ($result=$db->query($sql))
		if ($row=$result->fetch_assoc())
	    {
		 $roles=substr($row['roles'],5,-2);
		 return explode("','",$roles);
	    }
  return array();
 }
 
 function loadAccountDetails()
 {
  global $db;
	 
   if ($result=$db->query('SELECT id, name, email FROM users WHERE id='.$_SESSION['userid'].';'))
   if ($row=$result->fetch_assoc())
   {
	echo '<p>Name: <input type="text" id="username" value="'.htmlentities($row['name']).'" /></p>'.
		'<p>E-mail: <input type="text" id="useremail" value="'.htmlentities($row['email']).'" /></p>'.
		'<p>Current password: <input type="password" id="userpasscurent" /></p>'.
		'<p>New password: <input type="password" id="userpass" /></p>'.
		'<p>Repeat new password: <input type="password" id="userpassre" /></p>';
   }
 }
 
 function loadProjectDetails()
 {
  global $db;	 
  $sql='SELECT p.id, p.name, p.description, p.date FROM projects p, userroles ur WHERE ur.userid='.$_SESSION['userid'].' and ur.projectid='.$_SESSION['projectid'].' and ur.projectid=p.id';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
    {
	 echo '<p>Description:	</p><p>'.htmlentities($row['description']).'</p>';
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
 
 function insertUserList()
 {
	global $db;                    
	echo '<table class="users"><tr><th colspan="2">Name</th><th>E-mail</th><th>Role</th></tr>';
	 $sql='SELECT u.id, u.name, u.email, u.enabled, ifnull(GROUP_CONCAT(ar.role SEPARATOR \', \'),\'User\') as role, ifnull(GROUP_CONCAT(ar.role SEPARATOR \',\'),\'\') as roledata FROM `users` u LEFT JOIN `adminroles` ar ON (u.id=ar.userid) GROUP BY u.id ORDER BY enabled DESC, name ASC';                                                           
	  if ($result=$db->query($sql))
	   while ($row=$result->fetch_assoc())
       echo '<tr data-enabled="'.$row['enabled'].'" data-id="'.$row['id'].'"><td><span class="w3-tag w3-round button" onclick="deleteUser(this);">X</span></td><td>'.                       htmlentities($row['name']).'</td><td>'.htmlentities($row['email']).'</td><td data-data="'.$row['roledata'].'" onclick="editUserAdminRole(this);">'.htmlentities($row['role']).'</td></tr>';
     echo '</table>';
 }
 
 function insertGlobalAvatarParams()
 {
	 global $db;
	 $sql='SELECT apt.`id`, apt.`projectid`, apt.`name`, apt.`optional`, apt.`type`, apt.`language`, apt.`sortorder`, GROUP_CONCAT(apv.value) as values FROM `avatar_param_types` apt WHERE apt.projectid=0 and apt.language=\'mosim\' LEFT JOIN `avatar_param_values` apv ON (apv.typeid=apt.id and apv.language=apv.language) GROUP BY apt.id ORDER BY sortorder, id';
	 if ($result=$db->query($sql))
	 {
		 echo '<table class="avatarparams"><tr><th colspan="2">Name</th><th>Settings</th><th>Values</th></tr>';
		 while ($row=$result->fetch_assoc())
		 echo '<tr><td>'.htmlentities($row['name']).'</td><td>'.$row['optional'].'</td><td>'.$row['values'].'</td></tr>';	 
		 echo '</table>';
	 }
	 
 }
 
 function insertLocalAvatarParams()
 {
	 global $db;
 }
 
 function isUserManager()
 {
	global $db;
	if ($result=$db->query('SELECT count(*) as ile FROM `adminroles` WHERE userid='.$_SESSION['userid'].' and role=\'user manager\';'))
		 if ($row=$result->fetch_assoc())
	      return ($row['ile']>0);
	return false;
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
		<?php
		 $usermanager=isUserManager();
		 if ($usermanager)
		 {
			 ob_start();
		?>
		<p class="w3-large"><i class="fa fa-user fa-fw w3-margin-right iconback"></i>Create user</p>
		<p>Name: <input type="text" id="newusername" value="" /></p>
		<p>E-mail: <input type="text" id="newuseremail" value="" /></p>
		<p>Password: <input type="password" id="newuserpass" /></p>
		<p>Repeat password: <input type="password" id="newuserpassre" /></p>
		<p><span class="w3-tag w3-round button" onclick="createUser();">Add user</span></p>
	    <div id="responseMessage2"></div>
		<?php echo ob_get_clean(); 
		 }
		 ?>
		
        </div>
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
    
      <div id="tasklist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-user fa-fw w3-margin-right w3-xxlarge iconback"></i>Account data</h2>
		<?php
		loadAccountDetails();
		?>
		<p><span class="w3-tag w3-round button" onclick="saveUserData();">Save changes</span></p>
	    <div id="responseMessage"></div>
      </div>
	  
	  <?php 
	   if ($usermanager)
	   {
		  ob_start();
	  ?>
	  <div id="tasklist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-user fa-fw w3-margin-right w3-xxlarge iconback"></i>User management</h2>
		<?php insertUserList(); ?>
	    <div id="responseMessage3"></div>
      </div>
	  
	  <?php
	      $result=ob_get_clean();
	      echo $result;
	     }
	  ?>
	  
	  <div id="avatarparams" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-user fa-fw w3-margin-right w3-xxlarge iconback"></i>Avatar parameters</h2>
		 <h3>Global</h3>
		<?php insertGlobalAvatarParams(); ?>
		 <h3>Local (current project)</h3>
		<?php insertLocalAvatarParams(); ?>
	    <div id="responseMessage4"></div>
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
