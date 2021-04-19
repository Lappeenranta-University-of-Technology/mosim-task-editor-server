<?php
 session_start();
 
 include('db.php');
 
 if (!connectDB())
 exit();

//functions
 
 function camel($str) {
  return $str; //switch off this function
  $str=mb_convert_case(trim($str),MB_CASE_TITLE,'UTF-8');
  return str_replace(' ','',$str);
 }
 
 function deCamel($str) {
	 return $str; //switch off this function
  $out='';
  for ($i=0; $i<mb_strlen($str,'UTF-8'); $i++)
  {
   if (($i>0) && (preg_match('~^\p{Lu}~u', mb_substr($str,$i,1,'UTF-8'))))
   $out.=' ';
   $out.= mb_substr($str,$i,1,'UTF-8');
  }
  return $out;
 }
 
 function stationInCurrentProject($station)
 {
	global $db;
	$sql='SELECT count(*) as ile FROM userroles ur, stations s WHERE ur.userid='.
	$_SESSION['userid'].' and s.id='.$station.' and ur.projectid=s.projectid;';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return ($row['ile']>0);
  return false;
 }
 
 function tokenToProjectId($token) {
  global $db;
  $sql='SELECT userid, projectid FROM tokens WHERE token=\''.$db->real_escape_string($token).'\';';  
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return $row['projectid'];
  return 0;
 }
 
 function tokenToProjectIdAndRoleCheck($token,$role) {
  global $db;
  $sql='SELECT t.userid, t.projectid FROM tokens t, adminroles a WHERE a.role=\''.$role.'\' and a.userid=t.userid and t.token=\''.$db->real_escape_string($token).'\';';  
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return $row['projectid'];
  return 0;
 }
 
 function tokenToProjectIdAndName($token) {
  global $db;
  $sql='SELECT tokens.userid, projects.id, projects.name FROM tokens, projects WHERE projects.id=tokens.projectid and token=\''.$db->real_escape_string($token).'\';';  
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return $row;
  return array('userid'=>0, 'id'=>0, 'name'=>'');
 }
 
 function tokenAndPartToProjectID($token,$part)
 {
	global $db;
	$sql='SELECT t.projectid FROM tokens t, `parts` p  WHERE t.token=\''.$db->real_escape_string($token).'\' and p.id='.$part.' and p.projectid=t.projectid LIMIT 1';
	if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return $row['projectid'];
	return 0;
 }

 function getNextResultSet($token)
 {
  global $db;
  $projectid=tokenToProjectId($token);
  if ($projectid==0)
	  return array('status'=>false,'msg'=>'No project found for given token');
  $sql='SELECT IFNULL(MAX(resultset),0)+1 as nextid FROM mmutasks WHERE projectid='.$projectid;
  if ($result=$db->query($sql))
	  if ($row=$result->fetch_assoc())
		  return array('status'=>true,'msg'=>$row['nextid']);
  return array('status'=>false,'msg'=>'Database access error');
 }
 
 function saveMMUList($token,$data)
 {
  global $db;
  $projectid=tokenToProjectId($token);
  if ($projectid==0)
	  return 'No project found for given token';
  if (is_string($data))
   $data=json_decode($data,true); //decodig into array
  
  if (!array_key_exists('ID',$data) || !array_key_exists('Name',$data) || 
      !array_key_exists('MotionType',$data) || !array_key_exists('Properties',$data) || 
	  !array_key_exists('Constraints',$data) || !array_key_exists('StartCondition',$data) || 
	  !array_key_exists('EndCondition',$data) || !array_key_exists('SortOrder',$data) || 
	  !array_key_exists('ResultSet',$data) || !array_key_exists('Success',$data)) 
	  return 'Incomplete parameter set. Missing: '.
		 (!array_key_exists('ID',$data)?'ID ':'').
		 (!array_key_exists('Name',$data)?'Name ':'').
		 (!array_key_exists('MotionType',$data)?'MotionType ':'').
		 (!array_key_exists('Properties',$data)?'Properties ':'').
		 (!array_key_exists('Constraints',$data)?'Constraints ':'').
		 (!array_key_exists('StartCondition',$data)?'StartCondition ':'').
		 (!array_key_exists('EndCondition',$data)?'EndCondition ':'').
		 (!array_key_exists('SortOrder',$data)?'SortOrder ':'').
		 (!array_key_exists('ResultSet',$data)?'ResultSet ':'').
		 (!array_key_exists('Success',$data)?'Success ':'');
  
  if (in_array($data['ID'],array(NULL,'null'))) $data['ID']='';
  if (in_array($data['Name'],array(NULL,'null'))) $data['Name']='';  
  if (in_array($data['Properties'],array(NULL,'null'))) $data['Properties']='';
  if (in_array($data['Constraints'],array(NULL,'null'))) $data['Constraints']='';
  if (in_array($data['StartCondition'],array(NULL,'null'))) $data['StartCondition']='';
  if (in_array($data['EndCondition'],array(NULL,'null'))) $data['EndCondition']='';
  $mmuid='\''.$db->real_escape_string($data['ID']).'\'';
  $name='\''.$db->real_escape_string($data['Name']).'\'';
  $motiontype='\''.$db->real_escape_string($data['MotionType']).'\'';
  $properties='\''.$db->real_escape_string($data['Properties']!=''?json_encode($data['Properties']):'').'\'';
  $constraints='\''.$db->real_escape_string($data['Constraints']!=''?json_encode($data['Constraints']):'').'\'';
  $start='\''.$db->real_escape_string($data['StartCondition']).'\'';
  $end='\''.$db->real_escape_string($data['EndCondition']).'\'';
   if (!ctype_digit(strval($data['SortOrder'])))
	   return 'Incorrect SortOrder parameter value. Must be unsigned integer';
   if (!ctype_digit(strval($data['ResultSet'])))
	   return 'Incorrect ResultSet parameter value. Must be unsigned integer';
   if (!in_array($data['Success'],array(-1,0,1)))
	   return 'Incorrect Success parameter value. Must be -1, 0, or 1 for undetermined, failure, and success respectively.';
  $sortorder=$data['SortOrder'];
  $resultset=$data['ResultSet'];
  $success=$data['Success'];
  
  $sql='INSERT INTO `mmutasks`(`mmuid`, `name`, `motiontype`, `properties`, `constraints`, `startrule`, `endrule`, `projectid`, `sortorder`, `resultset`, `success`) VALUES ('.$mmuid.','.$name.','.$motiontype.','.$properties.','.$constraints.','.$start.','.$end.','.$projectid.','.$sortorder.','.$resultset.','.$success.')';
  //TODO: add checking if exactly the same entry is not already in the database before inserting.
  if ($db->query($sql))
	  return 'OK';
  else
	  return 'ERR';
 }
 
 function getSettings($token)
 {
	 if (tokenToProjectId($token)>0)
	 {
		 $canmanage=(tokenToProjectIdAndRoleCheck($token,'MMU Library manager')>0?"True":"False");
		 include_once('config.php');
		 
		 return '<chunkSize>'.$settings['chunkSize'].'</chunkSize>'.
				'<canDownload>True</canDownload>'.
				'<canUpload>'.$canmanage.'</canUpload>'.
				'<canRemove>'.$canmanage.'</canRemove>'; //TODO: user rights should be taken from database and detemined by the provided token
	 }
	 return false;
 }
 
 function getMMUList($token)
 {
  global $db;
  $projectid=tokenToProjectId($token);
   if ($projectid!=0)
   {
    $sql='SELECT mmus.id as url, mmus.vendorID, mmus.name, mmus.version, ifnull(mp.sortorder,0) as sortorder, ifnull(mp.enabled,1) as enabled FROM `mmus` LEFT JOIN mmu_project mp ON (mp.mmuid=mmus.id and mp.projectid='.$projectid.') ORDER BY vendorID;';
    $mmus=[];
	$url=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .'://'.$_SERVER['HTTP_HOST'];
	if (substr($url,-1)!='/')
		$url.='/';
	$p=strripos($_SERVER['PHP_SELF'],'/');
	  if ($p!==false)
		  $url.=substr($_SERVER['PHP_SELF'],0,$p+1);
	 $url.='api.php?action=downloadMMU&id=';
     if ($result=$db->query($sql))
     {
	  while ($row=$result->fetch_assoc())  
	  {
	   //$row['url']=$url.$row['url'];
	   $row['sortorder']=intval($row['sortorder']);
	   $row['enabled']=($row['enabled']=='1');
	   $mmus[]=$row;
	  }
      return json_encode($mmus); //returning list of MMUs with enable and sortorder taken from specific project   
     }
   }
   return false;
 }
 
 function addCadFromScene() {
  global $db;
  $projectid=tokenToProjectId($_POST['token']);  
  if ($projectid==0)
  return '<result>Error - insufficient user privileges</result>';
  $data=$db->real_escape_string('T='.$_POST['transform'][0].'#R='.$_POST['rotation'][0].'#S='.$_POST['scale'][0].'#v='.$_POST['vertices'][0].'#t='.$_POST['triangles'][0]);
  $sql='UPDATE parts SET cad=\''.$data.'\' WHERE id='.$_POST['partid'].' and projectid='.$projectid;
   if ($db->query($sql))
   {
	$reply="OK";
    return json_encode($reply);   
   }
  $reply="Error";
  return json_encode($reply);
 }
 
 function addCad_glTF_FromScene() {
  global $db;
  $projectid=tokenToProjectId($_POST['token']);
  if ($projectid==0)
  return json_encode('Error - insufficient user privileges');
  $data=$db->real_escape_string($_POST['glTF']);
  $sql='UPDATE parts SET cad=\''.$data.'\' WHERE id='.$_POST['partid'].' and projectid='.$projectid;
   if ($db->query($sql))
   {
	$reply="OK";
    return json_encode($reply);
   }
  $reply="Error";
  return json_encode($reply);
 }
 /*
 function getCadForPart($token,$partid) {
  global $db;
  $projectid=tokenToProjectId($token);  
  if ($projectid==0)
  return '<result>Error - insufficient user privileges</result>';
  $sql='SELECT cad FROM parts WHERE id='.$partid.' and projectid='.$projectid;
  if ($result=$db->query($sql))
   if ($row=$result->fetch_assoc())
   {
	 $result=array();
	 $data=explode('#',$row['cad']);
	 $names = array('T'=>'position','R'=>'rotation','S'=>'scale','v'=>'vertices','t'=>'triangles');
	 for ($i=0; $i<count($data); $i++)
	 {
		 $kv=explode('=',$data[$i]);
		 if (in_array($kv[0],array('T','S')))
		 {
		  $xyz=explode(',',$kv[1]);
		  $result[$names[$kv[0]]] = array('x'=>floatval($xyz[0]),'y'=>floatval($xyz[1]),'z'=>floatval($xyz[2]));	 
		 }
		 if (in_array($kv[0],array('R')))
		 {
		  $xyz=explode(',',$kv[1]);
		  $result[$names[$kv[0]]] = array('x'=>floatval($xyz[0]),'y'=>floatval($xyz[1]),'z'=>floatval($xyz[2]),'w'=>floatval($xyz[3]));	 
		 }
		 if (in_array($kv[0],array('v')))
		 {
		  $v=explode(';',$kv[1]);
		  for ($j=0; $j<count($v); $j++)
		  {
		   $xyz=explode(',',$v[$j]);
		   $result[$names[$kv[0]]][$j] = array('x'=>floatval($xyz[0]),'y'=>floatval($xyz[1]),'z'=>floatval($xyz[2]));
		  }
		 }
		 if (in_array($kv[0],array('t')))
		 {
		  $v=explode(';',$kv[1]);
		  for ($j=0; $j<count($v); $j++)
		  {			  
		   $result[$names[$kv[0]]][$j] = intval($v[$j]);
		  }
		 }
	 }
	return json_encode($result); 
   }
  $reply="Error";
  return json_encode($reply);
 }
 */
 function getCadForPart($token,$partid) {
  global $db;
  $projectid=tokenToProjectId($token);  
  if ($projectid==0)
  return json_encode('Error - insufficient user privileges');
 //update the sql statement now it should be correct
  $sql='SELECT cad FROM parts WHERE id='.$partid.' and projectid='.$projectid.' and cad is not null;';
  if ($result=$db->query($sql))
  {
	if ($result->num_rows==0)
		return json_encode("no cad data");
	else
	 if ($row=$result->fetch_assoc())
	 return $row['cad']; 
  }
  $reply="Error";
  return json_encode($reply);
 }
 
 function syncSceneToDB($token) {
	global $db;
	$projectid=tokenToProjectId($token);
	if ($projectid==0)
	return '<result>Error - insufficient user privileges</result>';
	$sql='DELETE FROM `scene_temp` WHERE project id='.$projectid.' and savename=\'current\';';
	$db->query($sql);
	$sql='INSERT INTO `scene_temp`(`id`, `engineid`, `parent`, `station` , `name`, `type`, `projectid`) VALUES ';
	var_dump($_POST);
	if (!(isset($_POST['names']) && isset($_POST['IDs']) && isset($_POST['MMIIDs']) && isset($_POST['parents']) && isset($_POST['stations']) && isset($_POST['types']) &&
      (count($_POST['names'])==count($_POST['IDs'])) &&
	  (count($_POST['names'])==count($_POST['MMIIDs'])) &&
	  (count($_POST['names'])==count($_POST['stations'])) &&
	  (count($_POST['names'])==count($_POST['types'])) &&
	  (count($_POST['names'])==count($_POST['parents']))))
	return '<result>Scene sync: Dataset error</result>';
	
	$types=getEnumValues('scene_temp','type');
	$itemscount=0;
	//loop over all components and add them to the insert sql statement.
	for ($i=0; $i<count($_POST['IDs']); $i++)
	{
		$_POST['names'][$i]=$db->real_escape_string($_POST['names'][$i]);
		if (in_array($_POST['types'][$i],$types) &&
			ctype_digit(strval($_POST['IDs'][$i])) &&
			ctype_digit(strval($_POST['MMIIDs'][$i])) &&
			ctype_digit(strval($_POST['stations'][$i])) &&
			ctype_digit(strval($_POST['parents'][$i])))
		{
		$_POST['types'][$i]=$db->real_escape_string($_POST['types'][$i]);
		$sql.='('.$_POST['IDs'][$i].','.$_POST['MMIIDs'][$i].','.$_POST['parents'][$i].','.$_POST['stations'][$i].',\''.$_POST['names'][$i].'\',\''.$_POST['types'][$i].'\','.$projectid.'),';
		$itemscount++;
		}
	}
	if ($itemscount>0) //update scene_temp table only if there are objects in the scene
	{
	 $sql=substr($sql,0,-1).' ON DUPLICATE KEY UPDATE parent=VALUES(parent), name=VALUES(name), station=VALUES(station), type=VALUES(type), changed=CURRENT_TIMESTAMP();';
	 $db->query($sql);
	 return '<resultsql>'.$sql.'</returnsql>';
	}
 }
 
 function addAvatarsFromScene($token) {
	global $db;
	$projectid=tokenToProjectId($token);
	if ($projectid==0)
	return '<result>Error - insufficient user privileges</result>';
	
	if (!(isset($_POST['avatarsNames']) && isset($_POST['avatarsIDs']) && isset($_POST['avatarsMMIIDs']) && isset($_POST['avatarsStation']) &&
      (count($_POST['avatarsNames'])==count($_POST['avatarsIDs'])) &&
	  (count($_POST['avatarsNames'])==count($_POST['avatarsStation'])) &&
	  (count($_POST['avatarsNames'])==count($_POST['avatarsMMIIDs']))))
	return '<result>Dataset error</result>';

	$sql='SELECT `id`, `engineid`, `name` as avatar, `projectid` FROM `avatars` WHERE projectid='.$projectid.' ORDER BY avatar';
	$avatars=[];
	if ($result=$db->query($sql))
	 while ($row=$result->fetch_assoc())
	 {
	  $row['changed']=false;
	  $avatars[]=$row;
	 }

	$sql='INSERT INTO avatars (`projectid`, `engineid`, `name`) VALUES ';
	$sqlu='INSERT INTO avatars (`id`,`projectid`, `engineid`, `name`) VALUES ';
	
	for ($i=0; $i<count($_POST['avatarsIDs']); $i++) 
	{
	  $found=false;
	  for ($j=0; $j<count($avatars); $j++)
	  { //finding maches by engineid (already synced earlier)
	   if (!$avatars[$j]['changed'] && ($_POST['avatarsMMIIDs'][$i]==$avatars[$j]['engineid']))
	   {
		$found=true;
		$avatars[$j]['changed']=true;
		if (($_POST['avatarsNames'][$i]!=$avatars[$j]['avatar']) || 
			($_POST['avatarsIDs'][$i]!=$avatars[$j]['id']))
			$sqlu.='('.$avatars[$j]['id'].','.$projectid.','.$_POST['avatarsMMIIDs'][$i].',\''.$db->real_escape_string($_POST['avatarsNames'][$i]).'\'),';
		break;
	   }
	  }

	  if (!$found) //finding maches by names for those that have engineid=0
		for ($j=0; $j<count($avatars); $j++)
	     if (!$avatars[$j]['changed'] && ($avatars[$j]['engineid']==0) && 
		    ($_POST['avatarsNames'][$i]==$avatars[$j]['avatar']))
		 {
			$found=true;
			$avatars[$j]['changed']=true;
			$sqlu.='('.$avatars[$j]['id'].','.$projectid.','.$_POST['avatarsMMIIDs'][$i].',\''.$db->real_escape_string($_POST['avatarsNames'][$i]).'\'),';
		 }
	  
		if (!$found)
		$sql.='('.$projectid.','.$_POST['avatarsMMIIDs'][$i].',\''.$db->real_escape_string($_POST['avatarsNames'][$i]).'\'),';
	}
	
	
	
	if (substr($sqlu,-1)==',') //updating existing ones
	{
		$sqlu=substr($sqlu,0,-1).' ON DUPLICATE KEY UPDATE engineid=VALUES(engineid), name=VALUES(name);';
		$db->query($sqlu);
	}
	if (substr($sql,-1)==',') //adding new ones
	{
		$sql=substr($sql,0,-1);
		$db->query($sql);
	}
	
	$dellist=[]; //deleting removed avatars from scene
	for ($i=0; $i<count($avatars); $i++)
		if (!$avatars[$i]['changed'] && $avatars[$i]['engineid']>0)
			$dellist[]=$avatars[$i]['id'];
	if (count($dellist)>0)
	{
	 $dellist=implode(',',$dellist);
	 $sqldel='UPDATE workers SET avatarid=0 WHERE avatarid in ('.$dellist.') and projectid='.$projectid;
	 $db->query($sqldel);
	 $sqldel='DELETE FROM avatars WHERE id in ('.$dellist.') and projectid='.$projectid;
	 $db->query($sqldel);
	}
	$output=[];
	$sql='SELECT `id`, `engineid` as localID, `name` as avatar FROM `avatars` WHERE projectid='.$projectid.' ORDER BY avatar';
	if ($result=$db->query($sql))
	 while ($row=$result->fetch_assoc())
	 $output[]=$row;
    
	return json_encode($output);
 }
 
 function addPartsFromScene($token) {
  global $db;
  $projectid=tokenToProjectId($token);
  if ($projectid==0)
  return '<result>Error - insufficient user privileges</result>';
  $sql='SELECT id, name, engineid FROM `parts` WHERE projectid='.$projectid.' ORDER BY name, engineid';
  $parts=[];
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	$row['changed']=false;
    $parts[]=$row;
   }
  $result='';

  if (!(isset($_POST['partsNames']) && isset($_POST['partsIDs']) && isset($_POST['partsMMIIDs']) && isset($_POST['partsStation']) &&
      (count($_POST['partsNames'])==count($_POST['partsIDs'])) &&
	  (count($_POST['partsNames'])==count($_POST['partsStation'])) &&
	  (count($_POST['partsNames'])==count($_POST['partsMMIIDs']))))
	return '<result>Dataset error</result>';
  
   $sql='INSERT INTO parts (projectid, description, name, engineid) VALUES ';
   $sqls='INSERT INTO part_station (part, station) VALUES ';
   $sqlu='INSERT INTO parts (id, projectid, description, name, engineid) VALUES ';
   $unityFound=array_fill(0,count($_POST['partsIDs']),false);
   
	for ($i=0; $i<count($_POST['partsIDs']); $i++)
     {
	  $found=false;
	  if ($_POST['partsIDs'][$i]!=0)
	   for ($j=0; $j<count($parts); $j++)
	   {
		//echo 'ByID: '.$parts[$j]['id'].'?='.$_POST['partsIDs'][$i]."<br>";
	    if ((!$parts[$j]['changed']) && ($parts[$j]['id']==$_POST['partsIDs'][$i]))
	    {
		 $decamelled=deCamel($_POST['partsNames'][$i]);
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
	      if (($parts[$j]['engineid']!=$_POST['partsMMIIDs'][$i]) || 
		      ($parts[$j]['name']!=$decamelled))
		 {
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsMMIIDs'][$i].'),';
		  $sqls.=' ('.$parts[$j]['id'].','.$_POST['partsStation'][$i].'),';
		 }
         break;	
	    }
	   }
	 }
	 
	  for ($i=0; $i<count($_POST['partsIDs']); $i++)
	   if (!$unityFound[$i])
	   for ($j=0; $j<count($parts); $j++)
	   {
		//echo 'ByLocalID: '.$parts[$j]['engineid'].'?='.$_POST['partsMMIIDs'][$i]."<br>";
	    if ((!$parts[$j]['changed']) && ($parts[$j]['engineid']==$_POST['partsMMIIDs'][$i]))
	    {
		 $decamelled=deCamel($_POST['partsNames'][$i]);
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
	      //if ($parts[$j]['engineid']!=$_POST['partsMMIIDs'][$i])		      
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsMMIIDs'][$i].'),';
	      $sqls.=' ('.$parts[$j]['id'].','.$_POST['partsStation'][$i].'),';
         break;	
	    }
	   }
	   
	  for ($i=0; $i<count($_POST['partsIDs']); $i++)
	   if (!$unityFound[$i])
	   for ($j=0; $j<count($parts); $j++)
	   {
		$decamelled=deCamel($_POST['partsNames'][$i]);
		//echo 'ByName: '.$parts[$j]['name'].'?='.$decamelled."<br>";
	    if ((!$parts[$j]['changed']) && (mb_strtoupper($parts[$j]['name'],'UTF-8')==mb_strtoupper($decamelled,'UTF-8')))
	    {
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
	      //if ($parts[$j]['engineid']!=$_POST['partsMMIIDs'][$i])
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsMMIIDs'][$i].'),';
	      $sqls.=' ('.$parts[$j]['id'].','.$_POST['partsStation'][$i].'),';
         break;	
	    }
	   }
    
	for ($i=0; $i<count($_POST['partsIDs']); $i++)
	 if (!$unityFound[$i])  
	 {
	  $decamelled=deCamel($_POST['partsNames'][$i]);
	  $sql.='('.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsMMIIDs'][$i].'),';
	 }
   
   $ok=true;
   if (substr($sql,-1)==',')
   {   
    $sql=substr($sql,0,-1);
	$ok=($db->query($sql));
   }
   
   if ($sqlu[strlen($sqlu)-1]==',') 
   {
    $sqlu=substr($sqlu,0,-1).' ON DUPLICATE KEY UPDATE name=values(name), engineid=values(engineid);';
	$ok=$ok && ($db->query($sqlu));
   }
   
   if ($sqls[strlen($sqls)-1]==',') 
   {
    $sqls=substr($sqls,0,-1).' ON DUPLICATE KEY UPDATE station=values(station);';
	echo '<sqls>'.$sqls.'</sqls>';
	$ok=$ok && ($db->query($sqls));
   }
   
   //echo '<sql>'.$sql.'</sql>'."\r\n"; //this is only for debug purposes
   //echo '<sqlu>'.$sqlu.'</sqlu>'."\r\n";  //this is only for debug purposes
   
   if ($ok)
   {
	 $sql='SELECT id, name, engineid FROM `parts` WHERE engineid>0 and projectid='.$projectid.' ORDER BY name, engineid';
	 $data=[];
	 if ($rset=$db->query($sql))
		 while ($row=$rset->fetch_assoc())
		 {
		  $row['name']=str_replace(' ','',$row['name']);
		  $data[]=$row;
		 }
	 $result.=json_encode($data); //printing entires from database as JSON
   }
   else
   {
	$reply="Error";
    $result.=json_encode($reply);
   }
   
  return $result;
 }
 
 function getEnumValues($table,$field) {
	global $db;
	$sql = "SHOW FIELDS FROM `{$table}` LIKE '{$field}'";
	$result = $db->query($sql);
	if ($row = $result->fetch_assoc())
	{
	 preg_match('#^enum\((.*?)\)$#ism', $row['Type'], $matches);
	 $enum = str_getcsv($matches[1], ",", "'");
	 return $enum;
	}
	return array();
 }
 
 function listStations($projectid)
 {
  global $db;
  $sql='SELECT id, name, engineid FROM `stations` WHERE parent=0 and projectid='.$projectid.' ORDER BY name, engineid';
  $stations=[];
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	$row['name']=camel($row['name']);
    $stations[]=$row;
   }
  header('Content-Type: application/json');	
  echo json_encode($stations);
 }
 
 function addStationsFromScene($token) {
  global $db;
  $projectid=tokenToProjectId($token);
  if ($projectid==0)
  return '<result>Error - insufficient user privileges</result>'; 

  if (!(isset($_POST['stationNames']) && isset($_POST['stationIDs']) && 
        isset($_POST['stationMMIIDs'])))
	{
	 listStations($projectid);
	 return '';
	}

  if (!((count($_POST['stationNames'])==count($_POST['stationIDs'])) &&
	   (count($_POST['stationNames'])==count($_POST['stationMMIIDs']))))
	return '<result>Dataset error, expected stationNames, stationIDs, and stationMMIIDs as arrays. of the same length</result>';

   $sql='SELECT id, name, engineid FROM `stations` WHERE parent=0 and projectid='.$projectid.' ORDER BY name, engineid';
  $parts=[];
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	$row['changed']=false;
    $parts[]=$row;
   }
  $result='';
  
  $sql='INSERT INTO stations (projectid, name, engineid) VALUES ';
  $sqlu='INSERT INTO stations (id, projectid, name, engineid) VALUES ';
  $unityFound=array_fill(0,count($_POST['stationIDs']),false);
  
  for ($i=0; $i<count($_POST['stationIDs']); $i++)
     {
	  $found=false;
	  if ($_POST['stationIDs'][$i]!=0)
	   for ($j=0; $j<count($parts); $j++)
	   {
	    if ((!$parts[$j]['changed']) && ($parts[$j]['id']==$_POST['stationIDs'][$i]))
	    {
		 $decamelled=deCamel($_POST['stationNames'][$i]);
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
	      if (($parts[$j]['engineid']!=$_POST['stationMMIIDs'][$i]) || 
		      ($parts[$j]['name']!=$decamelled))
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\''.$decamelled.'\','.$_POST['stationMMIIDs'][$i].'),';
         break;	
	    }
	   }
	 }
	
	for ($i=0; $i<count($_POST['stationIDs']); $i++)
	  if (!$unityFound[$i])
	   for ($j=0; $j<count($parts); $j++)
	   {
	    if ((!$parts[$j]['changed']) && ($parts[$j]['engineid']==$_POST['stationMMIIDs'][$i]))
	    {
		 $decamelled=deCamel($_POST['stationNames'][$i]);
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true; 
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\''.$decamelled.'\','.$_POST['stationMMIIDs'][$i].'),';
         break;	
	    }
	   }
	
	for ($i=0; $i<count($_POST['stationIDs']); $i++)
	   if (!$unityFound[$i])
	   for ($j=0; $j<count($parts); $j++)
	   {
		$decamelled=deCamel($_POST['stationNames'][$i]);
	    if ((!$parts[$j]['changed']) && (mb_strtoupper($parts[$j]['name'],'UTF-8')==mb_strtoupper($decamelled,'UTF-8')))
	    {
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\''.$decamelled.'\','.$_POST['stationMMIIDs'][$i].'),';
         break;	
	    }
	   }
	   
	for ($i=0; $i<count($_POST['stationIDs']); $i++)
	 if (!$unityFound[$i])  
	 {
	  $decamelled=deCamel($_POST['stationNames'][$i]);
	  $sql.='('.$projectid.',\''.$decamelled.'\','.$_POST['stationMMIIDs'][$i].'),';  
	 }
	 
	$ok=true;
   if (substr($sql,-1)==',')
   {   
    $sql=substr($sql,0,-1);
	$ok=($db->query($sql));
   }
   
   if ($sqlu[strlen($sqlu)-1]==',') 
   {
    $sqlu=substr($sqlu,0,-1).' ON DUPLICATE KEY UPDATE name=values(name), engineid=values(engineid);';
	$ok=$ok && ($db->query($sqlu));
   }
   
  listStations($projectid);
 }
 
 function addMarkersFromScene($token) {
  global $db;
  $projectid=tokenToProjectId($token);
  if ($projectid==0)
  return '<result>Error - insufficient user privileges</result>';

  if (!(isset($_POST['markerNames']) && isset($_POST['markersIDs']) && isset($_POST['markersMMIIDs']) && isset($_POST['markersType']) && isset($_POST['parentStation']) && 
      (count($_POST['markerNames'])==count($_POST['markersIDs'])) &&
	  (count($_POST['markerNames'])==count($_POST['markersMMIIDs'])) &&
	  (count($_POST['markerNames'])==count($_POST['parentStation'])) &&
	  (count($_POST['markerNames'])==count($_POST['markersType']))))
	return '<result>Dataset error 1</result>';

  $allowedTypes=getEnumValues('markers','type');
  for ($i=0; $i<count($_POST['markersIDs']); $i++)
	  if (!in_array($_POST['markersType'][$i],$allowedTypes))
		return '<result>Dataset error 2</result>';
  
  $sql='SELECT id, name, engineid FROM `markers` WHERE projectid='.$projectid.' ORDER BY name, engineid';
  $parts=[];
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	$row['changed']=false;
    $parts[]=$row;
   }
  $result='';

  $sql='INSERT INTO markers (projectid, type, name, engineid, stationid) VALUES ';
  $sqlu='INSERT INTO markers (id, projectid, type, name, engineid, stationid) VALUES ';
  $unityFound=array_fill(0,count($_POST['markersIDs']),false);
   
	for ($i=0; $i<count($_POST['markersIDs']); $i++)
     {
	  $found=false;
	  if ($_POST['markersIDs'][$i]!=0)
	   for ($j=0; $j<count($parts); $j++)
	   {
	    if ((!$parts[$j]['changed']) && ($parts[$j]['id']==$_POST['markersIDs'][$i]))
	    {
		 $decamelled=deCamel($_POST['markerNames'][$i]);
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
	      if (($parts[$j]['engineid']!=$_POST['markersMMIIDs'][$i]) || 
		      ($parts[$j]['name']!=$decamelled))
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\''.$_POST['markersType'][$i].'\',\''.$decamelled.'\','.$_POST['markersMMIIDs'][$i].','.$_POST['parentStation'][$i].'),';
         break;	
	    }
	   }
	 }
	 
	  for ($i=0; $i<count($_POST['markersIDs']); $i++)
	   if (!$unityFound[$i])
	   for ($j=0; $j<count($parts); $j++)
	   {
	    if ((!$parts[$j]['changed']) && ($parts[$j]['engineid']==$_POST['markersMMIIDs'][$i]))
	    {
		 $decamelled=deCamel($_POST['markerNames'][$i]);
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true; 
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\''.$_POST['markersType'][$i].'\',\''.$decamelled.'\','.$_POST['markersMMIIDs'][$i].','.$_POST['parentStation'][$i].'),';
         break;	
	    }
	   }
	   
	  for ($i=0; $i<count($_POST['markersIDs']); $i++)
	   if (!$unityFound[$i])
	   for ($j=0; $j<count($parts); $j++)
	   {
		$decamelled=deCamel($_POST['markerNames'][$i]);
		//echo 'ByName: '.$parts[$j]['name'].'?='.$decamelled."<br>";
	    if ((!$parts[$j]['changed']) && (mb_strtoupper($parts[$j]['name'],'UTF-8')==mb_strtoupper($decamelled,'UTF-8')))
	    {
	     $found=true;
		 $unityFound[$i]=true;
	     $parts[$j]['changed']=true;
	      //if ($parts[$j]['engineid']!=$_POST['partsMMIIDs'][$i])
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\''.$_POST['markersType'][$i].'\',\''.$decamelled.'\','.$_POST['markersMMIIDs'][$i].','.$_POST['parentStation'][$i].'),';
         break;	
	    }
	   }
    
	for ($i=0; $i<count($_POST['markersIDs']); $i++)
	 if (!$unityFound[$i])  
	 {
	  $decamelled=deCamel($_POST['markerNames'][$i]);
	  $sql.='('.$projectid.',\''.$_POST['markersType'][$i].'\',\''.$decamelled.'\','.$_POST['markersMMIIDs'][$i].','.$_POST['parentStation'][$i].'),';  
	 }
   
   $ok=true;
   if (substr($sql,-1)==',')
   {   
    $sql=substr($sql,0,-1);
	$ok=($db->query($sql));
   }
   
   if ($sqlu[strlen($sqlu)-1]==',') 
   {
    $sqlu=substr($sqlu,0,-1).' ON DUPLICATE KEY UPDATE name=values(name), engineid=values(engineid), stationid=values(stationid);';
	$ok=$ok && ($db->query($sqlu));
   }
  
 }
 
 function outputJSON($result,$token) {
  $workertasks=[];
  $output=[];
  $i=0;	
  $workerid=0;
  $avatarid=0;
  while ($row=$result->fetch_assoc())
  {
	if ($workerid!=$row['workerid'])
	{
		if ($workerid!=0)
		{
		 $workertasks[]=array('workerid'=>$workerid,'avatarid'=>$avatarid,'tasks'=>$output);
		 $output=[];
		}
		$workerid=$row['workerid'];
		$avatarid=$row['avatarid'];
		$i=0;
	}
   $output[]=array('step'=>$i,
                   'operation'=>camel($row['operation']),
	   		       'part'=>array('type'=>camel($row['partname']),
				   'id'=>"NULL"),
                   'tool'=>array('type'=>camel($row['toolname']),
				   'id'=>"NULL"),
			       'position'=>array('type'=>camel($row['positionname']),
				                     'id'=>"NULL"));
   $i++;
  }
  //at least one worker there is always
  if ($workerid!=0)
  $workertasks[]=array('workerid'=>$workerid,'avatarid'=>$avatarid,'tasks'=>$output);
  header('Content-Type: application/json');	
  echo                  	
  json_encode(array('callback'=>array('url'=>'https://taskeditor.mosim.eu/api.php',
                    'token'=>$token), //.'/'.$_GET['station'])
                    'scene'=>array('type'=>'default','id'=>"NULL"),
                    'avatars'=>array('type'=>'default','id'=>"NULL"),
                    'workers'=>$workertasks)); 
 }
 
 function outputXML($result) {
  $xml=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<tasks>
</tasks>
XML;
  $xml = new SimpleXMLElement($xml);
  $i=0;	
  while ($row=$result->fetch_assoc())
  {
   $xml->addChild('task','');
   $xml->task[$i]->addChild('task',($i+1));
   $xml->task[$i]->addChild('taskid',$row['id']);
   $xml->task[$i]->addChild('workerid',$row['workerid']);
   $xml->task[$i]->addChild('avatarid',$row['avatarid']);
   $xml->task[$i]->addChild('operation',camel($row['operation']));
   $xml->task[$i]->addChild('partname',camel($row['partname']));
   $xml->task[$i]->addChild('partid',$row['engineid']);
   $xml->task[$i]->addChild('toolname',camel($row['toolname']));
   $xml->task[$i]->addChild('toolid','');
   $xml->task[$i]->addChild('positionname',camel($row['positionname']));
   $xml->task[$i]->addChild('positionid','');
   $i++;
  }	
  header('Content-Type: application/xml; charset=utf-8');
  header("Content-Disposition: attachment; filename=tasks.xml");
  echo $xml->asXML();
 }

 function getToolTypes($token) {
  global $db;
  $sql='SELECT name FROM `tools` WHERE language=\'mosim\';';
  $tooltypes=array();
   if ($result=$db->query($sql))
    while ($row=$result->fetch_assoc())
	$tooltypes[]=camel($row['name']);
  $projectData=tokenToProjectIdAndName($token);
  
  header('Content-Type: application/json');	
  echo json_encode(array('projectID'=>$projectData['id'],
						 'projectName'=>$projectData['name'],
						 'type'=>'ToolTypes',
						 'tools'=>$tooltypes));
 }
 
 function getStationTypes($token) {
  global $db;
  $sql='SELECT s.id, s.name, s.sortorder FROM `stations` s, tokens t WHERE s.parent=0 and t.projectid=s.projectid and t.token=\''.$db->real_escape_string($token).'\' ORDER BY sortorder, id;';
  $stations=array();
   if ($result=$db->query($sql))
    while ($row=$result->fetch_assoc())
	$stations[]=array("id"=>$row['id'],"station" => $row['name']);
  header('Content-Type: application/json');	
  echo json_encode($stations);
 }
 
 function getWorkers($token) {
  global $db;
  $sql='SELECT w.id, w.name as worker, w.stationid, w.avatarid FROM `workers` w, `tokens` t WHERE w.projectid=t.projectid and t.token=\''.$db->real_escape_string($token).'\' ORDER BY stationid, worker;';
  $workers=array();
   if ($result=$db->query($sql))
    while ($row=$result->fetch_assoc())
	{
	 $row['simulate']=true;
	 $workers[]=$row;
	}
  header('Content-Type: application/json');	
  echo json_encode($workers);
 }
 
 function setWorkers($token) {
	global $db;
	if (!(isset($_POST['workerid']) && isset($_POST['simulate']) && isset($_POST['avatarid']) && (count($_POST['workerid'])==count($_POST['simulate']))
		 && (count($_POST['workerid'])==count($_POST['avatarid']))))
		 {
			ob_start();
			var_dump($_POST);
			$result = ob_get_clean();
			return '<result>Dataset error: '.$result.'</result>';
		 }
	 if (tokenToProjectId($token)==0)
	 return '<result>Invalid project token</result>';
	
	$sql='';
	for ($i=0; $i<count($_POST['workerid']); $i++)
		if (ctype_digit(strval($_POST['workerid'][$i])) && ctype_digit(strval($_POST['avatarid'][$i])))
		$sql.='UPDATE `workers` SET avatarid='.$_POST['avatarid'][$i].' WHERE id='.$_POST['workerid'][$i].' LIMIT 1; ';
	if ($sql=='')
	return '<result>Nothing to update</result>';
	$db->multi_query($sql);
	while ($db -> next_result());
	return '<result>OK</result>';
 }
 
 function testConnection($token) {
	 $idName=tokenToProjectIdAndName($token);
	 return json_encode(array("projectid"=>intval($idName['id']),
	 "projectName"=>$idName['name']));
 }
 
//main body
 
 //POST requests begin
 
 if (isset($_POST['action']) && isset($_POST['token']))
 {
  if (($_POST['action']=='getToolList') && (tokenToProjectId($_POST['token'])!==0))
  getToolTypes($_POST['token']);

  if (($_POST['action']=='getStationList') && (tokenToProjectId($_POST['token'])!==0))
  getStationTypes($_POST['token']);

  if (($_POST['action']=='getWorkerList') && (tokenToProjectId($_POST['token'])!==0))
  getWorkers($_POST['token']);

  if (($_POST['action']=='setWorkerList') && (tokenToProjectId($_POST['token'])!==0))
  echo setWorkers($_POST['token']);

  if (($_POST['action']=='removeMMU') && isset($_POST['vendorID']))
  {
	if (tokenToProjectIdAndRoleCheck($_POST['token'],'MMU Library manager')>0)
	{
     include_once 'mmu-functions.php';
     if (removeMMU($_POST['vendorID']))
     {
	  header('Content-Type: application/json');
	  echo getMMUList($_POST['token']);
     } //TODO: prepare response for error state
	}
  }

	if (($_POST['action']=='syncAvatars') && (tokenToProjectId($_POST['token'])!==0))
	{
		$result=addAvatarsFromScene($_POST['token']);
		 if ((substr($result,0,8)<>'<result>') && (substr($result,0,5)<>'<sql>'))
		 header('Content-Type: application/json');
		echo $result;
	}

  if (($_POST['action']=='addParts'))
  {
   header('Content-Type: application/json');
   echo addPartsFromScene($_POST['token']);
  }

  if (($_POST['action']=='addPartPicture') && isset($_POST['partid']) && ctype_digit($_POST['partid']) && isset($_FILES['part']) && file_exists($_FILES['part']['tmp_name']))
  {
	$projectid=tokenAndPartToProjectID($_POST['token'],$_POST['partid']);
	if ($projectid>0)
	{
	 $pic=file_get_contents($_FILES['part']['tmp_name']);
	 $pic=bin2hex($pic);
	 $sql='UPDATE parts SET picture=0x'.$pic.' WHERE id='.$_POST['partid'].' LIMIT 1;';
	 $db->query($sql);
	 if ($db->affected_rows>-1) //-1 means error, everything above is okay
		 echo '<result>OK</result>';
	 else
		 echo '<result>ERR</result><msg>'.$db->error.'</msg>';
	 echo '<uploadSize>'.filesize($_FILES['part']['tmp_name']).'</uploadSize>';
	}
	else
	{
		if (tokenToProjectId($_POST['token'])>0)
		echo '<result>ERR-FATAL</result><msg>Part does not belong to the project</msg>';
		else
		echo '<result>ERR</result><msg>Project not found</msg>';
	}
  }
  
  if (($_POST['action']=='addParts3D') && isset($_POST['token']) && isset($_POST['partid']) && ctype_digit($_POST['partid']))
	  if (isset($_POST['glTF']))
		{
		header('Content-Type: application/json');
		echo addCad_glTF_FromScene();
		}
		else
		{
		header('Content-Type: application/json');
		echo addCadFromScene();
		}

  if (($_POST['action']=='getPart3D') && isset($_POST['token']) && isset($_POST['partid']) && ctype_digit($_POST['partid']))
  {
	header('Content-Type: application/json');
	echo getCadForPart($_POST['token'],$_POST['partid']);
  }

 if (($_POST['action']=='getMMUList') && isset($_POST['token']))
 {
  header('Content-Type: application/json');
  echo getMMUList($_POST['token']);
 }
 
 if (($_POST['action']=='syncGroupsAndStations') && isset($_POST['token']))
 {
  echo addStationsFromScene($_POST['token']);
 }
 
 if (($_POST['action']=='getSettings') && isset($_POST['token']))
 echo getSettings($_POST['token']);
 
 if (($_POST['action']=='downloadMMU') && isset($_POST['token']) && isset($_POST['mmuID']) && ctype_digit($_POST['mmuID']))
 {
	if (tokenToProjectId($_POST['token'])>0)
	{
     include_once("mmu-functions.php");
	 downloadMMU('mmus/'.$_POST['mmuID'].'.zip');
	}
	else
		echo '<result>Incorrect project token</result>';
 }
 
 if (($_POST['action']=='uploadMMU') && isset($_POST['token']) && isset($_POST['chunkend'])  && isset($_POST['chunknum']) && isset($_POST['sessionID']) && isset($_POST['fileID']) && isset($_POST['TotalSize']) && ctype_digit($_POST['chunkend']) && ctype_digit($_POST['chunknum']) && isset($_FILES['chunk']) && ctype_digit($_POST['TotalSize']))
 {
	if (tokenToProjectIdAndRoleCheck($_POST['token'],'MMU Library manager')>0)
	{
     include_once("mmu-functions.php");
	 uploadMMU();
	}
	else
		if (tokenToProjectId($_POST['token'])>0)
		echo '<result>Insufficient user privileges</result>';
		else
		echo '<result>Incorrect project token</result>';
 }

  if (($_POST['action']=='testConnection'))
  {
	 header('Content-Type: application/json; charset=utf-8');
	 echo testConnection($_POST['token']);
  }
 
  if ($_POST['action']=='syncMarkers')
	echo addMarkersFromScene($_POST['token']);
 
  if ($_POST['action']=='saveMMUTask')
  {
	header('Content-Type: application/json; charset=utf-8');  
	if (isset($_POST['ID']) && isset($_POST['Name']) && isset($_POST['MotionType']) && isset($_POST['Properties']) && isset($_POST['Constraints']) && isset($_POST['StartCondition']) && isset($_POST['EndCondition']) && isset($_POST['SortOrder']) && isset($_POST['ResultSet']) && isset($_POST['Success']))
	{
	 echo json_encode(array("result"=>saveMMUList($_POST['token'],array('ID'=>$_POST['ID'], 'Name'=>$_POST['Name'], 'MotionType'=>$_POST['MotionType'], 'Properties'=>$_POST['Properties'],'Constraints'=>$_POST['Constraints'], 'StartCondition'=>$_POST['StartCondition'], 'EndCondition'=>$_POST['EndCondition'], 'SortOrder'=>$_POST['SortOrder'], 'ResultSet'=>$_POST['ResultSet'],'Success'=>$_POST['Success']))));
	}
	else
	if (isset($_POST['data']))
    {
	 $data=json_decode($_POST['data'],true);
	 echo json_encode(array("result"=>saveMMUList($_POST['token'],$_POST['data'])));
    }
  }
 
  if ($_POST['action']=='syncScene')
  {
	 echo syncSceneToDB($_POST['token']);
  }
 
 } //action issset (POST)
 
 //POST requests end
 //POST requests begin
 
 if (isset($_GET['action']) && (!isset($_GET['token'])))
 {
	if (($_GET['action']=='getTaskList') && isset($_GET['station']) && ctype_digit($_GET['station']))
	if (stationInCurrentProject($_GET['station']))
	{
	 $sql='SELECT ht.id, ht.workerid, w.avatarid, ht.sortorder, ht.positionname, p.engineid, p.name as partname, t.name as toolname, tt.name as operation '.
	     'FROM highleveltasks ht, workers w, parts p, tools t, tasktypes tt'.
	     ' WHERE w.stationid=ht.stationid and w.id=ht.workerid and ht.stationid='.$_GET['station'].' and ht.partid=p.id and ht.tasktype=tt.id and tt.language=t.language and ht.toolid=t.id and t.language=\'mosim\' '.
	     'ORDER BY ht.workerid, ht.sortorder, ht.id;';
	 if ($result=$db->query($sql))
	  if (isset($_GET['format']) && (strtoupper($_GET['format'])=='XML'))
	  outputXML($result);
      else 
  	  outputJSON($result);
	}
 }

 if (isset($_GET['action']) && isset($_GET['token']))
 {
  if (($_GET['action']=='getTaskList') && isset($_GET['station']) && ctype_digit($_GET['station']))
  {
	$projectid=tokenToProjectId($_GET['token']);
	if ($projectid>0)
	{
	$sql='SELECT ht.id, ht.workerid, w.avatarid, ht.sortorder, ht.positionname, p.engineid, p.name as partname, t.name as toolname, tt.name as operation '.
	     'FROM highleveltasks ht, workers w, parts p, tools t, tasktypes tt'.
	     ' WHERE w.stationid=ht.stationid and w.id=ht.workerid and ht.stationid='.$_GET['station'].' and ht.partid=p.id and ht.tasktype=tt.id and tt.language=t.language and ht.toolid=t.id and t.language=\'mosim\' '.
	     'ORDER BY ht.workerid, ht.sortorder, ht.id;';
	if ($result=$db->query($sql))
	 if (isset($_GET['format']) && (strtoupper($_GET['format'])=='XML'))
	 outputXML($result);
	 else 
	 outputJSON($result,$_GET['token']);
	}
  }

  if (($_GET['action']=='getToolList') && (tokenToProjectId($_GET['token'])!==0))
  getToolTypes($_GET['token']);

  if (($_GET['action']=='getWorkerList') && (tokenToProjectId($_GET['token'])!==0))
  getWorkers($_GET['token']);

  if (($_GET['action']=='getPart3D') && isset($_GET['partid']) && ctype_digit($_GET['partid']))
  {
   header('Content-Type: application/json');
   echo getCadForPart($_GET['token'],$_GET['partid']);
  }
  
  if (($_GET['action']=='downloadMMU') && isset($_GET['mmuID']) && ctype_digit($_GET['mmuID'])) 
  {
	if (tokenToProjectId($_GET['token'])>0)
	{
     include_once("mmu-functions.php");
	 if ($_GET['mmuID']!="0")
	 downloadMMU('mmus/'.$_GET['mmuID'].'.zip');
	}
	else
		echo '<result>Incorrect project token</result>';
  }
	 
  if ($_GET['action']=='getSettings')
  echo getSettings($_GET['token']);
	 
  if (($_GET['action']=='getMMUList'))
  {
    header('Content-Type: application/json');
    echo getMMUList($_GET['token']);
  }
 
  if ($_GET['action']=='testConnection')
  {
	 header('Content-Type: application/json; charset=utf-8');
	 echo testConnection($_GET['token']);
  }
  
  if ($_GET['action']=='getNextMMUTaskSet')
  {
	 header('Content-Type: application/json; charset=utf-8');	  
	 echo json_encode(getNextResultSet($_GET['token'])); //dfsdfsdg gfs gfsg sfg
  }
  
  if ($_GET['action']=='markerTypes') //just checking what are the marker types allowed
  { 
   echo '<p>Allowed values are: '.implode(", ",getEnumValues('markers','type'));
   if (isset($_GET['test']))
   echo '<p>In array: '.(in_array($_GET['test'],getEnumValues('markers','type'))?'yes':'no');
  }
  
  if ($_GET['action']=='saveMMUTask')
  {
	header('Content-Type: application/json; charset=utf-8');  
	if (isset($_GET['ID']) && isset($_GET['Name']) && isset($_GET['MotionType']) && isset($_GET['Properties']) && isset($_GET['Constraints']) && isset($_GET['StartCondition']) && isset($_GET['EndCondition']) && isset($_GET['SortOrder']) && isset($_GET['ResultSet']) && isset($_GET['Success']))
	{
	 //echo 'NewMMU task (GET)'."\r\n";
	 echo json_encode(array("result"=>saveMMUList($_GET['token'],array('ID'=>$_GET['ID'], 'Name'=>$_GET['Name'], 'MotionType'=>$_GET['MotionType'], 'Properties'=>$_GET['Properties'],'Constraints'=>$_GET['Constraints'], 'StartCondition'=>$_GET['StartCondition'], 'EndCondition'=>$_GET['EndCondition'], 'SortOrder'=>$_GET['SortOrder'], 'ResultSet'=>$_GET['ResultSet'],'Success'=>$_GET['Success']))));
	}
	else
	if (isset($_GET['data']))
    {
	 $data=json_decode($_GET['data'],true);
	 echo json_encode(array("result"=>saveMMUList($_GET['token'],$_GET['data'])));
    }
  }
 } //GET requests end
 
?>