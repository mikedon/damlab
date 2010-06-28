<?php
//Load CI DB Instance since we are not coming through index.php
require_once('amfci_db.php');

// Use this path if you are using standard install
require_once(AMFSERVICES.'/../../../models/model_sentencical_training.php');
class Service_sentencical_training extends Model_sentencical_training{

  function insert($task_data){
    foreach($task_data as $var => $val){
      $this->$var = trim($val);   
    }
    $this->save();
        
    return array($this->db->last_query());
  }
  
  function get_sentences($user_id){
    
    //$sentences = read_file('../../../../../assets/multimedia/tasks/Sentencical_training/user_lists/'.trim($user_id).'.list');
    $sentences = read_file(base_url() . 'assets/multimedia/tasks/Sentencical_training/user_lists/'.trim($user_id).'.list');
    if($sentences){
      return unserialize($sentences);
    }else{
      $row = 0;
      $files = array("List1.csv","List2.csv","List5.csv","List6.csv","List9.csv","List10.csv");
      foreach($files as $file){
        $handle = fopen('../../../../../assets/multimedia/tasks/Sentencical_training/sentences/'.$file,'r');
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if($row++ == 0)
            continue;
          $sentence = new stdClass;
          $sentence->sentenceID = $data[0];
          $sentence->sentence = $data[1];
          $sentence->question = $data[3];
          $sentence->answer = $data[4];
          $sentences[] = $sentence;
        }
        shuffle($sentences);
      }
      return $sentences;
    }
    return trim($user_id);
  }
  function set_sentences($user_id,$sentences){
    if(write_file('../../../../../assets/multimedia/tasks/Sentencical_training/user_lists/'.trim($user_id).'.list',serialize($sentences))){
      return "sentences updated";
    }else{
      return "unable to update";
    }
  }
}