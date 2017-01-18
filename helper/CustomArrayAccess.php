<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cornelius M.
 * Used to construct array access. When accessing a key that doesn't exist return default value specified by the user
 * @author User
 */
class Qpg_CustomArrayAccess implements ArrayAccess
{
    private $array;
    private $default;
    function __construct($array, $default = NULL)
    {
        $this->array = $array;
        $this->default = $default;
    }
    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->array[$offset]) ? $this->array[$offset] : $this->default;
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

//put your code here
}
