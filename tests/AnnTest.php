<?php

namespace guifcoelho\NeuralNetworks\Tests;

use guifcoelho\NeuralNetworks\Libs\Matrix;
use guifcoelho\NeuralNetworks\Libs\Helpers;
use guifcoelho\NeuralNetworks\Ann;

use League\Csv\Reader;
use League\Csv\Writer;


/**
 * Class AnnTest
 *
 * @category Test
 * @package  guifcoelho\NeuralNetworks\Tests
 */
class AnnTest extends TestCase
{

    public function test_create_model(){

        $csv = Reader::createFromPath(__DIR__.'\test_data.csv', 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        $X = [];
        $Y = [];
        foreach($records as $offset => $record){
            $X[] = [(float)$record["x1"], (float)$record["x2"]];
            $Y[] = (int)$record["y"];
        }
        $model = new Ann(
            [
                ["function" => "sigmoid","nodes" => 10],
                // ["function" => "sigmoid","nodes" => 3]
            ],
            "sigmoid",
            pow(10,-6),
            100
        );

        $model->loadDataset($X, $Y);   
        $model->train();

        $model->loadDataset($X, $Y, false);
        $results = $model->feed_forward(null,false);

        $header = ['Y_hat'];
        $records = $results["Y_hat"]->getMatrix();
        $csv = Writer::createFromPath(__DIR__.'\results.csv', 'w+');
        $csv->insertOne($header);
        $csv->insertAll($records);
    }
    

}
