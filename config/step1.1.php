<input type="hidden" name="trial" value="<?php echo (isset($_SESSION['loginattempt'])?$_SESSION['loginattempt']:0) ?>" />
  <input type="hidden" name="action" value="setupstep2" />
  <ul>
  <?php 
	echo $_SESSION['setuplog'].'<br />';
  ?>
  </ul>
  <span class="w3-tag w3-round button" onclick="this.parentNode.submit();">Next</span>
  </form>
  <div class="progress">
  <div class="step1"></div>
  <div>Step 1/3</div>
  </div>