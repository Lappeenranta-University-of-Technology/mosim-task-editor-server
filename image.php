<?php
 include('common.php');
 header("Content-type: image/png");
 
 if (isset($_GET['part']) && ctype_digit($_GET['part']))
 {
	$sql='SELECT picture, if(isnull(picture),1,0) as isunavailable FROM parts WHERE id='.$_GET['part'].' LIMIT 1;';
	if ($result=$db->query($sql))
	{
		if ($row=$result->fetch_assoc())
		{
			if ($row['isunavailable']=='1')
			readfile('nopicture.png');
			else
			echo $row['picture'];
		}
		else
			readfile('nopicture.png');
	}
	else
			readfile('nopicture.png');
 }
?>