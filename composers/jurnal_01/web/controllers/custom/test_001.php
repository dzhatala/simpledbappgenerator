<?php


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/test_001', function () use ($app) {
    

    return $app['twig']->render('custom/test_001.html', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
});

$app->match('/test_002', function () use ($app) {
    

    return $app['twig']->render('custom/test_002.html', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
});



?>
