<?php

/**
 * Comprehensive Email Notification Test Script for ZawajAfrica
 * Tests: Welcome emails, Like notifications, Match notifications
 * Uses Zoho Mail service for all notifications
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import necessary classes
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserMatch;
use App\Models\UserLike;
use App\Events\UserRegistered;
use App\Services\AIEmailService;
use App\Services\ZohoMailService;
use App\Notifications\NewLikeReceived;
use App\Notifications\NewMatchFound;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmailNotificationTester
{
    private $aiEmailService;
    private $zohoMailService;
    private $testUsers = [];
    private $results = [];
    public function __construct()
    {
        $this->aiEmailService = app(AIEmailService::class);
        $this->zohoMailService = app(ZohoMailService::class);
        
        echo "🔧 Email Notification Testing Suite for ZawajAfrica\n";
        echo "=================================================\n\n";
    }

    /**
     * Send welcome email directly to user (no override needed since we're using real user)
     */
    private function sendTestWelcomeEmail($user)
    {
        return $this->aiEmailService->sendWelcomeEmail($user);
    }

    /**
     * Run all email notification tests
     */
    public function runAllTests()
    {
        try {
            $this->setupTestData();
            $this->testZohoMailConfiguration();
            $this->testWelcomeEmails();
            $this->testLikeNotifications();
            $this->testMatchNotifications();
            $this->displayResults();
            $this->cleanup();
        } catch (Exception $e) {
            echo "❌ Fatal Error: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    /**
     * Setup test users and data
     */
    private function setupTestData()
    {
        echo "📊 Setting up test data...\n";

        try {
            // Use your actual user accounts for all tests
            $mainUser = User::find(11); // Iftikhar Ahmed
            $secondaryUser = User::find(8); // Jarinah
            if (!$mainUser) {
                throw new Exception("User with ID 11 (Iftikhar Ahmed) not found in database");
            }
            if (!$secondaryUser) {
                throw new Exception("User with ID 8 (Jarinah) not found in database");
            }
            
            echo "✅ Found main user: {$mainUser->name} ({$mainUser->email})\n";
            echo "✅ Found secondary user: {$secondaryUser->name} ({$secondaryUser->email})\n";

            $this->testUsers = [$mainUser, $secondaryUser];
            
            echo "📧 All emails will be sent to: {$mainUser->email} and {$secondaryUser->email}\n";
            echo "✅ Test data setup completed\n\n";

        } catch (Exception $e) {
            throw new Exception("Failed to setup test data: " . $e->getMessage());
        }
    }

    /**
     * Test Zoho Mail configuration
     */
    private function testZohoMailConfiguration()
    {
        echo "🔧 Testing Zoho Mail Configuration...\n";

        try {
            $isConfigured = $this->zohoMailService->isConfigured();
            if (!$isConfigured) {
                throw new Exception("Zoho Mail is not properly configured");
            }

            // Test connection to Zoho API
            $connectionResult = $this->zohoMailService->testConnection();
            if (!$connectionResult['success']) {
                throw new Exception("Failed to connect to Zoho API: " . ($connectionResult['error'] ?? 'Unknown error'));
            }

            echo "✅ Zoho Mail configuration is valid\n";
            echo "✅ Zoho API connection test successful\n";
            $this->results['zoho_config'] = true;

        } catch (Exception $e) {
            echo "❌ Zoho Mail configuration failed: " . $e->getMessage() . "\n";
            $this->results['zoho_config'] = false;
            throw $e;
        }

        echo "\n";
    }

    /**
     * Test AI-generated welcome emails
     */
    private function testWelcomeEmails()
    {
        echo "💌 Testing Welcome Email Functionality...\n";

        foreach ($this->testUsers as $index => $user) {
            echo "📧 Testing welcome email for: {$user->name}\n";

            try {
                // Test AI email generation using our test email
                $result = $this->sendTestWelcomeEmail($user);

                if ($result['success']) {
                    echo "✅ Welcome email sent successfully to {$user->name} ({$user->email})\n";
                    $this->results['welcome_emails'][$user->id] = true;
                } else {
                    echo "❌ Failed to send welcome email to {$user->name} ({$user->email}): " . ($result['error'] ?? 'Unknown error') . "\n";
                    $this->results['welcome_emails'][$user->id] = false;
                }

                // Also test via event system
                if ($index === 0) {
                    echo "🔄 Testing welcome email via UserRegistered event...\n";
                    event(new UserRegistered($user));
                    echo "✅ UserRegistered event fired successfully\n";
                }

            } catch (Exception $e) {
                echo "❌ Exception during welcome email test for {$user->name}: " . $e->getMessage() . "\n";
                $this->results['welcome_emails'][$user->id] = false;
            }

            // Add delay between emails
            sleep(2);
        }

        echo "\n";
    }

    /**
     * Test like notifications
     */
    private function testLikeNotifications()
    {
        echo "💕 Testing Like Notification Functionality...\n";

        if (count($this->testUsers) < 2) {
            echo "❌ Need at least 2 test users for like notifications\n";
            return;
        }

        $liker = $this->testUsers[0]; // Ahmed
        $receiver = $this->testUsers[1]; // Fatima

        try {
            echo "👍 Testing like from {$liker->name} to {$receiver->name}\n";

            // Create a like record
            $like = UserLike::create([
                'user_id' => $liker->id,
                'liked_user_id' => $receiver->id,
                'status' => 'pending',
                'liked_at' => now(),
            ]);

            // Test like notification directly
            $notification = new NewLikeReceived($liker, $receiver);
            
            // Send notification to receiver
            $receiver->notify($notification);
            
            echo "✅ Like notification sent to database successfully\n";
            
            // Test email portion manually via Zoho
            try {
                $mailResult = $notification->toMail($receiver);
                echo "✅ Like notification email template generated successfully\n";
                
                // Test immediate like email sending (as done in MatchController)
                $zohoMailService = app(ZohoMailService::class);
                if ($zohoMailService->isConfigured()) {
                    $subject = '💕 ' . $liker->name . ' likes you on ZawajAfrica!';
                    $content = "Hi " . $receiver->name . ",\n\n" .
                              $liker->name . " has liked your profile!\n\n" .
                              "Check out their profile and maybe like them back for a potential match!\n\n" .
                              "View their profile: " . url('/profile/view/' . $liker->id) . "\n\n" .
                              "Don't miss out on potential connections!\n\n" .
                              "Best regards,\nZawajAfrica Team";

                                                              $emailResult = $zohoMailService->sendNotificationEmail(
                         'support',
                         $receiver->email,
                         $subject,
                         $content,
                         $receiver->name
                     );

                     if ($emailResult['success']) {
                         echo "✅ Like email sent successfully to {$receiver->email} via Zoho Mail\n";
                         $this->results['like_notifications'] = true;
                     } else {
                         echo "❌ Failed to send like email to {$receiver->email} via Zoho: " . ($emailResult['error'] ?? 'Unknown error') . "\n";
                         $this->results['like_notifications'] = false;
                     }
                } else {
                    echo "❌ Zoho Mail not configured for like notifications\n";
                    $this->results['like_notifications'] = false;
                }

            } catch (Exception $e) {
                echo "❌ Failed to send like email: " . $e->getMessage() . "\n";
                $this->results['like_notifications'] = false;
            }

        } catch (Exception $e) {
            echo "❌ Exception during like notification test: " . $e->getMessage() . "\n";
            $this->results['like_notifications'] = false;
        }

        sleep(2);
        echo "\n";
    }

    /**
     * Test match notifications (mutual likes)
     */
    private function testMatchNotifications()
    {
        echo "🌟 Testing Match Notification Functionality...\n";

        if (count($this->testUsers) < 2) {
            echo "❌ Need at least 2 test users for match notifications\n";
            return;
        }

        $user1 = $this->testUsers[0]; // Ahmed
        $user2 = $this->testUsers[1]; // Fatima

        try {
            echo "💑 Testing mutual likes between {$user1->name} and {$user2->name}\n";

            // Create mutual likes to trigger a match
            UserLike::create([
                'user_id' => $user1->id,
                'liked_user_id' => $user2->id,
                'status' => 'pending',
                'liked_at' => now(),
            ]);

            UserLike::create([
                'user_id' => $user2->id,
                'liked_user_id' => $user1->id,
                'status' => 'pending',
                'liked_at' => now(),
            ]);

            // Create match record
            UserMatch::create([
                'user1_id' => min($user1->id, $user2->id),
                'user2_id' => max($user1->id, $user2->id),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo "✅ Match record created successfully\n";

            // Test match notification
            $notification = new NewMatchFound($user2);
            $user1->notify($notification);

            echo "✅ Match notification sent to database successfully\n";

            // Test match email via Zoho
            try {
                $zohoMailService = app(ZohoMailService::class);
                if ($zohoMailService->isConfigured()) {
                    $subject = '🌟 It\'s a Match! You and ' . $user2->name . ' liked each other!';
                    
                    $content = "Hi " . $user1->name . ",\n\n" .
                              "🎉 Congratulations! You have a new match!\n\n" .
                              "You and " . $user2->name . " both liked each other!\n\n" .
                              "This means you can now start messaging each other and get to know one another better.\n\n" .
                              "Start messaging: " . url('/messages') . "\n\n" .
                              "Next Steps:\n" .
                              "• Send a thoughtful first message\n" .
                              "• Be genuine and respectful in your conversations\n" .
                              "• Take your time to get to know each other\n\n" .
                              "We're excited to see where this connection leads!\n\n" .
                              "Best regards,\nZawajAfrica Team";

                                         $emailResult = $zohoMailService->sendNotificationEmail(
                         'support',
                         $user1->email,
                         $subject,
                         $content,
                         $user1->name
                     );

                     if ($emailResult['success']) {
                         echo "✅ Match email sent successfully to {$user1->email} for {$user1->name}\n";
                         
                         // Send to the second user as well (but since user1 is your account, only user1 will get the email)
                         if ($user1->id !== $user2->id) {
                             $emailResult2 = $zohoMailService->sendNotificationEmail(
                                 'support',
                                 $user2->email,
                                 '🌟 It\'s a Match! You and ' . $user1->name . ' liked each other!',
                                 str_replace($user1->name, $user2->name, str_replace($user2->name, $user1->name, $content)),
                                 $user2->name
                             );

                             if ($emailResult2['success']) {
                                 echo "✅ Match email sent successfully to {$user2->email} for {$user2->name}\n";
                             } else {
                                 echo "❌ Failed to send match email to {$user2->email} for {$user2->name}\n";
                             }
                         }
                         $this->results['match_notifications'] = true;
                     } else {
                         echo "❌ Failed to send match email to {$user1->email} for {$user1->name}\n";
                         $this->results['match_notifications'] = false;
                     }
                } else {
                    echo "❌ Zoho Mail not configured for match notifications\n";
                    $this->results['match_notifications'] = false;
                }

            } catch (Exception $e) {
                echo "❌ Failed to send match email: " . $e->getMessage() . "\n";
                $this->results['match_notifications'] = false;
            }

        } catch (Exception $e) {
            echo "❌ Exception during match notification test: " . $e->getMessage() . "\n";
            $this->results['match_notifications'] = false;
        }

        echo "\n";
    }

    /**
     * Display test results summary
     */
    private function displayResults()
    {
        echo "📊 TEST RESULTS SUMMARY\n";
        echo "======================\n\n";

        $totalTests = 0;
        $passedTests = 0;

        // Zoho Configuration
        echo "🔧 Zoho Mail Configuration: " . ($this->results['zoho_config'] ? "✅ PASS" : "❌ FAIL") . "\n";
        $totalTests++;
        if ($this->results['zoho_config']) $passedTests++;

        // Welcome Emails
        if (isset($this->results['welcome_emails'])) {
            $welcomePass = count(array_filter($this->results['welcome_emails']));
            $welcomeTotal = count($this->results['welcome_emails']);
            echo "💌 Welcome Emails: ✅ {$welcomePass}/{$welcomeTotal} PASSED\n";
            $totalTests += $welcomeTotal;
            $passedTests += $welcomePass;
        }

        // Like Notifications
        echo "💕 Like Notifications: " . (($this->results['like_notifications'] ?? false) ? "✅ PASS" : "❌ FAIL") . "\n";
        $totalTests++;
        if ($this->results['like_notifications'] ?? false) $passedTests++;

        // Match Notifications
        echo "🌟 Match Notifications: " . (($this->results['match_notifications'] ?? false) ? "✅ PASS" : "❌ FAIL") . "\n";
        $totalTests++;
        if ($this->results['match_notifications'] ?? false) $passedTests++;

        echo "\n";
        echo "📈 OVERALL RESULTS: {$passedTests}/{$totalTests} tests passed\n";
        
        if ($passedTests === $totalTests) {
            echo "🎉 ALL TESTS PASSED! Email notification system is working perfectly.\n";
        } else {
            echo "⚠️  Some tests failed. Please check the logs and configuration.\n";
        }

        echo "\n";
    }

    /**
     * Clean up test data
     */
    private function cleanup()
    {
        echo "🧹 Cleaning up test data...\n";

        try {
            DB::beginTransaction();

            foreach ($this->testUsers as $user) {
                // Only delete test users, not the main user (ID 11)
                if ($user->id !== 11) {
                    // Delete related records
                    UserLike::where('user_id', $user->id)->orWhere('liked_user_id', $user->id)->delete();
                    UserMatch::where('user1_id', $user->id)->orWhere('user2_id', $user->id)->delete();
                    
                    // Delete user profile
                    $user->profile()->delete();
                    
                    // Delete notifications
                    $user->notifications()->delete();
                    
                    // Delete user
                    $user->delete();
                    
                    echo "✅ Cleaned up test user: {$user->name}\n";
                } else {
                    // Just clean up test data for main user
                    UserLike::where('user_id', $user->id)->orWhere('liked_user_id', $user->id)->delete();
                    UserMatch::where('user1_id', $user->id)->orWhere('user2_id', $user->id)->delete();
                    echo "✅ Cleaned up test data for main user: {$user->name}\n";
                }
            }

            DB::commit();
            echo "✅ Cleanup completed successfully\n";

        } catch (Exception $e) {
            DB::rollBack();
            echo "❌ Cleanup failed: " . $e->getMessage() . "\n";
        }
    }
}

// Run the tests
try {
    $tester = new EmailNotificationTester();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "❌ Test suite failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🏁 Email notification testing completed!\n";
echo "📧 Check your email inbox for the test messages.\n";
echo "📝 Check storage/logs/laravel.log for detailed logs.\n";