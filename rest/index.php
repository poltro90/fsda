<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


// GET routes

// Default route
$app->get(
    '/',
    function () {
        echo "default";
    }
);

// Routes for each user type
$app->get(
    '/home/user',
    function () {
        echo "home/user";
    }
);
$app->get(
    '/home/doctor',
    function () {
        echo "home/doctor";
    }
);
$app->get(
    '/home/support',
    function () {
        echo "home/support";
    }
);
$app->get(
    '/home/admin',
    function () {
        echo "home/admin";
    }
);

// API routes
$app->group('/api', function () use ($app) {
    // All APIs are returning json format
    $app->response->headers->set('Content-Type', 'application/json');
    // User routes
    $app->get(
        '/user/:id',
        function ($id) {
            echo "user/$id";
        }
    );
    $app->get(
        '/user/list',
        function () {
            echo "user/list";
        }
    );
    
    // Data routes
    $app->group('/data', function () use ($app) {
        // Data APIs are user based and have a filter on the number of results to show
        $app->get(
            '/acc/:user/:results',
            function ($user, $results) {
                $cvs = fopen('./data/ACC.csv','r');
                for ($i=0; $i < ($results+2); $i++) {
                    $data = fgetcsv($cvs,0,",");
                    print_r($data);
                }
            }
        );
        $app->get(
            '/eda/:user/:results',
            function ($user, $results) {

            }
        );
        $app->get(
            '/hr/:user/:results',
            function ($user, $results) {

            }
        );
        $app->get(
            '/tags/:user/:results',
            function ($user, $results) {

            }
        );
        $app->get(
            '/temp/:user/:results',
            function ($user, $results) {

            }
        );
    });
    
    // Device route
    $app->get(
        '/device/:id',
        function ($id) {

        }
    );
});




// POST route
$app->post(
    '/post',
    function () {
        echo 'This is a POST route';
    }
);



// PUT route
$app->put(
    '/put',
    function () {
        echo 'This is a PUT route';
    }
);

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete(
    '/delete',
    function () {
        echo 'This is a DELETE route';
    }
);

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
