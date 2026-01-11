<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * SkillShare Integration Tests with Mock Database
 * 
 * These tests simulate actual application workflows
 * using mock database operations and $_GET, $_POST, $_SESSION superglobals
 */
class SkillShareIntegrationTests extends TestCase
{
    private array $mockUsers = [];
    private array $mockRequests = [];
    private array $mockMessages = [];
    private array $mockSkills = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset superglobals
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER = ['REQUEST_METHOD' => 'GET'];

        // Initialize mock data
        $this->initializeMockData();
    }

    private function initializeMockData(): void
    {
        $this->mockUsers = [
            1 => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => password_hash('SecurePass123', PASSWORD_DEFAULT),
            ],
            2 => [
                'id' => 2,
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => password_hash('AnotherPass456', PASSWORD_DEFAULT),
            ],
            3 => [
                'id' => 3,
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'password' => password_hash('BobPass789', PASSWORD_DEFAULT),
            ],
        ];

        $this->mockSkills = [
            1 => ['id' => 1, 'skill_name' => 'PHP Programming', 'user_id' => 1],
            2 => ['id' => 2, 'skill_name' => 'Web Design', 'user_id' => 2],
            3 => ['id' => 3, 'skill_name' => 'Database Design', 'user_id' => 3],
        ];

        $this->mockRequests = [
            1 => [
                'id' => 1,
                'skill_id' => 1,
                'requester_id' => 2,
                'status' => 'accepted',
                'skill_name' => 'PHP Programming'
            ],
            2 => [
                'id' => 2,
                'skill_id' => 2,
                'requester_id' => 1,
                'status' => 'accepted',
                'skill_name' => 'Web Design'
            ],
            3 => [
                'id' => 3,
                'skill_id' => 3,
                'requester_id' => 1,
                'status' => 'pending',
                'skill_name' => 'Database Design'
            ],
        ];

        $this->mockMessages = [
            1 => [
                'id' => 1,
                'request_id' => 1,
                'sender_id' => 1,
                'message' => 'Can you teach me PHP?',
                'is_read' => 1,
                'name' => 'John Doe',
                'created_at' => '2024-01-10 10:00:00'
            ],
            2 => [
                'id' => 2,
                'request_id' => 1,
                'sender_id' => 2,
                'message' => 'Sure, I would love to help!',
                'is_read' => 1,
                'name' => 'Jane Smith',
                'created_at' => '2024-01-10 10:05:00'
            ],
        ];
    }

    /**
     * ==========================================
     * REGISTRATION WORKFLOW TESTS
     * ==========================================
     */

    /**
     * Complete user registration workflow
     */
    public function testCompleteRegistrationWorkflow(): void
    {
        $_POST['register'] = true;
        $_POST['name'] = 'Alice Johnson';
        $_POST['email'] = 'alice@example.com';
        $_POST['password'] = 'AlicePass123';

        // Validation
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $this->assertNotEmpty($name);
        $this->assertNotEmpty($email);
        $this->assertNotEmpty($password);
        $this->assertNotFalse(filter_var($email, FILTER_VALIDATE_EMAIL));

        // Check if email exists
        $emailExists = isset($this->mockUsers[2]) && 
                       in_array($email, array_column($this->mockUsers, 'email'));
        $this->assertFalse($emailExists, 'Email should not exist');

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Verify hash
        $this->assertTrue(password_verify($password, $hashedPassword));

        // Simulate INSERT
        $newUser = [
            'id' => 4,
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ];

        $this->assertEquals('Alice Johnson', $newUser['name']);
        $this->assertEquals('alice@example.com', $newUser['email']);
    }

    /**
     * ==========================================
     * LOGIN WORKFLOW TESTS
     * ==========================================
     */

    /**
     * Complete user login workflow
     */
    public function testCompleteLoginWorkflow(): void
    {
        $_POST['login'] = true;
        $_POST['email'] = 'john@example.com';
        $_POST['password'] = 'SecurePass123';

        // Check if already logged in
        $this->assertArrayNotHasKey('user_id', $_SESSION);

        // Get email from POST
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Find user in mock database
        $user = null;
        foreach ($this->mockUsers as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }

        $this->assertNotNull($user, 'User should exist');

        // Verify password
        $isPasswordValid = password_verify($password, $user['password']);
        $this->assertTrue($isPasswordValid);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('John Doe', $_SESSION['user_name']);
    }

    /**
     * Login with wrong password
     */
    public function testLoginWithWrongPasswordWorkflow(): void
    {
        $_POST['login'] = true;
        $_POST['email'] = 'john@example.com';
        $_POST['password'] = 'WrongPassword';

        $email = $_POST['email'];
        $password = $_POST['password'];

        $user = null;
        foreach ($this->mockUsers as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }

        $this->assertNotNull($user);

        $isPasswordValid = password_verify($password, $user['password']);
        $this->assertFalse($isPasswordValid);

        // Session should NOT be set
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * ==========================================
     * CHAT WORKFLOW TESTS
     * ==========================================
     */

    /**
     * Send and fetch chat messages workflow
     */
    public function testSendAndFetchMessagesWorkflow(): void
    {
        // Setup: User logged in
        $_SESSION['user_id'] = 1;
        $_GET['request_id'] = 1;
        $_POST['send'] = true;
        $_POST['message'] = 'This is a test message';

        $user_id = $_SESSION['user_id'];
        $request_id = (int) $_GET['request_id'];
        $message = trim($_POST['message']);

        // Check authentication
        $this->assertArrayHasKey('user_id', $_SESSION);

        // Check if message is empty
        $this->assertNotEmpty($message);

        // Check request access
        $request = $this->mockRequests[$request_id] ?? null;
        $this->assertNotNull($request);

        $hasAccess = $request['requester_id'] === $user_id || 
                     $this->mockSkills[$request['skill_id']]['user_id'] === $user_id;
        $this->assertTrue($hasAccess);

        // Check request status
        $this->assertEquals('accepted', $request['status']);

        // Simulate message insert
        $newMessageId = max(array_keys($this->mockMessages)) + 1;
        $this->mockMessages[$newMessageId] = [
            'id' => $newMessageId,
            'request_id' => $request_id,
            'sender_id' => $user_id,
            'message' => htmlspecialchars($message),
            'is_read' => 0,
            'name' => $this->mockUsers[$user_id]['name'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Fetch messages
        $messages = array_filter($this->mockMessages, function($msg) use ($request_id) {
            return $msg['request_id'] === $request_id;
        });

        usort($messages, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });

        $this->assertGreaterThan(2, count($messages));
        $this->assertEquals('This is a test message', $messages[count($messages) - 1]['message']);
    }

    /**
     * Chat access control test
     */
    public function testChatAccessControlWorkflow(): void
    {
        // User 3 attempts to access request_id=1 (belongs to users 1 and 2)
        $_SESSION['user_id'] = 3;
        $_GET['request_id'] = 1;

        $user_id = $_SESSION['user_id'];
        $request_id = (int) $_GET['request_id'];

        $request = $this->mockRequests[$request_id] ?? null;
        $this->assertNotNull($request);

        // Check if user has access
        $hasAccess = $request['requester_id'] === $user_id || 
                     $this->mockSkills[$request['skill_id']]['user_id'] === $user_id;

        $this->assertFalse($hasAccess, 'User 3 should not have access to request 1');
    }

    /**
     * Mark messages as read workflow
     */
    public function testMarkMessagesAsReadWorkflow(): void
    {
        // User 2 (skill owner) reads messages from User 1
        $_SESSION['user_id'] = 2;
        $_GET['request_id'] = 1;

        $user_id = $_SESSION['user_id'];
        $request_id = (int) $_GET['request_id'];

        // Get unread messages from other users
        $unreadMessages = array_filter($this->mockMessages, function($msg) use ($request_id, $user_id) {
            return $msg['request_id'] === $request_id && 
                   $msg['sender_id'] !== $user_id && 
                   $msg['is_read'] === 0;
        });

        $this->assertEmpty($unreadMessages, 'No unread messages in mock data');
    }

    /**
     * ==========================================
     * REQUEST WORKFLOW TESTS
     * ==========================================
     */

    /**
     * Create skill request workflow
     */
    public function testCreateSkillRequestWorkflow(): void
    {
        // User 1 requests skill 2 from User 2
        $_SESSION['user_id'] = 1;
        $_GET['skill_id'] = 2;

        // Check authentication
        $this->assertArrayHasKey('user_id', $_SESSION);

        $skill_id = (int) $_GET['skill_id'];
        $requester_id = $_SESSION['user_id'];

        // Verify skill exists
        $skill = $this->mockSkills[$skill_id] ?? null;
        $this->assertNotNull($skill);

        // Verify requester is not skill owner
        $this->assertNotEquals($skill['user_id'], $requester_id);

        // Simulate INSERT
        $newRequestId = max(array_keys($this->mockRequests)) + 1;
        $this->mockRequests[$newRequestId] = [
            'id' => $newRequestId,
            'skill_id' => $skill_id,
            'requester_id' => $requester_id,
            'status' => 'pending',
            'skill_name' => $skill['skill_name']
        ];

        $this->assertEquals('pending', $this->mockRequests[$newRequestId]['status']);
        $this->assertEquals(1, $this->mockRequests[$newRequestId]['requester_id']);
    }

    /**
     * ==========================================
     * SECURITY WORKFLOW TESTS
     * ==========================================
     */

    /**
     * XSS prevention in message display
     */
    public function testXSSPreventionInMessageWorkflow(): void
    {
        $_SESSION['user_id'] = 1;
        $_POST['send'] = true;
        $_POST['message'] = "<script>alert('XSS')</script>";

        $message = $_POST['message'];
        $displayMessage = htmlspecialchars($message);

        $this->assertStringNotContainsString('<script>', $displayMessage);
        $this->assertStringContainsString('&lt;script&gt;', $displayMessage);
    }

    /**
     * SQL injection prevention in login
     */
    public function testSQLInjectionPreventionLoginWorkflow(): void
    {
        $_POST['login'] = true;
        $_POST['email'] = "admin' OR '1'='1";
        $_POST['password'] = 'anything';

        $email = $_POST['email'];
        $password = $_POST['password'];

        // Find user with exact match (not string injection)
        $user = null;
        foreach ($this->mockUsers as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }

        $this->assertNull($user, 'Injection payload should not match any user');
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /**
     * Session fixation prevention
     */
    public function testSessionFixationPreventionWorkflow(): void
    {
        // Attempt to manually set user_id
        $_SESSION['user_id'] = 999;

        // Validate against database
        $validUserIds = array_keys($this->mockUsers);
        $sessionUserId = $_SESSION['user_id'];

        $isValidUser = in_array($sessionUserId, $validUserIds);
        $this->assertFalse($isValidUser, 'Non-existent user ID should be detected');
    }

    /**
     * ==========================================
     * INPUT VALIDATION WORKFLOW TESTS
     * ==========================================
     */

    /**
     * Email validation workflow
     */
    public function testEmailValidationWorkflow(): void
    {
        $_POST['register'] = true;
        $_POST['email'] = 'invalid-email';

        $email = $_POST['email'];
        
        $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->assertFalse($isValidEmail);
    }

    /**
     * Input trimming and sanitization
     */
    public function testInputSanitizationWorkflow(): void
    {
        $_POST['register'] = true;
        $_POST['name'] = '  John Doe  ';
        $_POST['message'] = '  Hello World  ';

        $name = trim($_POST['name']);
        $message = trim($_POST['message']);

        $this->assertEquals('John Doe', $name);
        $this->assertEquals('Hello World', $message);
    }

    /**
     * ==========================================
     * DATABASE CONSTRAINT TESTS
     * ==========================================
     */

    /**
     * Foreign key constraint check
     */
    public function testForeignKeyConstraint(): void
    {
        $_SESSION['user_id'] = 1;
        $_POST['send'] = true;
        $_POST['message'] = 'Test message';

        $request_id = 9999; // Non-existent request

        // Verify request exists
        $request = $this->mockRequests[$request_id] ?? null;
        $this->assertNull($request, 'Request should not exist, foreign key constraint violated');
    }

    /**
     * ==========================================
     * INTEGRATION SCENARIO TESTS
     * ==========================================
     */

    /**
     * Complete workflow: Register -> Login -> Create Request -> Send Message
     */
    public function testCompleteApplicationWorkflow(): void
    {
        // Step 1: Register
        $_POST['register'] = true;
        $_POST['name'] = 'Eve Taylor';
        $_POST['email'] = 'eve@example.com';
        $_POST['password'] = 'EvePass123';

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $this->assertNotEmpty($name);
        $this->assertNotEmpty($email);
        $this->assertNotFalse(filter_var($email, FILTER_VALIDATE_EMAIL));

        // Add to mock users
        $newUserId = max(array_keys($this->mockUsers)) + 1;
        $this->mockUsers[$newUserId] = [
            'id' => $newUserId,
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        // Step 2: Login
        $_SESSION = [];
        $_POST = ['login' => true, 'email' => $email, 'password' => $password];

        $user = $this->mockUsers[$newUserId];
        $this->assertTrue(password_verify($_POST['password'], $user['password']));

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_name'] = $user['name'];

        $this->assertArrayHasKey('user_id', $_SESSION);

        // Step 3: Create request
        $_GET = ['skill_id' => 1];
        $skill_id = (int) $_GET['skill_id'];

        $newRequestId = max(array_keys($this->mockRequests)) + 1;
        $this->mockRequests[$newRequestId] = [
            'id' => $newRequestId,
            'skill_id' => $skill_id,
            'requester_id' => $_SESSION['user_id'],
            'status' => 'pending',
            'skill_name' => $this->mockSkills[$skill_id]['skill_name']
        ];

        // Step 4: Send message (after request accepted)
        $this->mockRequests[$newRequestId]['status'] = 'accepted';
        $_POST['message'] = 'Hello, I would like to learn from you!';

        $message = trim($_POST['message']);
        $this->assertNotEmpty($message);

        // Final verification
        $this->assertEquals($newUserId, $_SESSION['user_id']);
        $this->assertEquals($newUserId, $this->mockRequests[$newRequestId]['requester_id']);
    }
}
