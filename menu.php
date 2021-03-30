<?php
//$currentfile=pathinfo(__FILE__,PATHINFO_BASENAME);
$currentfile=basename($_SERVER["SCRIPT_FILENAME"], '');
$menuitems[0]=array('url'=>'projects.php','icon'=>'itemproject','caption'=>'Projects');
 $menuitems[0]['submenu'][0]=array('url'=>'projects.php','icon'=>'fa-user','caption'=>'Projects');
 $menuitems[0]['submenu'][1]=array('url'=>'stations.php','icon'=>'fa-user','caption'=>'Stations');
 $menuitems[0]['submenu'][2]=array('url'=>'parts.php','icon'=>'fa-user','caption'=>'Parts');
 $menuitems[0]['submenu'][3]=array('url'=>'markers.php','icon'=>'fa-user','caption'=>'Markers');
 $menuitems[0]['submenu'][4]=array('url'=>'tools.php','icon'=>'fa-user','caption'=>'Tools');
 $menuitems[0]['submenu'][5]=array('url'=>'avatars.php','icon'=>'fa-user','caption'=>'Avatars');
$menuitems[1]=array('url'=>'index.php','icon'=>'itemtask','caption'=>'Tasks');
 $menuitems[1]['submenu'][0]=array('url'=>'index-asem.php','icon'=>'fa-user','caption'=>'Assembly');
 $menuitems[1]['submenu'][1]=array('url'=>'index.php','icon'=>'fa-user','caption'=>'Workers');
 $menuitems[1]['submenu'][2]=array('url'=>'index-group.php','icon'=>'fa-user','caption'=>'Group work');
 $menuitems[1]['submenu'][3]=array('url'=>'index-auto.php','icon'=>'fa-user','caption'=>'Automation');
 //$menuitems[2]['url']='stations.php';
 //$menuitems[2]['icon']='fa-briefcase'; 
 //$menuitems[2]['caption']='Stations';
 //$menuitems[2]=array('url'=>'avatars.php','icon'=>'fa-user','caption'=>'Avatars');
 //$menuitems[4]=array('url'=>'parts.php','icon'=>'fa-briefcase','caption'=>'Parts');
 //$menuitems[4]=array('url'=>'tools.php','icon'=>'itemsimulation','caption'=>'Tools');
$menuitems[2]=array('url'=>'mmus.php','icon'=>'itemsimulation','caption'=>'Simulation');
 $menuitems[2]['submenu'][0]=array('url'=>'mmus.php','icon'=>'fa-briefcase','caption'=>'MMU library');
 $menuitems[2]['submenu'][1]=array('url'=>'mmusequence.php','icon'=>'mmutask','caption'=>'MMU sequence');
$menuitems[3]=array('url'=>'settings-user.php','icon'=>'itemsettings','caption'=>'Settings');
 $menuitems[3]['submenu'][0]=array('url'=>'settings-user.php','icon'=>'itemsettings','caption'=>'Account');
 $menuitems[3]['submenu'][1]=array('url'=>'settings-users.php','icon'=>'itemsettings','caption'=>'User management');
 $menuitems[3]['submenu'][2]=array('url'=>'settings-avatars.php','icon'=>'itemsettings','caption'=>'Avatars');
 
 $itemindex = -1;
 
 for ($i=0; $i<count($menuitems); $i++)
 {
  $current=!(($menuitems[$i]['url']!=$currentfile) || 
   ((strlen($menuitems[$i]['url'])>strlen($currentfile)) && (parse_url($menuitems[$i]['url'],PHP_URL_PATH)==$currentfile)));
   if (isset($menuitems[$i]['submenu']))
    for ($j=0; $j<count($menuitems[$i]['submenu']); $j++)
    $current=$current || !(($menuitems[$i]['submenu'][$j]['url']!=$currentfile) || 
   ((strlen($menuitems[$i]['submenu'][$j]['url'])>strlen($currentfile)) && (parse_url($menuitems[$i]['submenu'][$j]['url'],PHP_URL_PATH)==$currentfile)));

   if ($current)
   $itemindex = $i;

	echo '<a href="'.$menuitems[$i]['url'].'"'.($current?' class="selected"':'').'><i class="fa '.$menuitems[$i]['icon'].' fa-fw w3-margin-right w3-large iconback pointer"></i><span>'.$menuitems[$i]['caption'].'</span></a>';
   //echo '<p'.($current?' class="selected"':'').'><i class="fa '.$menuitems[$i]['icon'].' fa-fw w3-margin-right w3-large iconback pointer"></i><a href="'.$menuitems[$i]['url'].'">'.$menuitems[$i]['caption'].'</a></p>';

 }
 
  if (($itemindex>=0) && (isset($menuitems[$itemindex]['submenu'])))
  {
    echo '</div><div class="w3-container subtasks">';
	for ($j=0; $j<count($menuitems[$itemindex]['submenu']); $j++)
	{
	 $current=!(($menuitems[$itemindex]['submenu'][$j]['url']!=$currentfile) || 
   ((strlen($menuitems[$itemindex]['submenu'][$j]['url'])>strlen($currentfile)) && (parse_url($menuitems[$itemindex]['submenu'][$j]['url'],PHP_URL_PATH)==$currentfile))); 
     echo '<a '.($current?'class="selected" ':'').'href="'.$menuitems[$itemindex]['submenu'][$j]['url'].'">'.$menuitems[$itemindex]['submenu'][$j]['caption'].'</a>';
	}
  }
?>
 