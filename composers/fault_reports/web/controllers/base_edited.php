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


require_once __DIR__.'/academic_years/index.php';
require_once __DIR__.'/delayed_permission/index.php';
require_once __DIR__.'/departments/index.php';
require_once __DIR__.'/major_grades/index.php';
require_once __DIR__.'/majors/index.php';
require_once __DIR__.'/phpsp_users/index.php';
require_once __DIR__.'/subject_marks/index.php';
require_once __DIR__.'/subjects/index.php';
require_once __DIR__.'/user_details/index.php';



$app->match('/', function () use ($app) {

    return $app['twig']->render('ag_dashboard.html.twig', array());
        
})
->bind('dashboard');


$app->run();