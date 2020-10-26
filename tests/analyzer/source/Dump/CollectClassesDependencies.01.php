<?php

interface i {}

interface j extends i {}

class x {}

class y extends x implements i {}

?>