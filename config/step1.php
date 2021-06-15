<input type="hidden" name="trial" value="<?php echo (isset($_SESSION['loginattempt'])?$_SESSION['loginattempt']:0) ?>" />
  <input type="hidden" name="action" value="dblogin" />
  <input type="text" name="login" placeholder="login/email" />
  <br>
  <input type="password" name="password" placeholder="password" />
  <br />
  <?php 
   if ($loginerrorcode!=-1 && isset($loginerror))
	echo '<span id="loginerror">'.$loginerror.'</span><br />';
  ?>
  <span class="w3-tag w3-round button" onclick="this.parentNode.submit();">Log in</span>
  </form>
  <div class="progress">
  <div class="step1"></div>
  <div>Step 1/3</div>
  </div>