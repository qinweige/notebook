<?php

spl_autoload_register(function ($class) {
    include 'autoLoadModule.php';
});
$autoLoad = new autoLoadModule();
$autoLoad->loadTest();