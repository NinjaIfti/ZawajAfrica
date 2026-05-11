<?php

require __DIR__ . '/vendor/autoload.php';

use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

// Your credentials
$apiKey = '';
$fromEmail = 'admin@zawajafrica.online';
$fromName = 'ZawajAfrica';
$toEmail = 'ifti3061@gmail.com';

try {
    echo "🔄 Testing MailerSend API...\n\n";
    
    // Initialize MailerSend
    $mailersend = new MailerSend(['api_key' => $apiKey]);
    
    // Build email
    $recipients = [
        new Recipient($toEmail, 'Test Recipient'),
    ];
    
    $emailParams = (new EmailParams())
        ->setFrom($fromEmail)
        ->setFromName($fromName)
        ->setRecipients($recipients)
        ->setSubject('MailerSend Test - ZawajAfrica')
        ->setHtml('<h1>Success! ✅</h1><p>Your MailerSend API is working correctly.</p>')
        ->setText('Success! Your MailerSend API is working correctly.');
    
    // Send email
    echo "📧 Sending test email to: $toEmail\n";
    echo "📤 From: $fromName <$fromEmail>\n\n";
    
    $response = $mailersend->email->send($emailParams);
    
    echo "✅ SUCCESS! Email sent successfully!\n";
    echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch (\MailerSend\Exceptions\MailerSendValidationException $e) {
    echo "❌ Validation Error:\n";
    echo $e->getMessage() . "\n";
    print_r($e->getErrors());
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Details: " . $e->getTraceAsString() . "\n";
}
