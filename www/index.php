<!DOCTYPE html>
<html>
    <?php
    // Connexion BDD SQL:
    try{
        $connexion = new PDO('mysql:host=localhost;dbname=meteo;charset=utf8',
        'username',
        'password'
        );
        if ($_GET['time'] != null)
            $query = $connexion->prepare('SELECT `date`,`temperature`,`humidite` FROM `meteo`.`chambreAxel` WHERE `date` >= now() - INTERVAL '.$_GET['time'].' ORDER BY `id` DESC;');
        else
            $query = $connexion->prepare('SELECT `date`,`temperature`,`humidite` FROM `meteo`.`chambreAxel` WHERE `date` >= now() - INTERVAL 1 hour ORDER BY `id` DESC;');
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
        <script src="library/chart.js"></script>
        <script src="library/luxon.js"></script>
        <script src="library/chartjs-adapter-luxon.js"></script>
    </head>
    <body>
        <canvas id="chartContainer" style="height: 300px; width: 100%;"></canvas>
        <table>
            <thead>
                <tr>
                    <th colspan="2">Minimum</th>
                    <th colspan="2">Maximum</th>
                    <th colspan="2">Actuelle</th>
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
                    <td><?php echo $result[0]['temperature']." °C<br>(".$result[0]['date'].")"; ?></td>
                    <td><?php echo $result[0]['humidite']." %<br>(".$result[0]['date'].")"; ?></td>
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
        <script type="text/javascript">
            const ctx = document.getElementById('chartContainer').getContext('2d');
            const chartContainer = new Chart(ctx,
            {
                type: 'line',
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    plugins: {
                        title: {
                            display: true,
                            text: "Room"
                        },
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                tooltipFormat: 'DD T'
                            },
                            title: {
                                display: true,
                                text: 'Temps'
                            }
                        },
                        y: {
                            display: true,
                            position: 'left',
                            ticks: {
                                color: 'rgb(255,0,0)',
                            },
                            title: {
                                display: true,
                                color: 'rgb(255,0,0)',
                                text: 'Température (°C)'
                            }
                        },
                        y1: {
                            display: true,
                            position: 'left',
                            ticks: {
                                color: 'rgb(0,0,255)',
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            title: {
                                display: true,
                                color: 'rgb(0,0,255)',
                                text: 'Humidité (%)'
                            }
                        },
                    },
                },
                data:{
                    datasets: [
                        {
                            label: "Température (°C)",
                            yAxisID: "y",
                            borderColor: "rgb(255,0,0)",
                            backgroundColor: "rgba(255,0,0,0.5)",
                            data: [
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
                            label: "Humidité (%)",
                            yAxisID: "y1",
                            borderColor: "rgb(0,0,255)",
                            backgroundColor: "rgba(0,0,255,0.5)",
                            data: [
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
                }
            });
        </script>
    </body>
</html>