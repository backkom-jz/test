Push.php

<?php
    header('Content-Type:text/event-stream');//通知浏览器开启事件推送功能
    header('Cache-Control:no-cache');//告诉浏览器当前页面不进行缓存

    $time = date('r');
    echo "data: The server time is: {$time}\n\n";

    ob_flush();//刷新
    flush();//刷新
?>

Push.html

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>数据推送</title>
</head>
<body>
    <pre id="result">
        Initializing...
    </pre>
    <script type="text/javascript">

        if(typeof(EventSource)!=="undefined")
          {
          var source = new EventSource("./push.php");
          source.onmessage=function(event){              
            document.getElementById("result").innerHTML=event.data + "<br />";
            };
          }
        else
          {
          document.getElementById("result").innerHTML="Sorry, your browser does not support server-sent events...";
          }
    </script>
</body>
</html>
