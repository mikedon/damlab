<?php
  $CI =& get_instance();
  $CI->load->model('model_user','user',TRUE);
?>

<div class="navbar" id="navbar">
  <ul>
    <li>
      <?php if(!$CI->uri->segment(2) || $CI->uri->segment(2) == 'home'):?>
        <a class="selected" href="<?php echo base_url()?>index.php/increaseintellect">Home</a>
      <?php else:?>
        <a href="<?php echo base_url()?>index.php/increaseintellect">Home</a>
      <?php endif;?>
    </li>
    
    <?php if($CI->user->has_permission('assessment')):?>
    <li>
      <?php if($CI->uri->segment(2) == 'assessment'):?>
        <a class="selected" href="<?php echo base_url()?>index.php/increaseintellect/assessment">Assessment</a>
      <?php else:?>
        <a href="<?php echo base_url()?>index.php/increaseintellect/assessment">Assessment</a>
      <?php endif;?>
    </li>
    <?php endif;?>
    
    <?php if($CI->user->has_permission('training')):?>
    <li>
      <?php if($CI->uri->segment(2) == 'training'):?>
        <a class='selected' href="<?php echo base_url()?>index.php/increaseintellect/training" >Training</a>
      <?php else:?>
        <a href="<?php echo base_url()?>index.php/increaseintellect/training" >Training</a>
      <?php endif;?>
    </li>
    <?php endif;?>
    
    <?php if($CI->user->has_permission('research')):?>
      <li>
        <?php if($CI->uri->segment(2) == 'research'):?>
          <a class='selected' href="<?php echo base_url()?>index.php/increaseintellect/research" >Research</a>
        <?php else:?>
          <a href="<?php echo base_url()?>index.php/increaseintellect/research" >Research</a>
        <?php endif;?>
      </li>
    <?php endif;?>
  </ul>
</div>
