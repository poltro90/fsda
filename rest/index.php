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
                $response = csvDecode('acc',$results);
                echo json_encode($response,JSON_PRETTY_PRINT);
            }
        );
        $app->get(
            '/eda/:user/:results',
            function ($user, $results) {
                $response = csvDecode('eda',$results);
                echo json_encode($response,JSON_PRETTY_PRINT);
            }
        );
        $app->get(
            '/hr/:user/:results',
            function ($user, $results) {
                $response = csvDecode('hr',$results);
                echo json_encode($response,JSON_PRETTY_PRINT);
            }
        );
        $app->get(
            '/tags/:user/:results',
            function ($user, $results) {
                $response = csvDecode('tags',$results);
                echo json_encode($response,JSON_PRETTY_PRINT);
            }
        );
        $app->get(
            '/temp/:user/:results',
            function ($user, $results) {
                $response = csvDecode('temp',$results);
                echo json_encode($response,JSON_PRETTY_PRINT);
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

/* 
 * Get csv data from file and return an array
 * Source of data is dummy cvs files
 *
 * @param $type string
 * @param $results int
 *
 * @return array
 */
function csvDecode($type, $results) {
    $response['type'] = $type;
    $response['time'] = array();
    $response['rate'] = array();
    $response['data'] = array();
    $response['error'] = false;
    $file = './data/' . strtoupper($type) . '.csv';
    if ( ($cvs = fopen($file,'r')) !== false ) {
        $response['time'] = fgetcsv($cvs,0,",");
        $response['rate'] = fgetcsv($cvs,0,",");
        $i=0;
        while ( ($data = fgetcsv($cvs,0,",")) !== false && ($i < $results) ) {
            $dataset[0] = $i;
            $dataset[1] = $data;
            if (count($data) == 1) {
                $dataset[1] = $data[0];
            }
            array_push($response['data'],$dataset);
            $i++;
        }
        $response['results'] = $i;
    }
    else {
        $response['error'] = true;
        $response['data'] = array('Failed to read source of data.');
    }
    
    return $response;
}
