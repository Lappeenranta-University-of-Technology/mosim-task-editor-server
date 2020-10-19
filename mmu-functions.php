<?php
 function uploadMMU()
 {
	 global $db;
	 
	 if (isset($_POST['sessionID']) && ($_POST['sessionID']!=""))
	 $sessionID=$_POST['sessionID'];
      else
	 $sessionID=session_id();
	 
	 echo '<p>Chunk file upload';
	 echo '<nextChunk>'.($_POST['chunknum']+1).'</nextChunk>';
	 echo '<nextStart>'.$_POST['chunkend'].'</nextStart>';
	 echo '<sessionID>'.$sessionID.'</sessionID>';
	 $mmuUpload=file_get_contents($_FILES['chunk']['tmp_name']);
	 echo '<fileSize>'.filesize($_FILES['chunk']['tmp_name']).'</fileSize>';
	 
	 if ($_POST['chunknum']==0) //first chunk
	 {
	 $uploadName=md5($_FILES['chunk']['name'].date("Ymd").rand(0,2000));
	 $sql='INSERT INTO `upload`(`name`, `sessionID`, `chunkno`, `chunk`) VALUES '.
	 '(\''.$uploadName.'\', \''.
	 $db->real_escape_string($sessionID).'\', '.$_POST['chunknum'].', 0)'; 
	 $db->query($sql);
		if ($db->insert_id>0)
		{
			echo '<fileID>'.$uploadName.'</fileID>';
			if (file_put_contents('mmus/'.$uploadName.'.tmp',$mmuUpload,FILE_APPEND)!==false)
			echo '<chunkresult>OK</chunkresult>';
		}
		else echo '<chunkresult>ERR</chunkresult><chunkmsg>'.$db->error.'</chunkmsg>';
	 }
	 else //other chunks
	 {
	  $uploadName=$_POST['fileID'];
	  $sql='UPDATE `upload` SET chunkno='.$_POST['chunknum'].' WHERE `name`=\''.$db->real_escape_string($_POST['fileID']).'\' and `sessionID`=\''.$db->real_escape_string($sessionID).'\' LIMIT 1;';
	  $db->query($sql);
		 if ($db->affected_rows>0)
		 {
			echo '<fileID>'.$_POST['fileID'].'</fileID>';
			if (file_put_contents('mmus/'.$_POST['fileID'].'.tmp',$mmuUpload,FILE_APPEND)!==false)
			echo '<chunkresult>OK</chunkresult>';
			else	
			echo '<chunkresult>ERR</chunkresult><chunkmsg>Could not write new data chunk</chunkmsg>';
		 }
		  else
			 echo '<chunkresult>ERR</chunkresult><chunkmsg>File not found</chunkmsg>';
	 }
	 
	 //processing full archive when it is available
	 if ($_POST['chunkend']>=$_POST['TotalSize'])
	 {
		 echo "\r\nUpload complete\r\n\r\n";
		 $sql='SELECT name FROM upload WHERE `name`=\''.$db->real_escape_string($uploadName).'\' and `sessionID`=\''.$db->real_escape_string($sessionID).'\' LIMIT 1;';
		 echo "<sql>".$sql."</sql>\r\n";
		 if ($result=$db->query($sql))
		 if ($row=$result->fetch_assoc())
		 {
		  $result=false;
		  echo '<tempfile>Temp file ('.'mmus/'.$row['name'].'.tmp'.') size '.filesize('mmus/'.$row['name'].'.tmp').'</tempfile>';
		  $zip = new ZipArchive;
		  if ($zip->open('mmus/'.$row['name'].'.tmp') === TRUE) {
		  $fileID=$zip->locateName('description.json',ZipArchive::FL_NOCASE|ZipArchive::FL_NODIR);
		  echo "\r\n<p>Zip file ID: ".$fileID;
		  if ($fileID===false)
		  echo "\r\n<p>Description file cannot be found";
	      else
		  {
			$desc=json_decode($zip->getFromIndex($fileID),true);
			$lasterror=json_last_error();
			echo "\r\n<p>Json last error (0==success): ".$lasterror."\r\n";
			if ($lasterror==0)
			{
				$desc['ID']="'".$db->real_escape_string(trim($desc['ID']))."'";
				$desc['Name']="'".$db->real_escape_string(trim($desc['Name']))."'";
				$desc['Author']="'".$db->real_escape_string(trim($desc['Author']))."'";
				$desc['MotionType']="'".$db->real_escape_string(trim($desc['MotionType']))."'";
				$desc['Version']="'".$db->real_escape_string(trim($desc['Version']))."'";
				$desc['LongDescription']="'".$db->real_escape_string(trim($desc['LongDescription']))."'";
				$desc['ShortDescription']="'".$db->real_escape_string(trim($desc['ShortDescription']))."'";
				$sql='SELECT count(id) as ile FROM mmus WHERE vendorID='.$desc['ID'].';';
				if ($result=$db->query($sql))
				{
					if ($row=$result->fetch_assoc())
					{
					 if (intval($row['ile'])==0)
					 {
						$sql='INSERT INTO `mmus`(`name`, `author`, `vendorID`, `motiontype`, `version`, `longdescription`, `shortdescription`, `package`) VALUES '.
						'('.$desc['Name'].','.$desc['Author'].','.$desc['ID'].','.$desc['MotionType'].','.$desc['Version'].','.$desc['LongDescription'].','.$desc['ShortDescription'].',0);';
						if ($db->query($sql))
						{
						 if (rename('mmus/'.$uploadName.'.tmp','mmus/'.$db->insert_id.'.zip'))                    
						 {
						  echo '<result>OK</result><mmuName>'.$desc['Name'].'</mmuName>'.
						  '<mmuVersion>'.$desc['Version'].'</mmuVersion>';
						  $result=true;
						 }
						 else
						 echo '<result>ERR</result><msg>Uploaded MMMu cannot be moved to file library</msg>';
						}
						else
							echo '<result>ERR</result><msg>'.$db->error.'</msg>';
					 }
					 else
						 echo '<result>ERR</result><msg>MMU already in library</msg>';
					}
					else
						echo '<result>ERR</result><msg>Database error 2</msg><sql>'.$sql.'</sql>';
				}
				else
				echo '<result>ERR</result><msg>Database error 1</msg><sql>'.$sql.'</sql>';
			}
		  }
		  $zip->close();
		  }
		  else 
		  echo '<p>Zip failed';
	      echo "\r\nCleaning up file using: ".realpath('mmus/'.$uploadName.'.tmp')."\r\n";
		    if (file_exists(realpath('mmus/'.$uploadName.'.tmp')))
			{
			   chmod(realpath('mmus/'.$uploadName.'.tmp'), 0777);
			   echo "\r\nUnlink status: ".(unlink(realpath('mmus/'.$uploadName.'.tmp'))?"True":"False")."\r\n";
			}
			if (!file_exists(realpath('mmus/'.$uploadName.'.tmp'))) //cleaning up
			{
			  $sql='DELETE FROM upload WHERE `name`=\''.$db->real_escape_string($uploadName).'\' and `sessionID`=\''.$db->real_escape_string($sessionID).'\' LIMIT 1;';
			  echo 'Clean sql: '.$sql."\r\n";
			  $db->query($sql);
			  echo 'Sql error: '.$db->error."\r\n";
			}
			else
			 echo "\r\nCannot remove temp file\r\n";
		  //TODO: Add cleaning up files that were not removed in normal cleanup routine and stay on the server more than specified threshold.
		 }
	 }
 }
 
 function downloadMMU($file)
 {
	 if (file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	 }
	 else
		 echo '<result>File '.$file.'does not exist</result>';
 }
 
 function delMMU($ids) { //TODO: check user rights before performing this operation
  global $db;
  $sql='DELETE FROM mmus WHERE id in ('.implode(',',$ids).');';  
  $db->query($sql);
   if ($db->affected_rows>0)
   {
	for ($i=0; $i<Count($ids); $i++)
	{
	 chmod(realpath('mmus/'.$ids[$i].'.zip'), 0777);
	 $ids[$i]=unlink(realpath('mmus/'.$ids[$i].'.zip'));
	}
   }
   else
    for ($i=0; $i<Count($ids); $i++)
	$ids[$i]=false;
  return $ids;
 }
 
 function removeMMU($vendorID)
 {
	global $db;
	$sql='SELECT GROUP_CONCAT(id) as ids FROM mmus WHERE vendorID=\''.$db->real_escape_string($vendorID).'\';';
	 if ($result=$db->query($sql))
		if ($row=$result->fetch_assoc())
		{
			$delresult=delMMU(explode(',',$row['ids']));
			return in_array(true,$delresult);
		}
	return false;
 }
?>