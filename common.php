<?php
 session_start();
 
 include('db.php');

 connectDB();
 
  if (isset($_GET['action']))
   if ($_GET['action']=='logout')
   {
	 unset($_SESSION['userid']);
	 unset($_SESSION['projectid']);
	 unset($_SESSION['role']);
	 unset($_SESSION['showTaskID']);
   }	   
 
  if (!(isset($_SESSION['userid']) && isset($_SESSION['projectid'])))
  {
	if (isset($_POST['action']) && isset($_POST['login']) && isset($_POST['password']))
     if ($_POST['action']=='login') 
	  if ($result=$db->query('SELECT u.id, u.pass, u.lastprojectid, u.enabled, ur.role FROM users u LEFT JOIN userroles ur ON (ur.userid=u.id and ur.projectid=u.lastprojectid) WHERE email=\''.$db->real_escape_string($_POST['login']).'\';'))
	   if ($row=$result->fetch_assoc())
	   {
		if (password_verify($_POST['password'],$row['pass'])==true) 
		{
		if ($row['enabled']==1)
		{
		 $_SESSION['userid']=$row['id'];
         $_SESSION['projectid']=$row['lastprojectid'];
		 $_SESSION['role']=$row['role'];
		 $_SESSION['showTaskID']=false;
		 
		 
		   //if user logs in the first time and no project is selected
         if ($_SESSION['projectid']==0) 
          if ($result=$db->query('SELECT projectid FROM userroles WHERE  userid='.$_SESSION['userid'].';'))
	       if ($row=$result->fetch_assoc())
	       $_SESSION['projectid']=$row['projectid'];
		}
		else
			$loginerror='Your account is disabled.';
		}
		else
			$loginerror='Wrong login and/or password.';
	   }		 
  }
  
  if (!(isset($_SESSION['userid']) && isset($_SESSION['projectid'])))
  {
   include('login.php');
   exit();	
  } 
?>