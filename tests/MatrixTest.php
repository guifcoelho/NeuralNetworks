<?php

namespace guifcoelho\NeuralNetworks\Tests;

use guifcoelho\NeuralNetworks\Config;
use guifcoelho\NeuralNetworks\Libs\Matrix;
use guifcoelho\NeuralNetworks\Libs\Utils;
use guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions\DimensionsException;

/**
 * Class AnnTest
 *
 * @category Test
 * @package  guifcoelho\NeuralNetworks\Tests
 */
class MatrixTest extends TestCase
{

    public function test_create_matrix_ok()
    {
        $arr = [
            [1,2,3],
            [4,5,6]
        ];
        
        $matrix = new Matrix($arr);
        $this->assertTrue($matrix->getRows() == 2 && $matrix->getColumns() == 3);
    }

    public function test_create_matrix_nok()
    {
        $arr = [
            [1,2,3],
            [4,5]
        ];
        $this->setExpectedException(DimensionsException::class);
        $matrix = new Matrix($arr);
    }

    public function test_transpose_matrix()
    {
        $arr = [
            [1,2,3],
            [4,5,6]
        ];

        $matrix = new Matrix($arr);

        $matrix_T = $matrix->transpose();

        $this->assertTrue($matrix_T->getRows() == 3 && $matrix_T->getColumns() == 2);
    }

    public function test_multiply_matrix_ok(){
        $arr1 = [
            [1,2,3],
            [4,5,6]
        ];
        $matrix1 = new Matrix($arr1);

        $arr2 = [
            [1,2],
            [4,5],
            [6,7]
        ];
        $matrix2 = new Matrix($arr2);

        $matrix3 = $matrix1->multiply($matrix2);

        $this->assertTrue($matrix3->getRows() == 2 && $matrix3->getColumns() == 2);

        $matrix_result = [
            [27, 33],
            [60, 75]
        ];

        $this->assertTrue(Utils::checkEqualArrays($matrix3->getMatrix(), $matrix_result));
    }

}
