<?php

//The script is based on
// https://github.com/sarah-schuh/schuhgroessen-umrechner/blob/main/index.php
// restructured to return only json responses.
// it can also be used via CLI
// php index.php 39 (where 39 represents the EU shoes size)

// Configuration: Path to the CSV source
use Bakame\Shoes\Converter;
use Bakame\Shoes\Unit;

require 'vendor/autoload.php';

$csvFile = 'data/shoe_sizes.csv';

// Internationalization strings for error handling and feedback
$messages = [
    'no_match' => 'No matching shoe size found for EU size: ',
    'provide_eu_size' => 'Please provide an EU shoe size using the "eu_size" query parameter.',
    'invalid_input' => 'Invalid input. Please provide a numeric EU shoe size (e.g., 38 or 42.5).',
    'file_error' => 'System Error: Data source (CSV) is missing or not readable.'
];

/**
 * Validates file access before processing
 */

if (!file_exists($csvFile) || !is_readable($csvFile)) {
    http_response_code(500);
    header('Content-Type: application/problem+json; charset=UTF-8');
    echo json_encode([
        'type' => "about:blank",
        'title' => 'Internal Server Error',
        'status' => 500,
        'detail' => $messages['file_error'],
        'instance' => $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME'] ?? '',
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    exit;
}

// -------------------------------------------------------------------------
// Execution Logic
// -------------------------------------------------------------------------

// Retrieve and validate input via FILTER_VALIDATE_FLOAT for enhanced security
$input = PHP_SAPI === 'cli' ? ($argv[1] ?? null) : ($_GET['eu_size'] ?? null);
$euSize = null;
if (filter_var($input, FILTER_VALIDATE_INT) !== false) {
    $euSize = (int) $input;
} elseif (filter_var($input, FILTER_VALIDATE_FLOAT) !== false) {
    $euSize = (float) $input;
}

if ($euSize === false || $euSize === null) {
    http_response_code(400);
    header('Content-Type: application/problem+json; charset=UTF-8');
    echo json_encode([
        'type' => "about:blank",
        'title' => 'Bad Request',
        'status' => 400,
        'detail' => $messages['invalid_input'] . $euSize,
        'instance' => $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME'] ?? '',
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    exit;
}

// Search for the specific entity based on ISO conversion logic
$converter = Converter::fromPath($csvFile);
$result = $converter->equivalents(Unit::Eu->size($euSize));
if ([] === $result) {
    http_response_code(404);
    header('Content-Type: application/problem+json; charset=UTF-8');
    echo json_encode([
        'type' => "about:blank",
        'title' => 'Not Found',
        'status' => 404,
        'detail' => $messages['no_match'] . $euSize,
        'instance' => $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME'] ?? '',
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    exit;
}

http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'sizes' => array_values($result),
    'measurements' => [
        'centimeters' => $result[Unit::Cm->value]->value,
        'inches' => $converter->inInch($result[Unit::Cm->value]),
    ],
], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);