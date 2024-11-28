<?php

namespace App\Backend\Exception;

use LogicException;

class ValidationException extends LogicException
{
    protected array $errors = [];
    protected $message;
    protected $code;


    public function setCode($code)
    {
        $this->code = $code;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
        $this->setMessageBasedOnErrors();
    }

    public function setError($error)
    {
        $this->errors = [$error];
        $this->setMessageBasedOnErrors();
    }

    public function setErrors(array $errors = [])
    {
        $this->errors = $errors;
        $this->setMessageBasedOnErrors();
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }





    private function setMessageBasedOnErrors(): void
    {
        $message = null;

        if ($this->hasErrors()) {
            $errors = $this->getErrors();
            $errors = array_map(fn($error) => "{$error['message']}", $errors);
            $message = implode("; ", $errors);
        }

        $this->message = $message;
    }
}
