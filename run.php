<?php

require_once __DIR__.'\vendor\autoload.php';

use guifcoelho\NeuralNetworks\Ann;
use guifcoelho\NeuralNetworks\Libs\Matrix;
use guifcoelho\NeuralNetworks\Libs\Helpers;

use League\Csv\Reader;
use League\Csv\Writer;

//Read data
$csv = Reader::createFromPath(__DIR__.'\test_data.csv', 'r');
$csv->setHeaderOffset(0);
$records = $csv->getRecords();
$X = [];
$Y = [];
foreach($records as $offset => $record){
    $X[] = [(float)$record["x1"], (float)$record["x2"]];
    $Y[] = (int)$record["y"];
}

//Creates model
$config = [
    "hidden_layers" => [
        ["function" => "sigmoid", "nodes" => 2]
    ],
    "activation" => "sigmoid",
    "learning_rate" => pow(10,-3),
    "epochs" => 1000,
    "print_results" => 10
];
$model = new Ann($config);

//Train model
$model->loadDataset($X, $Y);   
$model->train();

//Test model
$model->loadDataset($X, $Y, false);
$results = $model->feed_forward(null,false);

//Export results
$header = ['Y_hat'];
$records = $results["Y_hat"]->getMatrix();
$csv = Writer::createFromPath(__DIR__.'\results.csv', 'w+');
$csv->insertOne($header);
$csv->insertAll($records);