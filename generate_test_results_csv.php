<?php
/**
 * Test Results CSV Generator
 * 
 * This script reads test_cases.csv and populates Actual Result, Status, 
 * and Remarks columns with test execution data.
 * 
 * Usage: php generate_test_results_csv.php
 * Or:    php -S localhost:8000 and navigate to the file
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration
define('CSV_FILE', __DIR__ . '/test_cases.csv');
define('TEST_LOG_FILE', __DIR__ . '/test-results.xml');

/**
 * Test ID to PHPUnit method name mapping
 */
$testMapping = [
    'TC_REG_001' => 'testRegistrationWithValidData',
    'TC_REG_002' => 'testRegistrationWithDuplicateEmail',
    'TC_REG_003' => 'testRegistrationWithEmptyFields',
    'TC_LOGIN_001' => 'testLoginWithValidCredentials',
    'TC_LOGIN_002' => 'testLoginWithInvalidEmail',
    'TC_LOGIN_003' => 'testLoginWithWrongPassword',
    'TC_LOGIN_004' => 'testLoginWithEmptyEmail',
    'TC_LOGIN_005' => 'testLoginWithEmptyPassword',
    'TC_LOGIN_006' => 'testAlreadyLoggedInUserRedirect',
    'TC_LOGOUT_001' => 'testLogoutDestroysSession',
    'TC_CHAT_001' => 'testSendChatMessageWithValidData',
    'TC_CHAT_002' => 'testSendEmptyMessage',
    'TC_CHAT_003' => 'testUnauthorizedChatAccess',
    'TC_CHAT_004' => 'testMarkIncomingMessagesAsRead',
    'TC_CHAT_005' => 'testMessagesDisplayInOrder',
    'TC_CHAT_006' => 'testUnreadBadgeDisplay',
    'TC_CHAT_007' => 'testCorrectSenderDisplay',
    'TC_REQ_001' => 'testCreateSkillRequest',
    'TC_REQ_002' => 'testUnauthorizedRequestCreation',
    'TC_REQ_003' => 'testAccessDeniedNonInvolved',
    'TC_REQ_004' => 'testAccessGrantedRequester',
];

/**
 * Step 1: Run PHPUnit tests and capture XML results
 */
function runTests() {
    echo "Running PHPUnit tests...\n";
    
    // Use absolute path to ensure it works on Windows
    $phpunitPath = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpunit';
    $outputFile = TEST_LOG_FILE;
    $phpExe = PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php';
    
    // Ensure the command works on Windows
    $command = $phpExe . ' ' . escapeshellarg($phpunitPath) . ' --log-junit=' . escapeshellarg($outputFile);
    
    exec($command, $output, $returnCode);
    
    echo "Tests completed (Exit code: $returnCode)\n";
    return $returnCode;
}

/**
 * Step 2: Parse PHPUnit XML output
 */
function parseTestResults() {
    $results = [];
    
    if (!file_exists(TEST_LOG_FILE)) {
        echo "Test results file not found. Please run tests first.\n";
        return $results;
    }
    
    try {
        $xml = simplexml_load_file(TEST_LOG_FILE);
        
        if (!$xml) {
            echo "Could not parse test results XML.\n";
            return $results;
        }
        
        // JUnit XML has nested testsuites, so we need to find all testcase elements recursively
        // SimpleXML doesn't recurse by default, so we use XPath
        $testcases = $xml->xpath('//testcase');
        
        if (empty($testcases)) {
            echo "No test cases found in XML file.\n";
            return $results;
        }
        
        // Parse test results from JUnit XML format
        foreach ($testcases as $testcase) {
            $testName = (string)$testcase['name'];
            $className = (string)$testcase['classname'];
            
            // Full test method name format: "Tests.SkillShareApplicationTests.testMethodName"
            $fullName = $className . '::' . $testName;
            
            // Check if test has failure or error
            $hasFailure = (count($testcase->failure) > 0);
            $hasError = (count($testcase->error) > 0);
            
            if ($hasFailure) {
                $status = 'FAIL';
                $message = (string)$testcase->failure[0];
            } elseif ($hasError) {
                $status = 'FAIL';
                $message = (string)$testcase->error[0];
            } else {
                $status = 'PASS';
                $message = 'Test passed successfully';
            }
            
            $time = (float)$testcase['time'] ?? 0;
            
            $results[$testName] = [
                'status' => $status,
                'message' => trim($message),
                'time' => $time,
                'fullName' => $fullName
            ];
        }
        
        echo "Parsed " . count($results) . " test results.\n";
        
    } catch (Exception $e) {
        echo "Error parsing XML: " . $e->getMessage() . "\n";
    }
    
    return $results;
}

/**
 * Step 3: Update CSV with results
 */
function updateCSVFile($testResults) {
    global $testMapping;
    
    if (!file_exists(CSV_FILE)) {
        echo "CSV file not found: " . CSV_FILE . "\n";
        return false;
    }
    
    // Read CSV
    $rows = [];
    $handle = fopen(CSV_FILE, 'r');
    
    if (!$handle) {
        echo "Cannot open CSV file for reading.\n";
        return false;
    }
    
    $headers = fgetcsv($handle);
    
    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }
    fclose($handle);
    
    echo "Read " . count($rows) . " test cases from CSV.\n";
    
    // Update rows with test results
    $updated = 0;
    foreach ($rows as &$row) {
        // Ensure row has 12 columns
        while (count($row) < 12) {
            $row[] = '';
        }
        
        $testCaseId = $row[2]; // Column 2 = Test Case ID
        $expectedResult = $row[8]; // Column 8 = Expected Result
        
        // Find matching test method
        $testMethod = $testMapping[$testCaseId] ?? null;
        
        if ($testMethod && isset($testResults[$testMethod])) {
            $result = $testResults[$testMethod];
            
            // Populate columns 9, 10, 11 (Actual Result, Status, Remarks)
            $row[9] = $result['status']; // Actual Result
            $row[10] = $result['status']; // Status (PASS/FAIL)
            $row[11] = $result['message'] ?: "Execution time: {$result['time']}s"; // Remarks
            
            $updated++;
        } else {
            // No test found for this case
            $row[9] = 'NOT RUN';
            $row[10] = 'PENDING';
            $row[11] = 'No corresponding PHPUnit test found';
        }
    }
    
    // Write updated CSV
    $handle = fopen(CSV_FILE, 'w');
    
    if (!$handle) {
        echo "Cannot open CSV file for writing.\n";
        return false;
    }
    
    fputcsv($handle, $headers);
    foreach ($rows as $row) {
        fputcsv($handle, array_slice($row, 0, 12));
    }
    fclose($handle);
    
    echo "Updated $updated test cases in CSV.\n";
    return true;
}

/**
 * Main execution
 */
function main() {
    echo "====================================\n";
    echo "Test Results CSV Generator\n";
    echo "====================================\n\n";
    
    // Step 1: Run tests
    echo "Step 1: Running PHPUnit tests\n";
    echo "------------------------------\n";
    $exitCode = runTests();
    echo "\n";
    
    // Step 2: Parse results
    echo "Step 2: Parsing test results\n";
    echo "------------------------------\n";
    $testResults = parseTestResults();
    echo "Found " . count($testResults) . " test results.\n\n";
    
    // Step 3: Update CSV
    echo "Step 3: Updating CSV file\n";
    echo "------------------------------\n";
    if (updateCSVFile($testResults)) {
        echo "\n✓ Success! CSV file has been updated.\n";
        echo "Location: " . CSV_FILE . "\n";
        echo "\nColumns populated:\n";
        echo "  - Column 10: Actual Result\n";
        echo "  - Column 11: Status (PASS/FAIL/PENDING)\n";
        echo "  - Column 12: Remarks\n";
        return true;
    } else {
        echo "\n✗ Failed to update CSV file.\n";
        return false;
    }
}

// Run only if called directly from command line
if (php_sapi_name() === 'cli') {
    $success = main();
    exit($success ? 0 : 1);
}
?>
