<?php
/**
 * Created by PhpStorm.
 * User: samarth
 * Date: 18/7/17
 * Time: 1:00 PM
 */

namespace App\Traits;

use Validator;
use Illuminate\Support\MessageBag;

trait ValidationTrait
{
    //=============== return errors after validation ===================
    private $errors;
    private $validation_type = [];
    private $skip_validations = false;

    public function getValidationType()
    {
        return $this->validation_type;
    }

    public function setValidationType($val)
    {
        array_push($this->validation_type, $val);
    }

    public function isValidationType($val)
    {
        return in_array($val, $this->validation_type);
    }

    public function set_skip_validation($val){
        $this->skip_validations=$val;
    }

    public function get_skip_validation(){
        return $this->skip_validations;
    }

    public function errors()
    {
        return $this->errors;
    }

    public function addError($messages)
    {
        $this->initialize_or_create_error_obj()->merge($messages);
    }

    private function initialize_or_create_error_obj(){
        if($this->errors==null){
            $this->errors = new MessageBag();
        }
        return $this->errors;
    }

    public function validateObject()
    {
        $v = Validator::make($this->toArray(), $this->getValidationRules());
        if ($v->fails())
        {
            // set errors and return false
            $this->addError($v->errors());
            return false;
        }

        // validation pass
        return true;
    }

    protected function getValidationRules(){
        return array();
    }
}
