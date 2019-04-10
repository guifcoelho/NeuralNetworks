<?php

namespace guifcoelho\NeuralNetworks\Tests;

use guifcoelho\NeuralNetworks\Libs\Utils;

class UtilsTest extends TestCase
{
    public function test_equal_arr_ok(){

        $arr1 = [
            [27, 33],
            [60, 75]
        ];

        $arr2 = [
            [27, 33],
            [60, 75]
        ];

        $this->assertTrue(Utils::checkEqualArrays($arr1, $arr2));
    }

    public function test_equal_arr_nok(){

        $arr1 = [
            [27, 33],
            [60, 75]
        ];

        $arr2 = [
            [0, 0],
            [0, 0]
        ];

        $this->assertFalse(Utils::checkEqualArrays($arr1, $arr2));

        $arr3 = [
            [0, 0]
        ];

        $this->assertFalse(Utils::checkEqualArrays($arr1, $arr3));
    }

}
