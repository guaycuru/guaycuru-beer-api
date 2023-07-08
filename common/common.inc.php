<?php

namespace Guaybeer;

use Guaybeer\Entities\User;
use Util\Shared;

// Set JSON as accept and content type
header('Accept: application/json');
header('Content-type: application/json');

// Composer Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Config
require_once(__DIR__.'/config.inc.php');

// Doctrine (ORM)
require_once(__DIR__.'/doctrine.inc.php');

// Timezone and default encoding
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

// Add support for JSON data in the body of POST and PUT requests
$method = strtoupper($_SERVER['REQUEST_METHOD']);
if ($method === 'POST' || $method === 'PUT') {
	if (!empty($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
		$raw = file_get_contents('php://input');
		if (!empty($raw)) {
			if ($method === 'POST')
				$_POST = json_decode($raw, true);
			else // PUT
				$_PUT = json_decode($raw, true);
		}
	}
}
unset($method, $raw);

// Check if user is logged in
$_user = User::ping();
if ($_user === false) {
	// Disallow anonymous access
	Shared::JSON_Error('Unauthorized, missing token', 401);
	exit;
} elseif ($_user->getId() == null) {
	// Disallow anonymous access
	Shared::JSON_Error('Unauthorized, invalid token', 401);
	exit;
}
