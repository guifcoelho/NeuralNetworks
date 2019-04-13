<?php

namespace guifcoelho\NeuralNetworks\Tests;

use guifcoelho\NeuralNetworks\Ann;
use guifcoelho\NeuralNetworks\Libs\Matrix;
use guifcoelho\NeuralNetworks\Libs\Helpers;

use League\Csv\Reader;


/**
 * Class AnnTest
 *
 * @category Test
 * @package  guifcoelho\NeuralNetworks\Tests
 */
class AnnTest extends TestCase
{

    public function test_create_model()
    {
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
            "problem_type" => "classification",
            "hidden_layers" => [
                ["function" => "sigmoid", "nodes" => 2]
            ],
            "activation" => "sigmoid",
            "classification_threshold" => 0.6,
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
    }
}
