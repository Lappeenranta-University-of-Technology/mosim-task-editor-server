<!DOCTYPE html>
<html>
<title>MOSIM task list editor - setup</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="../scripts.js"></script>
<link rel="stylesheet" href="../css/styles.css">
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
 div.loginbox span#loginerror {
	 color: crimson;
 }
 
 div.loginbox span.label {
	padding-left: 25%;
	text-align: left;
	display: block;
	transform: translateY(10px);
 }
 
 div.loginbox p {
	 text-align: left;
	 padding-left: 20px;
	 padding-right: 20px;
 }
 
 div.loginbox div.progress {
	padding-bottom: 10px;
	height: 30px;
	background-color: white;
	position: relative;
	border: 2px solid darkslateblue;
 }
 
 div.loginbox div.progress > div:nth-child(1) {
	position: absolute;
	left: 0px;
	top: 0px;
	background-color: cornflowerblue;
	height: 100%;
	opacity: 0.5;
 }
 
 div.loginbox div.progress > div:nth-child(2) {
	position: absolute;
	left: 50%;
	top: 50%;
	transform: translate(-50%,-50%);
	color: darkblue;
 }
 
 div.loginbox div.progress > div.step1 {
	 width:33%;
 }
 
 div.loginbox div.progress > div.step2 {
	 width:64%;
 }
 
 div.loginbox div.progress > div.step3 {
	 width:100%;
 }

ul {
	list-style: none;
	}

ul li {
	background-image: url('../info.png');
	background-position: left top;
	background-size: 25px 25px;
	background-repeat: no-repeat;
	text-align: left;
	padding-left: 30px;
}

.buttonwide {
	width:50% !important;
}

</style>
</head>

<body class="w3-light-grey">
<div class="w3-content w3-margin-top" style="max-width:1400px;">
 <div class="w3-row-padding">
 <div class="w3-white w3-text-grey w3-card-4 loginbox">
  <img src="mosim.png" style="width: 50%">
  <br>Task editor panel setup
  <?php
  if (($_SESSION['setup']==1.1) && isset($_POST['action']) && $_POST['action']=='setupstep2')
	$_SESSION['setup']=2;

    if ($_SESSION['setup']==1)
	echo '<br>Step 1 - Log in to MySQL database as admin';
	if ($_SESSION['setup']==2)
	echo '<br>Step 2 - Create task editor admin account';
	if ($_SESSION['setup']==3)
	echo '<br>Step 3 - Clean up installation files';
  ?>
  <br>
  <form action="index.php" method="POST">
  <?php
	include 'step'.$_SESSION['setup'].'.php';
  ?>
 </div> 
 </div>
</div> 
</body>
</html>