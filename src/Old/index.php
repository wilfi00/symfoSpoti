<?php

class Main
{
    function __construct()
    {
        require_once 'Src/Tools.php';

        Tools::getRequiredFiles();

        $auth = new Auth();
    }

}

$main = new Main();
