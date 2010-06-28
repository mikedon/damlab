swfobject.registerObject("task", "10", CI.base_url+"assets/js/expressInstall.swf");

$(document).ready(function(){
	
	$('#instructions').hide();
	
	if($('#instructions').length != 0 ){
	
		var boxy = new Boxy('#instructions',{
			title: "Instructions",
			modal: true,
			show: false
		 });
		
		function showInstructions(){
			boxy.show();
		}
		
		showInstructions();
		
		$('#instruction_link').click(function(){
			showInstructions();
			return false;
		});
	
	}
});
