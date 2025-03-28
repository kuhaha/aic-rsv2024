<?php

use aic\models\Model;

include 'common_env.php';

// Error reporting
// error_reporting(0);   // Product environment, reporting nothing
error_reporting(E_ERROR | E_PARSE); // Avoid E_WARNING, E_NOTICE, etc
// error_reporting(E_ALL); // Development environment, reporting all

Model::setConnInfo( [
    'host' => "localhost",
    'usename' => "root", 
    'password' => "yourpassword", 
    'dbname' => "yourdbname",
]);
