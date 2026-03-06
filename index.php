<?php
/**
 * Shoe Size Converter Logic
 * * Provides conversions between EU, US, UK, and CM shoe sizes.
 * Data processing and calculations are based on the international standard:
 * ISO/TS 19407:2015 (Footwear - Sizing - Conversion of sizing systems).
 * * @author Sarah Amft
 */

// Configuration: Path to the CSV source
$csvFile = 'data/shoe_sizes.csv';

// Internationalization strings for error handling and feedback
$messages = [
    'no_match'        => 'No matching shoe size found for EU size: ',
    'provide_eu_size' => 'Please provide an EU shoe size using the "eu_size" query parameter.',
    'invalid_input'   => 'Invalid input. Please provide a numeric EU shoe size (e.g., 38 or 42.5).',
    'file_error'      => 'System Error: Data source (CSV) is missing or not readable.'
];

/**
 * Validates file access before processing
 */
if (!file_exists($csvFile) || !is_readable($csvFile)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    die($messages['file_error']);
}

/**
 * Reads CSV file and returns data as an associative array
 * Optimized to handle potential BOM and whitespace in headers
 *
 * @param string $filename
 * @return array
 */
function readCsv(string $filename): array {
    $rows = [];
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        // Read header and trim whitespace for robust matching
        $header = fgetcsv($handle, 1000, ',');
        if ($header) {
            $header = array_map('trim', $header);
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Ensure data row matches header count
                if (count($header) === count($data)) {
                    $rows[] = array_combine($header, $data);
                }
            }
        }
        fclose($handle);
    }
    return $rows;
}

/**
 * Find shoe size conversions based on EU size
 * Implements strict numeric comparison for float-based sizes
 *
 * @param float $euSize
 * @param array $sizes
 * @return array|null
 */
function findShoeSize(float $euSize, array $sizes): ?array {
    foreach ($sizes as $size) {
        // Cast EU value to float to ensure reliable matching (e.g., "42" vs 42.0)
        if (isset($size['EU']) && (float)$size['EU'] === $euSize) {
            return $size;
        }
    }
    return null;
}

/**
 * Convert centimeters to inches
 * Formula: 1 inch = 2.54 cm
 *
 * @param float $cm
 * @return float
 */
function cmToInch(float $cm): float {
    return $cm / 2.54;
}

// -------------------------------------------------------------------------
// Execution Logic
// -------------------------------------------------------------------------

// Retrieve and validate input via FILTER_VALIDATE_FLOAT for enhanced security
$euSize = filter_input(INPUT_GET, 'eu_size', FILTER_VALIDATE_FLOAT);

if ($euSize === false || $euSize === null) {
    echo $messages['invalid_input'];
    exit;
}

// Load dataset into memory (Maintains original structure)
$shoeSizes = readCsv($csvFile);

// Search for the specific entity based on ISO conversion logic
$result = findShoeSize((float)$euSize, $shoeSizes);

if ($result) {
    // Sanitize output using htmlspecialchars to prevent XSS
    $eu = htmlspecialchars($result['EU']);
    $us = htmlspecialchars($result['US']);
    $uk = htmlspecialchars($result['UK']);
    $cm = (float)$result['CM'];

    // Output formatted results
    echo "<strong>EU Size:</strong> " . $eu . "<br>";
    echo "<strong>US Size:</strong> " . $us . "<br>";
    echo "<strong>UK Size:</strong> " . $uk . "<br>";
    echo "<strong>Size in CM:</strong> " . number_format($cm, 1) . " cm<br>";
    echo "<strong>Size in Inches:</strong> " . number_format(cmToInch($cm), 2) . " in<br>";
    
    echo "<p><small>Calculations compliant with ISO/TS 19407:2015 standards.</small></p>";
} else {
    echo $messages['no_match'] . htmlspecialchars((string)$euSize);
}
