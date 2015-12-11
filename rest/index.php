<?php
session_start();
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;


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
        if (isset($_COOKIE['token'])) {
            
            $dec = JWT::decode($_COOKIE['token'], 'example_key', array('HS256'));
            if (is_object($dec)) {
                echo '{"status":"ok", "message":""}';
                $app->response->setStatus(200);
            } else {
                setcookie('user','', time() - 3600);
                setcookie('token', '', time() - 3600);
                echo '{"status":"error", "message":"Invalid token"}';
                $app->response->setStatus(401);
            }
        } else {
            setcookie('user','', time() - 3600);
            setcookie('token', '', time() - 3600);
            echo '{"status":"error", "message":"Invalid token"}';
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
        
        $userData = null;
        foreach ($users as $key => $user) {
            if ( $user['username'] == $_POST['username'] && $user['password'] == $_POST['password'] ) {
                $success = true;
                $userData = $user;
                break;
            }
        }
        
        if ($success) {
            $key = "example_key";
            $payload = array (
                'user' => array (
                    'userId' => $userData['id'],
                    'username' => $userData['username'],
                    'birthdate' => $userData['birthdate'],
                    'sex' => $userData['sex'],
                    'type' => $userData['type'],
                    'name' => $userData['name'],
                    'surname' => $userData['surname']
                ),
            );
            $token = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "iat" => time(),
                "nbf" => time(),
                "PAYLOAD" => $payload
            );

            $jwt = JWT::encode($token, $key);
            $response = array(
                'status' => 'ok',
                'statusText' => '',
                'PAYLOAD' => $payload,
                'token' => $jwt
            );
            setcookie('token', $jwt, time() + 3600);
            setcookie('user', json_encode($payload['user']), time() + 3600);
            echo json_encode($response);
            $app->response->setStatus(200);
        } else {
            echo '{"status":"error", "message":"Invalid username or password"}';
            $app->response->setStatus(401);
        }

        $app->redirect("../web/index.html");
    }
);

$app->get(
    '/logout',
    function () use ($app) {
//        setcookie ("login", "", time() - 3600);
        setcookie('user','', time() - 3600);
        setcookie('token', '', time() - 3600);
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
            $key = "example_key";
            $jwt = JWT::decode($_COOKIE['token'], $key, array('HS256'));
            if (is_object($jwt)) {
                $userData = null;
                foreach ( $users as $user) {
                    if ($user['id'] == $id) {
                        $userData = array (
                            'userId' => $user['id'],
                            'username' => $user['username'],
                            'birthdate' => $user['birthdate'],
                            'sex' => $user['sex'],
                            'type' => $user['type'],
                            'name' => $user['name'],
                            'surname' => $user['surname']
                        );
                        break;
                    }
                }
                if (empty($userData)) {
                    $app->response->setStatus(201);
                } else {
                    echo json_encode($userData);
                    $app->response->setStatus(200);
                }
            } else {
                setcookie('user', '', time() - 3600);
                setcookie('token', "", time() - 3600);
                $app->response->setStatus(401);
                $app->redirect("../web/index.html");
            }
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
        function () use ($app) {
            include('users.php');
            $key = "example_key";
            $jwt = JWT::decode($_COOKIE['token'], $key, array('HS256'));
            if (is_object($jwt)) {
                $userData = null;
                foreach ( $users as $user) {
                    $userInfo = json_decode($_COOKIE['user'], true);
                    $id = $userInfo['userId'];
                    if ($user['id'] == $id) {
                        $userData = array (
                            'userId' => $user['id'],
                            'username' => $user['username'],
                            'birthdate' => $user['birthdate'],
                            'sex' => $user['sex'],
                            'type' => $user['type'],
                            'name' => $user['name'],
                            'surname' => $user['surname']
                        );
                        break;
                    }
                }
                if (empty($userData)) {
                    $app->response->setStatus(201);
                } else {
                    echo json_encode($userData);
                    $app->response->setStatus(200);
                }
            } else {
                setcookie('token', '', time() - 3600);
                setcookie('token', "", time() - 3600);
                $app->response->setStatus(401);
                $app->redirect("../web/index.html");
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
