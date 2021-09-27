<?php
 include 'common.php';
 include 'functions.php';
 
 if (!isset($_SESSION['showTaskID']))
 $_SESSION['showTaskID']=false;
?>
<!DOCTYPE html>
<html>
<title>MOSIM task list editor</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/windows.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script language="javascript" src="scripts.js"></script>
<script language="javascript" src="dragdrop.js"></script>
<!-- Babylon.js -->
<script src="https://preview.babylonjs.com/babylon.js"></script>
<script src="https://preview.babylonjs.com/loaders/babylonjs.loaders.min.js"></script>
<script src="https://code.jquery.com/pep/0.4.3/pep.js"></script>
<script src="babylon.stlFileLoader.min.js"></script>
<!-- End of Babylon.js -->
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="css/index.css">

<style>

div.menubar > div {
	display: inline-block;
	width: 50px;
	height: 50px;
	margin-left:10px;
	border-radius: 10px;
	border: 1px solid black;
	background-position: center center;
	background-repeat: no-repeat;
	background-size: contain;
	cursor: pointer;
	transition: 0.2s linear;
}

div.menubar > div:hover {
	filter: drop-shadow(0px 0px 10px orange);
}

div.menubar > div.add {
	background-image: url('ui/reasoning/add.png');
}

div.menubar > div.del {
	background-image: url('ui/reasoning/del.png');
}


/*decision tree styles*/


div#decisiontree {
	position:relative; min-height:1000px;
}

div#decisiontree > div {
	width: 200px;
	height: 40px;
	background-color: cadetblue;
	position: absolute;
	transition: background 0.2s linear;
	cursor: pointer;
	padding: 5px;
	z-index:1;
}

div#decisiontree > div.selected {
	filter: drop-shadow(0px 0px 10px orange);
}

div#decisiontree > div.accept {
	filter: drop-shadow(0px 0px 10px lime);
}

div#decisiontree > span {
	position: absolute;
	display: block;
	background-color: black;
}

div#decisiontree > span.left > span {
	display: block;
	position: absolute;
	width: 10px;
	height: 10px;
	left:1px;
	top:-4px;
	background-color: transparent;
	border-top: 2px solid black;
	border-right: 2px solid black;
	transform: rotate(-135deg);
}

div#decisiontree > span.right > span {
	display: block;
	position: absolute;
	width: 10px;
	right: 0px;
	top: 0px;
	height: 10px;
	background-color: transparent;
	border-top: 2px solid black;
	border-right: 2px solid black;
	transform: translateX(-1px) translateY(-4px) rotate(45deg);
}

div#decisiontree > span.up > span {
	display: block;
	position: absolute;
	width: 10px;
	right: -4px;
	top: 1px;
	height: 10px;
	background-color: transparent;
	border-top: 2px solid black;
	border-right: 2px solid black;
	transform: rotate(315deg);
}

div#decisiontree > span.down > span {
	display: block;
	position: absolute;
	width: 10px;
	right: -4px;
	bottom: 0px;
	height: 10px;
	background-color: transparent;
	border-top: 2px solid black;
	border-right: 2px solid black;
	transform: rotate(135deg);
}

/*decision tree connectors*/

div#decisiontree > div.connector {
	width: 30px;
	height: 30px;
	border-radius: 15px;
	background-color: burlywood;
}

</style>

<script>
class TPosition {
	constructor(conid) {
	this.id = conid;
	this.left = parseInt(Connections[conid][1].style.left.split("px")[0]);
	this.top = parseInt(Connections[conid][1].style.top.split("px")[0]);
  }
}

class TBox {
	constructor(boxType, ID, x, y) {
		this.kind = boxType;
		this.id=ID;
		this.left = x;
		this.top = y;
		this.outputs = [];
	}
	
	
}

var boxToMove = null;
var selectedBox = null;
var boxLeft = 0;
var boxTop = 0;
var MouseOffsetX = 0;
var MouseOffsetY = 0;
var Connections = Array(Array(1,2,""),Array(2,3,"OrangeRed"),Array(2,4,"Green"));
var ConnectorsToMove = [];
var Links = Array();
var AutoIncrementValue = 4;

 function UpdateConnections()
 {
	 var win = document.defaultView || window;
	 var t = document.getElementById("decisiontree");
	 for (var i=0; i<t.children.length; i++) //looping through children in search of Boxes
		 if (t.children[i].tagName=="DIV") //checking type of element
		 {
			 for (var a=0; a<Connections.length; a++)
			 {
				 if (Connections[a][0]==t.children[i].dataset.id)
					Connections[a][0]=t.children[i]; 
				 if (Connections[a][1]==t.children[i].dataset.id)
					Connections[a][1]=t.children[i];
			 }
		 }
		 
	 for (var a=0; a<Connections.length; a++)
	 {
		 var wa=parseInt(win.getComputedStyle(Connections[a][0]).width.split("px")[0]);
		 var ha=parseInt(win.getComputedStyle(Connections[a][0]).height.split("px")[0]);
		 var wb=parseInt(win.getComputedStyle(Connections[a][1]).width.split("px")[0]);
		 var hb=parseInt(win.getComputedStyle(Connections[a][1]).height.split("px")[0]);
		 var xa=parseInt(win.getComputedStyle(Connections[a][0]).left.split("px")[0]);
		 var ya=parseInt(win.getComputedStyle(Connections[a][0]).top.split("px")[0]);
		 var xb=parseInt(win.getComputedStyle(Connections[a][1]).left.split("px")[0]);
		 var yb=parseInt(win.getComputedStyle(Connections[a][1]).top.split("px")[0]);
		 
		 if ((Links.length>a && Links[a].length==0) || (Links.length<=a)) //creating linking elements structure if needed
		 {
			 Links[a]=Array(document.createElement("SPAN"),document.createElement("SPAN"),document.createElement("SPAN"));
			 t.appendChild(Links[a][0]);
			 t.appendChild(Links[a][1]);
			 t.appendChild(Links[a][2]);
			 Links[a][0].innerHTML="<span></span>";
		 }
		 
		 Links[a][0].style.backgroundColor=Connections[a][2];
		 Links[a][1].style.backgroundColor=Connections[a][2];
		 Links[a][2].style.backgroundColor=Connections[a][2];
		 
		 
		 if ((xa==xb) && (ya!=yb)) //vertical line
		 {
			Links[a][0].style.display="";
			Links[a][0].style.width="2px";
			Links[a][0].style.left=xa+wa/2+"px";
			 if (yb>ya)
			 {
			  Links[a][0].style.height=yb-ya-ha+"px";
			  Links[a][0].style.top=ya+ha+"px";
			  Links[a][0].className="down";
			 }
			 else
			 {
			  Links[a][0].style.height=ya-yb-hb+"px";
			  Links[a][0].style.top=yb+hb+"px";
			  Links[a][0].className="up";
			 } 
			Links[a][1].style.display="none";
			Links[a][2].style.display="none";
		 }
		 
		 if ((xa!=xb) && (ya==yb)) //horizontal line
		 {
			Links[a][0].style.display="";
			Links[a][0].style.height="2px";
			Links[a][0].style.top=ya+ha/2+"px";
			 if (xb>xa)
			 {
			  Links[a][0].style.width=xb-xa-wa+"px";
			  Links[a][0].style.left=xa+wa+"px";
			  Links[a][0].className="right";
			 }
			 else
			 {
			  Links[a][0].style.width=xa-xb-wb+"px";
			  Links[a][0].style.left=xb+wb+"px";
			  Links[a][0].className="left";
			 } 
			Links[a][1].style.display="none";
			Links[a][2].style.display="none";
		 }
		 
		 if ((xa+wa<xb) && (ya!=yb)) //broken line case 1 _|* or *|_
		 {
			Links[a][1].style.height="2px"; //horizontal going out of A on right side
			Links[a][1].style.width=(xb-xa-wa)/2+"px";
			Links[a][1].style.left=xa+wa+"px";
			Links[a][1].style.top=ya+ha/2+"px";
			
			Links[a][0].style.height="2px"; //horizontal going out of B on right side
			Links[a][0].style.width=(xb-xa-wa)/2+"px";
			Links[a][0].style.left=xb-((xb-xa-wa)/2)+"px";
			Links[a][0].style.top=yb+hb/2+"px";
			
			Links[a][2].style.width="2px"; //vertical connector
			Links[a][2].style.left=xa+wa+(xb-xa-wa)/2+"px";
			 if (ya+ha/2>yb+hb/2)
			 {
			  Links[a][2].style.top=yb+hb/2+"px";
			  Links[a][2].style.height=ya+ha/2-yb-hb/2+"px";
			 }
			 else
			 {
			  Links[a][2].style.top=ya+ha/2+"px";
			  Links[a][2].style.height=yb+hb/2-ya-ha/2+"px";
			 }
			
			Links[a][0].className="right";
			Links[a][0].style.display="";
			Links[a][1].style.display="";
			Links[a][2].style.display="";
		 }
		 
		 if ((xb+wb<xa) && (ya!=yb)) //broken line case 2 _|* or *|_
		 {
			Links[a][0].style.height="2px"; //horizontal going out of A on right side
			Links[a][0].style.width=(xa-xb-wb)/2+"px";
			Links[a][0].style.left=xb+wb+"px";
			Links[a][0].style.top=yb+hb/2+"px";
			
			Links[a][1].style.height="2px"; //horizontal going out of B on right side
			Links[a][1].style.width=(xa-xb-wb)/2+"px";
			Links[a][1].style.left=xa-((xa-xb-wb)/2)+"px";
			Links[a][1].style.top=ya+ha/2+"px";
			
			Links[a][2].style.width="2px"; //vertical connector
			Links[a][2].style.left=xb+wb+(xa-xb-wb)/2+"px";
			 if (ya+ha/2>yb+hb/2)
			 {
			  Links[a][2].style.top=yb+hb/2+"px";
			  Links[a][2].style.height=ya+ha/2-yb-hb/2+"px";
			 }
			 else
			 {
			  Links[a][2].style.top=ya+ha/2+"px";
			  Links[a][2].style.height=yb+hb/2-ya-ha/2+"px";
			 }
			
			Links[a][0].className="left";
			Links[a][0].style.display="";
			Links[a][1].style.display="";
			Links[a][2].style.display="";
		 }
		 
		 if ((xb<=xa+wa) && (xb+wb>=xa)) //broken line case 3 *-. or .-*
		 {
			Links[a][1].style.width="2px"; //vertical going out of A
			Links[a][1].style.left=xa+wa/2+"px";
			if (ya+ha<yb)
			{
			 Links[a][1].style.top=ya+ha+"px";
			 Links[a][1].style.height=(yb-ya-ha)/2+"px";
			}
			else
			{
			 Links[a][1].style.top=ya-(ya-yb-hb)/2+"px";
			 Links[a][1].style.height=(ya-yb-hb)/2+"px";
			}
			
			Links[a][0].style.width="2px"; //vertical going out of B
			Links[a][0].style.left=xb+wb/2+"px";
			if (ya+ha<yb)
			{
			 Links[a][0].style.top=ya+ha+(yb-ya-ha)/2+"px";
			 Links[a][0].style.height=(yb-ya-ha)/2+"px";
			 Links[a][0].className="down";
			}
			else
			{
			 Links[a][0].style.top=yb+hb+"px";
			 Links[a][0].style.height=(ya-yb-hb)/2+"px";
			 Links[a][0].className="up";
			}
			
			Links[a][2].style.height="2px"; //horizontal line
			if (ya+ha<yb)
			 Links[a][2].style.top=ya+ha+(yb-ya-ha)/2+"px";
			else
			 Links[a][2].style.top=yb+hb+(ya-yb-hb)/2+"px";
		 
			if (xa+wa/2<xb+wb/2)
			{
			 Links[a][2].style.left=xa+wa/2+"px";
			 Links[a][2].style.width=xb+wb/2-(xa+wa/2)+"px";
			}
			else
			{
			 Links[a][2].style.left=xb+wb/2+"px";
			 Links[a][2].style.width=xa+wa/2-(xb+wb/2)+"px";
			}
			
			Links[a][0].style.display="";
			Links[a][1].style.display="";
			Links[a][2].style.display="";
		 }
	 }
 }
 
 function selectBox()
 {
	 selectedBox.className+=" selected";
	 if (selectedBox.className.indexOf("connector")>-1)
		 return;
	 
	 var concount = 0;
		for (var a=0; a<Connections.length; a++)
			if (Connections[a][0]==selectedBox)
				concount++;
		
	if (concount>0)
		return;
	
	var win = document.defaultView || window;
	var t = document.getElementById("decisiontree");
	Connections.push(Array(selectedBox,document.createElement("DIV"),""));
	Connections[Connections.length-1][1].className="connector";
	Connections[Connections.length-1][1].onmousedown=startBoxMove;
	//Connections[Connections.length-1][1].draggable=true;
	t.appendChild(Connections[Connections.length-1][1]);
	var xa=parseInt(win.getComputedStyle(selectedBox).left.split("px"));
	var ya=parseInt(win.getComputedStyle(selectedBox).top.split("px"));
	var wa=parseInt(win.getComputedStyle(selectedBox).width.split("px"));
	var ha=parseInt(win.getComputedStyle(selectedBox).height.split("px"));
	var wb=parseInt(win.getComputedStyle(Connections[Connections.length-1][1]).width.split("px"));
	Connections[Connections.length-1][1].style.top=ya+ha+15+"px";
	Connections[Connections.length-1][1].style.left=xa+wa/2-wb/2+"px";
	
	deselectBox();
	selectedBox=Connections[Connections.length-1][1];
	selectBox();
 }
 
 function deselectBox()
 {
	if (selectedBox!=null)
	selectedBox.className=selectedBox.className.split("selected").join("").trim();
 }

 function connectorOverBox(box,x,y)
 {
	 var rect = box.getBoundingClientRect();
	 
	  if ((rect.left<=x) && (rect.top<=y) && (rect.right>=x) && (rect.bottom>=y))
	  {
		  if (box.className.indexOf("accept")==-1)
		  box.className+=" accept";
	  return true;
	  }
	  else
		box.className=box.className.split("accept").join("").trim();
	
	return false;
 }
 
 function startBoxMove()
 {
	 if (event.button>0)
		 return;
		deselectBox();
	boxToMove = event.target; //parsing required to avoid problesm with sting concatenation instead of mathematical addition
	boxLeft = parseInt(event.target.style.left.split("px")[0]);
	boxTop = parseInt(event.target.style.top.split("px")[0]); 
	MouseOffsetX = event.clientX;
	MouseOffsetY = event.clientY;
	
	ConnectorsToMove=[];
	for (var i=0; i<Connections.length; i++) //adding connectors to the move list
		if ((Connections[i][0]==boxToMove) && (Connections[i][1].className.indexOf("connector")>-1))
		ConnectorsToMove.push(new TPosition(i));
 }

 function boxMove()
 {
	if (boxToMove==null)
		return;
	
	boxToMove.style.left=boxLeft+(event.clientX-MouseOffsetX)+"px";
	boxToMove.style.top=boxTop+(event.clientY-MouseOffsetY)+"px";
	
	if (boxToMove.className.indexOf("connector")>-1)
	for (var i=0; i<Connections.length; i++)
	 if (Connections[i][1]!=boxToMove)
	 {
	  connectorOverBox(Connections[i][0],event.clientX,event.clientY);
	  connectorOverBox(Connections[i][1],event.clientX,event.clientY);
	 }

	moveUnconnectedOutputs(event.clientX,event.clientY);
 }

 function MakeConnection(conid, elid)
 {
	for (var i=0; i<Connections.length; i++)
		if (Connections[i][1]==boxToMove)
		{ //corrected reference to className
			Connections[conid][elid].className=Connections[conid][elid].className.split("accept").join("").trim();
			Connections[i][1]=Connections[conid][elid];
		}
	
	boxToMove.remove();
	selectedBox=null;
 }

 function moveUnconnectedOutputs(x,y)
 {
	 for (var i=0; i<ConnectorsToMove.length; i++)
	{
		Connections[ConnectorsToMove[i].id][1].style.left=ConnectorsToMove[i].left+(x-MouseOffsetX)+"px";
		Connections[ConnectorsToMove[i].id][1].style.top=ConnectorsToMove[i].top+(y-MouseOffsetY)+"px";
	}
 }

 function stopBoxMove() //added event parameter to recognize mouse buttons
 { 
	if (boxToMove==null)
		return;
	if (event.button>0)
		return;
	selectedBox=boxToMove;
	selectBox();
    //round position to 10x10 pixel grid
	boxToMove.style.left=Math.floor(parseInt(boxToMove.style.left.split("px")[0])/10)*10+"px";
	boxToMove.style.top=Math.floor(parseInt(boxToMove.style.top.split("px")[0])/10)*10+"px";
	if (boxToMove.className.indexOf("connector")>-1)
	{
		for (var i=0; i<Connections.length; i++)
			if (Connections[i][1]!=boxToMove)
			{
				if (connectorOverBox(Connections[i][0],event.clientX,event.clientY))
				{
					MakeConnection(i, 0);
					break;
				}
				if (connectorOverBox(Connections[i][1],event.clientX,event.clientY))
				{
					MakeConnection(i, 1);
					break;
				}
			}
	}
	boxToMove=null;
	UpdateConnections();
 }
 
 function addComputeNode()
 {
	 var win = document.defaultView || window;
	 var t = document.getElementById("decisiontree");
	 var newbox = document.createElement("DIV"); 
	 newbox.dataset.id=++AutoIncrementValue; //setting ID value to differentiate between nodes
	 t.appendChild(newbox);
	 var newx=5;
	 var newy=5;
	 if (selectedBox!=null)
	 {
		 newx=parseInt(selectedBox.style.left.split("px")[0])+
			parseInt(win.getComputedStyle(selectedBox).width.split("px")[0])+10;
		 newy=parseInt(selectedBox.style.top.split("px")[0]);
	 }
	 newbox.style.left=newx+"px";
	 newbox.style.top=newy+"px";
	 newbox.onmousedown=startBoxMove; 
	 //newbox.outerHTML="<div onmousedown=\"startBoxMove(event);\" "+S[1];
	 deselectBox();
	 selectedBox=newbox;
	 selectBox();
 }
</script>

<?php
 
 function loadDecisionTree()
 {
  global $db;
   
   $j=0;
   $sql='';

   if ($result=$db->query($sql))
   while ($row=$result->fetch_assoc())
   {
	 $j=$j+1;
	 echo '<div id="tool'.$j.'" data-level="0" data-order="'.$j.'" data-type="'.(($row['tasktypeid']==0)?'assembly" data-assembly="'.$row['stationid']:'taskitem').'" data-id="'.$row['id'].'" class="w3-container">';
	 echo '<div class="handle'.(($row['tasktypeid']==0)?' subassembly':'').'"></div>';
	 if ($row['tasktypeid']==0)
	 echo '<div class="unfold" onclick="unFoldSubAssembly(this);"></div>';
	 echo "<h6 class=\"iconback time\"><i class=\"fa fa-hourglass fa-fw w3-margin-right\"></i>".$row['esttime']."</h6>";
     echo "<h5 onclick=\"clickSel(this);\" class=\"w3-opacity task\"><b>".
	 ($_SESSION['showTaskID']?"ID ".$row['id']:"Task $j")."</b>";
	 
	 if ($row['tasktypeid']==0) //subassembly header/footer
	 {
	 echo "<span id=\"subassembly_".$row['stationid']."\" onclick=\"clickTaskType(this);\" class=\"subassembly\" data-id=\"".$row['stationid']."\">".htmlentities($row['tasktype'])."</span>";
	 echo "<span id=\"subassembly_".$row['stationid'].'_part'."\" data-id=\"".$row['partid']."\" onclick=\"clickPart(this);\" ".notBlankIcon($row['particon'])."class=\"w3-tag w3-round tagicon part\">".htmlentities($row['partname'])."</span>";
	 echo "<span id=\"subassembly_".$row['stationid'].'_position'."\" onclick=\"clickPosition(this);\" data-id=\"".$row['positionid']."\" ".notBlankIcon($row['toolicon'])." class=\"w3-tag w3-round tagicon position\">".htmlentities($row['positionname'])."</span></h5>";
	 }
	 else//tasks
	 {
	 echo "<span id=\"task_".$row['id'].'_operation'."\" onclick=\"clickTaskType(this);\" ".notBlankIcon($row['tticon'])." class=\"w3-tag w3-round tagicon operation\" data-id=\"".$row['tasktypeid']."\">".htmlentities($row['tasktype'])."</span>";
	 echo "<span id=\"task_".$row['id'].'_part'."\" data-id=\"".$row['partid']."\" onclick=\"clickPart(this);\" ".notBlankIcon($row['particon'])." class=\"w3-tag w3-round tagicon part\">".htmlentities($row['partname'])."</span>";
	 echo "<span id=\"task_".$row['id'].'_tool'."\" onclick=\"clickTool(this);\" data-id=\"".$row['toolid']."\" ".notBlankIcon($row['toolicon'])." class=\"w3-tag w3-round tagicon tools\">".htmlentities($row['toolname'])."</span></h5>";
     }
	 //TODO: add position id to the query
	 
     echo "<p>".str_replace("\n","<br>",htmlentities($row['description']))."</p>";
     echo '</div>';
   }
 }
?>

<body class="w3-light-grey">
<!-- First modal window is the general message window -->
<div id="msgwindow" class="modalwindow"><div></div>
<div class="modalbutton" onclick="windowOK(this);">Delete</div>
<div class="modalbutton" onclick="windowCancel(this);">Cancel</div>
<div class="modaltoolbar">Warning</div>
</div>

<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Central Column -->
    <div class="w3-container">
      <div class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 id="projectname"><?php projectName(); ?></h2>
	  </div>
	  <div class="w3-container menubar w3-card w3-white w3-margin-bottom">
        <div class="add" onclick="addComputeNode();"></div><div cladd="del"></div>
	  </div>
      <div id="decisiontree" class="w3-container w3-card w3-white w3-margin-bottom"
	        onmouseUp="stopBoxMove(event);" onmousemove="boxMove(event);">
       <div data-id="1" onmousedown="startBoxMove(event);" style="top: 20px;left: 10px;">Compute node</div>
       <div data-id="2" onmousedown="startBoxMove(event);" style="left: 10px;top: 75px;">V1=23*32 - B</div>
       <div data-id="3" onmousedown="startBoxMove(event);" style="left: 10px;top: 130px;">M5=234/23*(45-Q*T)</div>
	   <div data-id="4" onmousedown="startBoxMove(event);" style="left: 210px;top: 130px;">K2=54-T*U</div>
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
<script> <!-- Making connections between boxes -->
UpdateConnections();
</script>
</body>
</html>
