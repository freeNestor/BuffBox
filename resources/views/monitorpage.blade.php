@include('header')
<style type="text/css">
    .bar-r {
        padding-top: 20px;
        background-color: #FF8040;
        text-align: right;
        padding-right: 300px;
        height: 105px;
        border: 0;
    }
    .bar-r text {
        margin-top: 50px;
        margin-right: 200px;
    }
</style>
<script type="text/javascript" >
$(document).ready(function(){
    $("#joblist").click(function(){
        $("li").attr('class','');
        $("#li2").attr('class','active');
        $.get("/joblist",function(data,status){
            $("#pad").html(data);
            $("title").text("Job Page");
        });

    });
    $("#nodelist").click(function(){
        $("li").attr('class','');
        $("#li3").attr('class','active');
        $.get("/nodelist?fromHome=1",function(data,status){
            $("#pad").html(data);
            $("title").text("Node Page");
        });
    });
    
    $("#tasklist").click(function(){
        $("li").attr('class','');
        $("#li7").attr('class','active');
        $.get("/tasklist",function(data,status){
            $("#pad").html(data);
            $("title").text("Task Page");
        });
    });
    
    $("#configpage").click(function(){
        $("li").attr('class','');
        $("#li5").attr('class','active');
        $.get("/confighome",function(data,status){
            $("#pad").html(data);
            $("title").text("Config Page");
        });
    });
    
    var option = {
        title : {
            text: 'Top 10 Executes Jobs',
            subtext: '',
            x:'center'
        },
        tooltip : {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        series: [
            {
                name: 'Times/Num.',
                type: 'pie',
                radius : '55%',
                center: ['50%', '60%'],
                data:[],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };
    var option2 = {
        tooltip: {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        legend: {
            left: 'center',
            data: ['Job1','Job2','Job3','Job4','Job5']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis:  {
            type: 'value'
        },
        yAxis: {
            type: 'category',
            data: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']
        },
        series: [
            {
                name: 'Job1',
                type: 'bar',
                stack: 'executes',
                label: {
                    normal: {
                        show: true,
                        position: 'insideRight'
                    }
                },
                data: [320, 302, 301, 334, 390, 330, 320]
            },
            {
                name: 'Job2',
                type: 'bar',
                stack: 'executes',
                label: {
                    normal: {
                        show: true,
                        position: 'insideRight'
                    }
                },
                data: [120, 132, 101, 134, 90, 230, 210]
            },
            {
                name: 'Job3',
                type: 'bar',
                stack: 'executes',
                label: {
                    normal: {
                        show: true,
                        position: 'insideRight'
                    }
                },
                data: [220, 182, 191, 234, 290, 330, 310]
            },
            {
                name: 'Job4',
                type: 'bar',
                stack: 'executes',
                label: {
                    normal: {
                        show: true,
                        position: 'insideRight'
                    }
                },
                data: [150, 212, 201, 154, 190, 330, 410]
            },
            {
                name: 'Job5',
                type: 'bar',
                stack: 'executes',
                label: {
                    normal: {
                        show: true,
                        position: 'insideRight'
                    }
                },
                data: [820, 832, 901, 934, 1290, 1330, 1320]
            }
        ]
    };
    var mychart = echarts.init(document.getElementById("pie"),'shine');
    var mychart2 = echarts.init(document.getElementById("bar"),'infographic');
    mychart.setOption(option);
    mychart2.setOption(option);
    $.ajax({
        url:'/getpiedata',
        type:'get',
        dataType:'json',
        beforeSend:function(x){
            mychart.showLoading();
        },
        success:function(res,s) {
            mychart.hideLoading();
            mychart.setOption({
                legend: {
                    y: 'center',
                    left: 'left',
                    orient: 'vertical',
                    data: res['names']
                },
                series: [{
                    data: res['datas']
                }]
            });
        }
    });
    $.ajax({
        url:'/getstatesum',
        type:'get',
        dataType:'json',
        beforeSend:function(x){
            mychart2.showLoading();
        },
        success:function(res,s) {
            mychart2.hideLoading();
            mychart2.setOption({
                title: {
                    text: 'Job State Summary',
                    x: 'center'
                },
                legend: {
                    y: 'center',
                    left: 'left',
                    orient: 'vertical',
                    data: res['names']
                },
                color: res['color'],
                series: [{
                    data: res['datas']
                }]
            });
        }
    });
});
</script>
<body>
 <nav class="navbar navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">BuffBox</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li id="li1" class="active"><a href="/"><span class="glyphicon glyphicon-home">Home</span></a></li>
            <li id="li2" ><a href="#"><span id="joblist" class="glyphicon glyphicon-th-list">Job</span></a></li>
            <li id="li7" ><a href="#"><span id="tasklist" class="glyphicon glyphicon-th-list">Task</span></a></li>
            <li id="li3" ><a href="#"><span id="nodelist" class="glyphicon glyphicon-indent-left">Node</span></a></li>
            <li id="li6" ><a href="#"><span id="Command" class="glyphicon glyphicon-expand">CommandLine</span></a></li>
            <li id="li4" ><a href="#"><span class="glyphicon glyphicon-list-alt">Report</span></a></li>
            <li id="li5" ><a href="#"><span id="configpage" class="glyphicon glyphicon-wrench">Configure</span></a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Welcome: {{ $user }}</a></li>
            <li><a href="/logout"><span class="glyphicon glyphicon-user">Logout</span></a></li>
          </ul>
        </div>
      </div>
 </nav>
    <div class="container-fluid"  style="height: 500px;margin-top: 50px;">
        <div id="pad" class="row" style="margin: 0;margin-top: 1%;padding: 0;">
            <div><h1>Summary</h1></div>
            <div class="col-sm-6">
                <div id="pie" style="width: 700px;height: 400px;"></div>
                <div id="piestate" style="width: 700px;height: 200px;"></div>
            </div>

            <div class="col-sm-6">
                <div id="bar" style="width: 600px;height: 400px;"></div></div>
        </div>
    </div>
</body>
</html>