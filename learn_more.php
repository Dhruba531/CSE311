<?php
session_start();
require 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn More - Stock Trading App</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .learn-more-page {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(124, 58, 237, 0.08) 0%, transparent 25%);
        }

        .learn-header {
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
        }

        .learn-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #ffffff, #60a5fa);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        .learn-subtitle {
            color: #94a3b8;
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 4rem;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        .feature-card {
            background: rgba(30, 41, 59, 0.3);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2.5rem;
            border-radius: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.05), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            background: rgba(30, 41, 59, 0.5);
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.5);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: #60a5fa;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }

        .feature-card h3 {
            color: #f1f5f9;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #cbd5e1;
            line-height: 1.7;
            font-size: 1.05rem;
        }

        .cta-section {
            text-align: center;
            padding: 6rem 2rem;
            background: linear-gradient(to top, rgba(15, 23, 42, 1), transparent);
            position: relative;
        }

        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 100;
        }

        .back-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .learn-title {
                font-size: 2.5rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="learn-more-page">
    <a href="index.php" class="back-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7" />
        </svg>
        Back to Home
    </a>

    <header class="learn-header">
        <h1 class="learn-title">Why Choose Our Platform?</h1>
        <p class="learn-subtitle">Experience the next generation of stock trading with powerful tools designed for both
            beginners and pros.</p>
    </header>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <h3>Real-Time Data</h3>
            <p>Get instant access to live market data. Our platform processes thousands of updates per second to ensure
                you never miss a beat.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <h3>Bank-Grade Security</h3>
            <p>Your assets are protected by industry-leading encryption and security protocols. We prioritize your
                safety above all else.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
            </div>
            <h3>Smart Alerts</h3>
            <p>Set custom price alerts and get notified instantly when stocks hit your target price. Never miss a
                trading opportunity again.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <h3>Social Trading</h3>
            <p>Connect with friends, share strategies, and learn from top traders in our community. Trading is better
                together.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                    <path d="M22 12A10 10 0 0 0 12 2v10z" />
                </svg>
            </div>
            <h3>Advanced Analytics</h3>
            <p>Visualize your portfolio performance with beautiful, interactive charts. Understand your asset allocation
                at a glance.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
            </div>
            <h3>Cross-Platform</h3>
            <p>Trade seamlessly across all your devices. Our responsive design ensures a premium experience on desktop,
                tablet, and mobile.</p>
        </div>
    </div>

    <div class="cta-section">
        <h2 class="learn-title" style="font-size: 2.5rem;">Ready to Start Trading?</h2>
        <p class="learn-subtitle">Join thousands of traders who trust our platform.</p>
        <a href="register.php" class="btn-primary" style="font-size: 1.2rem; padding: 1rem 2.5rem;">Create Free
            Account</a>
    </div>
</body>

</html>