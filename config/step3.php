<input type="hidden" name="trial" value="<?php echo (isset($_SESSION['loginattempt'])?$_SESSION['loginattempt']:0) ?>" />
  <br><input type="hidden" name="action" value="setupcleanup" />
  <?php 
   if ($_SESSION['setuplog']!='')
   {
	   echo '<ul>'.$_SESSION['setuplog'].'</ul><br />';
	   $_SESSION['setuplog']='';
   }
  ?>
  
  <p>Installation files are no longer needed, it is strongy adviced to remove them on production servers. If you use installation just on a local computer, you can leave the installation files.</p>
  <span class="w3-tag w3-round button buttonwide" onclick="this.parentNode.submit();">Remove files</span>
  </form>
  <form action="index.php" method="POST">
  <input type="hidden" name="action" value="finishsetup">
  <span class="w3-tag w3-round button buttonwide" onclick="this.parentNode.submit();">Leave files</span>
  </form>
  <div class="progress">
  <div class="step3"></div>
  <div>Step 3/3</div>
  </div>