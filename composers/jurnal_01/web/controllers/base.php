<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/app.php';


require_once __DIR__.'/custom/test_001.php';
require_once __DIR__.'/applicant/index.php';
require_once __DIR__.'/cr_permission/index.php';
require_once __DIR__.'/crud_table/index.php';
require_once __DIR__.'/dr_permission/index.php';
require_once __DIR__.'/rr_permission/index.php';
require_once __DIR__.'/ur_permission/index.php';
require_once __DIR__.'/user_login/index.php';
require_once __DIR__.'/user_role/index.php';
require_once __DIR__.'/user_role_type/index.php';



$app->match('/', function () use ($app) {

    return $app['twig']->render('ag_dashboard.html.twig', array());
        
})
->bind('dashboard');


$app->run();