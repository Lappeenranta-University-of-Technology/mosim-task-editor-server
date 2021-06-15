<?php
 session_start();
 
 include('db.php');

 $dbokay=connectDB();
 
 function logout()
 {
	 unset($_SESSION['userid']);
	 unset($_SESSION['projectid']);
	 unset($_SESSION['role']);
	 unset($_SESSION['showTaskID']);
	 unset($_SESSION['dblogin']);
	 unset($_SESSION['dbpass']);
	 unset($_SESSION['loginattempt']);
 }
 
  if (isset($_GET['action']))
  {
   if (($_GET['action']=='logout') || ($_GET['action']=='finishsetup') || ($_GET['action']=='setupcleanup'))
	 logout();
  }
  
  if (isset($_SESSION['setup']) && ($_SESSION['setup']==3) && isset($_POST['action']))
  {
	if (($_POST['action']=='finishsetup') || ($_POST['action']=='setupcleanup'))
    {
	 unset($_SESSION['setup']); 
	 unset($_SESSION['setuplog']);
	 logout();
    }
	if ($_POST['action']=='setupcleanup')
	{
		$sqlfiles=array_values(array_diff(scandir('config/'),array('..', '.')));
		for ($i=0; $i<count($sqlfiles); $i++)
		  if (!is_dir('config/'.$sqlfiles[$i]) && (substr($sqlfiles[$i],-4)=='.sql'))
			  unlink('config/'.$sqlfiles[$i]);
	}
   }
  
 function WrongLogin($msg='')
 {
	global $loginerror;
	if (isset($_SESSION['loginattempt']))
	$_SESSION['loginattempt']=$_SESSION['loginattempt']+1;
	else
	$_SESSION['loginattempt']=1;
	$loginerror=($msg!=''?$msg:'Attempt '.$_SESSION['loginattempt'].': Wrong login and/or password.'); 
 }
 
 function CreateAdminUser()
 {
	 global $loginerror, $db;
	 if (isset($_POST['action']) && ($_POST['action']=='createadmin') && isset($_POST['login']) && isset($_POST['password']) && isset($_POST['rpassword']))
	 {
		 if ($_POST['password']!=$_POST['rpassword'])
		 {
		  WrongLogin('Paswords do not match');
		  return false;
		 }
			if ($result=$db->query('SELECT count(*) as howmany FROM users;'))
			{
				if (($row=$result->fetch_assoc()) && ($row['howmany']==0))
				{
					$sql='INSERT INTO `users`(`id`,`name`, `email`, `pass`, `lastprojectid`, `enabled`) VALUES (1,\''.$db->real_escape_string(trim($_POST['login'])).'\',\''.
					$db->real_escape_string(trim($_POST['login'])).'\',\''.password_hash($_POST['password'],PASSWORD_DEFAULT).'\',0,1);';
					$sqlrules='INSERT INTO `adminroles` (`userid`, `role`) VALUES (1, \'admin\'), (1, \'tool editor\'), (1, \'user manager\'), (1, \'MMU Library manager\');';
					$db->query($sql);
					$db->query($sqlrules);
					$_SESSION['setuplog']='<li>Admin account has been created.';
					$_SESSION['setup']=3;
					return true;
				}
			}
	 }
	 
	 WrongLogin();
	 return false;
 }
 
  if (!(isset($_SESSION['userid']) && isset($_SESSION['projectid'])) && $dbokay)
  {
	if (isset($_POST['action']) && isset($_POST['login']) && isset($_POST['password']))
	{
	 if (($_POST['action']=='createadmin') && (isset($_SESSION['setup'])) && $_SESSION['setup']==2)
	 CreateAdminUser();
		 
     if ($_POST['action']=='login') 
	  if ($result=$db->query('SELECT u.id, u.pass, u.lastprojectid, u.enabled, ur.role FROM users u LEFT JOIN userroles ur ON (ur.userid=u.id and ur.projectid=u.lastprojectid) WHERE email=\''.$db->real_escape_string(trim($_POST['login'])).'\';'))
	  {
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
			 if ($result=$db->query('SELECT projectid FROM userroles WHERE userid='.$_SESSION['userid'].';'))
			  if ($row=$result->fetch_assoc())
				$_SESSION['projectid']=$row['projectid'];
			}
			else
			$loginerror='Your account is disabled.';
		}
		else //if there are no users in the database (new installation), save the first user that tries to log in and make him an admin
			CreateAdminUser();
	   }
	   else
		CreateAdminUser();
	  }
	  else
		CreateAdminUser();
	}
  }
  
  if (!(isset($_SESSION['userid']) && isset($_SESSION['projectid'])))
  {
	if (isset($loginerrorcode) || isset($_SESSION['setup']))
	include('config/setup.php');
	else
	include('login.php');
   exit();
  } 
?>