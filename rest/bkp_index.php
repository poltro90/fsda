<?php
session_start();
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$api = "https://test-embrace-api.empatica.com/v1";

// GET routes

// Default route
$app->get(
    '/',
    function () {
        echo "default";
    }
);

$app->get(
    '/login',
    function () use ($app) {
        if (isset($_COOKIE['login']) && $_COOKIE['login'] == true) {
            $app->response->setStatus(200);
        } else {
            $app->response->setStatus(401);
        }
    }
);

$app->post(
    '/login',
    function () use ($app, $api) {
        /* DUMMY LOGIN DATA */
        include('users.php');
        $login_url =  "{$api}/login";
        if (isset($_POST['username'])) $username = $_POST['username']; 
        if (isset($_POST['password'])) $password = $_POST['password']; 
        $success = false;
        
        $data['email'] = $_POST['username'];
        $data['password'] = $_POST['password'];
        $json = json_encode($data);
        
        print_r($json);
        
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $login_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $success = json_decode(curl_exec($ch), true);
        
        print_r($success);
        
        if ( $success['status'] == "error" ) {
            $app->response->setStatus(401);
        } else if ( $success['status'] == "ok" ) {
            setcookie('login',true, time() + 3600);
            setcookie('username',$username, time() + 3600);
            setcookie('token', $success['payload']['token'], time() + 3600);
            $app->response->setStatus(200);
        } else {
            $app->response->setStatus(500);
        }
        
        curl_close($ch);
        //$app->redirect("../web/index.html");
    }
);

$app->get(
    '/logout',
    function () use ($app) {
        setcookie ("login", "", time() - 3600);
        setcookie('username',$username, time() - 3600);
        setcookie('token', "", time() - 3600);
        $app->response->setStatus(200);
        $app->redirect("../web/index.html");
    }
);

// API routes
$app->group('/api', function () use ($app, $api) {
    // All APIs are returning json format
    $app->response->headers->set('Content-Type', 'application/json');
    
    // User routes
    $app->get(
        '/user/id/:id',
        function ($id) use ($app, $api) {
            $user_url = "{$api}/users/{$id}";
            //$auth = "Authorization: {$_COOKIE['token']}";
            print_r($_COOKIE);
            $auth = "Authorization: Bearer {$_COOKIE['token']}";
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $user_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));
            curl_setopt($ch, CURLOPT_HEADER, 0);
        
            $success = json_decode(curl_exec($ch), true);
            print_r($success);
        }
    );
    
    
    
    
    $app->get(
        '/user/list',
        function () {
            /* DUMMY USER DATA */
            include('users.php');
            echo json_encode($users,JSON_PRETTY_PRINT);
        }
    );
    $app->get(
        '/user/whoami',
        function () {
            if (isset($_COOKIE['login']) && $_COOKIE['login'] == true) {
                /* DUMMY USER DATA */
                include('users.php');
                foreach ( $users as $key => $user ) {
                    if  ($user['username'] == $_COOKIE['username'] ) {
                        break;
                    }
                }
                echo json_encode($users[$key],JSON_PRETTY_PRINT);
            } else {
                $app->response->setStatus(401);
            }
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
            '/hr/:user/:results/avg',
            function ($user, $results) {
                $response = csvDecode('hr',$results);
                $sum=0;
                foreach ($response['data'] as $data) {
                    $sum += $data[1];
                }
                $return['avg'] = intval($sum / $response['results']);
                $return['results'] = $response['results'];
                echo json_encode($return,JSON_PRETTY_PRINT);
            }
        );
        $app->get(
            '/hr/:user/:results/minmax',
            function ($user, $results) {
                $response = csvDecode('hr',$results);
                $max=0;
                $min=null;
                foreach ($response['data'] as $data) {
                    if ($max < $data[1]) {
                        $max = $data[1];
                    }
                    if ( is_null($min) || $min > $data[1] ) {
                        $min = $data[1];
                    }
                }
                $return['max'] = $max;
                $return['min'] = $min;
                $return['results'] = $response['results'];
                echo json_encode($return,JSON_PRETTY_PRINT);
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
        $app->get(
            '/temp/:user/:results/avg',
            function ($user, $results) {
                $response = csvDecode('temp',$results);
                $sum=0;
                foreach ($response['data'] as $data) {
                    $sum += $data[1];
                }
                $return['avg'] = intval($sum / $response['results']);
                $return['results'] = $response['results'];
                echo json_encode($return,JSON_PRETTY_PRINT);
            }
        );
        $app->get(
            '/temp/:user/:results/minmax',
            function ($user, $results) {
                $response = csvDecode('temp',$results);
                $max=0;
                foreach ($response['data'] as $data) {
                    if ( $max < $data[1] ) {
                        $max = $data[1];
                    }
                    if ( is_null($min) || $min > $data[1] ) {
                        $min = $data[1];
                    }
                }
                $return['max'] = $max;
                $return['min'] = $min;
                $return['results'] = $response['results'];
                echo json_encode($return,JSON_PRETTY_PRINT);
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
