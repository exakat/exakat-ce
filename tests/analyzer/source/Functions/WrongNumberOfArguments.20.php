<?php

class FunctionAttribute
{
function __construct($a) {}

}

#[FunctionAttribute]
class y  {}

#[FunctionAttribute()]
interface x  {}

#[FunctionAttribute(1)]
trait t {}

#[FunctionAttribute(1,2)]
function foo () {}

?>