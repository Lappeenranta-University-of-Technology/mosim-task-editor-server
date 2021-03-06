<?php
 include 'common.php';
 include 'functions.php';
 include 'mmu-functions.php';
?>

<!DOCTYPE html>
<html>
<title>MOSIM MMU Library</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/windows.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<script language="javascript" src="dragdrop.js"></script>
<link rel="stylesheet" href="css/mmus.css">
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="css/styles.css">
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
 
 function linkMMULibraryToLauncher(token) {
   var projectName=document.getElementById("projectname").innerText;
   var a=document.location.pathname.lastIndexOf('/');
   var path = document.location.pathname.substr(0,a+1);
   var url=btoa(document.location.protocol+"//"+document.location.hostname+path+"api.php");
   var linkdata="mmulib://add?name="+btoa(projectName)+"&token="+token+"&url="+url;
   document.getElementById("importpartsmsg").innerHTML+=
   "Select <b>MMU Library Linker</b> as opening program to add the library<br />";
   window.location.href=linkdata;
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
      console.debug("Access token granted");
	  document.getElementById("importpartsmsg").innerHTML+="<img src=\"ok.png\" />Access token has been created<br>";
	  linkMMULibraryToLauncher(accessToken);
	 }
     else
	 {
	  console.debug("Error in obtaining access token");	 
	  document.getElementById("importpartsmsg").innerHTML+="<img src=\"err.png\" />Could not get access token<br>";
	 }
	});
 }
 
 function syncMMUsWithLauncher(projectid) {
  document.getElementById("importpartsmsg").innerHTML="";
   if (accessToken!='')
   linkMMULibraryToLauncher(accessToken);
   else
   getToken(projectid);
 }
 
</script>
</head>

<?php

 function loadMMUs() {
  global $db;
  $sql='SELECT `id`,`author`, `name`, `vendorID`, `motiontype`, `version`, `longdescription`, `shortdescription`, '.
  'ifnull(sortorder,0) as sortorder, ifnull(enabled,1) as enabled '.
  'FROM `mmus` LEFT JOIN `mmu_project` mmup '.
  'ON (mmus.id=mmup.mmuid and mmup.projectid='.$_SESSION['projectid'].') '.
  'ORDER BY motiontype, sortorder, name';

   if ($result=$db->query($sql))
	while ($row=$result->fetch_assoc())
    {		
       echo '<div data-enabled="'.$row['enabled'].'" data-project="'.$_SESSION['projectid'].'" data-cat="0" data-id="'.$row['id'].'">'.htmlentities($row['name'].' ('.$row['version'].'), '.$row['author']).'<br>'.htmlentities($row['motiontype']).'<span onclick="deleteTool(this);"><span>X</span></span><span onclick="setEnableMMU(this);" '.($row['enabled']=='1'?'class="check"':"").'></span></div>';
	}
  echo '<script>makeDraggable(\'mmulist\');</script>';
 }
 
 function MMUUploadForm() {
	global $db;
	if (isMMUManager())
	{
	ob_start(); ?>
	<form method="POST" enctype="multipart/form-data" id="mmuUploadForm">
	 <input type="hidden" name="action" value="addMMU"/>
	<p><input id="newMMUPackage" name="mmu" type="file" /><span class="w3-tag w3-round button" style="width:100px;" onclick="MMUS.addMMU('newMMUPackage','mmuUploadProgress');">Add MMU</span><span id="mmuUploadProgress" class="progress hide"><span></span><span>0%</span></span>
	</form>
	<?php
	$out = ob_get_clean();
	echo $out;
	}
	else
	echo '<p>You can modify MMU order, enable/disable MMU but you cannot add new MMUs. Only MMU library managers can upload new MMUs or delete existing ones.</p>';
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
		  <p style="text-align: center"><span class="w3-tag w3-round button" onclick="syncMMUsWithLauncher(<?php echo $_SESSION['projectid']; ?>);">Sync MMUs with launcher</span></p>
		  <p id="importpartsmsg"></p>
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
	  <div id="mmulist" class="w3-container w3-card w3-white w3-margin-bottom">
		<h2>MMU library</h2>
		<?php
		 loadMMUs();
		 MMUUploadForm();
		?>
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