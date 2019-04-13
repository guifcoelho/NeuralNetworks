<?php
declare(strict_types=1);

namespace guifcoelho\NeuralNetworks\Libs;

use guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions\DimensionsException;
use guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions\MultiplicationException;

/**
 * Class Matrix
 * 
 * Operations with matrixes.
 * 
 * Strongly inspired in https://github.com/php-ai/php-ml
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
            $matrix = [$matrix];
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
        if($this->rows == 1 && $this->columns == 1){
            return new Matrix($this->matrix[0]);
        }

        $newMatrix = [];
        if($this->rows == 1){
            forEach($this->matrix[0] as $el){
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
    public function multiply(self $matrix, bool $element_wise = false):self
    {
        $new_matrix = [];
        $matrix1 = $this->getMatrix();
        $matrix2 = $matrix->getMatrix();

        if(!$element_wise){
            //Columns = Rows?
            if($this->columns != $matrix->getRows()){
                throw new MultiplicationException();
            }else{
                $num_rows = $this->rows;
                $num_cols = $matrix->getColumns();
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
            }
        }else{
            if($this->rows != $matrix->getRows() || $this->columns != $matrix->getColumns()){
                throw new MultiplicationException();
            }else{
                $matrix1 = $this->getMatrix();
                $matrix2 = $matrix->getMatrix();
                for($i=0; $i < $this->rows; $i++){
                    $row = [];
                    for($j=0; $j < $this->columns; $j++){
                        $row[] = $matrix1[$i][$j] * $matrix2[$i][$j];
                    }
                    $new_matrix[] = $row;
                }
            }
        }
        return new self($new_matrix);
    }

    /**
     * Sums a row vector to each line of the matrix
     * 
     * @var Matrix $row_vector Row vector to be added
     * @var bool $sum True of summing and False for subtracting
     */
    public function add_row_vector(self $row_vector, bool $sum = true): self
    {
        $arr_row_vector = $row_vector->getMatrix()[0];
        $new_matrix = $this->matrix;
        $signal = $sum ? 1 : -1;
        for($i=0; $i < $this->rows; $i++){
            for($j=0; $j < $this->columns; $j++){
                $new_matrix[$i][$j] = $new_matrix[$i][$j] + $signal * $arr_row_vector[$j];
            }
        }

        return new self($new_matrix);
    }

    /**
     * Transforms the matrix by applying a callback function
     * 
     * @var callable $function Callback function to be applied
     * @return Matrix
     */
    public function transform(callable $function, bool $element_wise = true): self
    {
        $new_matrix = $this->matrix;
        for($i = 0; $i < $this->rows; $i++){
            if($element_wise){
                $new_matrix[$i] = array_map($function, $this->matrix[$i]);
            }else{
                $new_matrix[$i] = call_user_func($function, $this->matrix[$i]);
            }
        }

        return new self($new_matrix);
    }

    /**
     * Sums two matrixes element-wise. Both matrixes must have the same dimensions
     * 
     * @var Matrix $matrix Matrix to be added
     * @var float $scalar Multiplies the second matrix
     */
    public function add_matrix(self $matrix, float $scalar = 1): self
    {
        if($this->rows != $matrix->getRows() || $this->columns != $matrix->getColumns()){
            throw new DimensionsException();
        }else{
            $arr_matrix = $matrix->getMatrix();
            
            if($this->rows == 1 && $this->columns == 1 && is_array($this->matrix[0])){
                return new Matrix([[$this->matrix[0][0] + $scalar * $arr_matrix[0][0]]]);
            }

            $new_matrix = [];

            if($this->rows == 1 && $this->columns > 1 && is_array($this->matrix[0])){
                for($j = 0; $j < $this->columns; $j++){
                    $new_matrix[] = $this->matrix[0][$j] + $scalar * $arr_matrix[0][$j];
                }
                return new Matrix([$new_matrix]);
            }

            if($this->rows == 1){
                for($j = 0; $j < $this->columns; $j++){
                    $new_matrix[] = $this->matrix[$j] + $scalar * $arr_matrix[$j];
                }
            }else{
                for($i = 0; $i < $this->rows; $i++){
                    $new_matrix[] = array_fill(0,$this->columns,0);
                    for($j = 0; $j < $this->columns; $j++){
                        $new_matrix[$i][$j] = $this->matrix[$i][$j] + $scalar * $arr_matrix[$i][$j];
                    }
                }
            }
            
            return new self($new_matrix);
        }
    }

    /**
     * Sums the matrix along some axis
     * 
     * @var int $axis The axis to be summed
     * @return Matrix
     */
    public function sum_along_axis(int $axis = 1): self
    {
        $arr = [];
        if($axis == 1){
            //Returns a column vector
            for($i = 0; $i < $this->rows; $i++){
                $arr[] = [array_sum($this->matrix[$i])];
            }
        }else{
            //Returns row vector
            for($j = 0; $j < $this->columns; $j++){
                $column = array_column($this->matrix,$j);
                $arr[] = array_sum($column);
            }
        }
        return new self($arr);
    }

}

