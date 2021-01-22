<?php
 function projectName() {
  global $db;
  if ($_SESSION['projectid']==0)
  return false;

  if ($result=$db->query('SELECT name FROM projects WHERE id='.$_SESSION['projectid']))
   if ($row=$result->fetch_assoc())
   echo htmlentities($row['name']);
 }

 function getIcons() {
  $ikony=scandir('icons/');
  for ($i=0; $i<count($ikony); $i++)
   if (!is_dir($ikony[$i]) && in_array(pathinfo($ikony[$i], PATHINFO_EXTENSION),array('png','svg','jpg')))   
   echo '<div data-icon="'.$ikony[$i].'" style="background-image:url(\'icons/'.$ikony[$i].'\');"></div>';
 }           

 function insertSubTypes($maintype = 1)
 {
  global $db;  
   if ($result=$db->query('SELECT tt.id, tt.name, dtt.tooltype, dtt.parttype, dtt.restricttool, dtt.restrictpart, dtt.partlist, tt.sortorder FROM `tasktypes` tt LEFT JOIN defaulttooltype dtt ON (dtt.tasktype=tt.id) WHERE tt.parent='.$maintype.' and tt.language="mosim"'))
	while ($row=$result->fetch_assoc())
	{
	 if ($row['restricttool']=='notool')
	 $row['tooltype']=-1;
     if ($row['restrictpart']=='nopart')
	 $row['parttype']=-1;
	 echo '<option data-defaulttool="'.$row['tooltype'].'" data-defaultpart="'.$row['parttype'].'" '.($row['restrictpart']=='onlylisted'?'data-onlyparts="'.$row['partlist'].'"':'').'value="'.$row['id'].'">'.$row['name'].'</option>';
	}
 }

 function insertAvatars()
 {
  global $db;
  $sql='SELECT `id`, `name`, `sortorder` FROM `avatars` WHERE projectid='.$_SESSION['projectid'].' ORDER by sortorder, id;';
  echo '<option value="0">Default</option>';
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   echo '<option value="'.$row['id'].'">'.htmlentities($row['name']).'</option>';
 }

 function insertSubPositions()
 {
  global $db;
  $sql='SELECT id, name, sortorder FROM positions WHERE language=\'mosim\' ORDER BY sortorder, id;';
  if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   echo '<option value="'.$row['id'].'">'.htmlentities($row['name']).'</option>';
 }

 function insertPartTypes($stationid)
 {
  global $db;
  $selid=-2;
  $i=0;
   if ($result=$db->query('SELECT ifnull(pc.id,"-2") as id, ifnull(pc.name,\'Uncategorized\') as name, pc.sortorder FROM 
(SELECT p_c.cat from parts p LEFT JOIN part_cat p_c on (p.id=p_c.part) WHERE p.projectid='.$_SESSION['projectid'].' GROUP BY p_c.cat) catids 
LEFT JOIN partcat pc ON (catids.cat=pc.id) WHERE isnull(pc.projectid) or pc.projectid in (0,'.$_SESSION['projectid'].') ORDER BY sortorder;'))
	while ($row=$result->fetch_assoc())
	{
		if ($i==0)
		$selid=$row['id'];
		$i++;
		echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
	}
  if ($stationid>0)
  {
   if ($result=$db->query('SELECT count(*) as ile FROM `stations` WHERE parent='.$stationid.';'))
    if ($row=$result->fetch_assoc())
     if ($row['ile']>0)
	 {
      echo '<option value="-1">Subassemblies</option>'; 		
	   if ($i==0)
	   $selid=-2;
	 }
   }
   if ($stationid==0)
	echo '<option value="-3">Station output</option>';
  return $selid;
 } 

 function insertParts($parttype=1)
 {
  global $db;
   if ($result=$db->query('SELECT p.id, p.name, (p.id=pc.defaultpart) as selected FROM parts p, part_cat p_c, partcat pc WHERE pc.id=p_c.cat and p_c.cat='.$parttype.' and p_c.part=p.id and p.projectid='.$_SESSION['projectid'].' ORDER BY name ASC'))
	while ($row=$result->fetch_assoc())
	echo '<option '.($row['selected']?'selected="selected" ':'').'value="'.$row['id'].'">'.$row['name'].'</option>';	
 }
 
 function insertUncategorizedParts()
 {
  global $db;
   if ($result=$db->query('SELECT p.id, p.name FROM parts p LEFT JOIN part_cat p_c ON (p_c.part=p.id) WHERE isnull(p_c.cat) and p.projectid='.$_SESSION['projectid'].' ORDER BY name ASC'))
	$i=0;
	while ($row=$result->fetch_assoc())
	echo '<option '.($i++==0?' selected="selected" ':'').'value="'.$row['id'].'">'.$row['name'].'</option>';	
 }
 
 function insertTools($tooltype = 0)
 {
  global $db;
   if ($result=$db->query('SELECT tc.defaulttool, t.id, t.name, t_c.sortorder FROM tools t, tool_cat t_c, toolcat tc WHERE t_c.cat=tc.id and t_c.cat='.$tooltype.' and t_c.tool=t.id and t.language=tc.language and tc.language="mosim"'))
	while ($row=$result->fetch_assoc())
	echo '<option '.($row['defaulttool']==$row['id']?' selected="selected" ':'')
          .'value="'.$row['id'].'">'.$row['name'].'</option>';	
 }
 
 function insertUncategorizedTools()
 {
  global $db;
   if ($result=$db->query('SELECT t.id, t.name FROM tools t LEFT JOIN tool_cat t_c ON (t_c.tool=t.id) WHERE (isnull(t_c.cat) or t_c.cat=0) and t.language=\'mosim\''))
	$i=0;
	while ($row=$result->fetch_assoc())
	echo '<option '.($i++==0?' selected="selected" ':'')
          .'value="'.$row['id'].'">'.$row['name'].'</option>';	
 }

 function lastStation()
 {
  global $db;
  $sql='SELECT * FROM userrole WHERE userid='.$_SESSION['userid'].' and projectid='.$_SESSION['projectid'].';';
   if ($result=$db->query($sql))
	if ($row=$result->fetch_assoc())
	return $row['laststation'];	
  return 0;  	   
 }
?>