<?php
 
 include('db.php'); 
 
 $accessToken='mosim2020-983456';
 
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
 
 function tokenToProjectId($token) {
  global $db;
  $sql='SELECT userid, projectid FROM tokens WHERE token=\''.$db->real_escape_string($token).'\';';  
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return $row['projectid'];
  return 0;
 }
 
 function addPartsFromScene() {	 
  global $db;                                                                   
  
  $projectid=tokenToProjectId($_POST['token']);
  
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
  if (isset($_POST['partsNames']) && isset($_POST['partsIDs']) && 
      count($_POST['partsNames'])==count($_POST['partsIDs']))
  {
   $sql='INSERT INTO parts (projectid, description, name, engineid) VALUES ';	 
   $sqlu='INSERT INTO parts (id, projectid, description, name, engineid) VALUES ';
   for ($i=0; $i<count($_POST['partsIDs']); $i++)
   if (ctype_digit($_POST['partsIDs'][$i]))	   
   {
	$decamelled=deCamel($_POST['partsNames'][$i]);   
	$found=false;
	 for ($j=0; $j<count($parts); $j++)
	  if ((!$parts[$j]['changed']) && ($parts[$j]['name']==$decamelled))
	  {
	   $found=true;
	   $parts[$j]['changed']=true;
	    if ($parts[$j]['engineid']!=$_POST['partsIDs'][$i])
		$sqlu.='('.$parts[$j]['id'].','.$projectid.',\'\',\''.$decamelled.'\','.$_POST['partsIDs'][$i].'),';
       break;	
	  }
	if (!$found)  
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
    $sqlu=substr($sqlu,0,-1).' ON DUPLICATE KEY UPDATE engineid=values(engineid);';
	$ok=$ok && ($db->query($sqlu));
   }
   $result.=($ok?'<result>OK</result>':'<result>ERROR</result>').'<sql>'.$sql.'</sql>'."\r\n".'<sqlu>'.$sqlu.'</sqlu>';
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
  json_encode(array('callback'=>array('url'=>'https://kone.pc.lut.fi:80/api.php',
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


 function getToolTypes() {
  global $db;
  $sql='SELECT name FROM `tools` WHERE language=\'mosim\';';
  $tooltypes=array();
   if ($result=$db->query($sql))
    while ($row=$result->fetch_assoc())
	$tooltypes[]=camel($row['name']);
  header('Content-Type: application/json');	
  echo json_encode(array('type'=>'ToolTypes','tools'=>$tooltypes));
 }
 
//main body
 
 if (isset($_GET['token']) && isset($_GET['action']) && isset($_GET['station']))
  if (($_GET['action']=='getTaskList') && ($_GET['token']==$accessToken) && 
      ctype_digit($_GET['station']))
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
 
 if (isset($_GET['token']) && isset($_GET['action']) && ($_GET['action']=='getToolList') && ($_GET['token']==$accessToken))
 getToolTypes();

 if (isset($_POST['token']) && isset($_POST['action']) && ($_POST['action']=='getToolList') && (($_POST['token']==$accessToken) || (tokenToProjectId($_POST['token'])!==0)))
 getToolTypes();
 
 if (isset($_POST['action']))
 {
  if (($_POST['action']=='addParts') && ($_POST['token']))
  echo '<html><head></head><body>'.
   addPartsFromScene().
       '</body></html>';
 }
 
?>