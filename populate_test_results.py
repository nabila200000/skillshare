#!/usr/bin/env python3
"""
Test Results CSV Populator

This script reads the test_cases.csv file and populates the 
Actual Result, Status, and Remarks columns by running PHPUnit tests.

Usage: python populate_test_results.py
"""

import csv
import subprocess
import re
import sys
import json
from pathlib import Path

def run_phpunit_tests():
    """Run PHPUnit and capture output"""
    try:
        result = subprocess.run(
            ['vendor\\bin\\phpunit', '--testdox', '--log-json', 'test-results.json'],
            cwd=str(Path(__file__).parent),
            capture_output=True,
            text=True
        )
        return result
    except Exception as e:
        print(f"Error running tests: {e}")
        return None

def parse_test_results(json_file='test-results.json'):
    """Parse JSON test results from PHPUnit"""
    results = {}
    try:
        with open(json_file, 'r') as f:
            data = json.load(f)
            for test in data.get('tests', []):
                test_name = test.get('name', '')
                status = 'PASS' if test.get('status') == 'passed' else 'FAIL'
                results[test_name] = {
                    'status': status,
                    'message': test.get('message', ''),
                    'time': test.get('time', 0)
                }
    except Exception as e:
        print(f"Error parsing test results: {e}")
    return results

def map_test_case_id_to_function(test_id):
    """Map CSV test case ID to PHPUnit function name"""
    # Examples:
    # TC_REG_001 -> testRegistrationWithValidData
    # TC_LOGIN_001 -> testLoginWithValidCredentials
    mapping = {
        'TC_REG_001': 'testRegistrationWithValidData',
        'TC_REG_002': 'testRegistrationWithDuplicateEmail',
        'TC_REG_003': 'testRegistrationWithEmptyFields',
        'TC_LOGIN_001': 'testLoginWithValidCredentials',
        'TC_LOGIN_002': 'testLoginWithInvalidEmail',
        'TC_LOGIN_003': 'testLoginWithWrongPassword',
        'TC_LOGIN_004': 'testLoginWithEmptyEmail',
        'TC_LOGIN_005': 'testLoginWithEmptyPassword',
        'TC_LOGIN_006': 'testAlreadyLoggedInUserRedirect',
        'TC_LOGOUT_001': 'testLogoutDestroysSession',
        'TC_CHAT_001': 'testSendChatMessageWithValidData',
        'TC_CHAT_002': 'testSendEmptyMessage',
        'TC_CHAT_003': 'testUnauthorizedChatAccess',
        # Add more mappings as needed
    }
    return mapping.get(test_id, '')

def populate_csv(test_results):
    """Update CSV file with test results"""
    csv_file = Path(__file__).parent / 'test_cases.csv'
    
    try:
        # Read existing CSV
        rows = []
        with open(csv_file, 'r', encoding='utf-8') as f:
            reader = csv.reader(f)
            headers = next(reader)
            for row in reader:
                rows.append(row)
        
        # Update rows with test results
        updated_rows = []
        for row in rows:
            if len(row) < 3:
                continue
            
            test_id = row[2]  # Test Case ID is at index 2
            test_func = map_test_case_id_to_function(test_id)
            
            # Ensure row has 12 columns
            while len(row) < 12:
                row.append('')
            
            # Populate Actual Result, Status, Remarks (indices 9, 10, 11)
            if test_func and test_func in test_results:
                result = test_results[test_func]
                row[9] = result['status']  # Actual Result
                row[10] = result['status']  # Status
                row[11] = f"Execution time: {result['time']}s. {result['message']}"  # Remarks
            else:
                row[9] = 'Test not executed'
                row[10] = 'PENDING'
                row[11] = 'No test mapping found'
            
            updated_rows.append(row[:12])  # Keep only 12 columns
        
        # Write updated CSV
        with open(csv_file, 'w', newline='', encoding='utf-8') as f:
            writer = csv.writer(f)
            writer.writerow(headers)
            writer.writerows(updated_rows)
        
        print(f"✓ CSV file updated: {csv_file}")
        return True
        
    except Exception as e:
        print(f"✗ Error updating CSV: {e}")
        return False

def main():
    print("=" * 60)
    print("PHPUnit Test Results CSV Populator")
    print("=" * 60)
    
    print("\n1. Running PHPUnit tests...")
    result = run_phpunit_tests()
    
    if result is None:
        print("✗ Failed to run tests")
        return 1
    
    print(f"✓ Tests completed (exit code: {result.returncode})")
    
    print("\n2. Parsing test results...")
    test_results = parse_test_results()
    print(f"✓ Found {len(test_results)} test results")
    
    print("\n3. Updating CSV file...")
    if populate_csv(test_results):
        print("✓ All done!")
        return 0
    else:
        return 1

if __name__ == '__main__':
    sys.exit(main())
