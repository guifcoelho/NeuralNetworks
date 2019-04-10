<?php

namespace guifcoelho\NeuralNetworks\Libs;

use guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions\DimensionsException;
use guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions\MultiplicationException;

/**
 * Class Matrix
 * 
 * Operações em vetores como se fossem matrizes
 */
class Matrix
{
    /**
     * @var array
     * 
     * $matrix = [
     *  [a11, a12, a13]
     *  [a21, a22, a23]
     *  [a31, a32, a33]
     * ]
     * 
     * Or line vector
     * $matrix = [ a11, a12, a13 ]
     */
    private $matrix = [];

    /**
     * @var int
     */
    private $rows;

    /**
     * @var int
     */
    private $columns;

    public function __construct(array $matrix)
    {
        if(!is_array($matrix[0])){
            //Vetor linha
            $this->rows = 1;
            $this->columns = count($matrix);
        }else{
            if(!$this->validate_structure($matrix))
                throw new DimensionsException();
            $this->rows = count($matrix);
            $this->columns = count($matrix[0]);
        }
        $this->matrix = $matrix;
    }

    /**
     * Validates the matrix's structure
     * 
     * @param array $matrix Matrix to be validated
     * @return bool
     */
    public function validate_structure(array $matrix): bool
    {
        $num_columns = count($matrix[0]);
        forEach($matrix as $linha){
            if(count($linha) != $num_columns){
                return false;
            }
        }
        return true;
    }

    /**
     * Gets the matrix
     * 
     * @return array
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * Gets the number of rows for the matrix
     * 
     * @return int
     */
    public function getRows():int
    {
        return $this->rows;
    }

    /**
     * Gets the number of columns for the matrix
     * 
     * @return int
     */
    public function getColumns():int
    {
        return $this->columns;
    }

    /**
     * Transposes a matrix and returns a new one
     * 
     * @return Matrix
     */
    public function transpose(): self
    {
        $newMatrix = [];
        if($this->rows == 1){
            forEach($this->matrix as $el){
                //Sets each element as an array
                $newMatrix[] = [$el];
            }
        }else{
            //The function array_map return the array as a list of its elements after operating a callback function.
            //In this case the callback function is null
            $newMatrix = array_map(null, ...$this->matrix);
        }

        //Returns a new Matrix object from the transposed matrix
        return new self($newMatrix);
    }

    /**
     * Multiplies this matrix by another one
     * 
     * @param Matrix $matrix Matrix to be multiplied for
     * @return Matrix
     */
    public function multiply(self $matrix):self
    {
        //Columns = Rows?
        if($this->columns != $matrix->getRows()){
            throw new MultiplicationException();
        }else{
            $matrix1 = $this->getMatrix();
            $num_rows = $this->rows;
            $matrix2 = $matrix->getMatrix();
            $num_cols = $matrix->getColumns();
            
            $new_matrix = [];

            for($i=0; $i < $num_rows; $i++){
                $row = $matrix1[$i];
                $new_row = [];
                for($j=0; $j < $num_cols; $j++){
                    $col = array_column($matrix2, $j);
                    $sum = 0;
                    for($k = 0; $k < $this->columns; $k++){
                        $sum += $row[$k] * $col[$k];
                    }
                    $new_row[]=$sum;
                }
                $new_matrix[]=$new_row;
            }

            return new self($new_matrix);
        }
    }
}

