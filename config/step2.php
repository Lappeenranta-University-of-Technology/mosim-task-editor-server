<input type="hidden" name="trial" value="<?php echo (isset($_SESSION['loginattempt'])?$_SESSION['loginattempt']:0) ?>" />
  <input type="hidden" name="action" value="createadmin" />
  <span class="label">E-mail:</span>
  <input type="text" name="login" placeholder="login/email" />
  <br>
  <span class="label">Password:</span><input type="password" name="password" placeholder="password" />
  <br>
  <span class="label">Repeat password:</span><input type="password" name="rpassword" placeholder="password" />
  <br />
  <?php 
   if (isset($loginerror))
	echo '<span id="loginerror">'.$loginerror.'</span><br />';
  ?>
  <span class="w3-tag w3-round button buttonwide" onclick="this.parentNode.submit();">Create account</span>
  </form>
  <div class="progress">
  <div class="step2"></div>
  <div>Step 2/3</div>
  </div>