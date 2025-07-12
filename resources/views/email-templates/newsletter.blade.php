<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZawajAfrica Newsletter</title>
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
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .date {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 10px;
        }
        .content {
            padding: 0;
        }
        .section {
            padding: 30px;
            border-bottom: 1px solid #eee;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 15px;
            border-left: 4px solid #2ecc71;
            padding-left: 15px;
        }
        .section p {
            color: #666;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .highlight-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #2ecc71;
        }
        .success-story {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            position: relative;
        }
        .success-story::before {
            content: "💕";
            font-size: 30px;
            position: absolute;
            top: -10px;
            right: 20px;
        }
        .success-story h3 {
            color: #e65100;
            margin: 0 0 10px 0;
        }
        .success-story .quote {
            font-style: italic;
            color: #5d4037;
            font-size: 16px;
            margin: 10px 0;
        }
        .success-story .names {
            color: #bf360c;
            font-weight: 600;
            text-align: right;
            margin-top: 15px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 25px 0;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
            margin: 10px;
            flex: 1;
            min-width: 120px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2ecc71;
            display: block;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
        }
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .tip-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-top: 3px solid #2ecc71;
        }
        .tip-card h4 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .tip-card p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            margin: 10px 5px;
        }
        .footer {
            background-color: #34495e;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
        }
        @media (max-width: 600px) {
            .stats {
                flex-direction: column;
            }
            .tips-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💚 ZawajAfrica Newsletter</h1>
            <p>Connecting Hearts Across Africa</p>
            <div class="date">{{ date('F Y') }} Edition</div>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>🎉 This Month's Highlights</h2>
                <p>Welcome to another exciting edition of the ZawajAfrica newsletter! This month has been incredible for our community with new features, amazing success stories, and thousands of new connections.</p>
                
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-number">2,847</span>
                        <div class="stat-label">New Members</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1,205</span>
                        <div class="stat-label">Matches Made</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">34</span>
                        <div class="stat-label">Engagements</div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>💑 Success Story of the Month</h2>
                <div class="success-story">
                    <h3>From Lagos to Love: Amina & Kwame's Journey</h3>
                    <div class="quote">
                        "We both thought online dating wasn't for us, but ZawajAfrica proved us wrong! What started as a simple message turned into late-night conversations, weekend visits, and now we're planning our wedding in December. Thank you for bringing us together!"
                    </div>
                    <div class="names">- Amina (Lagos) & Kwame (Accra)</div>
                </div>
                <p>Congratulations to Amina and Kwame! Their story reminds us that love knows no borders. We're honored to be part of your journey. ✨</p>
            </div>
            
            <div class="section">
                <h2>🆕 New Features & Updates</h2>
                <div class="highlight-box">
                    <h3>🎥 Video Chat is Here!</h3>
                    <p>You can now have face-to-face conversations with your matches through our secure video chat feature. Perfect for getting to know each other better before meeting in person!</p>
                </div>
                
                <div class="highlight-box">
                    <h3>🔍 Enhanced Search Filters</h3>
                    <p>Find your perfect match easier with our new detailed search filters including education, profession, lifestyle preferences, and more!</p>
                </div>
                
                <a href="https://zawajafrica.com/features" class="btn">Explore New Features</a>
            </div>
            
            <div class="section">
                <h2>💡 Dating Tips & Advice</h2>
                <div class="tips-grid">
                    <div class="tip-card">
                        <h4>🖼️ Perfect Profile Photos</h4>
                        <p>Use natural lighting, smile genuinely, and include photos that show your interests and personality.</p>
                    </div>
                    <div class="tip-card">
                        <h4>💬 Great Conversation Starters</h4>
                        <p>Ask about their profile details, shared interests, or travel experiences to spark meaningful conversations.</p>
                    </div>
                    <div class="tip-card">
                        <h4>🤝 First Date Success</h4>
                        <p>Choose a public place, arrive on time, and be yourself. Remember, it's about having fun and getting to know each other!</p>
                    </div>
                    <div class="tip-card">
                        <h4>🛡️ Stay Safe Online</h4>
                        <p>Never share personal information too quickly and always trust your instincts when meeting someone new.</p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>📅 Upcoming Events</h2>
                <p><strong>Virtual Speed Dating Night</strong> - Join us on {{ date('F d, Y', strtotime('+2 weeks')) }} for an exciting virtual speed dating event! Meet multiple potential matches in one fun evening.</p>
                
                <p><strong>ZawajAfrica Meetup in Cairo</strong> - Our community meetup is happening {{ date('F d, Y', strtotime('+1 month')) }}. Network, make friends, and maybe find love!</p>
                
                <a href="https://zawajafrica.com/events" class="btn">View All Events</a>
            </div>
        </div>
        
        <div class="footer">
            <h3>Stay Connected</h3>
            <div class="social-links">
                <a href="#">📘 Facebook</a>
                <a href="#">🐦 Twitter</a>
                <a href="#">📷 Instagram</a>
                <a href="#">💼 LinkedIn</a>
            </div>
            <p>Questions or feedback? Reply to this email or contact us at <a href="mailto:hello@zawajafrica.com" style="color: #3498db;">hello@zawajafrica.com</a></p>
            <p style="margin-top: 20px; font-size: 12px; color: #95a5a6;">
                &copy; {{ date('Y') }} ZawajAfrica. All rights reserved.<br>
                You're receiving this because you're part of our amazing community.<br>
                <a href="#" style="color: #3498db;">Update preferences</a> | <a href="#" style="color: #3498db;">Unsubscribe</a> | <a href="https://zawajafrica.online/privacy-policy" style="color: #3498db;">Privacy Policy</a>
            </p>
        </div>
    </div>
</body>
</html> 