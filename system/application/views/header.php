<html>
<head>
<title><?php echo $header?></title>
<link rel="shortcut icon" href="http://thehygeneproject.org/damlab/assets/images/brain.ico" type="image/x-icon"/>
<link rel="icon" href="http://thehygeneproject.org/damlab/assets/images/brain.ico" type="image/x-icon"/>

<?php foreach($css as $c):?>
    <link rel="stylesheet" media="screen" type="text/css" href="<?php echo base_url();?>assets/themes/<?php echo $c?>"/>
<?php endforeach;?>
   <script type="text/javascript">
    var CI = { 
      'base_url': '<?php echo base_url(); ?>' 
    };
    </script>
<?php foreach($js as $j):?>
 <?php if(preg_match('/excanvas/i',$j)):?>
  <!--[if IE]>
    <script type="text/javascript" src="<?php echo base_url();?>assets/js/<?php echo $j?>"></script>
  <![endif]-->
 <?php else:?>
    <script type="text/javascript" src="<?php echo base_url();?>assets/js/<?php echo $j?>"></script>
 <?php endif;?>
<?php endforeach;?>
 
</head>
<body>
    <div id="userInfo">
      <?php if(get_instance()->session->userdata('uid')):?>
        Welcome: <?php echo get_instance()->session->userdata('uid'); ?> -
        <?php if(isset($curr_session)):?>
          <span style="color: #619DF8;">You have completed <?php echo $curr_session?>/<?php echo $max_session?> sessions </span> -
        <?php endif;?>
        <?php echo anchor('increaseintellect/logout','Log Out')?>
      <?php endif;?>
    </div>
  <div id="header">
    <?php if(get_instance()->session->userdata('uid')):?>
    <?php endif;?>
    <a href="<?php echo base_url()?>index.php/increaseintellect">
      <span id="title">Diagnostic Cognitive Testing and Cognitive Training</span>
    </a>
  </div>
