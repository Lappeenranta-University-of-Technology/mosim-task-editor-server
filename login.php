<!DOCTYPE html>
<html>
<title>MOSIM task list editor - log in</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<link rel="stylesheet" href="css/styles.css">
<style>
 input {width: 50%;
        margin-top: 10px;
		margin-bottom: 10px;}
 div.loginbox {text-align:center;}
 .button {width: 30%; 
         left: 10%;
         position:relative; 
		 margin-top:10px;
		 margin-bottom:20px;
		}
 @media (min-width:601px) {
   div.loginbox {
    margin-left: 30%;
    margin-right: 30%}
 }
 div.loginbox span:not(.button) {
	 color: crimson;
 }
</style>
<script>
 function resetMsg() {
	 /* temporarly disabled as autfill of user and password removed the message when it should be displayd
	 var msg = document.getElementById("loginerror");
	 if (msg!=null)
		 msg.innerHTML="";
	 */
 }
</script>
</head>

<body class="w3-light-grey">
<div class="w3-content w3-margin-top" style="max-width:1400px;">
 <div class="w3-row-padding">
 <div class="w3-white w3-text-grey w3-card-4 loginbox">
  <img src="mosim.png" style="width: 50%">
  <br>Task editor panel
  <br />
  <form action="index.php" method="POST">
  <input onchange="resetMsg();" type="hidden" name="action" value="login" />
  <input onchange="resetMsg();" type="text" name="login" placeholder="login/email" />
  <br>
  <input type="password" name="password" placeholder="password" />
  <br />
  <?php 
   if (isset($loginerror))
	echo '<span id="loginerror">'.$loginerror.'</span><br />';
  ?>
  <span class="w3-tag w3-round button" onclick="this.parentNode.submit();">Log in</span>
  </form>
 </div> 
 </div>
</div> 
</body>
</html>