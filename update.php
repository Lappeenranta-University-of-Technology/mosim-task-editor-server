<?php
 session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>MOSIM task list editor</title>
<meta charset="UTF-8">
</head>
<body>
<?php
 include('functions.php');
 include('db.php'); 
 
 function isUsersProject($projectid) {
  global $db;
   if ($result=$db->query('SELECT count(*) as ile FROM userroles ur WHERE ur.projectid='.$projectid.' and ur.userid='.$_SESSION['userid'].';'))
	 if ($row=$result->fetch_assoc())
	 return ($row['ile']==1);
  return false;		  
 }
 
 //user management
 function addUser($name,$email,$pass) {
  global $db;
  if (!($result=$db->query('SELECT count(*) as ile FROM adminroles WHERE role=\'user manager\' and userid='.$_SESSION['userid'].';')))
  return '<result>Error</result><message>Database error while checking user privileges (1).</message>';
  if (!($row=$result->fetch_assoc()))
  return '<result>Error</result><message>Database error while checking user privileges (1).</message>';
  if ($row['ile']==0) 
  return '<result>Error</result><message>You do not have privileges to create new user accounts.</message>';
	if (trim($pass)=='')
	return '<result>Error</result><message>New password cannot be empty and cannot contain only white spaces.</message>';
	if (strlen($pass)<8)
	return '<result>Error</result><message>Operation aborted. Password needs to be at least 8 characters long.</message>';
	if (substr_count($email,'@')!=1)
	return '<result>Error</result><message>Invalid e-mail address</message>';
  $emailinput=trim($email);
  $name='\''.$db->real_escape_string(trim($name)).'\'';
  $email='\''.$db->real_escape_string($emailinput).'\'';
  $pass='\''.password_hash($pass,PASSWORD_DEFAULT).'\'';
  $sql='SELECT count(*) as ile FROM users WHERE email='.$email.';';                
   if (!($result=$db->query($sql)))
   return '<result>Error</result><message>Database access error occured while checking account existance (1).</message>';
   if (!($row=$result->fetch_assoc()))
   return '<result>Error</result><message>Database access error occured while checking account existance (2).</message>';
   if ($row['ile']>0)
   return '<result>Error</result><message>User account cannot be created. Account associated with the e-mail '.htmlentities($emailinput).' already exists.</message>';
  $sql='INSERT INTO `users`(`name`, `email`, `pass`) VALUES ('.$name.','.$email.','.$pass.');';
   if (!($db->query($sql)))
   return '<result>Error</result><message>Database access error while creating user account (1).</message>';
   if ($db->insert_id==0)
   return '<result>Error</result><message>Database access error while creating user account (2).</message>';
  return '<result>OK</result><message>User account for '.htmlentities($emailinput).' has been successfully created.</message>';
 }
 
 function updateUser($name,$email,$passold,$passnew) {
  global $db;
	if (trim($name)=='')
	return '<result>Error</result><message>User name cannot be empty and cannot contain only white spaces.</message>';
	if (trim($email)=='')
	return '<result>Error</result><message>E-mail cannot be empty and cannot contain only white spaces.</message>';
	if (substr_count($email,'@')!=1)
	return '<result>Error</result><message>Invalid e-mail address</message>';
	$emailinput=trim($email);  
	$name='\''.$db->real_escape_string(trim($name)).'\'';
	$email='\''.$db->real_escape_string($emailinput).'\'';
	$passneworiginal=$passnew;
	$passnew='\''.password_hash($passnew,PASSWORD_DEFAULT).'\'';
	if (!($result=$db->query('SELECT COUNT(*) as ile FROM users WHERE email='.$email.' and id<>'.$_SESSION['userid'].';')))
	return '<result>Error</result><message>Database access error while checking user data (1).</message>';
	if (!($row=$result->fetch_assoc()))
	return '<result>Error</result><message>Database access error while checking user data (2).</message>';
	if ($row['ile']>0)
	return '<result>Error</result><message>Email '.htmlentities($emailinput).' is already assigned to another account. Information has not been updated.</message>';
  if ($passold=='') //only account information update
  {
	$sql='UPDATE users SET email='.$email.', name='.$name.' WHERE id='.$_SESSION['userid'].' LIMIT 1;';
	if (!($db->query($sql)))
	return '<result>Error</result><message>Database error occured while saving new data. Information has not been updated.</message>';
	return '<result>OK</result><message>Information has been updated.</message>';
  }
  else //password change and possible information update
  {
	if (trim($passneworiginal)=='')
	return '<result>Error</result><message>New password cannot be empty and cannot contain only white spaces.</message>';
	if (strlen($passneworiginal)<8)
	return '<result>Error</result><message>Data update canceled. New password needs to be at least 8 characters long.</message>';
	if (!($result=$db->query('SELECT pass FROM users WHERE id='.$_SESSION['userid'].';')))
	return '<result>Error</result><message>Database access error while checking user data (3).</message>';
	if (!($row=$result->fetch_assoc()))
	return '<result>Error</result><message>Database access error while checking user data (4).</message>';
	if (password_verify($passold,$row['pass'])==false)
	return '<result>Error</result><message>Current user password does not match entry in the database. Information has not been updated.</message>';
	$sql='UPDATE users SET pass='.$passnew.', email='.$email.', name='.$name.' WHERE id='.$_SESSION['userid'].' LIMIT 1;';
	if (!($db->query($sql)))
	return '<result>Error</result><message>Database error occured while saving new data. Information has not been updated.</message>';
	return '<result>OK</result><message>User information, including password, has been updated.</message>';
  }
 }
 
 function deleteUser($id) {
	 global $db;
	 if (!($result=$db->query('SELECT count(*) as ile FROM adminroles WHERE role = \'admin\' and userid='.$id.';')))
	 return '<result>Error</result><message>Database error while checking user privileges (1).</message>';
	 if (!($row=$result->fetch_assoc()))
	 return '<result>Error</result><message>Database error while checking user privileges (2).</message>';
		 
	 if (!($result=$db->query('SELECT count(*) as ile FROM adminroles WHERE role in ('.(($row['ile']==0)?'\'user manager\',':'').'\'admin\') and userid='.$_SESSION['userid'].';')))
	 return '<result>Error</result><message>Database error while checking user privileges (3).</message>';
	 if (!($row=$result->fetch_assoc()))
	 return '<result>Error</result><message>Database error while checking user privileges (4).</message>';
	 if ($row['ile']==0) 
	 return '<result>Error</result><message>You do not have privileges to delete user accounts.</message>';
	 $sql='DELETE FROM users WHERE id='.$id.' LIMIT 1;';
	 if ($db->query($sql))
		 return '<result>DEL-OK</result>';
	 return '<result>ERR</result><message>Operation failed!</message>';
	 //TODO: disable deleting admin account by user manager, only admins should be allowed to delete admins
	 //TODO: if user is the only owner of a project operation should fail
 }
 
 function activateUser($id, $active) {
	 global $db;
	 if (!($result=$db->query('SELECT count(*) as ile FROM adminroles WHERE role in (\'user manager\',\'admin\') and userid='.$_SESSION['userid'].';')))
	 return '<result>Error</result><message>Database error while checking user privileges (1).</message>';
	 if (!($row=$result->fetch_assoc()))
	 return '<result>Error</result><message>Database error while checking user privileges (1).</message>';
	 if ($row['ile']==0) 
	 return '<result>Error</result><message>You do not have privileges to change user accounts.</message>';
	 $sql='UPDATE users SET enabled='.$active.' WHERE id='.$id.' LIMIT 1;';
	 if ($db->query($sql))
		 return '<result>'.($active==0?'DEACT-OK':'EN-OK').'</result>';
	 return '<result>ERR</result><message>Operation failed!</message>';
 }
 //end user management
 
 //worker management
 function addWorker($name, $description, $station, $avatar, $project)
 {//TODO: user privileges check
  global $db;
    if (trim($name)=='')
		return '<result>ERR</result><message>Could not create worker, worker\'s name cannot be empty</message>';
	if (!(ctype_digit($station) && ctype_digit($avatar) && ctype_digit($project)))
		return '<result>ERR</result><message>Could not create worker due to data form error.</message>';
	if (($_SESSION['userid']>0) && isset($_SESSION['projectid']) && ($_SESSION['projectid']==$project))
	{
	 $name="'".$db->real_escape_string(trim($name))."'";
	 $description="'".$db->real_escape_string(trim($description))."'";
	 $sql='INSERT INTO workers (`name`, `description`, `stationid`, `projectid`, `avatarid`) VALUES ('.$name.','.$description.','.$station.','.$project.','.$avatar.');';
	  if ($db->query($sql))
		  return '<result>OK</result>';
	   else
		  return '<result>ERR</result><message>Could not create worker due to database error 1.</message>';
	}
	return '<result>ERR</result><message>Not enough privileges do create worker.</message>';
 }
 
 function editWorker($name, $description, $id, $avatar, $project)
 {//TODO: user privileges check
  global $db;
    if (trim($name)=='')
		return '<result>ERR</result><message>Could not change worker\'s name, worker\'s name cannot be empty</message>';
	if (!(ctype_digit(strval($id)) && ctype_digit(strval($avatar)) && ctype_digit(strval($project))))
		return '<result>ERR</result><message>Could not edit worker due to data form error.</message>';
	if (($_SESSION['userid']>0) && isset($_SESSION['projectid']) && ($_SESSION['projectid']==$project))
	{
	 $name="'".$db->real_escape_string(trim($name))."'";
	 $description="'".$db->real_escape_string(trim($description))."'";
	 $sql='UPDATE workers SET `name`='.$name.', `description`='.$description.', `avatarid`='.$avatar.' WHERE id='.$id.' and projectid='.$project;
	  if ($db->query($sql))
		  return '<result>OK</result>';
	   else
		  return '<result>ERR</result><message>Could not modify worker due to database error 1.</message>';
	}
	return '<result>ERR</result><message>Not enough privileges do create worker.</message>';
 }
 //end worker management
 
 
 function addProject($name,$desc) { //$db->real_escape_string(
  global $db; //add checking if name is used and then modify name by adding number suffix
  $name=$db->real_escape_string(trim($name));
  $desc=$db->real_escape_string(trim($desc));
  $sql='SELECT count(id) as ile FROM projects WHERE name=\''.$name.'\';';
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
    if ($row['ile']==0)
    {
     $sql='INSERT INTO `projects` (`name`, `description`) VALUES '.
       '(\''.$name.'\',\''.$desc.'\');';
     echo '<p>'.$sql;
     $db->query($sql);
     if ($db->insert_id>0)
     {
	  $sql='INSERT INTO userroles (userid,projectid) VALUES ('.$_SESSION['userid'].','.$db->insert_id.');';
	  echo '<p>'.$sql;
	  $db->query($sql);
	  echo '<result>OK</result>';
	  return true;
     }
	}
	else
	{
     echo '<result>Project already exists with the same name</result>';
	 return true;
	}
  return false;	
 }

 function cloneProject($id,$cloneName,$cloneList)
 {
	global $db;
	if (!ctype_digit($id))
	{
		echo '<result>Invalid project id.</result>';
		return false;
	}
	if (trim($cloneName)=='')
	{
		echo '<result>Project name cannot be empty</result>';
		return false;
	}
	if (is_array($cloneList))
	{
		echo '<result>Properties to clone have to be supplied as a list.</result>';
		return false;
	}
	
	if (!isUsersProject($id))
	{
		echo '<result>You are not a member of this project.</result>';
		return false;
	}
	
	if ($_SESSION['projectid']!=$id)
	{
		if (!($result=$db->query('SELECT count(*) as ile FROM userroles WHERE role=\'owner\' and userid='.$_SESSION['userid'].' and projectid='.$id)))
		{
			echo '<result>Database error 1.</result>';
			return false;
		}
		if (!($row=$result->fetch_assoc()))
		{
			echo '<result>Database error 2.</result>';
			return false;
		}
		
		if ($row['ile']==0)
		{
			echo '<result>Only project owner can do that (Err: 1).</result>';
			return false;
		}
	} else
		if ($_SESSION['role']!='owner')
		{
			echo '<result>Only project owner can do that (Err: 2).</result>';
			return false;
		}
	
	$desc='';
	 if ($result=$db->query('SELECT description FROM projects WHERE id='.$id))
		 if ($row=$result->fetch_assoc())
	      $desc=$row['description'];
	 if (!addProject($cloneName,$desc)) //cloning project
		return false;
	 $newId=$db->insert_id;
	 $result=$cloneList;
	 echo '<p>New project id: '.$newId.'</p>';
	 //cloning tools
	 if ($cloneList['tools']) //tools so far are common so there is no need to clone them explicitly
	 {
	 }
	 if ($cloneList['toolCats']) //tools' categories so far are common so there is no need to clone them explicitly
	 {
	 }
	 //cloning parts
	 if ($cloneList['parts'])
	 {
		 $sql='INSERT INTO parts (`projectid`, `name`, `engineid`, `picture`, `cad`, `description`) '.
		 'SELECT '.$newId.', `name`, `engineid`, `picture`, `cad`, `description` FROM `parts` WHERE projectid='.$id.' ORDER BY id;';
		 $result['parts']=($db->query($sql));
		 echo '<p>Parts SQL: '.htmlentities($sql).'</p>';
		 $minid=$db->insert_id;
		 $sql='Set @rownum='.($minid-1).'; CREATE TEMPORARY TABLE part_temp AS (SELECT id as oldid, (@rownum:=@rownum+1) as newid FROM `parts` WHERE projectid='.$id.' ORDER BY id);';
		 echo '<p>Parts temp SQL: '.htmlentities($sql).'</p>';
		 $db->multi_query($sql);
		  while ($db->next_result()) // flush multi_queries
			if (!$db->more_results()) break;
		 /*
		 $minid=$db->insert_id;
		 $sql='SELECT id FROM parts WHERE projectid='.$id.' ORDER by id;';
		 $partMapping=array()
		 if ($result=$db->query($sql))
			 while ($row=$result->fetch_assoc())
				 $partMapping[]=array('oldid'=>$row['id'],'newid'=>$minid++);*/
	 }
	 //cloning part categories
	 if ($cloneList['partCats'])
	 {
		 if ($cloneList['parts'])
		 $sql='INSERT INTO `partcat` (`projectid`, `name`,  `syncwith`, `defaultpart`, `icon`, `sortorder`, `language`) '.
		 'SELECT '.$newId.', `name`, `syncwith`, ifnull(newid,0), `icon`, `sortorder`, `language` FROM `partcat` LEFT JOIN part_temp ON (part_temp.oldid=partcat.defaultpart) WHERE projectid='.$id.' ORDER BY id;';
		 else
		 $sql='INSERT INTO `partcat` (`projectid`, `name`,  `syncwith`, `defaultpart`, `icon`, `sortorder`, `language`) '.
		 'SELECT '.$newId.', `name`, `syncwith`, 0, `icon`, `sortorder`, `language` FROM `partcat` WHERE projectid='.$id.' ORDER BY id;';
		 $result['partCats']=($db->query($sql));
		 echo '<p>Part categories SQL: '.htmlentities($sql).'</p>';
		 
	 }
	 //cloning avatars
	 if ($cloneList['avatars'])
	 {
		$sql='INSERT INTO avatars (`projectid`, `name`, `age`, `height`, `weight`, `gender`, `sortorder`) SELECT '.$newId.', `name`, `age`, `height`, `weight`, `gender`, `sortorder` FROM `avatars` WHERE projectid='.$id.' ORDER BY id;';
		 $result['avatars']=($db->query($sql));
		 echo '<p>Avatars SQL: '.htmlentities($sql).'</p>';
		 $minid=$db->insert_id;
		 $sql='Set @rownum='.($minid-1).'; CREATE TEMPORARY TABLE avatar_temp AS (SELECT id as oldid, (@rownum:=@rownum+1) as newid FROM `avatars` WHERE projectid='.$id.' ORDER BY id);';
		 $db->multi_query($sql);
		  while ($db->next_result()) // flush multi_queries
			if (!$db->more_results()) break;
		  echo '<p>Avatars temp SQL: '.htmlentities($sql).'</p>';
	 }
	 
	 if ($cloneList['stations'])
	 {
		 if ($cloneList['avatars'])
		 $sql='INSERT INTO stations (`parent`, `projectid`, `name`, `mainpart`, `main`, `position`, `avatarid`, `sortorder`, `lastchange`) SELECT s.`parent`, '.$newId.', s.`name`, s.`mainpart`, s.`main`, s.`position`, ifnull(at.newid,0), s.`sortorder`, s.`lastchange` FROM `stations` s LEFT JOIN avatar_temp at ON (s.avatarid=at.oldid) WHERE projectid='.$id.' ORDER BY id;';
		 else
		 $sql='INSERT INTO stations (`parent`, `projectid`, `name`, `mainpart`, `main`, `position`, `avatarid`, `sortorder`, `lastchange`) SELECT s.`parent`, '.$newId.', s.`name`, s.`mainpart`, s.`main`, s.`position`, 0, s.`sortorder`, s.`lastchange` FROM `stations` s WHERE projectid='.$id.' ORDER BY id;';
		 $result['stations']=($db->query($sql));
		 echo '<p>Stations SQL: '.htmlentities($sql).'</p>';
		 $minid=$db->insert_id;
		 $sql='Set @rownum='.($minid-1).'; CREATE TEMPORARY TABLE station_temp AS (SELECT id as oldid, (@rownum:=@rownum+1) as newid FROM `stations` WHERE projectid='.$id.' ORDER BY id);';
		 $db->multi_query($sql);
		  while ($db->next_result()) // flush multi_queries
			if (!$db->more_results()) break;
		echo '<p>Stations temp SQL: '.htmlentities($sql).'</p>'; 
		
		if ($cloneList['parts']) 
		$sql='INSERT INTO stations (id, mainpart, name) SELECT s.id, ifnull(if(s.main=\'part\',(SELECT newid FROM part_temp pt WHERE pt.oldid=s.mainpart LIMIT 1),(SELECT newid FROM station_temp st WHERE st.oldid=s.mainpart LIMIT 1)),0) ,\'\' FROM stations s WHERE projectid='.$newId;
		else
		$sql='INSERT INTO stations (id, mainpart, name) SELECT s.id, ifnull(if(s.main=\'part\',0,(SELECT newid FROM station_temp st WHERE st.oldid=s.mainpart LIMIT 1)),0) ,\'\' FROM stations s WHERE projectid='.$newId;
		$sql.=' ON DUPLICATE KEY UPDATE mainpart=VALUES(mainpart);';
		$db->query($sql);
		echo '<p>Stations update SQL: '.htmlentities($sql).'</p>'; 
	 }
	 
	 if ($cloneList['tasks'] && $cloneList['parts'] && $cloneList['stations'])
	 {
		 $sql='INSERT INTO highleveltasks (`stationid`, `tasktype`, `sortorder`, `partid`, `subpartid`, `toolid`, `positionname`, `description`, `esttime`) SELECT st.newid, hlt.`tasktype`, hlt.`sortorder`, if(hlt.`partid`=0,0,(SELECT pt.newid FROM part_temp pt WHERE pt.oldid=hlt.partid LIMIT 1)), if(hlt.`subpartid`=0,0,(SELECT newid FROM station_temp WHERE oldid=hlt.subpartid LIMIT 1)), hlt.`toolid`, hlt.`positionname`, hlt.`description`, hlt.`esttime` FROM `highleveltasks` hlt, station_temp st WHERE st.oldid=hlt.id ORDER BY hlt.id;';
		 $result['tasks']=($db->query($sql));
		 echo '<p>Tasks SQL: '.htmlentities($sql).'</p>'; 
	 }
	 
	 if ($cloneList['users'])
	 {
		$sql='INSERT INTO `userroles` (`userid`, `projectid`, `laststation`, `role`) SELECT `userid`, '.$newId.', `laststation`, `role` FROM `userroles` WHERE projectid='.$id;
		$result['users']=($db->query($sql));
		echo '<p>User roles SQL: '.htmlentities($sql).'</p>';
	 }
	 
	return true;
 }

 function addMarker($name,$type) {
  global $db;
   if (!isset($_SESSION['projectid']))
   return 'ERROR: No active project';
  $markertypes=getEnumValues('markers','type');
   if (!in_array($type,$markertypes))
   return 'ERROR: Unsupported marker type';
  $name=$db->real_escape_string($name);
  $sql='INSERT INTO markers (projectid,name,type) VALUES ('.$_SESSION['projectid'].',\''.$name.'\',\''.$type.'\');';
  echo '<sql>'.$sql.'</sql>';
  $db->query($sql);
   if ($db->affected_rows>0)
   return 'OK';
  return 'ERROR';
 }

 function addPart($name) {
  global $db;
  if (!isset($_SESSION['projectid']))
  return 'ERROR: No active project';
  $name=$db->real_escape_string($name);
  $sql='INSERT INTO parts (projectid,name,description) VALUES ('.$_SESSION['projectid'].',\''.$name.'\',\'\');';
  echo '<sql>'.$sql.'</sql>';
  $db->query($sql);
   if ($db->affected_rows>0)
   return 'OK';
  return 'ERROR';
 }

 function addTool($name) {
  global $db;
  $name=$db->real_escape_string($name);
  $sql='SELECT max(id) as id FROM tools WHERE language=\'mosim\';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
    {		
      $sql='INSERT INTO tools (id,name) VALUES ('.($row['id']+1).',\''.$name.'\');';
      $db->query($sql);
      if ($db->affected_rows>0)
      {
       echo '<result>OK</result>';	   
	   return;
      }
	} 
  echo '<result>ERROR</result>'; 
 }
 
 function changeToolCatIcon($toolcat,$icon) {
  global $db;
  if ((strpos($icon,'\\')!==false) || (strpos($icon,'/')!==false))
  return '<result>Invalid icon file!</result>';	  
  if (!file_exists('icons/'.$icon))
  return '<result>Invalid icon file!</result>';
	  
  $icon=$db->real_escape_string($icon);  
  $sql='UPDATE toolcat SET icon=\''.$icon.'\' WHERE id='.$toolcat;
  $db->query($sql);
   if ($db->affected_rows>0)
   return '<result>OK</result>';
  return '<result>Cannot change tool category icon</result>';	   
 }
 
 function changePartCatIcon($partcat,$icon) {
  global $db;
  if ((strpos($icon,'\\')!==false) || (strpos($icon,'/')!==false))
  return '<result>Invalid icon file!</result>';	  
  if (!file_exists('icons/'.$icon))
  return '<result>Invalid icon file!</result>';
	  
  $icon=$db->real_escape_string($icon);  
  $sql='UPDATE partcat SET icon=\''.$icon.'\' WHERE id='.$partcat;
  $db->query($sql);
   if ($db->affected_rows>0)
   return '<result>OK</result>';
  return '<result>Cannot change part category icon</result>';
 } 
 
 function addPartCat($name) {
  global $db;
  $name=$db->real_escape_string($name);
  $sql='SELECT max(id) as id FROM partcat WHERE language=\'mosim\';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
    {		
      $sql='INSERT INTO partcat (id,name,projectid) VALUES ('.($row['id']+1).',\''.$name.'\','.$_SESSION['projectid'].');';
      $db->query($sql);
      if ($db->affected_rows>0) 
	  return 'OK';
	} 
  return 'ERROR'; 	 
 }
 
 function addToolCat($name) {
  global $db;
  $name=$db->real_escape_string($name);
  $sql='SELECT max(id) as id FROM toolcat WHERE language=\'mosim\';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
    {		
      $sql='INSERT INTO toolcat (id,name) VALUES ('.($row['id']+1).',\''.$name.'\');';
      $db->query($sql);
      if ($db->affected_rows>0)
      {
       echo '<result>OK</result>';	   
	   return;
      }
	} 
  echo '<result>ERROR</result>'; 
 }

  function reorderMMUs($neworder) {
  global $db;
  $sql='INSERT INTO mmu_project (mmuid, sortorder, projectid) VALUES ';
   for ($i=0; $i<count($neworder); $i++)
   $sql.='('.$neworder[$i].','.$i.',\''.$_SESSION['projectid'].'\'),';
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE sortorder=values(sortorder);';
  $db->query($sql);
  echo '<sql>'.$sql.'</sql>';
   if ($db->affected_rows>0)
   {
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 }

 function reorderPartCat($neworder) {
  global $db;
  $sql='INSERT INTO partcat (id, sortorder, language) VALUES ';
   for ($i=0; $i<count($neworder); $i++)
   $sql.='('.$neworder[$i].','.$i.',\'mosim\'),';
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE sortorder=values(sortorder);';
  $db->query($sql);
  echo '<sql>'.$sql.'</sql>';
   if ($db->affected_rows>0)
   {
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 }
 
 function reorderToolCat($neworder) {
  global $db;
  $sql='INSERT INTO toolcat (id, sortorder, language) VALUES ';
   for ($i=0; $i<count($neworder); $i++)
   $sql.='('.$neworder[$i].','.$i.',\'mosim\'),';
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE sortorder=values(sortorder);';
  $db->query($sql);
  echo '<sql>'.$sql.'</sql>';
   if ($db->affected_rows>0)
   {
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 }
 
 function reorderStations($neworder) {
  global $db;
  $sql='INSERT INTO stations (id, sortorder, name) VALUES ';
   for ($i=0; $i<count($neworder); $i++)
   $sql.='('.$neworder[$i].','.$i.',\'\'),';
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE sortorder=values(sortorder);';
  $db->query($sql);
  echo '<sql>'.$sql.'</sql>';
   if ($db->affected_rows>0)
   {
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 }

 function reorderTaskList($neworder) {
  global $db;
  $order=array(0);
  $level=0;
  $assembly=0;
  if (count($neworder)>0)
  {
	$tid_sid=explode(';',$neworder[0]);    
	$assembly=$tid_sid[1];
  }	  
  $sqlS='INSERT INTO stations (id, name, sortorder) VALUES ';
  $sql='INSERT INTO highleveltasks (id, sortorder, stationid, positionname, description) VALUES '; 
  $sqlcount=0;
  $sqlScount=0;
   for ($i=0; $i<count($neworder); $i++)
   {
	$tid_sid=explode(';',$neworder[$i]); //taskid, stationid, level
	if ($tid_sid[0]==0)
	{
	 $sqlS.='('.$tid_sid[1].',\'\','.$order[intval($tid_sid[2])].'),';   
	 $sqlScount++;
	 $order[intval($tid_sid[2])]++;
	 //$level++;
	 $level=intval($tid_sid[2])+1;
	 $order[$level]=0;
	 $assembly=$tid_sid[1];
	}
	else
	{
	 //if ($assembly!=$tid_sid[1])
     //$level--;
     $level=intval($tid_sid[2]);
     if ($level<0)
	 return $sql.'  '.$i.'     Data error';	 
	 $sql.='('.$tid_sid[0].','.$order[$level].','.$tid_sid[1].',\'\',\'\'),';   
	 $order[$level]++;
	 $sqlcount++;
	}
   }
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE stationid=values(stationid), sortorder=values(sortorder);';
  $sqlS=substr($sqlS,0,-1).' ON DUPLICATE KEY UPDATE sortorder=values(sortorder);';
  
  $OK=true;
  if ($sqlcount>0)
   if (!($db->query($sql)))
   $OK=false;
  if ($sqlScount>0)
   if (!($db->query($sqlS)))
   $OK=false;
  if ($OK)
  return 'OK';
  return 'ERROR'; 
 }

 function reorderParts($neworder) {
  global $db;
  $sql='INSERT INTO part_cat (cat, part, sortorder) VALUES ';
  $sqlA='';
   for ($i=0; $i<count($neworder); $i++)
   {
	$vals=explode(',',$neworder[$i]);
	echo $i.' - '.$vals[0].', '.$vals[1].', '.$vals[2].
	((trim($vals[2])!="-1")?", true":", false")."\r\n";
	echo "\r\n";
    $sql.='('.trim($vals[1]).','.trim($vals[0]).','.$i.'),';
     if (trim($vals[2])>=0)
     $sqlA='DELETE FROM part_cat WHERE cat='.trim($vals[2]).' and part='.trim($vals[0]).' LIMIT 1;';
   }
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE  sortorder=values(sortorder);';
  
  $db->query($sql);
   if ($sqlA!='')
   $db->query($sqlA);
  echo '<sql>'.$sql.'</sql><sql>'.$sqlA.'</sql>';                    
   if ($db->affected_rows>0)
   { 
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 }
 
 function reorderPartsToStations($neworder) {
  global $db;
  $sql='INSERT INTO part_station (station, part, sortorder) VALUES ';
  $sqlA='';
   for ($i=0; $i<count($neworder); $i++)
   {
	$vals=explode(',',$neworder[$i]);
	echo $i.' - '.$vals[0].', '.$vals[1].', '.$vals[2].
	((trim($vals[2])!="-1")?", true":", false")."\r\n";
	echo "\r\n";
    $sql.='('.trim($vals[1]).','.trim($vals[0]).','.$i.'),';
     if (trim($vals[2])>=0)
     $sqlA='DELETE FROM part_station WHERE station='.trim($vals[2]).' and part='.trim($vals[0]).' LIMIT 1;';
   }
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE  sortorder=values(sortorder);';
  
  $db->query($sql);
   if ($sqlA!='')
   $db->query($sqlA);
  echo '<sql>'.$sql.'</sql><sql>'.$sqlA.'</sql>';                    
   if ($db->affected_rows>0)
   { 
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 } 
 
 function reorderTools($neworder) {
  global $db;
  $sql='INSERT INTO tool_cat (cat, tool, sortorder) VALUES ';
  $sqlA='';
   for ($i=0; $i<count($neworder); $i++)
   {
	$vals=explode(',',$neworder[$i]);
	echo $i.' - '.$vals[0].', '.$vals[1].', '.$vals[2].
	((trim($vals[2])!="-1")?", true":", false")."\r\n";
	echo "\r\n";
    $sql.='('.trim($vals[1]).','.trim($vals[0]).','.$i.'),';
     if (trim($vals[2])>=0)
     $sqlA='DELETE FROM tool_cat WHERE cat='.trim($vals[2]).' and tool='.trim($vals[0]).' LIMIT 1;';                                       
   }
  $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE  sortorder=values(sortorder);';
  
  $db->query($sql);
   if ($sqlA!='')
   $db->query($sqlA);
  echo '<sql>'.$sql.'</sql><sql>'.$sqlA.'</sql>';                    
   if ($db->affected_rows>0)
   { 
    echo '<result>OK</result>';
	return;
   }
  echo '<result>ERROR</result>'; 
 }
 
 function editUserRole($userid,$projectid,$newrole) {
  global $db; //add checking that at least one project owner remains
   if ($_SESSION['role']=='owner')
   {	   
    $sql='UPDATE userroles SET role=\''.$newrole.'\' WHERE projectid='.$projectid.' and userid='.$userid.' LIMIT 1;';
	$db->query($sql);
	 if ($db->affected_rows==1)
	 echo '<result>OK</result>';
     else
     echo '<result>Nothing to change</result>';
	return true;
   }
  echo '<result>You are not a project owner</result>';
  return false;
 }
 
 function editUserAdminRole($userid,$newadminroles) {
	global $db;
	$sqladd='';
	$sqldel=array();
	
	$sql='SELECT userid, role FROM adminroles WHERE userid='.$_SESSION['userid'].';';
	$adminlevel=0;
	if ($result=$db->query($sql))
		while ($row=$result->fetch_assoc())
		{
		 if (($row['role']=='admin') && ($adminlevel<2))
		 $adminlevel=2;
		 if (($row['role']=='user manager') && ($adminlevel<2))
		 $adminlevel=1;
		}
	
	 if ($adminlevel<1)
	 {
	  echo '<result>ERR - insufficient user rights</result>';
	  return;
     }
	
	$sql='SELECT userid, role FROM adminroles WHERE userid='.$userid.';';
	if ($result=$db->query($sql))
	{
		while ($row=$result->fetch_assoc())
		if (!in_array($row['role'],$newadminroles))
			if (($row['role']!='admin') || ($adminlevel>=2))
			$sqldel[]='DELETE FROM adminroles WHERE userid='.$userid.' and role=\''.$row['role'].'\';';
		for ($i=0; $i<count($newadminroles); $i++)
		{
			$found=false;
			$result->data_seek(0);
			while ($row=$result->fetch_assoc())
			 if ($newadminroles[$i]==$row['role'])
			 $found=true;
		 if (!$found)
			 if (($newadminroles[$i]!='admin') ||		
		         (($newadminroles[$i]=='admin') && ($adminlevel>=2)))
		     $sqladd.='('.$userid.',\''.$newadminroles[$i].'\'),';
		}
		$ok=true;
		if ($sqladd!='')
		{
		 $sqladd='INSERT INTO adminroles (userid,role) VALUES '.
	             substr($sqladd,0,-1).'; ';                     
		
		 if (!($db->query($sqladd)))
		 $ok=false;
		}
		
		if (count($sqldel)>0)
		{
		 for ($i=0; $i<count($sqldel); $i++)
		  if (!($db->query($sqldel[$i])))
		  $ok=false;
		}
		
		if ($ok)
		{
		 $sql='SELECT GROUP_CONCAT(role SEPARATOR \',\') as roles, GROUP_CONCAT(role SEPARATOR \', \') as rolesf FROM adminroles WHERE userid='.$userid.' GROUP BY userid;';
		  if ($result=$db->query($sql))
		  {
			if ($row=$result->fetch_assoc())
		    {
		     echo '<result>OK</result><data>'.$row['roles'].'</data><dataf>'.$row['rolesf'].'</dataf>';
		     return;
		    }
			else
			{
			 echo '<result>OK</result><data>User</data><dataf>User</dataf>';
		     return;	
			}
		  }
		  else
		  {
			  echo '<result>ERR - summary query error</result><sql>'.$sql.'</sql>';
			  return;
		  }
		}
	}
	echo '<result>ERR - final error</result>';
 }
 
 function delPartCat($id) {
  global $db;
  $sql='DELETE FROM partcat WHERE id='.$id.' and language=\'mosim\';';  
  $db->query($sql);
   if ($db->affected_rows>0)
   echo '<result>OK</result>'; 	   
   else
   echo '<result>ERROR</result>';	   
 }
 
 function delToolCat($id) {
  global $db;
  $sql='DELETE FROM toolcat WHERE id='.$id.' and language=\'mosim\';';  
  $db->query($sql);
   if ($db->affected_rows>0)
   echo '<result>OK</result>'; 	   
   else
   echo '<result>ERROR</result>';	   
 }
 /*
 function isMMUManager2() //copy from mmu-functions
 {
	global $db;
	$sql='SELECT count(*) as ile FROM `adminroles` WHERE userid='.$_SESSION['userid'].
		' and role=\'MMU Library manager\';';
	if ($result=$db->query($sql))
		if ($row=$result->fetch_assoc())
		if ($row['ile']>0)
		return true;
	return false;
 }*/
 /*
 function delMMU($id) {
  global $db;
  if (!isMMUManager2())
  {
	  echo '<result>ERROR</result><msg>You need MMU library manager rights to perform this action.</msg>';
	  return;
  }
  $sql='DELETE FROM mmus WHERE id='.$id.';';  
  $db->query($sql);
   if ($db->affected_rows>0)
   {
	chmod(realpath('mmus/'.$id.'.zip'), 0777);
	echo "\r\nUnlink status: ".(unlink(realpath('mmus/'.$id.'.zip'))?"True":"False")."\r\n";
    echo '<result>OK</result>'; 	   
   }
   else
   echo '<result>ERROR</result>';	   
 }
*/
 function setEnableMMU($id,$project,$action) {
  global $db;
  $sql='INSERT INTO `mmu_project` (`projectid`, `mmuid`, `enabled`) '.
  'VALUES ('.$project.','.$id.','.($action=='enableMMU'?'1':'0').') '.
  'ON DUPLICATE KEY UPDATE enabled=VALUES(enabled);';
  $db->query($sql);
  echo '<sql>'.$sql.'</sql>';
   if ($db->affected_rows>0)
   echo '<result>OK</result>'; 	   
   else
   echo '<result>ERROR</result>';	   
 }
 
 function setDefaultPartCat($id,$cat) {
  global $db;
  $sql='UPDATE partcat SET defaultpart='.$id.' WHERE id='.$cat;
  $db->query($sql);
   if ($db->affected_rows>0)
   echo '<result>OK</result>'; 	   
   else
   echo '<result>ERROR</result>';	   
 }
 
 function setDefaultToolCat($id,$cat) {
  global $db;
  $sql='UPDATE toolcat SET defaulttool='.$id.' WHERE id='.$cat;
  $db->query($sql);
   if ($db->affected_rows>0)
   echo '<result>OK</result>'; 	   
   else
   echo '<result>ERROR</result>';	   
 }
 
 function removeUserFromProject($userid,$projectid) {
  global $db;
  if ($_SESSION['role']!='owner')
  {
   echo '<result>Only owners can delete users</result>';
   return;
  }	  
  $sql='SELECT count(*) as ile, role FROM userroles WHERE userid<>'.$userid.' and projectid='.$projectid.' and role=\'owner\';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	 if ($row['ile']>0)
     {
	  $sql='DELETE FROM userroles WHERE userid='.$userid.' and projectid='.$projectid.' LIMIT 1;';
      $db->query($sql);
       if ($db->affected_rows>0)
   	   echo '<result>OK</result>';
       else
       echo '<result>Database error</result>';
	 }
     else
     echo '<result>You cannot remove the last owner</result>';
 }
 
 function searchUser($find) {
  global $db;
  $find='%'.$db->real_escape_string(trim($find)).'%';
  $sql='SELECT u.id, u.name, u.email, GROUP_CONCAT(ur.projectid) as projectid FROM users u LEFT JOIN userroles ur ON (ur.userid=u.id) WHERE (u.name like \''.$find.'\' or u.email like \''.$find.'\') GROUP BY u.id HAVING ifnull(FIND_IN_SET(\''.$_SESSION['projectid'].'\',projectid),0)=0 ORDER BY name LIMIT 10;';
  if ($result=$db->query($sql))
  {
   $ok=false;	  
   echo '<userlist><table class="users results"><tr><th>Name</th><th>E-mail</th></tr>';
   while ($row=$result->fetch_assoc())
   {
	echo '<tr data-id="'.$row['id'].'" onclick="addUserToProject(this);"><td>'.$row['name'].'</td><td>'.$row['email'].'</td></tr>';   
	$ok=true;   
   }
   echo '</table></userlist>';
    if ($ok)
	echo '<result>OK</result>';
   echo '<sql>'.$sql.'</sql>';
  }   	   
 }
 
 function addUserToProject($userid,$projectid) {
  global $db;
   if ($_SESSION['role']=='owner')
   {
	$sql='INSERT INTO userroles (userid,projectid,role) VALUES ('.$userid.','.$projectid.',\'editor\');';
	$db->query($sql);
	if ($db->affected_rows>0)
	echo '<result>OK</result>';
    else
	echo '<result>User is already a member of the project</result>';	
   }
   else
	echo '<result>You need to be the project owner to add users the project</result>';   
 }
 
 function normalizeTime($time) {
  $time=trim($time);
  if ($time=='')
  $time='00:00:00';
  else	  
  {
  $time=explode(':',$time);
   switch (count($time))
   {
	case 1: $time='00:00:'.($time[0]<10?'0':'').intval($time[0]);
    break;
    case 2: $time='00:'.($time[0]<10?'0':'').intval($time[0]).':'.($time[1]<10?'0':'').intval($time[1]);
    break;		
	case 3: $time=($time[0]<10?'0':'').intval($time[0]).':'.($time[1]<10?'0':'').intval($time[1]).':'.($time[2]<10?'0':'').intval($time[2]);
   }
  }	 
  return $time;
 }
 
 function addMultipleTasks($parts) //adding multiple tasks where all data is same except part
 {
	$ok=0;
	$i=0;
	for ($i=0; $i<count($parts); $i++)
	$ok+=(addTask($parts[$i])=='OK'?1:0);
   if ($ok==count($parts))
	return 'OK';
   else
	return 'Error. Could not add '.(count($parts)-$ok).' out of '.count($parts).' new tasks.';
 }
 
 function addTask($part) { //$db->real_escape_string(
  global $db;
  if ($_SESSION['projectid']==0)
  return 'You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.';	  
  if (($_POST['stationid']=='') || (!ctype_digit($_POST['stationid'])))
  return 'You need to select station before you can add tasks.';	  
  if (($_POST['workerid']=='') || (!ctype_digit($_POST['workerid'])))
  return 'You need to select worker before you can add tasks.';
  if (!isUsersProject($_SESSION['projectid']))
  return 'You do not have rights to edit this project';
  $time=normalizeTime($_POST['time']);
  $partsubpart=$part.',0,0';
   if (substr($part,0,1)=='S')
   $partsubpart='0,'.substr($part,1).',0';
   if (substr($part,0,1)=='M')
   $partsubpart='0,0,'.substr($part,1);
  $sql='SELECT sum(ile)+1 as ile FROM (SELECT count(*) as ile FROM highleveltasks WHERE stationid='.$_POST['stationid'].' UNION ALL SELECT count(*) as ile FROM stations WHERE parent='.$_POST['stationid'].') dane;';
  $nextorder=1000;
    if ($result=$db->query($sql))
	  if ($row=$result->fetch_assoc())
	  $nextorder=$row['ile'];
  $sql='INSERT INTO `highleveltasks` (`workerid`, `stationid`, `tasktype`, `sortorder`, `partid`, `subpartid`, `markerid`,`toolid`,`esttime`, `positionname`, `description`) VALUES ('.$_POST['workerid'].','.$_POST['stationid'].','.$_POST['type'].','.$nextorder.','.$partsubpart.','.$_POST['tool'].',\''.$db->real_escape_string($time).'\',\''.
  $db->real_escape_string($_POST['position']).'\',\''.
  $db->real_escape_string($_POST['desc']).'\');';
  if ($db->query($sql))
  return 'OK';
  else
  return 'Error. Could not add new task.';
 }
 
 function updateTask($taskid,$operationid,$partid,$toolid,$description,$time) {
  global $db;
  if ($_SESSION['projectid']==0)
  return '<result>You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.</result>';	  
  if (!isUsersProject($_SESSION['projectid']))
  return '<result>You do not have rights to edit this project</result>';
  $time=normalizeTime($time);
  $sql='UPDATE highleveltasks SET '.
  'tasktype='.$operationid.', '. 
  'partid='.$partid.', '. 
  'toolid='.$toolid.', '. 
  'esttime=\''.$time.'\', '.
  'description=\''.$db->real_escape_string($description).'\' '.
  'WHERE id='.$taskid.' LIMIT 1;';   
  $db->query($sql);
   if ($db->affected_rows>0)
   return '<result>OK</result><time>'.($time=='NULL'?'00:00:00':$time).'</time>';
   else
   return '<result>ERR</result><sql>'.$sql.'</sql>';	   

 }
 
 function delPart($id,$cat) {
  global $db;
   $sql='DELETE FROM part_cat WHERE cat='.$cat.' and part='.$id.' LIMIT 1;';
   $db->query($sql);
   $sql='SELECT count(*) as ile FROM part_cat WHERE part='.$id.';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
     if ($row['ile']==0)
	 {
	  $db->query('DELETE FROM parts WHERE id='.$id.' LIMIT 1;');
	  if ($db->affected_rows>0)
	  echo '<result>deleted</result>';	  
	 }
	 else
	 echo '<result>OK</result>';
 }
 
 function delTool($id,$cat) {
  global $db;
   $sql='DELETE FROM tool_cat WHERE cat='.$cat.' and tool='.$id.' LIMIT 1;';
   $db->query($sql);
   $sql='SELECT count(*) as ile FROM tool_cat WHERE tool='.$id.';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
     if ($row['ile']==0)
	 {
	  $db->query('DELETE FROM tools WHERE id='.$id.' LIMIT 1;');
	  if ($db->affected_rows>0)
	  echo '<result>deleted</result>';	  
	 }
	 else
	 echo '<result>OK</result>';
 }
 
 //TODO: deleting stations orphans actions that previously belonged to station
  function delStation($id) {
  global $db;
  if (!isUsersProject($_SESSION['projectid']))
  return '<result>You do not have rights to edit this project</result>';
   $sql='DELETE FROM stations WHERE id='.$id.' and projectid='.$_SESSION['projectid'].' and parent=0 LIMIT 1;';
	if ($db->query($sql))
	{
	  if ($db->affected_rows>0)
	  echo '<result>deleted</result>';
	}
	else
	echo '<result>'.$db->error.'</result>';
 }
 
 function getTask($taskid) {
  global $db;
  $sql='SELECT hvt.tasktype, tt.name as taskname, hvt.partid, p.name as partname, hvt.toolid, t.name as toolname, hvt.positionname, hvt.description, hvt.esttime FROM highleveltasks hvt, tasktypes tt, parts p, tools t WHERE tt.id=hvt.tasktype and p.id=hvt.partid and hvt.toolid=t.id and hvt.id='.$taskid;
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
   {	
	echo '<taskname>'.htmlentities($row['taskname']).'</taskname>';
	echo '<taskid>'.$row['tasktype'].'</taskid>';
	echo '<partname>'.htmlentities($row['partname']).'</partname>';
	echo '<partid>'.$row['partid'].'</partid>';
	echo '<toolname>'.htmlentities($row['toolname']).'</toolname>';
	echo '<toolid>'.$row['toolid'].'</toolid>';
	echo '<time>'.$row['esttime'].'</time>';
	echo '<description>'.htmlentities($row['description']).'</description>';
	echo '<result>OK</result>';	  
	return;
   }
  echo '<result>ERR</result>';
 }
 
 function delTask() {
  global $db;
  if ($_SESSION['projectid']==0)
  return 'You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.';	  
  if (!isUsersProject($_SESSION['projectid']))
  return 'You do not have rights to edit this project';
   $sql='DELETE FROM highleveltasks WHERE id in ('.$_POST['task_ids'].')';  
   $db->query($sql);
   return 'OK';
 }
 
 function moveSubassembly() {
  global $db;
  
 }
 
 function moveTask() {
  global $db;
  if ($_SESSION['projectid']==0)
  return 'You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.';	  
  if (!isUsersProject($_SESSION['projectid']))
  return 'You do not have rights to edit this project';
  if (!areUserStations($_POST['tostation'].','.$_POST['fromstation']))
  return 'You do not have rights to move tasks between selected stations';
  if (!ctype_digit($_POST['toworker']))
  return 'Incorrect worker id.';
  $ok=true;
  if ($_POST['task_ids']!='')
  {
   $sql='UPDATE highleveltasks SET stationid='.$_POST['tostation'].', workerid='.$_POST['toworker'].' WHERE id in ('.$_POST['task_ids'].') and stationid in ('.$_POST['fromstation'].')';
   if (!($db->query($sql)))
   $ok=false;
  }
  if ($_POST['subassembly_id']!='')
  {
   $sql='UPDATE stations SET parent='.$_POST['tostation'].' WHERE id in ('.$_POST['subassembly_id'].') and parent in ('.$_POST['fromstation'].')';
   if (!($db->query($sql)))
   $ok=false;
  }
  if ($ok)
  return 'OK';
  return 'Database communication error';
  
 }

 function areUserStations($stations) {
	global $db;
	$s=explode(',',$stations);
	 for ($i=0; $i<count($s); $i++)
		 if (!ctype_digit($s[$i]))
		 return false;
	$sql='SELECT s.id, ifnull(ur.role,\'0\') as role FROM stations s LEFT JOIN `userroles` ur ON (ur.projectid=s.projectid and ur.userid='.$_SESSION['userid'].') WHERE s.id in ('.$stations.');';
	 if (!($result=$db->query($sql)))
	 return false;
	
	while ($row=$result->fetch_assoc())
		if (!in_array($row['role'],array('owner','editor')))
			 return false;
	return true;
 }
 
 function isUsersAvatar($avatarid)
 {
	global $db;
	$sql='SELECT count(*) as ile, a.projectid, u.role FROM `avatars` a, userroles u WHERE a.id='.$avatarid.' and role in (\'owner\',\'editor\') and u.userid='.$_SESSION['userid'].' and u.projectid=a.projectid';
	if ($result=$db->query($sql))
		if ($row=$result->fetch_assoc())
			return ($row['ile']>0);
	return false;
 }

 function isinUsersProject($tasks) {
	global $db;
	$sql='SELECT count(hlt.id) as ile FROM `highleveltasks` hlt, stations s, userroles ur WHERE s.id=hlt.stationid and s.projectid=ur.projectid and ur.userid='.$_SESSION['userid'].' and ur.role in (\'owner\',\'editor\') and hlt.id in ('.implode(',',$tasks).');';
	if ($result=$db->query($sql))
		if ($row=$result->fetch_assoc())
			return ($row['ile']==count($tasks));
	return false;
 }
 
 function cloneTasks($tasks) {
	global $db;
	for ($i=0; $i<count($tasks); $i++)
		if (!ctype_digit($tasks[$i]))
		return '<result>Data error.</result>';
	if ($_SESSION['projectid']==0)
	return '<result>You do not have any active project.</result>';
    if (!isinUsersProject($tasks))
	return '<result>Selected tasks do not belong to you project.</result>';
	
	$newids=array();
	for ($i=0; $i<count($tasks); $i++)
	{
		$sql='INSERT INTO highleveltasks (`stationid`, `tasktype`, `sortorder`, `partid`, `subpartid`, `toolid`, `positionname`, `description`, `esttime`) SELECT `stationid`, `tasktype`, `sortorder`, `partid`, `subpartid`, `toolid`, `positionname`, `description`, `esttime` FROM highleveltasks WHERE id='.$tasks[$i].';';
		if ($db->query($sql))
		$newids[]=$db->insert_id;
		else
		$newids[]=0;
	}
	return '<result>OK</result><ids>'.implode(',',$newids).'</ids>';
 }
 
 function addStation($name) {	 
  global $db;
  if ($_SESSION['projectid']==0)
  return 'You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.';	  
  if (!isUsersProject($_SESSION['projectid']))
  return 'You do not have rights to edit this project';
  $name=$db->real_escape_string(trim($name));
   if ($name=='')
   {
	$sql='SELECT count(*) as ile FROM stations WHERE projectid='.$_SESSION['projectid'];
	 if ($result=$db->query($sql))
	  if ($row=$result->fetch_assoc())
      {
	   $name='Station '.($row['ile']+1);
	  }		  
   }	   
  if ($name!='')
  {
   $sql='INSERT INTO stations (projectid,name,sortorder) VALUES ('.$_SESSION['projectid'].',\''.$name.'\','.($row['ile']+1).');';
   $db->query($sql);
   if ($db->insert_id>0)
   return 'OK';
  }
 }
 
 function addSubStation($name,$parentstation,$mainpart,$position) {
  global $db;
  $main='part';
   if (!ctype_digit($mainpart))
	   if ((substr($mainpart,0,1)=='S') && (ctype_digit(substr($mainpart,1))))
	   {
		$main='station';
		$mainpart=substr($mainpart,1);
	   }
	   else
	   return 'Data form error: data do not agree with the template (1).';
		   
  if (!(ctype_digit($parentstation) && ctype_digit($position)))
  return 'Data form error: data do not agree with the template (2).';

  if ($_SESSION['projectid']==0)
  return 'You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.';
  if (!isUsersProject($_SESSION['projectid']))
  return 'You do not have rights to edit this project';
  if (($parentstation=="0") || ($parentstation==''))
  return 'You need to create station first, before you can add any sub assemblies';	  
  $name=$db->real_escape_string(trim($name));
  if ($name=='') //create default name
  {
	$sql='SELECT count(*) as ile FROM stations WHERE projectid='.$_SESSION['projectid'].' and parent='.$parentstation;
	 if ($result=$db->query($sql))
	  if ($row=$result->fetch_assoc())
	  $name='Sub assembly '.($row['ile']+1);
   //TODO: When subassembly name would exist, it should throw an error or figure out another name
   //TODO: When database connection fails it should return "database error" message
  }
  else //check if the name already exists and if yes, throw an error, else use it.
  {
  	$sql='SELECT count(*) as ile FROM stations WHERE projectid='.$_SESSION['projectid'].' and parent='.$parentstation.' name=\''.$name.'\'';
	 if ($result=$db->query($sql))  
	  if ($row=$result->fetch_assoc())
	   if ($row['ile']>0)
       return 'Sub assembly station with the name "'.htmlentities($name).'" already exists, use other name, or leave name field blank if you want default name to be created for you.';
  }
  
  $order=1000;
  
  $sql='INSERT INTO stations (projectid,name,sortorder,parent,mainpart,main,position) '.
  'VALUES ('.$_SESSION['projectid'].',\''.$name.'\','.$order.','.$parentstation.','.$mainpart.',\''.$main.'\','.$position.');';
   $db->query($sql);
   if ($db->insert_id>0)
   return 'OK';
   else
   return 'ERROR: '.$sql;	   
 }
 
 function generateToken() {
	//$token=openssl_random_pseudo_bytes(16); 
	//return bin2hex($token);
	$allowed='QWERTYUIOPASDFGHJKLZXCVBNMmnbvcxzasdfghjklpoiuytrewq0123456789-';
	$token='';
	 for ($i=0; $i<30; $i++)
	 $token.=$allowed[mt_rand(0, strlen($allowed))];
	return $token;
 }
 
 function getAccessToken($projectid) {
  global $db;
  if ($_SESSION['projectid']==0)
  return '<result>You do not have active project. Go to <a href=projects.php>projects</a> and select an existing project or create a new one.</result>';
  if (!isUsersProject($projectid))
  return '<result>You do not have rights to edit this project</result>';
  $sql='SELECT token FROM tokens WHERE userid='.$_SESSION['userid'].' and projectid='.$projectid;
   if ($result=$db->query($sql))
   {
	if ($row=$result->fetch_assoc())
	return '<result>OK</result><token>'.$row['token'].'</token>';
   }
   else
	return '<result>Database error</result>';
  $token=generateToken();
  $sql='INSERT INTO tokens (token, projectid, userid) VALUES '.
  '(\''.$token.'\','.$projectid.','.$_SESSION['userid'].');';
  if ($db->query($sql))
  return '<result>OK</result><token>'.$token.'</token>';	  
  return '<result>Cannot create new token</result>';
 }
 
 function clickTaskType($taskid,$operationid) {
  global $db;
  $sql='SELECT tt.name, tt.parent, tt1.name as parentname FROM tasktypes tt LEFT JOIN tasktypes tt1 ON (tt1.id=tt.parent and tt1.language=tt.language) WHERE tt.id='.$operationid.' and tt.language=\'mosim\'';
  echo '<sql>'.$sql.'</sql>';
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
   {
    echo '<result>OK</result>';
	echo '<parent>'.$row['parent'].'</parent>';
	return true;
   }
  echo '<result>ERR</result>'; 
 }
 
 function clickPart($taskid,$partid) {
  global $db;
  $sql='SELECT p_c.part as id, p_c.cat as parent FROM part_cat p_c WHERE p_c.part='.$partid;
  echo '<sql>'.$sql.'</sql>';
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
   {
    echo '<result>OK</result>';
	echo '<parent>'.$row['parent'].'</parent>';
	return true;
   }
  echo '<result>ERR</result>'; 	 
 }
 
 function clickTool($taskid,$toolid) {
  global $db;
  $sql='SELECT tool as id, cat as parent FROM tool_cat WHERE tool='.$toolid;
  echo '<sql>'.$sql.'</sql>';
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
   {
    echo '<result>OK</result>';
	echo '<parent>'.$row['parent'].'</parent>';
	return true;
   }
  echo '<result>ERR</result>'; 	 
 }
 
 function deleteProject($projectid)
 {
	global $db, $log;
	$log['info']='Delete project';
	$log['log']='';
	if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
	return 'You do not have permissions to delete any project.'; 
	if ($_SESSION['projectid']!=$projectid)
	return 'Project number from the session does not match selected project. Refresh page and try again';
	$sql='SELECT count(*) as ile FROM userroles WHERE (role=\'owner\') and projectid='.$_SESSION['projectid'].' and userid='.$_SESSION['userid'];
	$log['log'].=$sql."\r\n";
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
    if ($row['ile']>0)
	{
	 $sql='SELECT id FROM stations WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	 if (!$result=$db->query($sql))
		return 'Error while fetching station list within project: <br>'.$db->error.'<br>'.$sql;
	 $stations='';
	 while ($row=$result->fetch_assoc())
		 $stations.=$row['id'].',';
	 if ($stations!='')
	 {
		 $stations=substr($stations,0,-1);
		 $sql='DELETE FROM highleveltasks WHERE stationid in ('.$stations.')';
		 $log['log'].=$sql."\r\n";
		 if ((!$db->query($sql)))
		  return 'Error while deleting high level tasks within current project: <br>'.$db->error.'<br>'.$sql;
		 $sql='DELETE FROM part_station WHERE station in ('.$stations.')';
		 $log['log'].=$sql."\r\n";
		 if ((!$db->query($sql)))
		 return 'Error while deleting part to station assignments within current project: <br>'.$db->error.'<br>'.$sql;
		 $sql='DELETE FROM stations WHERE projectid='.$projectid;
		 $log['log'].=$sql."\r\n";
		 if ((!$db->query($sql)))
		  return 'Error while deleting stations within current project: <br>'.$db->error.'<br>'.$sql;
	 }
	 $sql='SELECT id FROM parts WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	 $parts='';
	 while ($row=$result->fetch_assoc())
		 $parts.=$row['id'].',';
	 if ($parts!='')
	 {
	 $parts=substr($parts,0,-1);
	 $sql='DELETE FROM part_cat WHERE part in ('.$parts.')';
	 $log['log'].=$sql."\r\n";
	  if ((!$db->query($sql)))
	  return 'Error while deleting part categories within current project: <br>'.$db->error.'<br>'.$sql;
	 $sql='DELETE FROM part_station WHERE part in ('.$parts.')';
	 $log['log'].=$sql."\r\n";
	  if ((!$db->query($sql)))
	  return 'Error while deleting part to station assignments within current project: <br>'.$db->error.'<br>'.$sql;
	 $sql='DELETE FROM parts WHERE projectid = '.$projectid;
	 $log['log'].=$sql."\r\n";
	  if ((!$db->query($sql)))
	  return 'Error while deleting parts within current project: <br>'.$db->error.'<br>'.$sql;
	 }
	 $sql='DELETE FROM workers WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting workers within the project.'.'<br>'.$sql;
	 $sql='DELETE FROM partcat WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting part categories within the project.'.'<br>'.$sql;
	 $sql='DELETE FROM mmu_project WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting MMU settings within the project.'.'<br>'.$sql;
	 $sql='DELETE FROM avatars WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting avatars within the project.'.'<br>'.$sql;
	 $sql='DELETE FROM tokens WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting project tokens.'.'<br>'.$sql;
	 $sql='DELETE FROM userroles WHERE projectid='.$projectid;
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting project permissions.'.'<br>'.$sql;
	 $sql='DELETE FROM projects WHERE id='.$projectid.' LIMIT 1;';  //finally delete the actual project
	 $log['log'].=$sql."\r\n";
	  if (!($db->query($sql)))
	  return 'Error while deleting project.'.'<br>'.$sql;
     //file_put_contents("log.log",$log['log']); //logging data
	 return 'OK';
	}
  return 'You do not have permissions to delete this project, only project owner can do that.';
 }
 
 function deleteWorker($stationid,$workerid)
 {
	global $db;
	if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
   return 'You do not have permissions to delete any worker.';
  $sql='SELECT count(*) as ile FROM userroles WHERE (role=\'owner\' or role=\'editor\') and projectid='.$_SESSION['projectid'].' and userid='.$_SESSION['userid'];
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
    if ($row['ile']>0)
	{
	 $sql='DELETE FROM highleveltasks WHERE stationid='.$stationid.' and workerid='.$workerid;
	 if ((!$db->query($sql)))
	  return 'Error while deleting high level tasks associated with the current worker: <br>'.$db->error.'<br>'.$sql;
	 $sql='DELETE FROM workers WHERE projectid='.$_SESSION['projectid'].' and stationid='.$stationid.' and id='.$workerid;
	  if (!($db->query($sql)))
	  return 'Error while deleting current worker.'.'<br>'.$sql;
	 return 'OK';
	}
  return 'You do not have permissions to delete any worker from this project.';
 }
 
 function deleteStation($stationid) {
  global $db;
   if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
   return 'You do not have permissions to delete any station.';
  $sql='SELECT count(*) as ile FROM userroles WHERE (role=\'owner\' or role=\'editor\') and projectid='.$_SESSION['projectid'].' and userid='.$_SESSION['userid'];
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
    if ($row['ile']>0)
	{
	 $sql='DELETE FROM highleveltasks WHERE stationid='.$stationid;
	  if ((!$db->query($sql)))
	  return 'Error while deleting high level tasks associated with the current station: <br>'.$db->error.'<br>'.$sql;	  
	 $sql='DELETE FROM stations WHERE projectid='.$_SESSION['projectid'].' and id='.$stationid;
	  if (!($db->query($sql)))
	  return 'Error while deleting current station.'.'<br>'.$sql;
	 return 'OK';	
	}
  return 'You do not have permissions to delete any station from this project.';
 }
 
 function insertSubassemblies($stationid) {
  global $db;	 
  if ($result=$db->query('SELECT id, name FROM `stations` WHERE parent='.$stationid.' and projectid='.$_SESSION['projectid'].' ORDER BY name ASC'))
  {
   echo '<result>OK</result><maintype>-1</maintype><response>';
	while ($row=$result->fetch_assoc())
	echo '<option value="S'.$row['id'].'">'.$row['name'].'</option>';	
   echo '</response>';
  }
  else
  echo '<result>ERROR</result>';
 }
 
 function insertStationsAsParts($stationid) {
  global $db;	 
  if ($result=$db->query('SELECT id, name FROM `stations` WHERE parent=0 and id<>'.$stationid.' and projectid='.$_SESSION['projectid'].' ORDER BY sortorder, name'))
  {
   echo '<result>OK</result><maintype>-3</maintype><response>';
	while ($row=$result->fetch_assoc())
	echo '<option value="S'.$row['id'].'">'.$row['name'].'</option>';	
   echo '</response>';
  }
  else
  echo '<result>ERROR</result>';
 }
  
 function getSubAssemblies($assembly) {
  global $db;
  $sql='SELECT  hlt.`id`, hlt.`stationid`, tt.id as tasktypeid, tt.name as `tasktype`, hlt.`sortorder`, hlt.toolid as toolid, hlt.partid as `partid`, p.name as `partname`, t.name as `toolname`, 0 as positionid, hlt.`positionname`, hlt.`description`, hlt.`esttime`, pc.icon as particon, tc.icon as toolicon, if(tt.icon=\'\',(SELECT icon FROM tasktypes WHERE id=tt.parent),tt.icon) as tticon FROM highleveltasks hlt, tools t, toolcat tc, tool_cat t_c, parts p, tasktypes tt, partcat pc, part_cat p_c WHERE t_c.tool=t.id and t_c.cat=tc.id and p_c.part=hlt.partid and p_c.cat=pc.id and tt.id=hlt.tasktype and p.id=hlt.partid and t.id=hlt.toolid and hlt.stationid='.$assembly.' '.               
  'UNION ALL '.                                                                 
  'SELECT 0, s.id, 0, s.name, s.sortorder, 0, s.mainpart, p.name, \'\', pp.id, pp.name, \'\', \'00:00:00\', pc.icon, \'\', \'\' FROM stations s, parts p, positions pp, partcat pc, part_cat p_c WHERE s.mainpart=p.id and s.position=pp.id and pc.id=p_c.cat and p_c.part=s.mainpart and s.parent='.$assembly.' '.
  'ORDER BY sortorder, id'; 
  $response='';
  if (!($result=$db->query($sql)))
  return '<result>Database query error</result>';

  $i=0;
   while ($row=$result->fetch_assoc())
   {
	$response.='<task'.$i.'>';
	 foreach ($row as $key => $value)
	  if (in_array($key,array('tasktype','partname','toolname','positionname')))
	  $response.='<'.$key.'>'.htmlentities($value).'</'.$key.'>';
	  else	  
	   if ($key=='description')
	   $response.='<'.$key.'>'.str_replace("\n","<br>",htmlentities($value)).'</'.$key.'>';	   
       else		   
	   $response.='<'.$key.'>'.$value.'</'.$key.'>';
	$response.='</task'.$i.'>';
	$i++;
   }
  return '<result>OK</result><count>'.$i.'</count><data>'.$response.'</data>';

 }
 
 function editDescription($projectid,$description) {
  global $db;
   if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
   return 'You do not have permissions to edit this project.';	   
  $sql='SELECT count(*) as ile FROM userroles WHERE role=\'owner\' and projectid='.$projectid.' and userid='.$_SESSION['userid'];
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
    if ($row['ile']>0)
	{
		$sql='UPDATE projects SET description=\''.$db->real_escape_string($description).'\' WHERE id='.$projectid;
		if ($db->query($sql))
		return 'OK';
	}
  return 'You do not have permissions to edit this project.';
 }
 
 function saveNewTaskSettings($taskid, $toolid) {
	 global $db;
	 if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
	 return 'You do not have permissions to save settings.';
	 $sql='INSERT INTO settings (projectid,userid,property,value) '.
	 'VALUES ('.$_SESSION['projectid'].','.$_SESSION['userid'].',\'defaulttask\','.$taskid.'), '.
	 '('.$_SESSION['projectid'].','.$_SESSION['userid'].',\'defaulttool\','.$toolid.') '.
	 'ON DUPLICATE KEY UPDATE value=VALUES(value);';
	 $db->query($sql);
 }
 
 function editName($projectid,$name) {
  global $db;
   if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
   return 'You do not have permissions to edit this project.';	   
  $sql='SELECT count(*) as ile FROM userroles WHERE role=\'owner\' and projectid='.$projectid.' and userid='.$_SESSION['userid'];
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
    if ($row['ile']>0)
	{
		$sql='UPDATE projects SET name=\''.$db->real_escape_string($name).'\' WHERE id='.$projectid;
		if ($db->query($sql))
		return 'OK';
	}
  return 'You do not have permissions to edit this project.';
 }
 
 function delAvatar($avatarid, $workersids) {
	global $db;
	if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
	return '<result>You do not have permissions to edit this project.</result>';
	if (!isUsersProject($_SESSION['projectid']))
	return '<result>You do not have rights to edit this project</result>';
	if (!isUsersAvatar($avatarid))
	return '<result>You do not have rights to remove avatars</result>';
	if (count($workersids)==0)
	$workersids[]=0;
	$sql='SELECT id, name FROM `workers` WHERE avatarid='.$avatarid.' and id not in ('.implode(',',$workersids).');';
	$question='<result><p>Removing this avatar will change avatar to default for the following workers:</p><ul>';
	$ids=[];
	if ($result=$db->query($sql))
		while ($row=$result->fetch_assoc())
		{
			$question.='<li>'.htmlentities($row['name']).'</li>';
			$ids[]=$row['id'];
		}
	$question.='</ul></result>';
	if (count($ids)>0)
		return $question.'<ids>'.implode(',',$ids).'</ids>';
	$sql='DELETE FROM avatars WHERE id='.$avatarid.' and projectid='.$_SESSION['projectid'].' LIMIT 1;';
	$db->query($sql);
	$sql='UPDATE workers SET avatarid=0 WHERE avatarid='.$avatarid.' and projectid='.$_SESSION['projectid'];
	$db->query($sql);
	return '<result>OK</result>';
 }
 
 function addAvatar($name,$age,$height,$weight,$gender) {
	global $db;
	if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
    return 'You do not have permissions to edit this project.';
	if (!isUsersProject($_SESSION['projectid']))
    return 'You do not have rights to edit this project';
	$name='\''.$db->real_escape_string($name).'\'';
	$gender='\''.$db->real_escape_string($gender).'\'';
	$sql='INSERT INTO avatars (`name`, `projectid`, `age`, `height`, `weight`, `gender`) VALUES ('.$name.','.$_SESSION['projectid'].','.$age.','.$height.','.$weight.','.$gender.');';
	if ($db->query($sql))
		if ($db->insert_id>0)
		return 'OK';
	return 'Input data error.';
 }
 
 function updateAvatar($id,$name,$age,$height,$weight,$gender) {
	global $db;
	if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
    return 'You do not have permissions to edit this project.';
	if (!isUsersProject($_SESSION['projectid']))
    return 'You do not have rights to edit this project';
	$name='\''.$db->real_escape_string($name).'\'';
	$gender='\''.$db->real_escape_string($gender).'\'';
	$sql='UPDATE avatars SET `name`='.$name.', `height`='.$height.', `weight`='.$weight.', `age`='.$age.', `gender`='.$gender.' WHERE id='.$id.' and projectid='.$_SESSION['projectid'].' LIMIT 1';
	if ($db->query($sql))
	return 'OK';
	return 'Input data error.';
 } 
 
  function updateStation($id,$name,$main,$avatar,$location) {
	global $db;
	if ((!isset($_SESSION['userid'])) || (!isset($_SESSION['projectid'])))
    return 'You do not have permissions to edit this project.';
	if (!isUsersProject($_SESSION['projectid']))
    return 'You do not have rights to edit this project';
	$name='\''.$db->real_escape_string($name).'\'';
	$sql='UPDATE stations SET `name`='.$name.', `mainpart`='.(substr($main,0,1)=='S'?substr($main,1):$main).', main=\''.(substr($main,0,1)=='S'?'station':'part').'\', `position`='.$location.', `avatarid`='.$avatar.' WHERE id='.$id.' and projectid='.$_SESSION['projectid'].' LIMIT 1';
	if ($db->query($sql))
	return 'OK';
	return 'Input data error.';
 } 
 
 if (connectDB()==false) exit;
 
 if (isset($_POST['action']))
 if (isset($_POST['action']))
 {
  if ($_POST['action']=='addTask')
   if (isset($_POST['stationid']) && isset($_POST['type']) && isset($_POST['part']) && isset($_POST['tool']) && isset($_POST['time']) && isset($_POST['position']) && isset($_POST['desc']))
	if (ctype_digit($_POST['type']) && array_part_or_assembly($_POST['part'])  && ctype_digit($_POST['tool']))
	{
		if (is_array($_POST['part']))
		echo '<result>'.addMultipleTasks($_POST['part']).'</result>';
		else
		echo '<result>'.addTask($_POST['part']).'</result>';
	}
	else 
		echo '<result>Wrong input data format.</result>';

  if ($_POST['action']=='delTask')
   if (isset($_POST['task_ids']))
   echo '<result>'.delTask().'</result>';

  if ($_POST['action']=='moveTask')
   if (isset($_POST['task_ids']) && isset($_POST['tostation']) && isset($_POST['subassembly_id']) && isset($_POST['fromstation']) && ctype_digit($_POST['tostation'])) 
    echo '<result>'.moveTask().'</result>';

  if ($_POST['action']=='getSubTypes')
   if (isset($_POST['maintype']) && ctype_digit($_POST['maintype']))
   {
	echo '<result>OK</result><maintype>'.$_POST['maintype'].'</maintype><response>';
	insertSubTypes($_POST['maintype']);
    echo '</response>';
   }

  if (($_POST['action']=='getSubParts') && isset($_POST['maintype']))
   if (ctype_digit($_POST['maintype']))
   {
	echo '<result>OK</result><maintype>'.$_POST['maintype'].'</maintype><response>';
	insertParts($_POST['maintype']);
    echo '</response>';	
   } 	
   else
	 switch ($_POST['maintype'])
	 {
	   case -4: //Markers WalkTargets
	    echo '<result>OK</result><maintype>'.$_POST['maintype'].'</maintype><response>';
		insertWalkTargetMarkers();
		echo '</response>';
	   break;
	   case -3: //stations
	    if (isset($_POST['stationid']) && ctype_digit($_POST['stationid']))
		insertStationsAsParts($_POST['stationid']);
	   break;
	   case -2:	
	    echo '<result>OK</result><maintype>'.$_POST['maintype'].'</maintype><response>';
	    insertUncategorizedParts();
	    echo '</response>';	
       break;
	   case -1:
        if (isset($_POST['stationid']) && ctype_digit($_POST['stationid'])) //subassemblies
        insertSubassemblies($_POST['stationid']);
		break;
	 }
   
  if (($_POST['action']=='getSubTools') && isset($_POST['maintype']))
   if (ctype_digit($_POST['maintype']))
   {
	echo '<result>OK</result><maintype>'.$_POST['maintype'].'</maintype><response>';
	insertTools($_POST['maintype']);
    echo '</response>';	
   }
   else
	if ($_POST['maintype']==-2)
    {
	 echo '<result>OK</result><maintype>'.$_POST['maintype'].'</maintype><response>';
	 insertUncategorizedTools();
     echo '</response>';	
    }
 
  if ($_POST['action']=='addProject')                                               
   if (isset($_POST['name']) && isset($_POST['desc']))	 
   addProject($_POST['name'],$_POST['desc']);	

  if ($_POST['action']=='editUserRole')
   if (isset($_POST['userid']) && isset($_POST['projectid']) && isset($_POST['newrole']))
	if (ctype_digit($_POST['userid']) && ctype_digit($_POST['projectid']) && in_array($_POST['newrole'],array('owner','editor','viewer','reviewer')))
    editUserRole($_POST['userid'],$_POST['projectid'],$_POST['newrole']);	   

  if ($_POST['action']=='editUserAdminRole')
  {
   if (isset($_POST['userid']) && ($_POST['userid']!=$_SESSION['userid']))
   {
    if (isset($_POST['newadminroles']))
    editUserAdminRole($_POST['userid'],$_POST['newadminroles']);
    else
	editUserAdminRole($_POST['userid'],array());
   }
  }

  if ($_POST['action']=='searchUser')
   if (isset($_POST['search']) && isset($_SESSION['userid']))
   searchUser($_POST['search']); 	   

  if ($_POST['action']=='addUserToProject')
   if (isset($_POST['userid']) && isset($_POST['projectid']) && isset($_SESSION['userid']))
	if (ctype_digit($_POST['userid']) && ctype_digit($_POST['projectid']))   
	addUserToProject($_POST['userid'],$_POST['projectid']);	

  if ($_POST['action']=='addStation')
   if (isset($_POST['name']))
   echo '<result>'.addStation($_POST['name']).'</result>';

  if ($_POST['action']=='addSubStation')
   if (isset($_POST['name']) && isset($_POST['parentstation']) && isset($_POST['mainpart']) && isset($_POST['position']))
   echo '<result>'.addSubStation(trim($_POST['name']),$_POST['parentstation'],$_POST['mainpart'],$_POST['position']).'</result>';
  
  if ($_POST['action']=='removeUserFromProject')
   if (isset($_POST['userid']) && isset($_POST['projectid']))
	if (ctype_digit($_POST['userid']) && ctype_digit($_POST['projectid']))
    removeUserFromProject($_POST['userid'],$_POST['projectid']);
  
  if ($_POST['action']=='addMarker')
   if (isset($_POST['name']) && (trim($_POST['name'])!='') &&
       isset($_POST['type']) && (trim($_POST['type'])!=''))
   echo '<result>'.addMarker(trim($_POST['name']),trim($_POST['type'])).'</result>';
  
  if ($_POST['action']=='addPart')
   if (isset($_POST['name']) && (trim($_POST['name'])!=''))
   echo '<result>'.addPart(trim($_POST['name'])).'</result>';
  
  if ($_POST['action']=='addPartCat')
   if (isset($_POST['name']) && (trim($_POST['name'])!=''))
   echo '<result>'.addPartCat(trim($_POST['name'])).'</result>';
   
  if ($_POST['action']=='addToolCat')
   if (isset($_POST['name']) && (trim($_POST['name'])!=''))
   addToolCat(trim($_POST['name']));	
  
  if ($_POST['action']=='reorderMMUs')
   if (isset($_POST['neworder']))
   reorderMMUs($_POST['neworder']);
  
  if ($_POST['action']=='reorderPartCat')
   if (isset($_POST['neworder']))
   reorderPartCat($_POST['neworder']);
  
  if ($_POST['action']=='reorderToolCat')
   if (isset($_POST['neworder']))
   reorderToolCat($_POST['neworder']);	

  if ($_POST['action']=='reorderStations')
   if (isset($_POST['neworder']))
   reorderStations($_POST['neworder']);	

  if ($_POST['action']=='delPartCat')
   if (isset($_POST['id']) && ctype_digit($_POST['id']))
   delPartCat($_POST['id']); 	   

  if ($_POST['action']=='delToolCat')
   if (isset($_POST['id']) && ctype_digit($_POST['id']))
   delToolCat($_POST['id']); 	   
 
  if ($_POST['action']=='delMMU')
   if (isset($_POST['id']) && ctype_digit($_POST['id']))
   {
	include "mmu-functions.php"; //most mmu related function
	if (isMMUManager())                            
	{
		$result=delMMU($_POST['id']);
		if ($result[0])
			echo '<result>OK</result>';
		else
			echo '<result>ERROR</result><msg>MMU cannot be deleted, file access error</msg>';
	}
	else
	  echo '<result>ERROR</result><msg>You need MMU library manager rights to perform this action.</msg>';
   }
	
  if ($_POST['action']=='delAvatar')
   if (isset($_POST['avatarid']) && ctype_digit($_POST['avatarid']))
   {
	   if (isset($_POST['workersids']) && ctype_digit(implode('',$_POST['workersids'])))
		echo delAvatar($_POST['avatarid'], $_POST['workersids']);
		else
		echo delAvatar($_POST['avatarid'], array());
   }

  if ($_POST['action']=='delStation')
   if (isset($_POST['id']) && ctype_digit($_POST['id']))
   delStation($_POST['id']);

 if ($_POST['action']=='addTool')
   if (isset($_POST['name']) && (trim($_POST['name'])!=''))
   addTool(trim($_POST['name']));	

 if ($_POST['action']=='delPart')
   if (isset($_POST['id']) && ctype_digit($_POST['id']) &&
       isset($_POST['cat']) && ctype_digit($_POST['cat']))
   delPart($_POST['id'],$_POST['cat']);	

 if ($_POST['action']=='delTool')
   if (isset($_POST['id']) && ctype_digit($_POST['id']) &&
       isset($_POST['cat']) && ctype_digit($_POST['cat']))
   delTool($_POST['id'],$_POST['cat']);	

 if ($_POST['action']=='reorderParts')
   if (isset($_POST['neworder']))
   reorderParts($_POST['neworder']);	  

 if ($_POST['action']=='reorderPartsToStations')
   if (isset($_POST['neworder']))
   reorderPartsToStations($_POST['neworder']);	  
   
 if ($_POST['action']=='reorderTools')
   if (isset($_POST['neworder']))
   reorderTools($_POST['neworder']);	

 if ($_POST['action']=='reorderTaskList')
   if (isset($_POST['neworder']))
   echo '<result>'.reorderTaskList($_POST['neworder']).'</result>';	

 if (($_POST['action']=='enableMMU') || ($_POST['action']=='disableMMU'))
   if (isset($_POST['id']) && ctype_digit($_POST['id']) &&
	   isset($_POST['projectid']) && ctype_digit($_POST['projectid']))
	if (($_POST['projectid']==$_SESSION['projectid']) || isUsersProject($_POST['projectid']))
   setEnableMMU($_POST['id'],$_POST['projectid'],$_POST['action']);
 
  if ($_POST['action']=='setDefaultPartCat')
   if (isset($_POST['id']) && ctype_digit($_POST['id']) &&
	   isset($_POST['catid']) && ctype_digit($_POST['catid']))
   setDefaultPartCat($_POST['id'],$_POST['catid']); 	

  if ($_POST['action']=='setDefaultToolCat')
   if (isset($_POST['id']) && ctype_digit($_POST['id']) &&
	   isset($_POST['catid']) && ctype_digit($_POST['catid']))
   setDefaultToolCat($_POST['id'],$_POST['catid']); 	
   
  if ($_POST['action']=='clickTaskType')
   if (isset($_POST['taskid']) && isset($_POST['operationid']))
    if (ctype_digit($_POST['taskid']) && ctype_digit($_POST['operationid']))	
    clickTaskType($_POST['taskid'],$_POST['operationid']);		

  if ($_POST['action']=='clickPart')
   if (isset($_POST['taskid']) && isset($_POST['partid']))
    if (ctype_digit($_POST['taskid']) && ctype_digit($_POST['partid']))	
    clickPart($_POST['taskid'],$_POST['partid']);		
  
  if ($_POST['action']=='clickTool')
   if (isset($_POST['taskid']) && isset($_POST['toolid']))
    if (ctype_digit($_POST['taskid']) && ctype_digit($_POST['toolid']))	
    clickTool($_POST['taskid'],$_POST['toolid']);		
  
  if ($_POST['action']=='changeToolCatIcon')
   if (isset($_POST['toolcat']) && ctype_digit($_POST['toolcat']) &&
       isset($_POST['icon']))	  
   echo changeToolCatIcon($_POST['toolcat'],$_POST['icon']);
   else
   echo '<result>Incomplete form</result>';	   

  if ($_POST['action']=='changePartCatIcon')
   if (isset($_POST['partcat']) && ctype_digit($_POST['partcat']) &&
       isset($_POST['icon']))	  
   echo changePartCatIcon($_POST['partcat'],$_POST['icon']);
   else
   echo '<result>Incomplete form</result>';	   
  
  if ($_POST['action']=='getTask')
   if (isset($_POST['taskid']) && ctype_digit($_POST['taskid']))
   getTask($_POST['taskid']);
 
 if ($_POST['action']=='updateTask') 
  if (isset($_POST['taskid']) && ctype_digit($_POST['taskid']) &&
      isset($_POST['operationid']) && ctype_digit($_POST['operationid']) &&
	  isset($_POST['partid']) && ctype_digit($_POST['partid']) &&
	  isset($_POST['toolid']) && ctype_digit($_POST['toolid']) &&
	  isset($_POST['description']) && isset($_POST['time']))	 
  echo updateTask($_POST['taskid'],$_POST['operationid'],$_POST['partid'],$_POST['toolid'],$_POST['description'],$_POST['time']);	  

 //chunk file upload

 if (($_POST['action']=='addMMU') && isset($_FILES['chunk']) && isset($_POST['chunknum']) && isset($_POST['fileID']) && isset($_POST['chunkend']) && isset($_POST['TotalSize']) && ctype_digit($_POST['chunknum']) && ctype_digit($_POST['chunkend']) && ctype_digit($_POST['TotalSize']))
 { //TODO: add user right checking before commencing with upload
	 include_once 'mmu-functions.php';
	 uploadMMU();
 }

 //full file upload - not needed anymore
 if (($_POST['action']=='addMMU') && isset($_FILES['mmu']))
 {
	 echo '<p>Checking mmu upload';
	 if (is_uploaded_file($_FILES['mmu']['tmp_name']))
	 {
	  echo 'MMU file is uploaded: '.$_FILES['mmu']['name'];
	  $zip = new ZipArchive;
	  if ($zip->open($_FILES['mmu']['tmp_name']) === TRUE) {
		  $fileID=$zip->locateName('description.json',ZipArchive::FL_NOCASE|ZipArchive::FL_NODIR);
		  echo "\r\n<p>Zip file ID: ".$fileID;
		  if ($fileID===false)
		  echo "\r\n<p>Description file cannot be found";
	      else
		  {
			$desc=json_decode($zip->getFromIndex($fileID),true);
			$lasterror=json_last_error();
			echo "\r\n<p>Json last error (0==success): ".$lasterror."\r\n";
			if ($lasterror==0)
			{
				$desc['ID']="'".$db->real_escape_string(trim($desc['ID']))."'";
				$desc['Name']="'".$db->real_escape_string(trim($desc['Name']))."'";
				$desc['Author']="'".$db->real_escape_string(trim($desc['Author']))."'";
				$desc['MotionType']="'".$db->real_escape_string(trim($desc['MotionType']))."'";
				$desc['Version']="'".$db->real_escape_string(trim($desc['Version']))."'";
				$desc['LongDescription']="'".$db->real_escape_string(trim($desc['LongDescription']))."'";
				$desc['ShortDescription']="'".$db->real_escape_string(trim($desc['ShortDescription']))."'";
				$sql='SELECT count(id) as ile FROM mmus WHERE vendorID='.$desc['ID'].';';
				if ($result=$db->query($sql))
				{
					if ($row=$result->fetch_assoc())
					{
					 if (intval($row['ile'])==0)
					 {
						$mmuUpload=file_get_contents($_FILES['mmu']['tmp_name']);
						$sql='INSERT INTO `mmus`(`name`, `author`, `vendorID`, `motiontype`, `version`, `longdescription`, `shortdescription`, `package`) VALUES '.
						'('.$desc['Name'].','.$desc['Author'].','.$desc['ID'].','.$desc['MotionType'].','.$desc['Version'].','.$desc['LongDescription'].','.$desc['ShortDescription'].',0x'.bin2hex($mmuUpload).');';
						if ($db->query($sql))
						echo '<result>Success</result>';
					 }
					 else
						 echo '<result>MMU already in library</result>';
					}
					else
						echo '<result>Database error 2</result><sql>'.$sql.'</sql>';
				}
				else
				echo '<result>Database error 1</result><sql>'.$sql.'</sql>';
			}
		  }
       $zip->close();
      }
	  else 
       echo '<p>Zip failed';
	 }
 }
  
 if (($_POST['action']=='uploadIcons') && isset($_FILES['icon']))
 {
  for ($i=0; $i<count($_FILES['icon']['name']); $i++)                       
   if (in_array($_FILES['icon']['type'][$i],array('image/jpeg','image/png','image/svg+xml')))
   {
	$j=0;   
	do {  
     $saveas=strtolower(substr(pathinfo($_FILES['icon']['name'][$i],PATHINFO_FILENAME),0,20));	                                 
	  if ($j==0)
	  $saveas='icons/'.$saveas.'.'.strtolower(pathinfo($_FILES['icon']['name'][$i],PATHINFO_EXTENSION));	  
	  else	  
	  $saveas='icons/'.$saveas.'_'.$j.'.'.strtolower(pathinfo($_FILES['icon']['name'][$i],PATHINFO_EXTENSION));
	 $j++;
	} while (file_exists($saveas)); 
	if (move_uploaded_file($_FILES['icon']["tmp_name"][$i], $saveas))   
	echo '<icon'.$i.'>'.$saveas.'</icon'.$i.'>';
    else
	echo '<icon'.$i.'>ERR</icon'.$i.'>';	
   }	     
  echo '<result>OK</result><count>'.count($_FILES['icon']['name']).'</count>';
 }

 if (($_POST['action']=='deleteProject') && isset($_POST['projectid']) &&
     isset($_POST['codetyped']) && isset($_POST['codeset']) &&
     ctype_digit($_POST['projectid']) && isset($_POST['codetyped']) &&
     ctype_digit($_POST['codeset']))
	 {
	  if ($_POST['codeset']!=$_POST['codetyped'])
		 echo '<result>Confirmation code mismatch.</result>';
	  else
		 echo '<result>'.deleteProject($_POST['projectid']).'</result>';
	 }

 if (($_POST['action']=='deleteWorker') && isset($_POST['stationid']) &&
     ctype_digit($_POST['stationid']) && isset($_POST['workerid']) &&
     ctype_digit($_POST['workerid']))
 echo '<result>'.deleteWorker($_POST['stationid'],$_POST['workerid']).'</result>';

 if (($_POST['action']=='deleteStation') && isset($_POST['stationid']) &&
     ctype_digit($_POST['stationid']))
 echo '<result>'.deleteStation($_POST['stationid']).'</result>';
 
 if (($_POST['action']=='taskIDNumberToggle') && isset($_POST['value']) && in_array($_POST['value'],array('0','1')))
 {
  $_SESSION['showTaskID']=($_POST['value']=="1");
  echo '<result>OK</result>';
 }
 
 if (($_POST['action']=='getSubAssemblies') && isset($_POST['assembly']) && ctype_digit($_POST['assembly']))
 echo getSubAssemblies($_POST['assembly']);
 
 if (($_POST['action']=='getAccessToken') && isset($_POST['projectid']) && ctype_digit($_POST['projectid']))
 echo getAccessToken($_POST['projectid']);	 
 
 if (($_POST['action']=='editProjectDescription') && isset($_POST['projectid']) && ctype_digit($_POST['projectid']) && isset($_POST['description']))
 echo '<result>'.editDescription($_POST['projectid'],$_POST['description']).'</result>';

 if (($_POST['action']=='editProjectName') && isset($_POST['projectid']) && ctype_digit($_POST['projectid']) && isset($_POST['name']))
 echo '<result>'.editName($_POST['projectid'],$_POST['name']).'</result>';
 
 if (($_POST['action']=='cloneTasks') && isset($_POST['tasks']))
 echo cloneTasks($_POST['tasks']);

 if (($_POST['action']=='addAvatar') && isset($_POST['name']) && isset($_POST['height']) && isset($_POST['weight']) && isset($_POST['gender']) && isset($_POST['age']))
  if ((trim($_POST['name'])!='') && ctype_digit($_POST['height']) && ctype_digit($_POST['weight']) && ctype_digit($_POST['age']) && ($_POST['age']>0) && ($_POST['age']<130))
  echo '<result>'.addAvatar(trim($_POST['name']),trim($_POST['age']),trim($_POST['height']),trim($_POST['weight']),trim($_POST['gender'])).'</result>';

 if (($_POST['action']=='updateAvatar') && isset($_POST['id']) && isset($_POST['name']) && isset($_POST['height']) && isset($_POST['age']) && isset($_POST['weight']) && isset($_POST['gender']))
  if ((trim($_POST['name'])!='') && ctype_digit($_POST['id']) && ctype_digit($_POST['height']) && ctype_digit($_POST['weight']) && ctype_digit($_POST['age']) && ($_POST['age']>0) && ($_POST['age']<130))
  echo '<result>'.updateAvatar(trim($_POST['id']),trim($_POST['name']),trim($_POST['age']),trim($_POST['height']),trim($_POST['weight']),trim($_POST['gender'])).'</result>';
 
 
  if (($_POST['action']=='updateStation') && isset($_POST['id']) && isset($_POST['name']) && isset($_POST['avatarid']) && isset($_POST['mainid']) && isset($_POST['location']))
	if ((trim($_POST['name'])!='') && ctype_digit($_POST['id']) && ctype_digit($_POST['avatarid']) && ctype_digit($_POST['location']) && 
     (ctype_digit($_POST['mainid']) || ((substr($_POST['mainid'],0,1)=='S') && ctype_digit(substr($_POST['mainid'],1)))))
     echo '<result>'.updateStation($_POST['id'],$_POST['name'],$_POST['mainid'],$_POST['avatarid'],$_POST['location']).'</result>';
 
  if (($_POST['action']=='addWorker') && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['station']) && isset($_POST['avatar']))
  echo addWorker($_POST['name'], $_POST['description'], $_POST['station'], $_POST['avatar'], $_SESSION['projectid']);

  if (($_POST['action']=='editWorker') && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['id']) && isset($_POST['avatar']))
  echo editWorker($_POST['name'], $_POST['description'], $_POST['id'], $_POST['avatar'], $_SESSION['projectid']);
 
 //user management
 if (($_POST['action']=='updateUser') && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['passcurrent']) && isset($_POST['passnew']))
 echo updateUser($_POST['name'],$_POST['email'],$_POST['passcurrent'],$_POST['passnew']);

 if (($_POST['action']=='addUser') && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['passnew']))
 echo addUser($_POST['name'],$_POST['email'],$_POST['passnew']);

 if (($_POST['action']=='deleteUser') && isset($_POST['id']) && ctype_digit($_POST['id']))
	 if ($_POST['id']!=$_SESSION['userid'])
     echo deleteUser($_POST['id']);
     else
	 echo '<result>ERR</result><message>You cannot delete own account!</message>';	 

 if (($_POST['action']=='activateUser') && isset($_POST['id']) && ctype_digit($_POST['id']))
 {
  if ($_POST['id']!=$_SESSION['userid'])
  echo activateUser($_POST['id'],1);
  else
  echo '<result>ERR</result><message>You cannot activate own account!</message>';
 }

 if (($_POST['action']=='deactivateUser') && isset($_POST['id']) && ctype_digit($_POST['id']))
 {
  if ($_POST['id']!=$_SESSION['userid'])
  echo activateUser($_POST['id'],0);
  else
  echo '<result>ERR</result><message>You cannot deactivate own account!</message>';
 }
 //end user management
 
 //project management
 if (($_POST['action']=='saveNewTaskSettings') && isset($_POST['task']) && isset($_POST['tool']) && ctype_digit($_POST['task']) && ctype_digit($_POST['tool']))
	 saveNewTaskSettings($_POST['task'], $_POST['tool']);
 
  if (($_POST['action']=='duplicateProject') && isset($_POST['id']) && isset($_POST['cloneList']) && isset($_POST['cloneName']))
  cloneProject($_POST['id'],$_POST['cloneName'],$_POST['cloneList']);
 //end project management
 
}

if (isset($_GET['date']))
	echo md5(date("Ymd").rand(0,2000)); //just for tests remove later     

if (isset($log))
echo '<log name="'.$log['info'].'">'.$log['log'].'</log>';

?>

</body>
</html>