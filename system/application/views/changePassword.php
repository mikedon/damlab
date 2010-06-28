

<h4>Before entering you must reset your password.</h4>
<?php echo form_open('user/setPassword',array('id'=>'changePass'))?>
  <label for="passw1">New Password:<label><br><input type="password" name="passw1" id="passw1"/><br><br>
  <label for="passw2">ReType Password:</label><br><input type="password" name="passw2" id="passw2"/><br><br>
  <input type="submit" name="changePasswordSubmit" value="Update"/>
</form>

</body>
</html>