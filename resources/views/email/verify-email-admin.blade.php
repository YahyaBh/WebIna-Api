<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Administration Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
        }

        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }

        .content {
            text-align: center;
        }

        .content h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
            color: #555;
            margin-bottom: 30px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #ffe662;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #d8ba21;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
        }

        .social-icons a {
            display: inline-block;
            margin: 0 10px;
        }

        .social-icons img {
            width: 40px;
            height: auto;
        }

        .footer p {
            font-size: 14px;
            color: #555;
        }

        @media (max-width: 480px) {
            .email-container {
                padding: 10px;
                border-radius: 0;
            }

            .header img {
                width: 80px;
            }

            .button {
                padding: 10px 20px;
                font-size: 14px;
            }

            .social-icons img {
                width: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <img src="https://i.ibb.co/vLt7StG/WEBINA2.png" alt="Webina Digital Logo">
        </div>
        <div class="content">
            <h1>Verify Your Email Address</h1>
            <p>Hello {{ $name }},</p>
            <p>You're almost ready to access your administration space. Please click the button below to verify your
                email address and get started.</p>
            <a href="{{ $link }}" class="button">VERIFY YOUR EMAIL</a>
        </div>
        <div class="footer">
            <div class="social-icons">
                <a href="https://linkedin.com/webina_digital" target="_blank">
                    <img src="https://cdn.tools.unlayer.com/social/icons/circle-white/linkedin.png" alt="LinkedIn">
                </a>
                <a href="https://instagram.com/_webina" target="_blank">
                    <img src="https://cdn.tools.unlayer.com/social/icons/circle-white/instagram.png" alt="Instagram">
                </a>
                <a href="https://youtube.com/webina_digital" target="_blank">
                    <img src="https://cdn.tools.unlayer.com/social/icons/circle-white/youtube.png" alt="YouTube">
                </a>
                <a href="mailto:webinadigital.com" target="_blank">
                    <img src="https://cdn.tools.unlayer.com/social/icons/circle-white/email.png" alt="Email">
                </a>
            </div>
            <p>&copy; Company All Rights Reserved</p>
        </div>
    </div>
</body>

</html>
