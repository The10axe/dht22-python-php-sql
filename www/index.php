<!DOCTYPE html>
<html>
    <?php
    try{
        $connexion = new PDO('mysql:host=localhost;dbname=sensor;charset=utf8',
        'username',
        'password'
        );
        if ($_GET[time] != null)
            $query = $connexion->prepare('SELECT `date`,`temperature`,`humidite` FROM `sensor`.`bedroom` WHERE `date` >= now() - INTERVAL '.$_GET[time].' ORDER BY `id` DESC;');
        else
            $query = $connexion->prepare('SELECT `date`,`temperature`,`humidite` FROM `sensor`.`bedroom` WHERE `date` >= now() - INTERVAL 1 hour ORDER BY `id` DESC;');
        $query->execute();
        $result = $query->fetchAll();
    }
    catch (Exception $e)
    {
		echo $e->getMessage();
            die('Erreur: '.$e->getMessage());
    }
    ?>
    <head>
        <meta http-equiv="refresh" content="60"/>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <title>Thermomètre connecté</title>
        <script type="text/javascript">
            window.onload = function () {
            var chart = new CanvasJS.Chart("chartContainer",
            {
                toolTip: {
                    shared: true
                },
                theme: "light2",
                title: {
                    text: "Bedroom"
                },
                axisX: {
                    title: "Temps",
                    valueFormatString: "DD/MM/YY HH:mm"
                },
                
                axisY: [
                {
                    title: "Température",
                    lineColor: "#FF0000",
                    tickColor: "#FF0000",
                    labelFontColor: "#FF0000",
                    titleFontColor: "#FF0000",
                    lineThickness: 2
                },
                {
                    title: "Humidité",
                    lineColor: "#0000FF",
                    tickColor: "#0000FF",
                    labelFontColor: "#0000FF",
                    titleFontColor: "#0000FF",
                    lineThickness: 2
                }
                ],
                
                data: [
                {
                    name: "Température",
                    type: "spline",
                    color: "#FF0000",
                    xValueType: "dateTime",
                    xValueFormatString: "DD/MM/YYYY 'à' HH':'mm",
                    yValueFormatString: "##.0 °C",
                    dataPoints: [
                        <?php
                            foreach ($result as $line){
                        ?>
                        {x: <?php echo strtotime($line['date'])*1000;?>, y: <?php echo $line['temperature'];?> },
                        <?php
                            }
                        ?>
                    ]
                },
                {
                    name: "Humidité",
                    type: "spline",
                    axisYIndex: 1,
                    color: "#0000FF",
                    minimum: 0,
                    maximum: 100,
                    yValueFormatString: "##.0 '%'",
                    xValueType: "dateTime",
                    dataPoints: [
                        <?php
                            foreach ($result as $line){
                        ?>
                        { x: <?php echo strtotime($line['date'])*1000;?>, y: <?php echo $line['humidite'];?>},
                        <?php
                            }
                        ?>
                    ]
                }
            ]
        });
        chart.render();
    }
                </script>
        <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script></head>
    </head>
    <body>
        <div id="chartContainer" style="height: 300px; width: 100%;"></div>
        <table>
            <thead>
                <tr>
                    <th colspan="2">Minimum</th>
                    <th colspan="2">Maximum</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php
                    $humidite = 100;
                    $temperature = 100;
                    foreach ($result as $line){
                        if ($humidite > $line['humidite']){
                            $humidite = $line['humidite'];
                            $hdate = $line['date'];
                        }
                        if ($temperature > $line['temperature']){
                            $temperature = $line['temperature'];
                            $tdate = $line['date'];
                        }
                    }
                    echo $temperature." °C<br>(".$tdate.")";
                    ?></td>
                    <td><?php echo $humidite." %<br>(".$hdate.")"; ?></td>
                    <td><?php
                    $humidite = 0;
                    $temperature = -100;
                    foreach ($result as $line){
                        if ($humidite < $line['humidite']){
                            $humidite = $line['humidite'];
                            $hdate = $line['date'];
                        }
                        if ($temperature < $line['temperature']){
                            $temperature = $line['temperature'];
                            $tdate = $line['date'];
                        }
                    }
                    echo $temperature." °C<br>(".$tdate.")";
                    ?></td>
                    <td><?php echo $humidite." %<br>(".$hdate.")"; ?></td>
                </tr>
            </tbody>
        </table>
        <p>Voir les relevés des dernières:</p>
        <ul>
            <li><a href="?time=30 minute">30 minutes</a></li>
            <li><a href="?time=1 hour">1 heure</a></li>
            <li><a href="?time=3 hour">3 heures</a></li>
            <li><a href="?time=6 hour">6 heures</a></li>
            <li><a href="?time=9 hour">9 heures</a></li>
            <li><a href="?time=12 hour">12 heures</a></li>
            <li><a href="?time=1 day">24 heures</a></li>
            <li><a href="?time=3 day">72 heures</a></li>
            <li><a href="?time=1 week">1 semaine</a></li>
            <li><a href="?time=1 month">1 mois</a></li>
        </ul>
    </body>
</html>