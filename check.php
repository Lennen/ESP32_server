<?
// Script to check are login and pass correct. If so, there is a page displayed in the case of Success.

include("_dbconnect.php");

if (isset($_COOKIE['id']) and isset($_COOKIE['hash']))
{
    $query = mysqli_query($link, "SELECT *,INET_NTOA(user_ip) AS user_ip FROM users WHERE user_id = '".intval($_COOKIE['id'])."' LIMIT 1");
    $userdata = mysqli_fetch_assoc($query);

    if(($userdata['user_hash'] !== $_COOKIE['hash']) or ($userdata['user_id'] !== $_COOKIE['id'])
 or (($userdata['user_ip'] !== $_SERVER['REMOTE_ADDR'])  and ($userdata['user_ip'] !== "0")))
    {
        setcookie("id", "", time() - 3600*24*30*12, "/");
        setcookie("hash", "", time() - 3600*24*30*12, "/");
        print "Хм, что-то не получилось";
    }
    else
    {
        print "Привет, ".$userdata['user_login'].". Всё работает!";
        $user1 = $userdata['user_login'];
        $user_id = intval($_COOKIE['id']);
        
        //Запрос для случая с user_id (запрос http://esp32.tfeya.ru/check.php?val=700&user_id=8)
         if($_GET['val']!="" & $_GET['user_id']!="" & $_GET['user_id']==$user_id)
        {
            $get_user_id = filter_var($_GET['user_id'],FILTER_SANITIZE_STRING);
            $get_val = filter_var($_GET['val'],FILTER_SANITIZE_STRING);
            echo($get_user_id);
            $result = mysqli_query($link,"INSERT INTO `vals`(`user_id`, `val`) VALUES ($get_user_id,$get_val)");
            //echo($result);
        }
        
        //Запрос для случая с user_login (запрос http://esp32.tfeya.ru/check.php?val=700&user_login=kras1)
         if($_GET['val']!="" & $_GET['user_login']!="" & $_GET['user_login']==$userdata['user_login'])
        {
            $get_user_login = filter_var($_GET['user_login'],FILTER_SANITIZE_STRING);
            $get_val = filter_var($_GET['val'],FILTER_SANITIZE_STRING);
            echo($get_user_login);
            $result = mysqli_query($link,"INSERT INTO `vals`(`user_id`, `val`) VALUES ($user_id,$get_val)");
            //echo($result);
        }
        
        
        
        $query1 = mysqli_query($link,"SELECT val, datetime FROM vals WHERE user_id='$user_id'");
        while ($row = $query1->fetch_array(MYSQLI_ASSOC)) {
            foreach ($row as $key=>$value)
                $arr[$key][] = $value;
        }
        //$data1 = mysqli_fetch_assoc($query1);
        print_r($arr);
        echo($arr['val'][0]);
     ?>
        
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
          google.charts.load('current', {'packages':['corechart']});
          google.charts.setOnLoadCallback(drawChart);
    
          function drawChart() {
            var data = google.visualization.arrayToDataTable([
              ['time', 'Sensor Value'],
              <?php $cnt_all = count($arr['val']); $x = $cnt_all-10; while ($x++<$cnt_all-1): ?>
              ['<?=$x?>',  <?=$arr['val'][$x]?>],
              <?php endwhile ?>
              ['<?$cnt_all-1?>',  <?=$arr['val'][$cnt_all-1]?>]
            ]);
    
            var options = {
              title: 'Sensor Values',
              
              legend: { position: 'bottom' }
            };
    
            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
    
            chart.draw(data, options);
          }
        </script>
    </head>
    
    <body>
        <?=$arr['val'][0];?>
        <div id="curve_chart" style="width: 900px; height: 500px"></div>
    </body>
</html>
      
<?
    }
}
else
    {
        print "Включите куки";
    }
?>