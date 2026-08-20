<?php

//The script is based on https://github.com/sarah-schuh/schuhgroessen-umrechner/blob/main/index.php
// re-implemented to return only JSON responses.
// it can also be used via CLI
// php index.php UK 9.5 (where UK represents the shoe unit and 39 its size
// The script works with a SQLite Database backend

use Bakame\Shoes\Converter;
use Bakame\Shoes\Unit;

require __DIR__.'/../vendor/autoload.php';

$bold = "\033[1m";
$cyan = "\033[36m";
$green = "\033[32m";
$yellow = "\033[33m";
$reset = "\033[0m";
$red = "\033[31m";
$isCli = PHP_SAPI === 'cli';
$flags = $isCli ? JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT : JSON_UNESCAPED_SLASHES;
$fail = static function (string $message, int $status) use ($isCli, $flags, $bold, $yellow, $red, $reset): never {
    if ($isCli) {
        fwrite(STDOUT, "{$bold}{$yellow}Shoe size conversion{$reset}\n");
        fwrite(STDERR, $bold.$red.$message.$reset . "\n");
        exit(1);
    }

    http_response_code($status);
    header('Content-Type: application/problem+json; charset=UTF-8');
    echo json_encode([
        'type' => 'about:blank',
        'title' => match ($status) {
            400 => 'Bad Request',
            404 => 'Not Found',
            default => 'Internal Server Error',
        },
        'status' => $status,
        'detail' => $message,
        'instance' => $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME'] ?? '',
    ], $flags);
    exit;
};

$unit = $isCli ? ($argv[1] ?? null) : ($_GET['unit'] ?? null);
$input = $isCli ? ($argv[2] ?? null) : ($_GET['size'] ?? null);

$unit = Unit::tryFrom(strtoupper((string) $unit));
null !== $unit || $fail('Please provide a valid shoe-size unit (e.g., EU, US, UK, or CM).', 400);

if (!is_string($input) || trim($input) === '') {
    $fail('Please provide a shoe size.', 400);
}

$input = trim($input);
if ($unit === Unit::Eu) {
    $size = filter_var($input, FILTER_VALIDATE_INT);
    false !== $size || $fail('Please provide a valid EU shoe size (e.g., 38 or 42).', 400);
} else {
    $size = filter_var($input, FILTER_VALIDATE_FLOAT);
    false !== $size || $fail("Please provide a valid {$unit->value} shoe size.", 400);
}

try {
    $pdo = new PDO('sqlite:'. __DIR__ . '/../data/shoe_sizes.sqlite');
    $converter = Converter::fromPdo($pdo);
} catch (Throwable $exception) {
    $fail("Data source is missing or not readable.", 500);
}

$result = $converter->equivalents($unit->size($size));
if ([] === $result) {
    $fail('No matching shoe size found for '.$unit->value.' '.$size, 404);
}

if ($isCli) {
    fwrite(STDOUT, "{$bold}{$yellow}Shoe size conversion{$reset}\n");
    fwrite(STDOUT, "{$bold}Input{$reset}\n");
    fwrite(STDOUT, "  {$bold}{$cyan}{$unit->size($size)->human()}{$reset}\n");
    fwrite(STDOUT, "{$bold}Sizes{$reset}\n");
    foreach ($result as $size) {
        fwrite(STDOUT, "  $green{$size->unit->value} $size->value$reset\n");
    }
    fwrite(STDOUT, "{$bold}Measurements{$reset}\n");
    fwrite(STDOUT, "  {$green}{$result[Unit::Cm->value]->value} cm{$reset}\n");
    fwrite(STDOUT, "  {$green}{$converter->inInch($result[Unit::Cm->value])} in{$reset}\n");
    exit(0);
}

http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'sizes' => array_values($result),
    'measurements' => [
        'centimeters' => $result[Unit::Cm->value]->value,
        'inches' => $converter->inInch($result[Unit::Cm->value]),
    ],
], $flags);
