<?php
 $f=file_get_contents("Version.txt");
 $f=explode("\n",$f);
 $version="V".trim($f[0]);
 //database access
 $db=false;
 
 function mosimInstall($dbname){
	global $db;
	$sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="'.$dbname.'"';
	if ($result=$db->query($sql))
	 if ($row=$result->fetch_assoc())
		if (!$row['exists'])
		{ //needs instalation
		 $_SESSION['setuplog']='<li>Mosim task editor server needs database installation</li>';
		 $sql='CREATE USER \'mosim\'@localhost identified by "mosim2020"';
		 $db->query($sql);
		 $dbinitsql=file_get_contents('config/dbtemplate.sql');
		 $db->multi_query($dbinitsql);
		 while ($db->next_result());
		 
		 $sqlfiles=array_values(array_diff(scandir('config/'),array('..', '.','dbtemplate.sql')));
		 
		 for ($i=0; $i<count($sqlfiles); $i++)
		  if (!is_dir('config/'.$sqlfiles[$i]) && (substr($sqlfiles[$i],-4)=='.sql'))
		  {
			$dbinitsql=file_get_contents('config/'.$sqlfiles[$i]);
			$db->multi_query($dbinitsql);
			while ($db->next_result());
		  }
		 $sql='GRANT SELECT, INSERT, DELETE, UPDATE ON '.$dbname.'.* TO \'mosim\'@\'localhost\';';
		 $db->query($sql);
		 $_SESSION['setuplog'].='<li>Database installation has been completed.</li>';
		 unset($_SESSION['dblogin']);
		 unset($_SESSION['dbpass']);
		 global $loginerrorcode;
				$loginerrorcode=0;
		 //$loginerrorcode = -3; //next step - create admin user
		$_SESSION['setup']=1.1;
		}
 }
 
 function connectDB() {
  global $db, $serverdatabase;
  $serverdatabase='mosim';
  $dbuser='mosim'; //default credentials - never use on production server, this is just for testing
  $dbpass='mosim2020';
  if (isset($_SESSION['dblogin']) && isset($_SESSION['dbpass']))
  {
	$dbuser=$_SESSION['dblogin'];
	$dbpass=$_SESSION['dbpass'];
  }
  else
	  if (isset($_SERVER['DATABASE']) && isset($_SERVER['DATABASE_USER']) && isset($_SERVER['DATABASE_PASS']))
	  {
		$serverdatabase=$_SERVER['DATABASE'];
		$dbuser=$_SERVER['DATABASE_USER'];
		$dbpass=$_SERVER['DATABASE_PASS'];
	  }

   $db = @mysqli_connect("localhost", $dbuser, $dbpass, $serverdatabase);
	if ($db==null)
	{
		if (isset($_POST['action']) && isset($_POST['login']) && isset($_POST['password']) &&
		    $_POST['action']=='dblogin')
			$db = @mysqli_connect("localhost", $_POST['login'], $_POST['password']);
			if ($db==null)
			{
			$_SESSION['setup']=1;
			global $loginerror, $loginerrorcode;
			$loginerrorcode=-1; //first login error
			$loginerror="Cannot connect to MySQL.";
			if (!isset($_SESSION['loginattempt']))
			$_SESSION['loginattempt']=1;
				if (isset($_POST['trial']))
				{
				$loginerrorcode=-2; //repeated login error
				$_SESSION['loginattempt']=$_SESSION['loginattempt']+1;
				$loginerror='Attempt '.$_SESSION['loginattempt'].": Cannot connect to MySQL.";
				}
			}
			else
				if ($db->connect_errno)
				echo "<p>Failed to connect to MySQL: " . mysqli_connect_error();
				else
				{
				$db->set_charset("utf8"); 
				mosimInstall($serverdatabase);
				$_SESSION['dblogin']=$_POST['login'];
				$_SESSION['dbpass']=$_POST['password'];
				$_POST['action']=""; //reset action so that new user of mosim would not be the same as database root user
				return true;
				}
	}
	else
     if ($db->connect_errno)
     echo "<p>Failed to connect to MySQL: " . mysqli_connect_error();
     else
     {
      $db->set_charset("utf8"); 
      return true;
     }
  return false;
 }
?>