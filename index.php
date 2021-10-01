<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
    <script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
</head>
<body>

    <?php

        $pool = load("data");
        // dump($pool);

        $gps = get_gps($pool);

        $res = get_pilots($pool);
        // dump($res);

        $series = [];
        for ($i=0; $i < 10; $i++) { 
            $series[] = get_pilots_results($pool, $res[$i]);
        }
        // dump($series);
        //arsort($res);

        //$pilots = json_encode(array_keys($res));
        //$points = json_encode(array_values($res));

        // dump($res);

    ?>
    <div class="ct-chart ct-perfect-fourth"></div>
</body>
</html>

<script>
var data = {
  // A labels array that can contain any sort of values
  labels: <?= json_encode($gps) ?>,
  // Our series array that contains series objects or in this case series data arrays
  series: [
    <?php foreach($series as $serie): ?>
        <?= json_encode($serie) ?>,
    <?php endforeach; ?>

  ]
};

// Create a new line chart object where as first parameter we pass in a selector
// that is resolving to our chart container element. The Second parameter
// is the actual data object.
new Chartist.Line('.ct-chart', data, {
        high: 330,
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'Value'
                },
                min: 0,
                max: 200,
                ticks: {
                // forces step size to be 50 units
                    stepSize: 10
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
            $res[$gp_name][$n] = $p;
        }
    }

    return $res;
}

function get_pilots($p) {

    $res = [];

    $data = get_data_by_gp($p, "pilot");

    foreach ($data as $gp_name => $result) {
        foreach ($result as $pilot => $points) {
            $res[$pilot] += $points;
        }
    }

    arsort($res);

    return array_keys($res);
}

function get_pilots_results($pool, $pilot) {

    $res = [];

    $data = get_data_by_gp($pool, "pilot");

    $res2 = [];
    $total = 0;
    foreach ($data as $result) {
        if (array_key_exists($pilot, $result)) {
            $v = $result[$pilot];
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