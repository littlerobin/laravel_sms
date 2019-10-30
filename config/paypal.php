<?php
return [
	"APIUsername" => env('PAYPAL_USERNAME'),
	"APIPassword" => env('PAYPAL_PASSWORD'),
	"APISignature" => env('PAYPAL_SIGNATURE'),
	"ApplicationID" => env('PAYPAL_APPLICATION_ID'),
	"Sandbox" => env('PAYPAL_TEST_MODE')
];