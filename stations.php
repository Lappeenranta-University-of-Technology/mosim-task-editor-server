<?php
 include('common.php');
 include('functions.php');
?>

<!DOCTYPE html>
<html>
<title>MOSIM station editor</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="w3.css">
<link rel="stylesheet" href="windows.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<script language="javascript" src="dragdrop.js"></script>
<link rel="stylesheet" href="styles.css">
<script>
 function windowShow(obj) {
  var w=document.getElementsByClassName("modalwindow")[0];	 
  w.dataset.catid=obj.parentNode.dataset.id;
  w.lastElementChild.innerHTML=obj.parentNode.dataset.catname;
  w.className=w.className+" show";
  for (var i=0; i<w.firstChild.children.length; i++)
  {
   w.firstChild.children[i].onclick=choose;
    if (w.firstChild.children[i].dataset.icon==obj.parentNode.dataset.icon)
	w.firstChild.children[i].className="chosen";
    else
    w.firstChild.children[i].className="";   
  }
 }

 function windowUpload(obj) {
  	 
 }
 
 function windowOK(obj) {
  var go=true;	 
  for (var i=0; (i<obj.parentNode.firstChild.children.length) && go; i++)
   if (obj.parentNode.firstChild.children[i].className.indexOf('chosen')>-1)
   {
    var w=document.getElementById("partcatlist");
	 for (var j=0; j<w.children.length; j++)
	  if ((w.children[j].tagName=='DIV') && (w.children[j].dataset.id==obj.parentNode.dataset.catid))
	  {
	   var iconchanged=(w.children[j].dataset.icon!=obj.parentNode.firstChild.children[i].dataset.icon);	  
	   w.children[j].dataset.icon=obj.parentNode.firstChild.children[i].dataset.icon;
	   w.children[j].children[1].style.backgroundImage='url(\'icons/'+w.children[j].dataset.icon+'\')';
	   if (iconchanged)
	    $.post("update.php",
        {
         action: "changePartCatIcon",                                     
	     partcat: obj.parentNode.dataset.catid,
		 icon: w.children[j].dataset.icon                           
        },
         function(data, status){ 	
          if (getTagValue(data,'result')!='OK')
          alert(getTagValue(data,'result'));
	    });  
	   obj.parentNode.dataset.catid=0;
	   go=false;
	   break;
	  }
   }
  windowCancel(obj);	 
 }
 
 function windowCancel(obj) {
  obj.parentNode.className=obj.parentNode.className.split(' show').join('');	 
 }
 
 function choose(e) {
  for (var i=0; i<e.target.parentNode.children.length; i++)
   if (e.target.parentNode.children[i].className.indexOf('chosen')>-1)
   e.target.parentNode.children[i].className="";
  e.target.className="chosen";	   
 }
 
 function uploadIcons(obj) {
  var formData = new FormData();
   for (var i=0; i<obj.files.length; i++)
   formData.append("icon[]",obj.files[i],obj.files[i].name);
  formData.append("upload_file",true);  
  formData.append("action","uploadIcons");  
  
  $.ajax({
        type: "POST",
        url: "update.php",
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            return myXhr;
        },
        success: function (data) {
            // your callback here
		 if (getTagValue(data,'result')=='OK')
         {
		  var maxc=getTagValue(data,'count');
           for (var j=0; j<maxc; j++)
           {
			var icon = getTagValue(data,'icon'+j);
			 if (icon!='ERR')
			 {
			  var img = document.createElement('DIV');
			  img.dataset.icon=icon.split('icons/').join('');
          	  img.style.backgroundImage='url(\''+icon+'\')';
			  img.onclick=choose;
			  obj.parentNode.firstChild.appendChild(img);   
			 }
		   }			   
		 }			 
        },
        error: function (error) {
            // handle error
        },
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });
 }
 
 var accessToken="";
 
 function startPartsFromSceneImport(token) {	 
   var projectname=document.getElementById("projectname").innerText;
   var a=document.location.pathname.lastIndexOf('/');
   var path = document.location.pathname.substr(0,a+1);
   var getstring="?url="+encodeURI(document.location.protocol+"//"+document.location.hostname+path+"api.php")+"&action=importparts&token="+encodeURI(token)+'&name='+encodeURI(projectname);
   $.ajax({
        type: "GET",
        url: "http://127.0.0.1/"+getstring,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            return myXhr;
        },
        success: function (data) { //success callback
		 if (getTagValue(data,'response')=='OK')
         {
		  document.getElementById("importpartsmsg").innerHTML+="<img src=\"ok.png\" />Connected to Unity<br>";
		  console.debug("Parts synchronization has been started...");
		 }			 
        },
        error: function (error) { //error callback
		  document.getElementById("importpartsmsg").innerHTML+="<img src=\"err.png\" />Error: Unity is unreachable, is unity project open?<br><img src=\"info.png\" />You can input the following parameters in Unity:<ul><li>"+document.location.protocol+"//"+document.location.hostname+"/mosim/api.php</li><li>"+token+"</li></ul>";
          console.debug("Scene not available, is target enginge up and running?");
        },
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });	 
 }
 
 function getToken(projectid) {
  $.post("update.php",
    {
        action: "getAccessToken",
		projectid: projectid
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
	 {
	  accessToken=getTagValue(data,'token');	 
	  startPartsFromSceneImport(accessToken);
      console.debug("Access token granted");
	  document.getElementById("importpartsmsg").innerHTML+="<img src=\"ok.png\" />Access token has been created<br>";
	 }
     else
	 {
	  console.debug("Error in obtaining access token");	 
	  document.getElementById("importpartsmsg").innerHTML+="<img src=\"err.png\" />Could not get access token<br>";
	 }
	});
 }
 
 function syncPartsWithScene(projectid) {
  if (accessToken!='')
  startPartsFromSceneImport(accessToken);
  else
  getToken(projectid);
 }
 
 function editStation(e) {
	var station=document.getElementById('stationdetails');
	var stationname=document.getElementById('stationname');
	var stations=document.getElementById('stations');
	var assemblylocation=document.getElementById('assemblylocation');
	var avatar=document.getElementById('avatar');
	var mainparttype=document.getElementById('mainparttype');
	var mainpart=document.getElementById('mainpart');
	stations.value=e.currentTarget.dataset.id;
	assemblylocation.value=e.currentTarget.dataset.position;
	avatar.value=e.currentTarget.dataset.avatar;
	stationname.innerHTML=e.currentTarget.dataset.name;
	mainparttype.value=e.currentTarget.dataset.pc;
	mainpart.dataset.setvalue=e.currentTarget.dataset.mainpart;                         
	mainparttype.onchange(mainparttype);
	//main part is selected
	e.currentTarget.lastChild.className="mark";
	 for (var i=0; i<e.currentTarget.parentNode.children.length; i++)
		 if (e.currentTarget.parentNode.children[i]!=e.currentTarget)
			 e.currentTarget.parentNode.children[i].lastChild.className="mark hidden";
	
 }
 
 function saveStation() {
	var stationname=document.getElementById('stationname');
	var stations=document.getElementById('stations');
	var assemblylocation=document.getElementById('assemblylocation');
	var avatar=document.getElementById('avatar');
	var mainpart=document.getElementById('mainpart');
	var partcategory=document.getElementById('mainparttype');
	$.post("update.php",
    {
        action: "updateStation",
		id: stations.value,
		location: assemblylocation.value,
		avatarid: avatar.value,
		mainid: mainpart.value,
		name: stationname.innerText
    },
    function(data, status){
	 if (getTagValue(data,'result')=='OK')
	 {
		var allstations=document.getElementById('stationlist');
		for (var i=0; i<allstations.children.length; i++)
			if (allstations.children[i].hasAttribute('data-id'))
			if (allstations.children[i].dataset.id==stations.value)
			{
				allstations.children[i].dataset.position=assemblylocation.value;
				allstations.children[i].dataset.avatar=avatar.value;
				allstations.children[i].dataset.name=stationname.innerText;
				allstations.children[i].dataset.pc=partcategory.value;
				allstations.children[i].dataset.mainpart=mainpart.value;
				allstations.children[i].childNodes[0].replaceWith(stationname.innerText);
			}
	 }
	});
 }
 
</script>
<style>
 div#partcatlist > div {
  display: inline-block;
  position: relative;
  box-sizing: border-box;
  width: 100%;
  padding: 5px 40px 5px 40px;
  margin-bottom: 5px;
  color: white;
  border: none;
  background-color: #009688;
  cursor: pointer;
  transition: border-top 0.5s linear;
 }
 
 div#partcatlist > div > span {
  position: absolute;
  top: 0px;
  right: 0px;
  width: 40px;
  height: 100%;
  padding: 5px 0px 0px 0px;
  border-radius: 5px;
  background-color: darkseagreen;
  background-size: contain;
  background-position: center center;
  background-repeat: no-repeat;
  text-align: center;
 }
 
 div#partcatlist > div > span:nth-of-type(2) {
  right: 45px;	 
 }
 
 div#partcatlist > div > span:first-of-type {
  left: 3px;
  width: 30px;
  height: calc(100% - 6px);
  top: 3px;
  color: crimson;
  background-color: darkslategray;
  padding-top: 3px;
  transition: background-color 0.4s linear, width 0.6s linear;
  cursor: pointer;
  overflow: hidden;
 }
 
 div#partcatlist > div > span:first-of-type:hover {
  background-color: antiquewhite;	 
 }
 
 div#partcatlist > div > span.clicked {
  width: 50%;  
 }
 
 div#partcatlist > div > span:first-of-type > span {
  margin-left: 10px;
  margin-right: 10px;
  padding: 3px 10px;
  border-radius: 5px;
  transition: background-color 0.4s linear;
 }
 
 div#partcatlist > div > span:nth-of-type(2) {
  margin-right:0px;	 
 }
 
 div#partcatlist > div > span:first-of-type > span:hover {
  background-color: gold;
 }
 
 div#stationlist > div {
  display: inline-block;
  position: relative;
  box-sizing: border-box;
  width: 100%;
  padding: 5px 40px 5px 40px;
  margin-bottom: 5px;
  color: white;
  border: none;
  background-color: #009688;
  cursor: pointer;
  transition: border-top 0.5s linear;
 }
 
 div#stationlist > div > div.mark::before {
	 content: "\003E";
 }
 
 div#stationlist > div > div.mark {
    position: absolute;
    left: -13px;
    top: 2px;
    width: 30px;
    color: darkgreen;
    font-size: 20px;
    font-weight: bold;
 }
 
 div#stationlist > div > span {
  position: absolute;
  top: 0px;
  right: 0px;
  width: 40px;
  height: 100%;
  padding: 5px 0px 0px 0px;
  border-radius: 5px;
  background-color: darkseagreen;
  text-align: center;
  background-position:center center;
  background-size:contain;
 }
 
 div#stationlist > div > span.check {
  background-image:url('ok.png');	 
 }
 
 div#stationlist > div > span.dialog {
  display:inline-block;
  width: calc(100% - 84px);  
  left:40px;
 }
 
 div#stationlist > div > span.dialog > span {
  display:inline-block;
  border-radius: 5px;
  box-sizing:border-box;
  margin-left: 5px;
  margin-right: 5px;  
  width: calc(33% - 15px);
  transition: background-color 0.4s linear;
  cursor: pointer;
 }
 
 div#stationlist > div > span.dialog > span:hover {
  background-color: gold;	 
 }
 
 div#stationlist > div:not(.category) > span:first-of-type {
  left: 3px;
  width: 30px;
  height: calc(100% - 6px);
  top: 3px;
  color: crimson;
  background-color: darkslategray;
  padding-top: 3px;
  transition: background-color 0.4s linear, width 0.6s linear;
  cursor: pointer;
 }
 
 div#stationlist > div > span:first-of-type:hover {
  background-color: antiquewhite;	 
 }
 
 div#stationlist > div:not(.category) > span.clicked {
  width: 50%;  
 }

 div#stationlist > div:not(.category) > span:first-of-type > span {
  margin-left: 10px;
  margin-right: 10px;
  padding: 3px 10px;
  border-radius: 5px;
  transition: background-color 0.4s linear;
 }
 
 div#stationlist > div:not(.category) > span:first-of-type > span:hover {
  background-color: gold;
 }
 
 div#stationlist > div:not(.category) > span:first-of-type {
  overflow: hidden;	 
 }
 
 div#stationlist > div.category {
  padding-left: 10px;
  color: #009688;
  background-color: gainsboro;
  cursor: default;  
 }
 
 div#stationlist > div.category > span:first-of-type:not(.dialog) {
  position: absolute;
  top: 0px; 
  right: 0px;
  width: 40px;
  height: 100%;
  padding: 5px 0px 0px 0px;
  border-radius: 5px;
  background-color: darkseagreen;
  text-align: center;	 
  cursor: pointer;
  background-image:url("fold.png");
  background-size:contain;
  background-position:center center;
 }
 
 div#stationlist > div.category > span:first-of-type.folded {
  background-image:url("expand.png"); 	 
 }
 
 div#stationlist > div {
  transition: height 0.4s linear; 	 
 }
 
 div#stationlist > div.hidden {
  height: 0px;
  display: none;  
 }
 
 span.button {margin-left:5px;}
</style>
</head>

<?php
 function loadStations() {
  global $db; //it assumes that stations can only have parts as man parts, but they can also have station results as main parts so union has to be created from two queries
  $sql='SELECT s.`id`, s.`name`, s.`mainpart`, s.`main`, s.`position`, s.`avatarid`, s.`sortorder`, '.
       '(SELECT count(*) FROM highleveltasks hlv WHERE hlv.stationid=s.id)+(SELECT count(*) FROM stations s1 WHERE s1.parent=s.id) as howmany, '.
       ' p.name as mainpartname, '.
	   '(SELECT p_c.cat FROM partcat pc, part_cat p_c WHERE pc.id=p_c.cat and pc.projectid=s.projectid and p_c.part=p.id LIMIT 1) as partcategory '.
       'FROM `stations` s LEFT JOIN parts p ON (p.id=s.mainpart and s.projectid=p.projectid) WHERE s.parent=0 and s.projectid='.$_SESSION['projectid'].' '.
       'ORDER BY sortorder;';
	   //echo '<sql>'.$sql.'</sql>';
   if ($result=$db->query($sql))
	while($row=$result->fetch_assoc())
    {
       echo '<div data-position="'.$row['position'].'" data-avatar="'.$row['avatarid'].'" data-pc="'.
	   ($row['main']=='part'?$row['partcategory']:'-3').'" data-mainpart="'.($row['main']=='part'?'':'S').$row['mainpart'].'" data-name="'.htmlentities($row['name']).'" data-id="'.$row['id'].'">'.htmlentities($row['name']).'<span onclick="deleteStation(this);">X</span><span>'.$row['howmany'].'</span><div class="mark hidden"></div></div>';
	}		
  echo '<script>makeDraggable(\'stationlist\');</script>';	
 }
 /*
 function loadParts() {
  global $db;
  $sql='SELECT tc.id as tcid, tc.name as tcname, tc.sortorder, t.id, t.name, t_c.sortorder as sortorder1, defaultpart FROM '.
  '(SELECT id, name, sortorder, language, defaultpart FROM `partcat` WHERE projectid='.$_SESSION['projectid'].') tc '.
  ' LEFT JOIN (part_cat t_c, parts t) ON (t_c.cat=tc.id and t_c.part=t.id and t.projectid='.$_SESSION['projectid'].') '.
  ' UNION ALL '.
  'SELECT 0, \'Uncategorized\', 0, t.id, t.name, 0, 0 FROM parts t '.
  'LEFT JOIN part_cat t_c ON (t_c.part=t.id) '.
  'WHERE (isnull(t_c.cat) or t_c.cat=0) and t.projectid='.$_SESSION['projectid'].' '.
  'ORDER BY sortorder, tcid, sortorder1';
  $lastname=-1;
   if ($result=$db->query($sql))
	while($row=$result->fetch_assoc())
    {
	   if ($row['tcid']!=$lastname)
       {
		 echo '<div class="category" data-id="'.$row['tcid'].'">'.htmlentities($row['tcname']).'<span onclick="foldTools(this);"></span></div>';  
		 $lastname=$row['tcid'];
	   }		
       if ($row['id']!=0)                                       	   
       echo '<div data-cat="'.$row['tcid'].'" data-id="'.$row['id'].'">'.htmlentities($row['name']).'<span onclick="deleteTool(this);">X</span><span onclick="setDefaultPart(this);" '.($row['defaultpart']==$row['id']?'class="check"':"").'></span></div>';
	}		
  echo '<script>makeDraggableTool(\'partlist\');</script>';	
 }*/
?>

<body class="w3-light-grey">
<div class="modalwindow"><div><?php getIcons(); ?></div>
<div class="modalbutton" onclick="windowOK(this);">OK</div>
<div class="modalbutton" onclick="windowCancel(this);">Cancel</div>
<label for="iconfiles" class="modalbutton" onclick="windowUpload(this);"></label>
<input type="file" name="icons[]" id="iconfiles" multiple="true" onchange="uploadIcons(this);" />
<div class="modaltoolbar">Cutting tool icon</div>
</div>
<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Left Column -->
    <div class="w3-third">
    
      <div class="w3-white w3-text-grey w3-card-4">
        <?php include('header.php'); ?>
        <div class="w3-container">
		  <?php include('menu.php'); ?>
          <br>
        </div>
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
	  <div class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 id="projectname"><?php projectName(); ?></h2>
	  </div>
      <div id="stationlist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2>Stations</h2>
		<?php
		 loadStations();
		?>
		<p><input id="new_stationname" type="text" /><span class="w3-tag w3-teal w3-round button" onclick="addStation();">Add station</span>
      </div>
		
      <div id="stationdetails" class="w3-container w3-card w3-white w3-margin-bottom"><input type="hidden" id="stations" value="0" />
	  <h2 id="stationname"></h2>
	  <p>Main part type: <select id="mainparttype" data-sub="mainpart" onchange="getSubParts(event);"><?php insertPartTypes(0); ?></select></p>
	  <p>Main part: <select id="mainpart"><?php insertParts(); ?></select></p>
	  <p>Assembly location: <select id="assemblylocation"><?php insertSubPositions(); ?></select></p>
	  <p>Avatar: <select id="avatar"><?php insertAvatars(); ?></select></p>
	  <p class="right"><span class="w3-tag w3-teal w3-round button" onclick="saveStation();">Save changes</span></p>
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
</body>
</html>