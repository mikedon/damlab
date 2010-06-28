<div id="content" class="content" style="padding-top:10px">
  	<?php if($instructions != ""):?>
		<a href='' id="instruction_link" style="float:left;padding-left:10px;text-decoration: underline;color: #619DF8;">Instructions</a>
			<div style="width:500px;" id="instructions">
				<p><?php echo $instructions?></p>	
			</div>
		<?php endif;?>
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="600" height="500" id="task">
	<param name="movie" value="<?php echo base_url() . $task_url?>" />
	<param name="wmode" value="opaque"/>
	<param name="flashvars" value="user_id=<?php echo get_instance()->session->userdata('uid')?>
	&amp;url=<?php echo base_url()?>
	<?php if(isset($curr_session)):?>
		&amp;session=<?php echo $curr_session?>
  <?php endif;?>
	<?php if(isset($time_limit)):?>
	  &amp;time=<?php echo $time_limit?>
  <?php endif;?>" />
	    <!--[if !IE]>-->
	<object type="application/x-shockwave-flash" data="<?php echo base_url() . $task_url?>" width="600" height="500">
		<param name="wmode" value="opaque"/>
	    <param name="flashvars" value="user_id=<?php echo get_instance()->session->userdata('uid')?>
			&amp;url=<?php echo base_url()?>
			<?php if(isset($curr_session)):?>
				&amp;session=<?php echo $curr_session?>
		  <?php endif;?>
			<?php if(isset($time_limit)):?>
				&amp;time=<?php echo $time_limit?>
		  <?php endif;?>" />


	    <!--<![endif]-->
	    <!--<a href="http://www.adobe.com/go/getflashplayer">
	    <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
	    </a>-->
			<p>A minimum of Flash Player Version 9 is required for this task</p>
	    <!--[if !IE]>-->
	</object>
	<!--<![endif]-->
    </object>
	
</div>
</body>
</html>


