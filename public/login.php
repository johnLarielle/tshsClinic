<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Record Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
        }

        h1 {
            color: #1e3a8a;
            text-align: center;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-weight: 600;
        }

        .message.error {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .message.success {
            background: #efe;
            color: #060;
            border: 1px solid #cfc;
        }

        .user-type {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .user-type label {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .user-type input[type="radio"] {
            display: none;
        }

        .user-type input[type="radio"]:checked + label {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        .demo-credentials {
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9em;
        }

        .demo-credentials h3 {
            color: #1e3a8a;
            margin-bottom: 10px;
            font-size: 1em;
        }

        .demo-credentials p {
            margin: 5px 0;
            color: #666;
        }

        .demo-credentials strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <h1>Record Management System</h1>
            <p class="subtitle">Talavera Senior High School</p>
        </div>

        <div id="message" class="message"></div>

        <form id="loginForm">
            <input type="hidden" name="user_type" value="admin">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="demo-credentials">
            <h3>Demo Credentials</h3>
            <p><strong>Username:</strong> admin</p>
            <p><strong>Password:</strong> admin123</p>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const messageDiv = document.getElementById('message');

        function showMessage(message, type) {
            messageDiv.textContent = message;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const data = {
                username: formData.get('username'),
                password: formData.get('password'),
                user_type: formData.get('user_type')
            };

            try {
                const response = await fetch('../routes/auth_api.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Login successful! Redirecting...', 'success');
                    
                    // Redirect to admin dashboard
                    setTimeout(() => {
                        window.location.href = 'admin/dashboard.php';
                    }, 1000);
                } else {
                    showMessage(result.error || 'Login failed', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            }
        });
    </script>
</body>
</html>
