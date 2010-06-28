<div id="content" class="content">
  <?php if($user_exists):?>
    <p>You have successfully logged in as : <br><br><?php echo $user_id;?><br><br>
    After clicking CONTINUE you will be taken to the homepage.</p>
    <br><br>
    <input type="button" value="CONTINUE" onclick="Javascript:window.location = '<?php echo base_url();?>index.php/increaseintellect'"/>
  <?php else:?>
    <p>User: <br><br><?php echo $user_id;?><br><br> does not exist.  <br><br>After clicking CONTINUE
    you will be redirected to the researcher page.</p>
    <br><br>
    <input type="button" value="CONTINUE" onclick="Javascript:window.location = '<?php echo base_url();?>index.php/increaseintellect/research'"/>
  <?php endif;?>

</div>