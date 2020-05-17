<?php
 //$currentfile=pathinfo(__FILE__,PATHINFO_BASENAME);
 $currentfile=basename($_SERVER["SCRIPT_FILENAME"], '');
 
 $menuitems[0]['url']='projects.php';
 $menuitems[0]['icon']='fa-briefcase'; 
 $menuitems[0]['caption']='Projects'; 
 $menuitems[1]['url']='index.php';
 $menuitems[1]['icon']='fa-briefcase'; 
 $menuitems[1]['caption']='Task editor'; 
 $menuitems[2]['url']='parts.php';
 $menuitems[2]['icon']='fa-briefcase'; 
 $menuitems[2]['caption']='Part editor';
 $menuitems[3]['url']='tools.php';
 $menuitems[3]['icon']='fa-briefcase'; 
 $menuitems[3]['caption']='Tool editor';
 $menuitems[4]['url']='settings.php';                
 $menuitems[4]['icon']='fa-gear';                  
 $menuitems[4]['caption']='Settings';                  
 
 for ($i=0; $i<count($menuitems); $i++)                            
  if (($menuitems[$i]['url']!=$currentfile) || 
      ((strlen($menuitems[$i]['url'])>strlen($currentfile)) && (parse_url($menuitems[$i]['url'],PHP_URL_PATH)==$currentfile)))	 
  {
   echo '<p><i class="fa '.$menuitems[$i]['icon'].' fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="'.$menuitems[$i]['url'].'">'.$menuitems[$i]['caption'].'</a></p>';	 
  }
?>
 