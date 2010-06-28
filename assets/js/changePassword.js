$(document).ready(function(){
  
  $('#changePass').submit(function(){
    var errorMSG = $(document.createElement('div'));
    errorMSG.css('color','#990000');
    
    $('#passw1 + div').remove();
    $('#passw2 + div').remove();
    
    if($('#passw1').val().length < 6){
      errorMSG.text('Password must be at least 6 characters long.');
      $('#passw1').after(errorMSG);
      return false; 
    }    
    if($('#passw1').val() != $('#passw2').val()){
      errorMSG.text('Passwords do not match.');
      $('#passw2').after(errorMSG);
      return false;
    }
  });
});