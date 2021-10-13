<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css"> -->
    <!-- <script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>
    
</head>
<body>

    <?php

        $pool = load("data");
        // dump($pool);

        $gps = get_gps($pool);

        $pilots = get_data($pool, "pilot");
        $teams = get_data($pool, "team");
        $constructors = get_data($pool, "constructor");
        dump($teams);

        $series = [];
        for ($i=0; $i < 6; $i++) { 
            $series[] = get_results($pool, "team", $teams[$i]);
        }
        // dump($series);
        //arsort($res);

        //$pilots = json_encode(array_keys($res));
        //$points = json_encode(array_values($res));

        // dump($res);

    ?>
    <canvas id="myChart" width="400" height="400"></canvas>

</body>
</html>


<script>
var ctx = document.getElementById('myChart');

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
            label: '# of Votes',
            data: [12, 19, 3, 5, 2, 3],
            borderWidth: 1
        },
        {
            label: '# of Votes',
            data: [5, 4, 30, 55, 2, 3],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php


function load($dir) {

    $res = [];

    $files = array_diff(scandir($dir), array('..', '.'));

    foreach ($files as $file) {
        // dump($file);
        if (preg_match("/^gp(\d+)-(.+)\.csv$/", $file, $matches)) {
            // dump($matches);
            $key = $matches[1] . "-" . $matches[2];
            $res[$key] = load_file($dir . "/" . $file);
        }
    }
    
    return $res;
}

function load_file($file) {

    $res = [];

    $handle = fopen($file, "r");
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
        $res[] = [
            "pilot" => $data[3],
            "points" => $data[1],
            "team" => $data[5],
            "constructor" => $data[6],
        ];
    }
    fclose($handle);

    return $res;    
}

function get_gps($p) {

    $res = [];

    $gps = array_keys($p);
    sort($gps);

    foreach ($gps as $gp_name) {
        $res[] = explode("-", $gp_name)[1];
    }

    return $res;
}

function get_data_by_gp($p, $key) {

    $res = [];

    foreach ($p as $gp_name => $result) {
        foreach ($result as $line) {
            $n = $line[$key];
            $p = $line["points"];
            if (!array_key_exists($gp_name, $res)) $res[$gp_name] = [];
            if (!array_key_exists($n, $res[$gp_name])) $res[$gp_name][$n] = 0;
            $res[$gp_name][$n] += intval($p);
        }
    }

    return $res;
}

function get_data($p, $key) {

    $res = [];

    $data = get_data_by_gp($p, $key);

    foreach ($data as $gp_name => $result) {
        foreach ($result as $elt => $points) {
            if (!array_key_exists($elt, $res)) $res[$elt] = 0;
            $res[$elt] += $points?$points:0;
        }
    }

    arsort($res);

    return array_keys($res);
}

function get_results($pool, $key, $search) {

    $res = [];

    $data = get_data_by_gp($pool, $key);

    $res2 = [];
    $total = 0;
    foreach ($data as $result) {
        if (array_key_exists($search, $result)) {
            $v = $result[$search]?$result[$search]:0;
        }
        else {
            $v = 0;
        }
        $total += $v;
        $res2[] = $total;
    }

    return $res2;
}

function dump($v) {
    echo "<pre>";
    print_r($v);
    echo "</pre>";
}