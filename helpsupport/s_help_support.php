<?php
// help.php — Routine Management System Help Page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support | Routine Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        header {
            background-color:#001F3F;
            color: white;
            text-align: center;
            padding: 20px 10px;
        }

        header h1 {
            font-size: 28px;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h2 {
            color: #0d6efd;
            margin-bottom: 10px;
            border-left: 5px solid #0d6efd;
            padding-left: 10px;
        }

        .faq, .tech, .contact, .tips {
            margin-top: 30px;
        }

        .faq p, .tech p, .contact p, .tips p {
            margin: 8px 0;
        }

        .faq strong {
            color: #0d6efd;
        }

        ul {
            margin-left: 20px;
        }

        .contact-info {
            background: #f1f4ff;
            border-left: 5px solid #0d6efd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            padding: 15px 0;
            background-color: #0d6efd;
            color: white;
            font-size: 14px;
        }

        @media(max-width: 600px) {
            .container {
                padding: 20px;
            }
            header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Help & Support</h1>
    <p>Routine Management System</p>
</header>

<div class="container">
    <section class="intro">
        <p>Welcome to the <strong>Routine Management System Help Center</strong>. This page will help students, teachers, and administrators understand how to use the system and troubleshoot common issues.</p>
    </section>

    <section class="faq">
        <h2>💡 Frequently Asked Questions</h2>
        <p><strong>1. How do I log in to the system?</strong><br>
        Go to the login page and enter your username and password. Contact the admin if you don’t have an account.</p>

        <p><strong>2. I forgot my password. What should I do?</strong><br>
        Currently, password reset requests are handled manually. Please contact your department administrator or system admin to reset your account credentials.</p>

        <p><strong>3. How can I view my class routine?</strong><br>
        After logging in, go to <em>Dashboard → View Routine</em> to see your schedule.</p>

        <p><strong>4. Can I update my profile details?</strong><br>
        Yes, go to <em>Profile Settings</em>, edit your information, and click <em>Save</em>.</p>

        <p><strong>5. The routine isn’t showing correctly.</strong><br>
        Try refreshing or clearing cache. If it continues, contact support below.</p>
    </section>

    <section class="tech">
        <h2>⚙️ Technical Help</h2>
        <ul>
            <li><strong>Recommended Browser:</strong> Google Chrome (latest version)</li>
            <li><strong>Supported Devices:</strong> Laptop, Desktop, Tablet</li>
            <li><strong>If server doesn’t start:</strong> Run <code>sudo /opt/lampp/lampp start</code></li>
            <li><strong>If database error:</strong> Check MySQL connection in <code>config.php</code></li>
        </ul>
    </section>

    <section class="contact">
        <h2>📞 Contact Support</h2>
        <div class="contact-info">
            <p><strong>Email:</strong> support@rms.com</p>
            <p><strong>Message:</strong> Use the “Contact Us” form on the homepage to report issues (include screenshots if possible).</p>
            <p><strong>Response Time:</strong> Within 24 hours (for demo purposes, immediate reply).</p>
        </div>
    </section>

    <section class="tips">
        <h2>🧩 Tips for Better Experience</h2>
        <ul>
            <li>Always log out after use, especially on shared devices.</li>
            <li>Don’t share your password with others.</li>
            <li>Report incorrect class timings or conflicts quickly.</li>
            <li>Keep your browser up to date.</li>
        </ul>
    </section>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Routine Management System | All Rights Reserved
</footer>

</body>
</html>