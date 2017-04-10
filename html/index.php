<?php
// Expand memory being used by PHP
ini_set('memory_limit','400M');
// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 3600);
// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600);
session_start();
$now = time();

if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
    // this session has worn out its welcome; kill it and start a brand new one
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['discard_after'] = $now + 3600;
date_default_timezone_set('America/New_York'); 

if (!file_exists('credentials.inc.php')) {
   echo "My credentials are missing!";
   exit;
}

// Include libraries added with composer
require 'vendor/autoload.php';
// Include credentials
require 'credentials.inc.php';

function connect_db(){
	// Connecting, selecting database
	$dbconn = pg_connect('host=' . DBHOST . ' dbname=' .DBNAME . ' user=' .DBUSER . ' password=' . DBPASS)
	    or die('Could not connect: ' . pg_last_error());
	return $dbconn;
}

// Start Slim instance
//-------------------------------
$app = new \Slim\Slim(array(
    'debug' => true
));

// Handle not found
$app->notFound(function () use ($app) {
	// Temporarily route /map, /viz to /map.html
	$actual_link = "$_SERVER[REQUEST_URI]";
	print($actual_link);
	exit;
	// Let's make sure we remove a trailing "/" on any not found paths
    $actual_link = rtrim($actual_link, '/');
        
	// if ($actual_link == "/phppgadmin"){
	// 	echo "this is phppgadmin";
	// } else {		
 //    	$app->redirect('/404.html');
 //    }
});


$app->get('/index.html', function () use ($app) {
  	$app->redirect("/");
});

$app->get('/', function () use ($app) {
    $content['title'] = "Human Face of Big Data";
    $content['intro'] = <<<HTML
		<p>Asheville, NC</p>
HTML;
	// return $app->response->setBody($response);
	// Render content with simple bespoke templates
	$app->view()->setData(array('content' => $content));
	$app->render('tp_main_map.php');
});

$app->get('', function () use ($app) {
    $app->redirect("/");
});

$app->get('/map/', function () use ($app) {
	
    $content['title'] = "Human Face of Big Data";
    $content['intro'] = <<<HTML
		<p>Asheville, NC</p>
HTML;
	// return $app->response->setBody($response);
	// Render content with simple bespoke templates
	$app->view()->setData(array('content' => $content));
	$app->render('tp_main_map.php');
    
});

# Data management interface
$app->get('/data/', function () use ($app) {
	// Connecting, selecting database
	$dbconn = connect_db();

	// Performing SQL query
	$query = "SELECT max(parcel_id) FROM humanface.parcels";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());

	$content['title'] = "Data Management";
    $content['intro'] = "This is an admin interface for data management";
	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	$content['parcel_id'] = $line['max'] + 1;
	
	$app->view()->setData(array('content' => $content));
	$app->render('tp_data.php');
});

$app->get('/crowd/', function () use ($app) {
	// Connecting, selecting database
	$dbconn = connect_db();

	// Performing SQL query
	$query = "SELECT max(parcel_id) FROM humanface.parcels";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	$content['parcel_id'] = $line['max'] + 1;

	$content['title'] = "Data Management";
    $content['intro'] = "This is an admin interface for data management";
	
	$query = "SELECT type FROM humanface.event_types";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());

	$line = pg_fetch_all($result);
	$event_types = array();

	for ($i=0; $i<sizeof($line); $i++){
		$event_types[$i] = $line[$i]["type"];
	}
	
	$content['event_types'] = $event_types;
	
	$app->view()->setData(array('content' => $content));
	$app->render('tp_crowdsourcing.php');
});

$app->post('/input/:parcel_id/', function($pid) use ($app) {
	$dbconn = connect_db();

	$vars = $app->request->post(); 	
	$log_time = date_format(date_create(), 'Y-m-d H:i:s');

	echo "<pre>";
	print_r($vars);
	echo "</pre>";
	print($log_time);
	

});

$app->get('/num/:abc/', function($n) use ($app) {
	echo $n;
});

$app->run();
?>