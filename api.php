<?php
 session_start();
 
 include('db.php'); 
 
 if (!connectDB())
 exit();

//functions
 
 function camel($str) {
  $str=mb_convert_case(trim($str),MB_CASE_TITLE,'UTF-8');
  return str_replace(' ','',$str);
 }
 
 function deCamel($str) {
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

  if (!(isset($_POST['partsNames']) && isset($_POST['partsIDs']) && isset($_POST['partsMMIIDs']) && 
      (count($_POST['partsNames'])==count($_POST['partsIDs'])) &&
	  (count($_POST['partsNames'])==count($_POST['partsMMIIDs']))))
	return '<result>Dataset error</result>';
  
   $sql='INSERT INTO parts (projectid, description, name, engineid) VALUES ';
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
		  $sqlu.='('.$parts[$j]['id'].','.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsMMIIDs'][$i].'),';
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
         break;	
	    }
	   }
    
	for ($i=0; $i<count($_POST['partsIDs']); $i++)
	 if (!$unityFound[$i])  
	 {
	  $decamelled=deCamel($_POST['partsNames'][$i]);
	  $sql.='('.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsIDs'][$i].'),';  
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
 
 function outputJSON($result) {
  $output=[];
  $i=0;	
  while ($row=$result->fetch_assoc())
  {
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
  header('Content-Type: application/json');	
  echo                  	
  json_encode(array('callback'=>array('url'=>'https://taskeditor.mosim.eu/api.php',
                    'token'=>'mosim2020-983456/'.$_GET['station']),
                    'scene'=>array('type'=>'default','id'=>"NULL"),
                    'avatars'=>array('type'=>'default','id'=>"NULL"),
                    'tasks'=>$output)); 
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
 
 } //action issset (POST)
 
 //POST requests end
 //GET requests begin
 
 if (isset($_GET['action']) && (!isset($_GET['token'])))
 {
	if (($_GET['action']=='getTaskList') && isset($_GET['station']) && ctype_digit($_GET['station']))                             
	if (stationInCurrentProject($_GET['station']))                   
	{
	 $sql='SELECT ht.id, ht.sortorder, ht.positionname, p.engineid, p.name as partname, t.name as toolname, tt.name as operation '.
	     'FROM highleveltasks ht, parts p, tools t, tasktypes tt'.
	     ' WHERE ht.stationid='.$_GET['station'].' and ht.partid=p.id and ht.tasktype=tt.id and tt.language=t.language and ht.toolid=t.id and t.language=\'mosim\' '.
	     'ORDER BY ht.sortorder, ht.id;';
	 if ($result=$db->query($sql))
	  if (	isset($_GET['format']) && (strtoupper($_GET['format'])=='XML'))
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
	$sql='SELECT ht.id, ht.sortorder, ht.positionname, p.engineid, p.name as partname, t.name as toolname, tt.name as operation '.
	     'FROM highleveltasks ht, parts p, tools t, tasktypes tt'.
	     ' WHERE ht.stationid='.$_GET['station'].' and ht.partid=p.id and ht.tasktype=tt.id and tt.language=t.language and ht.toolid=t.id and t.language=\'mosim\' '.
	     'ORDER BY ht.sortorder, ht.id;';
	if ($result=$db->query($sql))
	 if (isset($_GET['format']) && (strtoupper($_GET['format'])=='XML'))
	 outputXML($result);	
     else 
  	 outputJSON($result);
	}
  }

  if (($_GET['action']=='getToolList') && (tokenToProjectId($_GET['token'])!==0))
  getToolTypes($_GET['token']);

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
	 
  if (($_GET['action']=='getSettings'))
  echo getSettings($_GET['token']);	 
	 
  if (($_GET['action']=='getMMUList'))
  {
    header('Content-Type: application/json');
    echo getMMUList($_GET['token']);
  }
 
  if (($_GET['action']=='testConnection'))
  {
	 header('Content-Type: application/json; charset=utf-8');
	 echo testConnection($_GET['token']);
  }
 } //GET requests end
 
?>