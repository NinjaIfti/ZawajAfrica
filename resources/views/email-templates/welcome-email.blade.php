<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ZawajAfrica</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 300;
        }
        .welcome-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #333;
            font-size: 26px;
            margin-bottom: 20px;
            text-align: center;
        }
        .content p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
        .steps {
            margin: 30px 0;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .step-number {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-content h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .step-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .tips {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            border-left: 4px solid #3498db;
        }
        .tips h3 {
            color: #2980b9;
            margin-top: 0;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .footer h3 {
            color: #ecf0f1;
            margin-bottom: 15px;
        }
        .contact-info {
            margin: 20px 0;
        }
        .contact-info a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome-icon">🎉</div>
            <h1>Welcome to ZawajAfrica!</h1>
            <p>We're thrilled to have you join our community</p>
        </div>
        
        <div class="content">
            <h2>Your journey to finding love begins now!</h2>
            
            <p>Thank you for choosing ZawajAfrica - where hearts connect across the beautiful continent of Africa. We're excited to help you find meaningful relationships and your perfect match.</p>
            
            <a href="https://zawajafrica.com/profile/complete" class="btn-primary">Complete Your Profile</a>
            
            <div class="steps">
                <h3 style="text-align: center; color: #333; margin-bottom: 25px;">Get Started in 3 Easy Steps:</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Complete Your Profile</h3>
                        <p>Add photos, write about yourself, and set your preferences to attract the right matches.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Browse & Connect</h3>
                        <p>Explore profiles, send likes, and start conversations with people who interest you.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Meet Your Match</h3>
                        <p>Build meaningful connections and plan your first meeting with someone special.</p>
                    </div>
                </div>
            </div>
            
            <div class="tips">
                <h3>💡 Pro Tips for Success:</h3>
                <ul>
                    <li>Upload 3-5 high-quality photos that show your personality</li>
                    <li>Write a genuine bio that reflects who you are</li>
                    <li>Be active - check your matches and messages regularly</li>
                    <li>Stay safe - always meet in public places for first dates</li>
                </ul>
            </div>
            
            <p style="text-align: center; font-weight: 500;">Ready to find your soulmate? Let's get started!</p>
        </div>
        
        <div class="footer">
            <h3>Need Help?</h3>
            <div class="contact-info">
                <p>Our support team is here to help you succeed:</p>
                <p>📧 Email: <a href="mailto:support@zawajafrica.com">support@zawajafrica.com</a></p>
                <p>💬 Live Chat: Available 24/7 on our website</p>
                <p>📱 WhatsApp: +234 XXX XXX XXXX</p>
            </div>
            <p style="margin-top: 30px; font-size: 14px; color: #bdc3c7;">
                &copy; {{ date('Y') }} ZawajAfrica. All rights reserved.<br>
                You're receiving this email because you recently signed up for ZawajAfrica.
            </p>
        </div>
    </div>
</body>
</html> 