<?php
namespace PRS\Manager;

//////////////////
   // RESPONSE //
  //////////////////////////////////////////////////////////////////////////////

class Response
{
    public $success;
    public $errors;
    
    function __construct()
    {
        $this->success = false;
        $this->errors = false;
    }

    public function returnJson()
    {
        echo json_encode($this);
        return true;
    }

    public function returnBool()
    {
        echo $this->success ? 'true' : 'false';
        return true;
    }

    public function returnPrintR()
    {
        echo "<pre>";
        print_r($this);
        echo "</pre>";
        return true;
    }

    public function returnVarDump()
    {
        echo "<pre>";
        var_dump($this);
        echo "</pre>";
        return true;
    }
}