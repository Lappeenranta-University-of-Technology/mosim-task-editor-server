<?php
 $f=file_get_contents("Version.txt");
 $f=explode("\n",$f);
 $version="V".trim($f[0]);
 //database access
 $db=false;
 
 function connectDB() {
  global $db, $serverdatabase;
  $serverdatabase='mosim';
   if (isset($_SERVER['DATABASE']) && isset($_SERVER['DATABASE_USER']) && isset($_SERVER['DATABASE_PASS']))
   {
   $db = mysqli_connect("localhost", $_SERVER['DATABASE_USER'], $_SERVER['DATABASE_PASS'], $_SERVER['DATABASE']);	   
   $serverdatabase=$_SERVER['DATABASE'];
   }
   else
   $db = mysqli_connect("localhost", $serverdatabase, "mosim2020", "mosim");
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