<?php

!foo();
~foo();

!fooBool();
~fooBool();

!fooInt();
~fooInt();

!fooArray();
~fooArray();

function foo() {}
function fooBool() : bool {}
function fooInt() : int {}
function fooArray() : array {}

?>