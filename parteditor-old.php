<!DOCTYPE html>
<html>
<title>MOSIM part editor</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">         
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<link rel="stylesheet" href="styles.css">
 
<?php
 $stationid=1;
 if (isset($_GET['station']))
  if (ctype_digit($_GET['station']))
  $stationid=$_GET['station'];
 
 function loadTasks()
 {
  global $db, $stationid;
   
   $j=0;
   if ($result=$db->query('SELECT  hlt.`id`, hlt.`stationid`, tt.name as `tasktype`, hlt.`sortorder`, p.name as `partname`, t.name as `toolname`, hlt.`positionname`, hlt.`description`, hlt.`esttime` FROM highleveltasks hlt, tools t, parts p, tasktypes tt  WHERE tt.id=hlt.tasktype and p.id=hlt.partid and t.id=hlt.toolid and hlt.stationid='.$stationid.' ORDER BY sortorder, id'))
   while ($row=$result->fetch_assoc())
   {
	 $j=$j+1;	
	 echo '<div data-type="taskitem" data-id="'.$row['id'].'" class="w3-container">';
	 echo "<h6 class=\"w3-text-teal time\"><i class=\"fa fa-hourglass fa-fw w3-margin-right\"></i>".$row['esttime']."</h6>";
     echo "<h5 onclick=\"clickSel(this);\" class=\"w3-opacity task\"><b>Task $j</b><span class=\"w3-tag w3-teal w3-round w3-margin-left\">".htmlentities($row['tasktype'])."</span><span class=\"w3-tag w3-teal w3-round w3-margin-left\">".htmlentities($row['partname'])."</span><span class=\"w3-tag w3-teal w3-round w3-margin-left\">".htmlentities($row['toolname'])."</span></h5>";
     echo "<p>".str_replace("\n","<br>",htmlentities($row['description']))."</p>";
     echo '<hr>';
     echo '</div>';
   }
 }
 
 function insertStations($selectCurrent=true, $projectid=1)
 {
  global $db, $stationid;
  
   if ($result=$db->query('SELECT id, name, sortorder FROM stations WHERE projectid='.$projectid.' ORDER BY sortorder'))
	while ($row=$result->fetch_assoc())
	echo '<option '.($selectCurrent && ($row['id']==$stationid)?'selected="" ':'').'value="'.$row['id'].'">'.$row['name'].'</option>';	
 }	 
  
 function insertParts($stationid=1)
 {
/*  global $parts;
   for ($i=0; $i<count($parts); $i++)
	echo '<option value="'.$i.'">'.$parts[$i].'</option>';   */

  global $db;
   if ($result=$db->query('SELECT p.id, p.name FROM parts p, partstation ps WHERE ps.partid=p.id and ps.stationid='.$stationid.' ORDER BY name ASC'))
	while ($row=$result->fetch_assoc())
	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
 }
 
 function insertTools()
 {
/*  global $tools;
   for ($i=0; $i<count($tools); $i++)
	echo '<option>'.$tools[$i].'</option>';   */

  global $db;
   if ($result=$db->query('SELECT id, name, sortorder FROM tools WHERE language="mosim"'))
	while ($row=$result->fetch_assoc())
	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
 }
 
 function insertPositions()
 {
  global $positions;
   for ($i=0; $i<count($positions); $i++)
	echo '<option>'.$positions[$i].'</option>';   
 }
 
 function insertTypes()
 {
  global $db;  
   if ($result=$db->query('SELECT id, name, sortorder FROM `tasktypes` WHERE language="mosim"'))
	while ($row=$result->fetch_assoc())
	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';		 
 }
 
 //database access
 $db=false;
 
 function connectDB() {
  global $db;
  $db = mysqli_connect("localhost", "mosim", "mosim2020", "mosim");
   if ($db->connect_errno)
   echo "<p>Failed to connect to MySQL: " . mysqli_connect_error();
   else
   {
    $db->set_charset("utf8"); 
	return true;
   }
  return false;   
 }

 connectDB();
?>

<body class="w3-light-grey">

<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Left Column -->
    <div class="w3-third">
    
      <div class="w3-white w3-text-grey w3-card-4">
        <div class="w3-display-container">
          <img src="mosim.png" style="width:100%" alt="Logo">
          <div class="w3-display-bottomleft w3-container w3-text-black">
            <h2></h2>
          </div>
        </div>
        <div class="w3-container">
          <p><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-teal pointer"></i>New station</p>
		  <p><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="parteditor.php">Part editor</a></p>
          <p><i class="fa fa-gear fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#newtask">New task</a></p>
          <p><i class="fa fa-trash fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><span class="pointer" onclick="removeTask();">Remove task</span></p>
          <p><i class="fa fa-sort fa-fw w3-margin-right w3-large w3-text-teal pointer"></i><a href="#movetoother">Move task to other station</a></p>
          <hr>

          <p id="newtask" class="w3-large"><b><i class="fa fa-gear fa-fw w3-margin-right w3-text-teal"></i>New task</b></p>
          <p>Type: <select id="new_type"><?php insertTypes(); ?></select></p>
          <p>Part: <select id="partselector" onchange="partSelect(this);"><?php insertParts(); ?></select></p>
		  <p>Tool: <select id="new_tool"><?php insertTools(); ?></select></p>
		  <p>Position: <!-- <select><?php insertPositions(); ?></select>--></p>
		  <p><div id="positions" class="position"><img src="car1.jpg" width="100%" />
		  <div onclick="selectPosition(this);"></div>
		  <div onclick="selectPosition(this);"></div>
		  <div onclick="selectPosition(this);">1</div>
		  <div onclick="selectPosition(this);">2</div>
		  <div onclick="selectPosition(this);">3</div>
		  <div onclick="selectPosition(this);">4</div>
		  <div onclick="selectPosition(this);">5</div>
		  <div onclick="selectPosition(this);">6</div>
		  <div onclick="selectPosition(this);">7</div>
		  <div onclick="selectPosition(this);">8</div>
		  </div></p>
		  <p>Time estimate: <input id="new_time" onchange="timeEstimate(this);" type="text" placeholder="00:00:00" value="" /></p>
		  <p>Description: <textarea id="new_description" style="width:100%"></textarea></p>
          <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="addTask();">Add</span></p>
          
          <br>

          <p id="movetoother" class="w3-large w3-text-theme"><b><i class="fa fa-sort fa-fw w3-margin-right w3-text-teal"></i>Move tasks to other station</b></p>
          <p>To station: <select id="tostation"><?php insertStations(false); ?></select></p>
		  <p style="text-align: center"><span class="w3-tag w3-teal w3-round button" onclick="moveSelected();">Move selected</span></p>               
          <br>
        </div>
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
    
      <div id="tasklist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-gear fa-fw w3-margin-right w3-xxlarge w3-text-teal"></i><span class="fa fa-angle-left w3-margin-right pointer" onclick="prevClick();"></span><select onchange="changeStation(this);" id="stations"><?php insertStations(); ?></select><span class="fa fa-angle-right w3-margin-left pointer" onclick="nextClick();"></span></h2>
		<?php
		loadTasks();
		?>
      </div>

    <!-- End Right Column -->
    </div>
    
  <!-- End Grid -->
  </div>
  
  <!-- End Page Container -->
</div>

<footer class="w3-container w3-teal w3-center w3-margin-top">
<!--  <p>Find me on social media.</p> -->
<!--  <i class="fa fa-facebook-official w3-hover-opacity"></i>
  <i class="fa fa-instagram w3-hover-opacity"></i>
  <i class="fa fa-snapchat w3-hover-opacity"></i>
  <i class="fa fa-pinterest-p w3-hover-opacity"></i>
  <i class="fa fa-twitter w3-hover-opacity"></i>
  <i class="fa fa-linkedin w3-hover-opacity"></i> -->
  <p>MOSIM ITEA project</p>
</footer>
<script>
partSelect(document.getElementById("partselector"));
</script>
</body>
</html>
