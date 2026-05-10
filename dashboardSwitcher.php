<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Workspace - FK-SCEMS</title>
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
            <div class="switcher-container">
                <h1>Select Workspace</h1>
                <p class="subtitle">You have multiple access levels. Choose one to continue.</p>

                <div class="cards-row">
                    <a href="studentDashboard.php" class="switcher-card">
                        <div class="card-image-content">
                            <img src="images/student.png" alt="Student Icon" class="card-icon-img">
                            <span class="card-label-text">Student</span>
                        </div>
                    </a>

                    <a href="adminDashboard.php" class="switcher-card">
                        <div class="card-image-content">
                            <img src="images/committee.png" alt="Committee Icon" class="card-icon-img">
                            <span class="card-label-text">Committee</span>
                        </div>
                    </a>
                </div>

                <a href="login.html" class="sign-in-btn">Back to Login</a>
            </div>
        </div>
    </div>

</body>

</html>