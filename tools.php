<?php
 include('common.php');
 include('functions.php');
?>

<!DOCTYPE html>
<html>
<title>MOSIM tool editor</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/windows.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<script language="javascript" src="dragdrop.js"></script>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="css/tools.css">

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
    var w=document.getElementById("toolcatlist");
	 for (var j=0; j<w.children.length; j++)
	  if ((w.children[j].tagName=='DIV') && (w.children[j].dataset.id==obj.parentNode.dataset.catid))
	  {
	   var iconchanged=(w.children[j].dataset.icon!=obj.parentNode.firstChild.children[i].dataset.icon);	  
	   w.children[j].dataset.icon=obj.parentNode.firstChild.children[i].dataset.icon;
	   w.children[j].children[1].style.backgroundImage='url(\'icons/'+w.children[j].dataset.icon+'\')';
	   if (iconchanged)
	    $.post("update.php",
        {
         action: "changeToolCatIcon",
	     toolcat: obj.parentNode.dataset.catid,
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

  function startToolsFromSceneImport(token) {	
   var projectname=document.getElementById("projectname").innerText;
   var a=document.location.pathname.lastIndexOf('/');
   var path = document.location.pathname.substr(0,a+1);
   var getstring="?url="+encodeURI(document.location.protocol+"//"+document.location.hostname+path+"api.php")+"&action=importtools&token="+encodeURI(token)+'&name='+encodeURI(projectname);	 
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
		  document.getElementById("importtoolsmsg").innerHTML+="<img src=\"ok.png\" />Connected to Unity<br>";
		  console.debug("Tools synchronization has been started...");
		 }			 
        },
        error: function (error) { //error callback
		  document.getElementById("importtoolsmsg").innerHTML+="<img src=\"err.png\" />Error: Unity is unreachable, is unity project open?<br><img src=\"info.png\" />You can input the following parameters in Unity:<ul><li>"+document.location.protocol+"//"+document.location.hostname+"/mosim/api.php</li><li>"+token+"</li></ul>";
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
	  startToolsFromSceneImport(accessToken);
      console.debug("Access token granted");
	  document.getElementById("importtoolsmsg").innerHTML+="<img src=\"ok.png\" />Access token has been created<br>";
	 }
     else
	 {
	  console.debug("Error in obtaining access token");	 
	  document.getElementById("importtoolsmsg").innerHTML+="<img src=\"err.png\" />Could not get access token<br>";
	 }
	});
 }
 
 function syncToolsWithScene(projectid) {
  if (accessToken!='')
  startToolsFromSceneImport(accessToken);
  else
  getToken(projectid);
 }
</script>
</head>

<?php
 function loadToolCategories() {
  global $db;
  $sql='SELECT tc.id, tc.name, tc.sortorder, tc.icon, count(t_c.cat) as howmany FROM toolcat tc LEFT JOIN `tool_cat` t_c ON (tc.id=t_c.cat) WHERE language=\'mosim\' GROUP BY tc.id ORDER BY sortorder;';
   if ($result=$db->query($sql))
	while($row=$result->fetch_assoc())
    {
       echo '<div data-icon="'.$row['icon'].'" data-catname="'.htmlentities($row['name']).'" data-id="'.$row['id'].'">'.htmlentities($row['name']).'<span onclick="deleteToolCat(this);"><span>X</span></span><span onclick="windowShow(this);" style="background-image:url(\'icons/'.$row['icon'].'\')"></span><span>'.$row['howmany'].'</span></div>';
	}		
  echo '<script>makeDraggable(\'toolcatlist\');</script>';	
 }
 
 function loadTools() {
  global $db;
  $sql='SELECT tc.id as tcid, tc.name as tcname, tc.defaulttool, tc.sortorder, t.id, t.name, t_c.sortorder as sortorder1 FROM '.
  ' (SELECT id, name, defaulttool, sortorder, language FROM `toolcat` UNION ALL SELECT 0, \'Uncategorized\', 0, 0, \'mosim\') tc '.
  ' LEFT JOIN (tool_cat t_c, tools t) ON (t_c.cat=tc.id and t_c.tool=t.id) '.
  ' UNION ALL '.
  'SELECT 0, \'Uncategorized\', 0, 0, t.id, t.name, 0 FROM tools t LEFT JOIN tool_cat t_c ON (t_c.tool=t.id) WHERE isnull(t_c.cat) '.
  ' ORDER BY sortorder, tcid, sortorder1';
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
       echo '<div data-cat="'.$row['tcid'].'" data-id="'.$row['id'].'">'.htmlentities($row['name']).'<span onclick="deleteTool(this);"><span>X</span></span><span onclick="setDefaultTool(this);" '.($row['defaulttool']==$row['id']?'class="check"':"").'></span></div>';
	}
  echo '<script>makeDraggableTool(\'toollist\');</script>';	
 }

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
        <div class="w3-container menuitems">
		 <?php include('menu.php'); ?>
		</div>
		<div class="w3-container">
		<hr />
		  <p style="text-align: center"><span class="w3-tag w3-round button" onclick="syncToolsWithScene(<?php echo $_SESSION['projectid']; ?>);">Sync tools with scene</span></p>
		  <p id="importtoolsmsg"></p>
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
      <div id="toolcatlist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2>Tool categories</h2>
		<?php
		 loadToolCategories();
		?>
		<p><input id="newToolCatName" type="text" /><span class="w3-tag w3-round button" onclick="addToolCat('newToolCatName');">Add tool category</span>
      </div>
      
	  <div id="toollist" class="w3-container w3-card w3-white w3-margin-bottom">
		<h2>Tools</h2>
		<?php
		 loadTools();
		?>
		<p><input id="newToolName" type="text" /><span class="w3-tag w3-round button" onclick="addTool('newToolName');">Add tool</span>
      </div>
    <!-- End Right Column -->
    </div>
	
  <!-- End Grid -->
  </div>
  
  <!-- End Page Container -->
</div>

<footer class="w3-container banner w3-center w3-margin-top">
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