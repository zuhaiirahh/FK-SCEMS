<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FK-SCEMS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div class="left-panel">
            <div class="info-content">
                <img src="images/umpsaLogo.png" alt="UMPSA Logo" class="logo">
                <h2>FK-SCEMS</h2>
                <p class="system-name">FK Student Club & Event Management System</p>
                <p class="university">Universiti Malaysia Pahang Al-Sultan Abdullah</p>
                <p class="location">Pekan | Gambang</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="login-box">
                <h1>Welcome Back</h1>
                <p class="subtitle">Please enter your credentials to access the portal</p>

                <form action="login_process.php" method="POST">
                    <div class="input-group">
                        <label>User ID</label>
                        <input type="text" name="userid" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="sign-in-btn">Sign In</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>