<?php

namespace guifcoelho\NeuralNetworks;

use guifcoelho\NeuralNetworks\Functions;
use guifcoelho\NeuralNetworks\Libs\Helpers;
use guifcoelho\NeuralNetworks\Libs\Matrix;

/**
 * Class Ann
 */
class Ann
{
    private $namespace_functions = '\\guifcoelho\\NeuralNetworks\\Functions';

    /**
     * ```string``` Type of problem: ```classification``` or ```regression```
     * 
     * @param string
     */
    private $problem_type;

    /**
     * ```array``` Hidden layers configuration
     * 
     * $hidden_layers_config = [
     *      ["function", "nodes"],
     *      ["function", "nodes"],
     *      ...
     *      ["function", "nodes"]
     * ]
     * 
     * @param array 
     */
    private $hidden_layers_config;

    /**
     * ```string``` Name of the activation function
     * 
     * @param string 
     */
    private $activation;

    /**
     * ```float``` If greater or equals to the threshold then it will be classified as 1
     * 
     * @param float 
     */
    private $classification_threshold;

    /**
     * ```float``` Learning rate for training the naural network
     * 
     * @param float
     */
    private $learning_rate;

    /**
     * ```int``` Number of epochs to train the neural network
     * 
     * @param int
     */
    private $epochs;

    /**
     * ```int``` Print training progression every given epochs
     * 
     * @param int 
     */
    private $print_intervals;

    /**
     * ```array``` Input values for the training set
     * 
     * @param array
     */
    private $Xtrain;

    /**
     * ```array``` Response values for the training set
     * 
     * @param array
     */
    private $Ytrain;

    /**
     * ```array``` Input values for the test set
     * 
     * @param array
     */
    private $Xtest;

    /**
     * ```array``` Response values for the test set
     * 
     * @param array
     */
    private $Ytest;

    /**
     * ```int``` Number of labels of the response variable
     * 
     * @param int
     */
    private $labels;

    /**
     * ```int``` Number of features of the training dataset
     * 
     * @param int
     */
    private $features;

    /**
     * ```array``` Weights of the neural network
     * 
     * @param array
     */
    private $weights;

    /**
     * Initializes the neural network model
     */
    public function __construct(array $config)
    {
        $problem_type = "classification";
        if(isset($config["problem_type"])){
            $problem_type = $config["problem_type"];
        }else{
            print "\nNo problem type defined: setting as '{$problem_type}'\n";
        }

        $hidden_layers_config = array(
            ["function" => "sigmoid","nodes" => 2] // 1 layer
        );
        if(isset($config["hidden_layers"])){
            $hidden_layers_config = $config["hidden_layers"];
        }else{
            print "\nNo hidden layers defined: setting as {count($hidden_layers_config)} layer with {$hidden_layers_config['nodes']} nodes\n";
        }

        $activation = "sigmoid";
        if(isset($config["activation"])){
            $activation = $config["activation"];
        }else{
            print "\nNo activation function defined: setting as '{$activation}'\n";
        }

        $classification_threshold = 0.5;
        if(isset($config["classification_threshold"])){
            $classification_threshold = $config["classification_threshold"];
        }else{
            print "\nNo classification threshold defined: setting as {$classification_threshold}\n";
        }

        $learning_rate = 0.001;
        if(isset($config["learning_rate"])){
            $learning_rate = $config["learning_rate"];
        }else{
            print "\nNo learning rate defined: setting as {$learning_rate}\n";
        }

        $epochs = 10000;
        if(isset($config["epochs"])){
            $epochs = $config["epochs"];
        }else{
            print "\nNo epochs defined: setting as {$epochs}\n";
        }

        $print_intervals = 100;
        if(isset($config["print_intervals"])){
            $print_intervals = $config["print_intervals"];
        }else{
            print "\nNo print intervals defined: setting as {$print_intervals}\n";
        }

        $this->problem_type = $problem_type;
        $this->hidden_layers_config = $hidden_layers_config;
        $this->activation = $activation;
        $this->classification_threshold = $classification_threshold;
        $this->learning_rate = $learning_rate;
        $this->epochs = $epochs;
        $this->print_intervals = $print_intervals;
    }

    /**
     * Loads the dataset (training set or test set)
     * 
     * @var array $X Array of inputs
     * @var array $Y Array of responses
     * @var bool $training True for traning and False for testing
     */
    public function loadDataset(array $X, array $Y, bool $training = true){
        if(count($X) != count($Y)){
            throw new Exceptions\DatasetException();
        }

        if($training){
            if(!is_array($X[0])){
                $this->features = 1;
            }else{
                $this->features = count($X[0]);
            }
            
            if(!is_array($Y[0])){
                //Transforms a problem with one label with a problem with 2 labels
                for($i = 0; $i < count($Y); $i++){
                    $arr = [0,0];
                    if($Y[$i] == 0){
                        $arr[0] = 1;
                    }else{
                        $arr[1] = 1;
                    }
                    $Y[$i] = $arr;
                }
            }
            $this->labels = count($Y[0]);
            if($this->problem_type == "classification" && $this->activation != "softmax"){
                $this->activation = "softmax";
                print "\nChanging activation function into 'softmax'\n";
                print "---------------\n";
            }
            $this->Xtrain = $X;
            $this->Ytrain = $Y;
        }else{
            $this->Xtest = $X;
            $this->Ytest = $Y;
        }
        
    }

    /**
     * Helper function to randomly initialize the weights
     * 
     * @return array
     */
    private function init_layer_layer_weights(int $D1, int $D2): array
    {
        $W = [];
        for($i = 0; $i < $D1; $i++){
            $W[] = Helpers::array_of_random_numbers($D2);
        }
        $b = Helpers::array_of_random_numbers($D2);
        return ["W" => $W, "b" => $b];
    }
    /**
     * Initializes and returns the weights
     * 
     * @return array
     */
    public function initWeights():array
    {
        $H = count($this->hidden_layers_config);
        $weights = [];

        //Weights for input layer and first hidden layer
        $nodes = $this->hidden_layers_config[0]["nodes"];
        $weights[] = $this->init_layer_layer_weights($this->features, $nodes);

        //Weights for hidden layer to hidden layer
        for($h = 0; $h < $H - 1 ; $h++){
            $nodes1 = $this->hidden_layers_config[$h]["nodes"];
            $nodes2 = $this->hidden_layers_config[$h+1]["nodes"];
            $weights[] = $this->init_layer_layer_weights($nodes1, $nodes2);
        }

        //Weights for output layer and last hidden layer
        $nodes = $this->hidden_layers_config[$H-1]["nodes"];
        $weights[] = $this->init_layer_layer_weights($nodes, $this->labels);
        $matWeights = [];
        forEach($weights as $weight){
            $arr = [
                "W" => new Matrix($weight["W"]),
                "b" => new Matrix($weight["b"])
            ];
            $matWeights[] = $arr;
        }
        return $matWeights;
    }

    /**
     * Computes the cost function for the model or the derivative
     * 
     * @var array $Y_hat
     * @var bool $derivative
     * @var bool $training
     */
    private function compute_cost(array $Y_hat, bool $training = true)
    {
        $Y = $training ? $this->Ytrain : $this->Ytest;
        if($this->problem_type == "classification"){
            return Functions::cross_entropy($Y, $Y_hat, $this->labels);
        }else{
            //Regression problem
            //return Functions::mean_square_error($Y, $Y_hat, $derivative);
        }
    }

    /**
     * Computes the classification prediction
     * 
     * @var array $Y_hat
     * @return array
     */
    public function classification_prediction(array $Y_hat): array
    {
        $prediction = [];
        for($i = 0; $i < count($Y_hat); $i++){
            $arr = array_fill(0,$this->labels, 0);
            $arg_max = array_keys($Y_hat[$i], max($Y_hat[$i]))[0];
            $arr[$arg_max] = 1;
            $prediction[] = $arr;
        }
        return $prediction;
    }

    /**
     * Computes the classification rate
     * 
     * @return float
     */
    public function compute_classification_rate($Y, $Y_prediction): float
    {
        $sum = 0;
        for($i = 0; $i < count($Y); $i++){
            $class_true = array_keys($Y[$i], 1)[0];
            $sum += $Y[$i][$class_true] == $Y_prediction[$i][$class_true] ? 1 : 0;
        }
        return $sum/count($Y);
    }


    /**
     * Computes the feed forward calculations and returns Y_hat and the hidden layers
     * 
     * @return array
     */
    public function feed_forward($weights, bool $training = true): array
    {
        if($weights == null){
            $weights = $this->weights;
        }
        $H = count($this->hidden_layers_config);
        $hidden_layers = [];

        for($h = 0; $h < $H; $h++){
            $layer = [];
            if($h == 0){
                if($training){
                    $layer = new Matrix($this->Xtrain);
                }else{
                    $layer = new Matrix($this->Xtest);
                }
            }else{
                $layer = $hidden_layers[$h-1];
            }
            
            $W = $weights[$h]["W"];
            $b = $weights[$h]["b"];
            $A = $layer->multiply($W)->add_row_vector($b, true);
            $hidden_layers[] = $A->transform(function($value) use ($h) {
                $function = [
                    $this->namespace_functions,
                    $this->hidden_layers_config[$h]["function"]
                ];
                return call_user_func_array($function, [$value, false]);
            });
        }

        $layer = $hidden_layers[$H-1];
        $W = $weights[$H]["W"];
        $b = $weights[$H]["b"];
        $A = $layer->multiply($W)->add_row_vector($b);

        $element_wise = true;
        if($this->problem_type == 'classification'){
            $element_wise = false;
        }
        $Y_hat = $A->transform(function($value){
            $function = [$this->namespace_functions, $this->activation];
            return call_user_func_array($function, [$value, false]);
        }, $element_wise);
  
        $cost = $this->compute_cost($Y_hat->getMatrix());

        return [
            "hidden_layers" => $hidden_layers,
            "Y_hat" => $Y_hat,
            "cost" => $cost
        ];
    }

    /**
     * Helper function to compute the derivatives for backpropagation
     * 
     * @var Matrix $A Auxiliary matrix for the chain rule
     * @var $W Set of weights. Can be null when necessary
     * @var $Z Current layer. Can be null when necessary
     * @var Matrix $Z_minus Next layer (going backwards on the neural network)
     * @var $function The activation function
     * @var $element_wise True for calculating on each element or False to calculate on the row
     */
    private function compute_derivatives(Matrix $A, $W, $Z, Matrix $Z_minus, $function, $element_wise): array
    {
        if($Z != null || $W != null){
            $dZ = $Z->transform(function($value) use($function){
                $function = [$this->namespace_functions, $function];
                return call_user_func_array($function, [$value, true]);
            }, $element_wise);
            $A = $A->multiply($W->transpose())->multiply($dZ, true);
        }
        $dCost_dW = $Z_minus->transpose()->multiply($A);
        $dCost_db = $A->sum_along_axis(0);

        return [
            "A" => $A,
            "derivatives" => ["W" => $dCost_dW, "b" => $dCost_db]
        ];
    }

    /**
     * Returns the derivatives for weights
     * 
     * @var array $weights Weights of the neural network
     * @var array $feed_forward Hidden layers and Y_hat
     * @return array
     */
    private function backpropagation(array $weights, array $feed_forward): array
    {
        $hidden_layers = $feed_forward['hidden_layers'];
        $H = count($hidden_layers);
        $Y_hat = $feed_forward['Y_hat'];
        
        $matX = new Matrix($this->Xtrain);
        if($this->features == 1)
            $matX = $matX->transpose();

        $matY = new Matrix($this->Ytrain);

        $derivatives = [];

        //------------------------------------
        // Last hidden layer - Output layer
        //------------------------------------
        $A = $Y_hat->add_matrix($matY, -1);
        $Z_minus = $hidden_layers[$H-1];
        $result = $this->compute_derivatives($A, null, null, $Z_minus, null, null);
        $A = $result["A"];
        $dW = $result["derivatives"]["W"];
        $db = $result["derivatives"]["b"];
        $weights[$H]["W"] = $weights[$H]["W"]->add_matrix($dW, -$this->learning_rate);
        $weights[$H]["b"] = $weights[$H]["b"]->add_matrix($db, -$this->learning_rate);

        //------------------------------------
        // Interior layers
        //------------------------------------
        for($h = $H-1; $h>0; $h--){
            $W = $weights[$h+1]["W"];
            $Z = $hidden_layers[$h];
            $Z_minus = $hidden_layers[$h-1];
            $result = $this->compute_derivatives($A, $W, $Z, $Z_minus, $this->hidden_layers_config[$h]['function'], true);
            $A = $result["A"];
            $dW = $result["derivatives"]["W"];
            $db = $result["derivatives"]["b"];
            $weights[$h]["W"] = $weights[$h]["W"]->add_matrix($dW, -$this->learning_rate);
            $weights[$h]["b"] = $weights[$h]["b"]->add_matrix($db, -$this->learning_rate);
        }

        //------------------------------------
        // Input layer - first hidden layer
        //------------------------------------
        $W = $weights[1]["W"];
        $Z = $hidden_layers[0];
        $result = $this->compute_derivatives($A, $W, $Z, $matX, $this->hidden_layers_config[0]['function'], true);
        $dW = $result["derivatives"]["W"];
        $db = $result["derivatives"]["b"];
        $weights[0]["W"] = $weights[0]["W"]->add_matrix($dW, -$this->learning_rate);
        $weights[0]["b"] = $weights[0]["b"]->add_matrix($db, -$this->learning_rate);

        return $weights;
    }

    /**
     * Trains the neural network model
     * 
     * @return void
     */
    public function train():void
    {
        $cost = pow(10,10);
        $weights = $this->initWeights();
        $feed_forward = $this->feed_forward($weights);
        
        for($iter = 1; $iter <= $this->epochs; $iter++){
            $weights_ = $this->backpropagation($weights, $feed_forward);
            $feed_forward = $this->feed_forward($weights_);
            $Y_hat = $feed_forward["Y_hat"]->getMatrix();
            $cost_ = $feed_forward["cost"];
            
            if($this->problem_type == 'classification'){
                $Y_prediction = $this->classification_prediction($Y_hat);
                $class_rate = $this->compute_classification_rate($this->Ytrain, $Y_prediction);
            }

            if($iter % $this->print_intervals  == 0){
                print "Iter: {$iter} | Cost: {$cost_}";
                if($this->problem_type == 'classification'){
                    print  " | Class. rate: {$class_rate}";
                }
                print "\n";
            }
            if($cost_ > $cost){
                $this->learning_rate = $this->learning_rate*0.99;
            }else{
                $cost = $cost_;
                $weights = $weights_;
            }
            if($this->learning_rate < pow(10,-8)){
                break;
            }
        }
        $this->weights = $weights;
    }

    /**
     * Returns the trained weights
     * 
     * @return array
     */
    public function getWeights(): array
    {
        return $this->weights;
    }
}
