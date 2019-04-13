<?php

require_once __DIR__.'\vendor\autoload.php';

use guifcoelho\NeuralNetworks\Ann;
use guifcoelho\NeuralNetworks\Functions;
use guifcoelho\NeuralNetworks\Libs\Matrix;
use guifcoelho\NeuralNetworks\Libs\Helpers;

use League\Csv\Reader;
use League\Csv\Writer;

//Read data
// $csv = Reader::createFromPath(__DIR__.'\test_data.csv', 'r');
$csv = Reader::createFromPath(__DIR__.'\data_multi_class.csv', 'r');

$csv->setHeaderOffset(0);
$records = $csv->getRecords();
$X = [];
$Y = [];
foreach($records as $offset => $record){
    $X[] = [(float)$record["x1"], (float)$record["x2"]];
    // $Y[] = (int)$record["y"];
    $Y[] = [(int)$record["y1"], (int)$record["y2"], (int)$record["y3"], (int)$record["y4"]];
}

//Creates model
$config = [
    "problem_type" => "classification",
    "hidden_layers" => [
        ["function" => "sigmoid", "nodes" => 3]
    ],
    "activation" => "sigmoid",
    "classification_threshold" => 0.5,
    "learning_rate" => 0.01,
    "epochs" => 10000,
    "print_intervals" => 10
];
$model = new Ann($config);

//Train model
$model->loadDataset($X, $Y);   
$model->train();

// //Test model
$model->loadDataset($X, $Y, false);
$results = $model->feed_forward(null,false);

//Export results
$header = ['Y_hat1', 'Y_hat2', 'Y_hat3', 'Y_hat4'];
// $header = ['Y_hat'];
$records = $results["Y_hat"]->getMatrix();
$csv = Writer::createFromPath(__DIR__.'\results.csv', 'w+');
$csv->insertOne($header);
$csv->insertAll($records);