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
     * @var array Hidden layers configuration
     * 
     * $hidden_layers_config = [
     *      ["function", "nodes"], <- layer 1
     *      ["function", "nodes"], <- layer 2
     *      ...
     *      ["function", "nodes"], <- layer M
     * ]
     */
    private $hidden_layers_config;

    /**
     * @var string Name of the activation function
     */
    private $activation;

    /**
     * @var float Learning rate for training the naural network
     */
    private $learning_rate;

    /**
     * @var int Number of epochs to train the neural network
     */
    private $epochs;

    /**
     * @var int Print training progression every given epochs
     */
    private $print_results;
    /**
     * @var array Input values for the training set
     */
    private $Xtrain;

    /**
     * @var array Response values for the training set
     */
    private $Ytrain;

    /**
     * @var array Input values for the test set
     */
    private $Xtest;

    /**
     * @var array Response values for the test set
     */
    private $Ytest;

    /**
     * @var int Number of labels of the response variable
     */
    private $labels;

    /**
     * @var int Number of features of the training dataset
     */
    private $features;

    /**
     * @var array Weights of the neural network
     */
    private $weights;

    /**
     * Initializes the neural network model
     */
    public function __construct(array $config){
        $hidden_layers_config = array(
            ["function" => "sigmoid","nodes" => 2] // 1 layer
        );
        if(isset($config["hidden_layers"])){
            $hidden_layers_config = $config["hidden_layers"];
        }

        $activation = "sigmoid";
        if(isset($config["activation"])){
            $activation = $config["activation"];
        }

        $learning_rate = 0.001;
        if(isset($config["learning_rate"])){
            $learning_rate = $config["learning_rate"];
        }

        $epochs = 10000;
        if(isset($config["epochs"])){
            $epochs = $config["epochs"];
        }

        $print_results = 100;
        if(isset($config["print_results"])){
            $print_results = $config["print_results"];
        }

        $this->hidden_layers_config = $hidden_layers_config;
        $this->activation = $activation;
        $this->learning_rate = $learning_rate;
        $this->epochs = $epochs;
        $this->print_results = $print_results;
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
                $this->labels = 1;
            }else{
                $this->labels = count($Y[0]);
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
            $W[] = Helpers::array_of_random_numbers(-2,2,$D2);
        }
        $b = Helpers::array_of_random_numbers(-2,2,$D2);
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
        if($this->labels == 1 & $this->activation != 'regression'){
            //Classification problem with one label -> Binary cross entropy function
            return Functions::binary_cross_entropy($Y, $Y_hat);
        }else{
            if($this->labels > 1){
                //Classification problem with more than one label
                //return Functions::cross_entropy($Y, $Y_hat, $derivative);
            }else{
                //Regression problem
                //return Functions::mean_square_error($Y, $Y_hat, $derivative);
            }
        }
        
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
        $W = $weights[count($weights)-1]["W"];
        $b = $weights[count($weights)-1]["b"];
        $A = $layer->multiply($W)->add_row_vector($b);
        $Y_hat = $A->transform(function($value){
            $function = [$this->namespace_functions, $this->activation];
            return call_user_func_array($function, [$value, false]);
        });

        $cost = $this->compute_cost($Y_hat->transpose()->getMatrix()[0]);

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
     * @var Matrix $W Set of weights. Can be null when necessary
     * @var Matrix $Z Current layer. Can be null when necessary
     * @var Matrix $Z_minus Next layer (going backwards on the neural network)
     */
    private function compute_derivatives(Matrix $A, $W, $Z, Matrix $Z_minus, string $function = 'sigmoid'): array
    {
        if($Z != null || $W != null){
            $dZ = $Z->transform(function($value) use($function){
                $function = [$this->namespace_functions, $function];
                return call_user_func_array($function, [$value, true]);
            });
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
        if($this->labels == 1)
            $matY = $matY->transpose();

        $derivatives = [];

        //------------------------------------
        // Last hidden layer - Output layer
        //------------------------------------
        $A = $Y_hat->add_matrix($matY, -1);
        $Z_minus = $hidden_layers[$H-1];
        $computation = $this->compute_derivatives($A, null, null, $Z_minus, $this->activation);
        $A = $computation["A"];
        $derivatives = array_merge([$computation["derivatives"]], $derivatives);

        //------------------------------------
        // Interior layers
        //------------------------------------
        for($h = $H-1; $h>0; $h--){
            $W = $weights[$h+1]["W"];
            $Z = $hidden_layers[$h];
            $Z_minus = $hidden_layers[$h-1];
            $computation = $this->compute_derivatives($A, $W, $Z, $Z_minus, $this->hidden_layers_config[$h]['function']);
            $A = $computation["A"];
            $derivatives = array_merge([$computation["derivatives"]], $derivatives);
        }

        //------------------------------------
        // Input layer - first hidden layer
        //------------------------------------
        $W = $weights[1]["W"];
        $Z = $hidden_layers[0];
        $computation = $this->compute_derivatives($A, $W, $Z, $matX, $this->hidden_layers_config[0]['function']);
        $A = $computation["A"];
        $derivatives = array_merge([$computation["derivatives"]], $derivatives);

        return $derivatives;
    }

    /**
     * Trains the neural network model
     * 
     * @return void
     */
    public function train():void
    {
        $weights = $this->initWeights();
        $cost = pow(10,10);
        $feed_forward = $this->feed_forward($weights);

        for($iter = 1; $iter <= $this->epochs; $iter++){
            $derivatives = $this->backpropagation($weights, $feed_forward);
            for($i = 0; $i < count($weights); $i++){
                $d = $derivatives[$i];
                $weights[$i]["W"] = $weights[$i]["W"]->add_matrix($d["W"], -$this->learning_rate);
                $weights[$i]["b"] = $weights[$i]["b"]->add_matrix($d["b"], -$this->learning_rate);
            }
            $feed_forward = $this->feed_forward($weights);
            // $Y_hat = $feed_forward["Y_hat"]->transpose()->getMatrix()[0];
            // $cost_ = Functions::binary_cross_entropy($this->Ytrain, $Y_hat, false);
            $cost_ = $feed_forward["cost"];
            if($iter % $this->print_results  == 0)
                print "Iter: {$iter} | Cost: {$cost_}\n";
            if($cost_ > $cost){
                break;
            }else{
                $cost = $cost_;
            }
        }
        $this->weights = $weights;
    }

    /**
     * Returns the trained weights
     */
    public function getWeights(){
        return $this->weights;
    }
}
