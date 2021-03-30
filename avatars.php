<?php
 include('common.php');
 include('functions.php');
?>

<!DOCTYPE html>
<html>
<title>MOSIM avatar editor</title>
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
<link rel="stylesheet" href="css/avatars.css">
<script>
 function windowShow() {
  var w=document.getElementsByClassName("modalwindow")[0];
  w.className=w.className+" show";
 }

 function windowOK(obj) {
  $.post("update.php",
    {
        action: "delAvatar",
		workersids: (obj.parentNode.hasAttribute('data-workerids')?obj.parentNode.dataset.workerids.split(','):''), //zero array to make sure it passes validation through isset
		avatarid: obj.parentNode.dataset.avatarid
    },
    function(data, status){
		obj.parentNode.removeAttribute('data-workerids');
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
		else
		 if (getTagValue(data,'ids')!='')
		 {
			windowUpdate('Avatar reassignment confirmation',getTagValue(data,'result'),Array('Delete','Cancel'));
			obj.parentNode.dataset.workerids=getTagValue(data,'ids');
		 }
		 else
		 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));
	});
 }
 
 function windowCancel(obj) {
  obj.parentNode.className=obj.parentNode.className.split(' show').join('');
  obj.parentNode.removeAttribute('data-workerids');
 }
  
  
 function deleteAvatar(obj) {
	windowShow();
	windowSetContext('avatarid', obj.parentNode.dataset.id);
	windowUpdate('Avatar removal confirmation','Do you want to remove avatar \''+obj.parentNode.dataset.name+'\'?',Array('Delete','Cancel'));
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
 
 function addAvatar() {
	var name=document.getElementById('new_avatarname');
	var height=document.getElementById('new_avatarheight');
	var weight=document.getElementById('new_avatarweight');
	var gender=document.getElementById('new_avatargender');
	var age=document.getElementById('new_avatarage');
	$.post("update.php",
    {
        action: "addAvatar",
		name: name.value,
		height: height.value,
		weight: weight.value,
		gender: gender.value,
		age: age.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
	    else
		{
		 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
         windowShow(null);
		}
	});
 }
 
 function editAvatar(e) {
	var name=document.getElementById('avatarname');
	var height=document.getElementById('avatarheight');
	var weight=document.getElementById('avatarweight');
	var gender=document.getElementById('avatargender');
	var age=document.getElementById('avatarage');
	name.dataset.id=e.target.dataset.id;
	name.innerHTML=e.currentTarget.dataset.name;
	height.value=e.currentTarget.dataset.height; 
	weight.value=e.currentTarget.dataset.weight;
	gender.value=e.currentTarget.dataset.gender;
	age.value=e.currentTarget.dataset.age;
	e.currentTarget.lastChild.className="mark";
	 for (var i=0; i<e.currentTarget.parentNode.children.length; i++)
		 if ((e.currentTarget.parentNode.children[i]!=e.currentTarget) &&
			(e.currentTarget.parentNode.children[i].tagName=='DIV') &&
			(e.currentTarget.parentNode.children[i].hasAttribute('data-gender')))			 
			 e.currentTarget.parentNode.children[i].lastChild.className="mark hidden";
 }

 function saveAvatar() {
	var name=document.getElementById('avatarname');
	var height=document.getElementById('avatarheight');
	var weight=document.getElementById('avatarweight');
	var gender=document.getElementById('avatargender');
	var age=document.getElementById('avatarage');
	$.post("update.php",
    {
        action: "updateAvatar",
		id: name.dataset.id,
		name: name.innerText,
		height: height.value,
		weight: weight.value,
		gender: gender.value,
		age: age.value
    },
    function(data, status){
		if (getTagValue(data,'result')=='OK')
		document.location.reload();
	    else
		{
		 windowUpdate('Error',getTagValue(data,'result'),Array('OK'));	
         windowShow(null);
		}
	});
 }
 
</script>
</head>

<?php
 function loadAvatars() {
  global $db;
  $sql='SELECT `id`, `name`, `age`, `height`, `weight`, `gender`, `sortorder` '.
  'FROM `avatars` '.
  'WHERE projectid='.$_SESSION['projectid'].' '.
  'ORDER BY sortorder, id;';
   if ($result=$db->query($sql))
	while($row=$result->fetch_assoc())
    {
       echo '<div data-name="'.htmlentities($row['name']).'" data-age="'.htmlentities($row['age']).'" data-height="'.htmlentities($row['height']).'" data-weight="'.htmlentities($row['weight']).'" data-gender="'.htmlentities($row['gender']).'" data-id="'.$row['id'].'">'.htmlentities($row['name']).'<span onclick="deleteAvatar(this);">X</span><span>'.$row['age'].'</span><span>'.strtoupper(substr($row['gender'],0,1)).'</span><span>'.$row['weight'].'</span><span>'.$row['height'].'</span><div class="mark hidden"></div></div>';
	}
  echo '<script>makeDraggable(\'avatarlist\');</script>';	
 }
 
?>

<body class="w3-light-grey">
<div class="modalwindow"><div></div>
<div class="modalbutton" onclick="windowOK(this);">OK</div>
<div class="modalbutton" onclick="windowCancel(this);">Cancel</div>
<div class="modaltoolbar">Delete avatar confirmation</div>
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
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
	  <div class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 id="projectname"><?php projectName(); ?></h2>
	  </div>
      <div id="avatarlist" class="w3-container w3-card w3-white w3-margin-bottom">
        <h2>Avatars</h2>
		<?php
		 loadAvatars();
		?>
		<hr class="grey">
		<p>Name: <input id="new_avatarname" type="text" /></p>
		<p>Age: <input id="new_avatarage" type="text" /> years</p>
		<p>Height: <input id="new_avatarheight" type="text" /> cm</p>
		<p>Weight: <input id="new_avatarweight" type="text" /> kg</p>
		<p>Gender: <select id="new_avatargender"><option value="male">Male</option><option value="female">Female</option></select></p>
		<p class="right"><span class="w3-tag w3-round button" onclick="addAvatar();">Add avatar</span></p>
      </div>
		
      <div id="avatardetails" class="w3-container w3-card w3-white w3-margin-bottom">
	  <h2 id="avatarname"></h2>
	  <p>Age: <input id="avatarage" type="text" /> years</p>
	  <p>Height: <input id="avatarheight" type="text" /></p>
	  <p>Weight: <input id="avatarweight" type="text" /></p>
	  <p>Gender: <select id="avatargender"><option value="male">Male</option><option value="female">Female</option></select></p>
	  <p class="right"><span class="w3-tag w3-round button" onclick="saveAvatar();">Save changes</span></p>
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