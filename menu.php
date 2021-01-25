<?php
 //$currentfile=pathinfo(__FILE__,PATHINFO_BASENAME);
 $currentfile=basename($_SERVER["SCRIPT_FILENAME"], '');
 
 $menuitems[0]['url']='projects.php';
 $menuitems[0]['icon']='fa-briefcase'; 
 $menuitems[0]['caption']='Projects'; 
 $menuitems[1]['url']='index.php';
 $menuitems[1]['icon']='fa-briefcase'; 
 $menuitems[1]['caption']='Task editor'; 
 $menuitems[2]['url']='stations.php';
 $menuitems[2]['icon']='fa-briefcase'; 
 $menuitems[2]['caption']='Stations editor'; 
 $menuitems[3]=array('url'=>'avatars.php','icon'=>'fa-user','caption'=>'Avatar editor');
 $menuitems[4]=array('url'=>'parts.php','icon'=>'fa-briefcase','caption'=>'Part editor');
 $menuitems[5]=array('url'=>'tools.php','icon'=>'fa-briefcase','caption'=>'Tools editor');
 $menuitems[6]=array('url'=>'mmus.php','icon'=>'fa-briefcase','caption'=>'MMU library');
 $menuitems[7]=array('url'=>'settings.php','icon'=>'fa-gear','caption'=>'Settings');
 
 for ($i=0; $i<count($menuitems); $i++)                            
  if (($menuitems[$i]['url']!=$currentfile) || 
      ((strlen($menuitems[$i]['url'])>strlen($currentfile)) && (parse_url($menuitems[$i]['url'],PHP_URL_PATH)==$currentfile)))	 
  {
   echo '<p><i class="fa '.$menuitems[$i]['icon'].' fa-fw w3-margin-right w3-large iconback pointer"></i><a href="'.$menuitems[$i]['url'].'">'.$menuitems[$i]['caption'].'</a></p>';	 
  }
?>
 