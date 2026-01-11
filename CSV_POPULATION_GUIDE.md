# How to Populate Test Results in CSV File

The `test_cases.csv` file has columns for **Actual Result**, **Status**, and **Remarks**, but they're empty because they need to be populated by running the PHPUnit tests.

## Solution

I've created tools to automatically populate these columns:

### Option 1: PHP Script (Recommended for Windows)

Run this command from the project root:

```bash
php generate_test_results_csv.php
```

**What it does:**
1. Runs all PHPUnit tests with JSON output
2. Parses the test results
3. Maps test results to test case IDs
4. Updates columns in the CSV file:
   - **Column 10 (Actual Result)**: PASS or FAIL
   - **Column 11 (Status)**: PASS, FAIL, or PENDING
   - **Column 12 (Remarks)**: Test error messages and execution time

### Option 2: Python Script

Run from project root:

```bash
python populate_test_results.py
```

This does the same thing as the PHP script but uses Python.

## Manual Approach

If you want to manually update the CSV for specific tests:

1. **Run tests manually:**
   ```bash
   vendor\bin\phpunit --testdox
   ```

2. **Open `test_cases.csv`** in Excel or a text editor

3. **Manually enter results** in columns:
   - Column J (10): Actual Result (e.g., "Test passed", "Test failed")
   - Column K (11): Status (e.g., "PASS", "FAIL", "PENDING")
   - Column L (12): Remarks (e.g., "All assertions passed", "Expected X but got Y")

## Understanding the CSV Structure

```
Column  | Name                | Content
--------|-------------------|-------------------------------------------
1       | Test Sequence ID  | 1, 2, 3, etc.
2       | Test Description  | Human description of test
3       | Test Case ID      | TC_REG_001, TC_LOGIN_002, etc.
4       | Test Case Desc.   | Detailed test description
5       | Test Steps        | Step-by-step instructions
6       | Pre-condition     | Initial state required
7       | Test Data         | Input values to use
8       | Post-condition    | Expected final state
9       | Expected Result   | What should happen (FILLED)
10      | Actual Result     | What actually happened (EMPTY - NEEDS POPULATING)
11      | Status            | Test pass/fail status (EMPTY - NEEDS POPULATING)
12      | Remarks           | Additional notes (EMPTY - NEEDS POPULATING)
```

## Why Columns 10-12 Are Empty

The CSV file is a **test specification template**. The first 9 columns define what tests should do. The last 3 columns document what actually happens when tests run.

These columns must be populated by:
- Running automated tests (PHPUnit) and parsing results, OR
- Manually executing tests and recording results

## Test Case ID Mapping

Each CSV test case ID maps to a PHPUnit test method:

| CSV Test ID    | PHPUnit Method                      |
|----------------|-------------------------------------|
| TC_REG_001     | testRegistrationWithValidData       |
| TC_REG_002     | testRegistrationWithDuplicateEmail  |
| TC_REG_003     | testRegistrationWithEmptyFields     |
| TC_LOGIN_001   | testLoginWithValidCredentials       |
| TC_LOGIN_002   | testLoginWithInvalidEmail           |
| TC_LOGIN_003   | testLoginWithWrongPassword          |
| TC_LOGIN_004   | testLoginWithEmptyEmail             |
| TC_LOGIN_005   | testLoginWithEmptyPassword          |
| TC_LOGIN_006   | testAlreadyLoggedInUserRedirect     |
| TC_LOGOUT_001  | testLogoutDestroysSession           |
| TC_CHAT_001    | testSendChatMessageWithValidData    |
| TC_CHAT_002    | testSendEmptyMessage                |
| TC_CHAT_003    | testUnauthorizedChatAccess          |
| TC_CHAT_004    | testMarkIncomingMessagesAsRead      |
| TC_CHAT_005    | testMessagesDisplayInOrder          |
| TC_CHAT_006    | testUnreadBadgeDisplay              |
| TC_CHAT_007    | testCorrectSenderDisplay            |
| TC_REQ_001     | testCreateSkillRequest              |
| TC_REQ_002     | testUnauthorizedRequestCreation     |
| TC_REQ_003     | testAccessDeniedNonInvolved         |
| TC_REQ_004     | testAccessGrantedRequester          |

## Troubleshooting

**Issue:** Command not found
- Make sure you're in the project root directory (`c:\xampp\htdocs\skillshare\`)

**Issue:** PHP not found
- Check that PHP is in your system PATH, or use full path: `C:\xampp\php\php.exe generate_test_results_csv.php`

**Issue:** vendor\bin\phpunit not found
- Run `composer install` to download dependencies

**Issue:** Some test results not populated
- Check that the test method names in `generate_test_results_csv.php` match the actual method names in the test files
- Run individual tests to debug: `vendor\bin\phpunit --filter testLoginWithValidCredentials`
