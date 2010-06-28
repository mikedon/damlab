<div id="content">
<table width="100%" id="content_table">
    <?php foreach($tasks as $t):?>
    <tr>
        <?php if($t['playable']):?>
        <td><a href="<?php echo base_url()?>index.php/increaseintellect/task/<?php echo $t['name']?>"><h4><?php echo $t['task_name']?></h4><img height="200" width="300" src="<?php echo base_url().$t['pic_url']?>"/><br></a></td>
        <?php else:?>
        <td>
            <span style="color:#990000"><h3>This task is currently unavailable.</h3></span>
            <div class="opaque">
               <h2><?php echo $t['task_name']?></h2> <img height="200" width="300" src="<?php echo base_url().$t['pic_url']?>"/><br>
            </div>
        </td>
        <?php endif;?>
        <td style="padding-top:40px;">
          <div class="data_vis_plot" id="data_vis_plot_<?php echo $t['name']?>" task_type="<?php echo $t['type']?>" task_id="<?php echo $t['id']?>" style="width: 475px; height:350px">
        </td>
    </tr>
    <?php endforeach;?>
</table>
</div>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  $('.data_vis_plot').each(function(){
    
    var container = this.id;
    var type = $(this).attr('task_type');
    var reggie = /training/;
    var x_min = 0;
    var y_min = 0;
    var x_max = 0;
    var y_max = 0;
    var y_tick_interval = 0;
    var max_score = 0;
    var max_score_x = 0;
    
    $.ajax({
        url: CI.base_url + 'index.php/user/get_data/' +  $(this).attr('task_id'),
        method: 'GET',
        dataType: 'json',
        success: onDataReceived
    });
    
    function onDataReceived(data){
      
      var seriesData = new Array();
      var user_scores = new Array();
      var avg_scores = new Array();
      
      if(reggie.test(type)){  //Training task
        for(d in data.user_data){
            if(max_score < parseInt(data.user_data[d].y)){
                max_score = parseInt(data.user_data[d].y);
                max_score_x = parseInt(data.user_data[d].x);
            }
        }
        for(d in data.user_data){
          if(data.user_data[d].x == max_score_x){
            user_scores.push({
              x: data.user_data[d].x,
              y: data.user_data[d].y,
              start_time: data.user_data[d].start_time,
              end_time: data.user_data[d].end_time,
              marker: {
                symbol: 'url(' + CI.base_url + 'assets/images/star.png)'
              },
              name: 'Best Score!'            
            })
          }else{
            user_scores.push({
              x: data.user_data[d].x,
              y: data.user_data[d].y,
              start_time: data.user_data[d].start_time,
              end_time: data.user_data[d].end_time
            })
          }
        }
        
        formatFunction = function(){
            if(this.point.name){
              return 'Best Score!<br>Session: ' + this.x +
				      '<br>Score: ' + this.y;
            }else{
              return 'Session: ' + this.x +
              '<br>Score: ' + this.y;
            }
        }
        userGraphType = 'scatter';
        avgGraphType = 'line';
        x_max = data.max_session;
        y_max = data.max_score;
        
      }else{  //Assessment Task
        for(d in data.user_data){
            if(max_score < parseInt(data.user_data[d].y)){
                max_score = parseInt(data.user_data[d].y);
                max_score_x = parseInt(data.user_data[d].x);
            }
        }
        
        for(d in data.user_data){
          if(data.user_data[d].x == max_score_x){
            user_scores.push({
              x: data.user_data[d].x,
              y: data.user_data[d].y,
              marker: {
                symbol: 'url(' + CI.base_url + 'assets/images/star.png)'
              },
              name: 'Best Score!'
            });
            
          }else{
            user_scores.push({
              x: data.user_data[d].x,
              y: data.user_data[d].y,
            })
          }
        }
        
        userGraphType = 'scatter';
        avgGraphType = 'line';
        x_max = data.user_data.length + 1;
        y_max = data.max_score;
        formatFunction = function(){
            if(this.point.name){
              return this.point.name + "<br>" + this.y;
            }else{
              return this.y;
            }
        }
      }
      
      user_series = {
        data : user_scores,
        type : userGraphType,
        name : "High Score",
      }
      
      seriesData.push(user_series);
      
      //Get avg scores
      for(d in data.avg_data){
        avg_scores.push({
          x: data.avg_data[d].x,
          y: data.avg_data[d].y
        })
      }
      avg_series = {
        data : avg_scores,
        type : avgGraphType,
        name : 'Avg High Score',
      }
      seriesData.push(avg_series);
      
      if(y_max > 1000){
        y_tick_interval = 500;
      }else{
        y_tick_interval = 100;
      }
      
      //Create chart
      var chart = new Highcharts.Chart({
        chart: {
          defaultSeriesType: "line",
          renderTo: container,
          plotShadow: true,
        },
        credits: {
          enabled: false
        },
        title: {
          text: "Your High Scores vs Avg High Score",
          style: {
				    margin: '10px 0 0 20px' // center it
				  }
        },
        xAxis: {
          min: x_min,
          max: x_max,
          tickInterval: 1
        },
        yAxis: {
          title: {
            text: "Score"
          },
          min: y_min,
          max: y_max,
          tickInterval: y_tick_interval
        },
        tooltip: {
				  formatter: formatFunction
				},
        series: seriesData
      });
    }
  });
});
</script>
</body>
</html>
