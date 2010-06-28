<?php
//Load CI DB Instance since we are not coming through index.php
require_once('amfci_db.php');

// Use this path if you are using standard install
require_once(AMFSERVICES.'/../../../models/model_numberpiles_training.php');
class Service_numberpiles_training extends Model_numberpiles_training{

  function insert($task_data){
    foreach($task_data as $var => $val){
      $this->$var = trim($val);   
    }
    $this->save();
        
    return $task_data;
  }
  
  function insert_leveldata($task_data){
    $targetValue = trim($task_data['targetValue']);
    $user_values = trim($task_data['user_values']);
    $user_id = trim($task_data['user_id']);
    $id = trim($task_data['id']);
    $levelNumber = trim($task_data['levelNumber']);
    
    $ret = $this->save_levelData($user_id, $user_values, $targetValue, $levelNumber, $id);
    return $ret;
  }
  
  function get_highest_score($user_id){
    $this->user_id = trim($user_id);
    $scores = $this->get_high_score();
    
    return $scores;
    
    //$high_score = 0;
    //foreach($scores as $id => $score){
    //  if($high_score < $score){
    //    $high_score = $score; 
    //  }
    //}
    //return $scores;
  }
  
  function get_schema($schemaName){
    $schemaName = trim($schemaName);
    //$data = read_file('./assets/multimedia/tasks/NumberPiles/LevelSchema/'.$schemaName.'.xml');
    $data = read_file('default_training.xml');
    
    if($data){
      return $data;
    }else{
      return 'error';
    }  
    
  }
  
  function test(){
    return "blah";
  }
}