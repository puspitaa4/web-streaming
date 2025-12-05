<?php
require '../koneksi.php';

if (isset($_POST['loginbtn'])) {
    // Ambil data dari form
    $usernameOrEmail = htmlspecialchars($_POST['username_or_email']);
    $password = htmlspecialchars($_POST['password']);

    // Query untuk mengambil data user
    $query = "SELECT user_id, username, email, password, role FROM users WHERE username = ? OR email = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah user ditemukan
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {

            $update_status_sql = "UPDATE users SET status = 'active' WHERE user_id = ?";
            $update_stmt = $koneksi->prepare($update_status_sql);
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();

            $activity_sql = "INSERT INTO activity_logs (user_id, activity_type) VALUES (?, 'Melakukan login.')";
            $activity_stmt = $koneksi->prepare($activity_sql);
            $activity_stmt->bind_param("i", $user['user_id']);
            $activity_stmt->execute();

            session_start();
            // Simpan informasi user ke session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];


            $user_id = $user['user_id'];
            $current_date = date("Y-m-d");
            $querySub = "SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active'";
            $stmtsub = $koneksi->prepare($querySub);
            $stmtsub->bind_param("i", $user_id);
            $stmtsub->execute();
            $resultsub = $stmtsub->get_result();

            if ($resultsub->num_rows > 0) {
                $subscription = $resultsub->fetch_Assoc();
                $subscription_id = $subscription['subscription_id'];
                $end_date = $subscription['end_date'];

                if ($current_date > $end_date) {
                    $updatequery = "UPDATE subscriptions SET status = 'expired' WHERE subscription_id = ?";
                    $updatestmt = $koneksi->prepare($updatequery);
                    $updatestmt->bind_param(i, $subscription_id);
                    $updatestmt->execute();
                }
            }

            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header("Location: ../home.php"); // Interface admin
            } elseif ($user['role'] === 'user') {
                header("Location:../user/1_HalamanBeranda.php"); // Interface user
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username atau email tidak ditemukan!";
    }

    $stmt->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login</title>
    <style>
        .main {
            height: 100vh;
            background-color: #1d1d1d;
        }
        .login-box {
            width: 400px;
            color: white;
            background-color: #2c2c2c;
            border-radius: 15px;
            transition: all 0.6s;
        }

        .login-box:hover {
            box-shadow: 0px 0px 10px 10px rgba(41, 43, 187,0.2);
        }
 
        .input-group {
        display: flex;
        gap: 10px;
        position: relative;
        padding: 7px 0 0;
        width: 100%;
        margin-bottom: 5px;
        margin-top: 2px;
        }

        .input-text {
        font-family: inherit;
        width: 100%;
        border: none;
        border-bottom: 2px solid #7f7f7f;
        border-radius: 0 !important;
        outline: 0;
        font-size: 17px;
        color: #7f7f7f;
        padding: 7px 0;
        background: transparent;
        transition: border-color 0.5s ease;
        }

        .input-text::placeholder {
        color: transparent;
        }

        .input-text:placeholder-shown ~ .input-text-label {
        font-size: 17px;
        cursor: text;
        }

        .input-text-label {
        position: absolute;
        display: block;
        top: 0;
        left: 0;
        transition: all 0.5s ease;
        font-size: 17px;
        color: #7f7f7f;
        pointer-events: none;
        }

        .input-text:focus {
        padding-bottom: 6px;
        border-width: 3px;
        border-image: linear-gradient(to right, rgba(41, 43, 187,0.1), rgba(41, 43, 187,0.7));;
        border-image-slice: 1;
        }

        .input-text:focus ~ .input-text-label {
        color: rgba(41, 43, 187,1);
        }

        .input-text:not(:placeholder-shown) ~ .input-text-label,
        .input-text:focus ~ .input-text-label {
        position: absolute;
        display: block;
        transition: all 0.5s ease;
        font-size: 15px;
        top: -15px;
        }

        .input-text:required,
        .input-text:invalid {
        box-shadow: none;
        }

        .check {
            display: flex;
        }

        .a {
            font-size: 10px;
            margin-left: 3px;
            cursor: pointer;
        }

        .switch {
        font-size: 11px;
        position: relative;
        display: inline-block;
        width: 2.8em;
        height: 1.5em;
        }

        /* Hide default HTML checkbox */
        .switch input {
        opacity: 0;
        width: 0;
        height: 0;
        }

        /* The slider */
        .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        border: 2px solid #414141;
        border-radius: 50px;
        transition: all 0.4s cubic-bezier(0.23, 0.850, 0.3, 1.275);
        }

        .slider:before {
        position: absolute;
        content: "";
        height: 0.8em;
        width: 0.8em;
        left: 0.2em;
        bottom: 0.2em;
        background-color: white;
        border-radius: inherit;
        transition: all 0.4s cubic-bezier(0.23, 0.850, 0.3, 1.275);
        }

        .switch input:checked + .slider {
        box-shadow: 0 0 20px rgba(41, 43, 187,0.3);
        border: 2px solid rgba(41, 43, 187,0.6);
        }

        .switch input:checked + .slider:before {
        transform: translateX(1.2em);
        }

        .button {
            display: flex;
        }
        .btnn {
            margin: 5px;
            width: 345px;
        }

        button {
        font-size: 15px;
        margin: 5px;
        padding: 0.7em 2.7em;
        letter-spacing: 0.06em;
        position: relative;
        font-family: inherit;
        border-radius: 0.6em;
        overflow: hidden;
        transition: all 0.6s;
        line-height: 1.4em;
        background: #2c2c2c;
        color:#ccc;
        
        }

        button:hover {
        --blue: rgb(41, 43, 150);
        box-shadow: inset 0 0 10px rgba(41, 43, 187,0.2), 0 0 9px 3px rgba(41, 43, 187,0.6);
        background: linear-gradient(to right, rgba(41, 43, 187,0.1) 1%, transparent 40%,transparent 60% , rgba(41, 43, 187,0.1) 100%);
        color: var(--blue);
        }

        button:before {
        content: "";
        position: absolute;
        left: -4em;
        width: 4em;
        height: 100%;
        top: 0;
        transition: transform .5s ease-in-out;
        background: linear-gradient(to right, transparent 1%, rgba(41, 43, 187,0.1) 40%,rgba(41, 43, 187,0.1) 60% , transparent 100%);
        }

        button:hover:before {
        transform: translateX(25em);
        }
    </style>
</head>
<body>
    <div class="main d-flex flex-column justify-content-center align-items-center">
        <div class="login-box p-4">
            <h3 class="text-center mb-4">Login</h3>
            <br>
            <?php
            if (isset($error)) {
                echo "<div class='alert alert-danger mt-3'>$error</div><br>";
            }
            ?>
            <form action="" method="post">
                <div class="mb-3">
                    <div class="input-group">
                    <input
                        class="input-text"
                        name="username_or_email"
                        type="text"
                        placeholder="Type here"
                        autocomplete="off"
                        required
                    />
                    <label class="input-text-label" for="text">Username or Email</label>
                    </div>

                </div>
                <br>
                <div class="mb-3">
                <div class="input-group">
                    <input
                        class="input-text"
                        name="password"
                        type="password"
                        id="password"
                        placeholder="Type here"
                        autocomplete="off"
                    />
                    <label class="input-text-label" for="text">Password</label>
                    </div>
                </div>
                <div class="check mb-3">
                    <label class="switch">
                    <input type="checkbox" id="togglePassword">
                    <span class="slider"></span>
                    </label>
                    <label class="a" for="togglePassword">show password?</label>
                </div>
                <div class="button">
                    <button class="bt w-50" type="submit" name="loginbtn">Login</button>
                    <button class="bt w-50" type="button" name="Register" onclick="register()">Register</button><br>
                </div>
                <button class="btnn" type="button" name="forgot" onclick="forgota()">Forgot Password</button>

            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("change", function () {
            const passwordField = document.getElementById("password");
    // Switch between "text" and "password" types
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });

        function register(){
            window.location = "register.php";
        };
        function forgota(){
            window.location = "forgot_password.php";
        };
    </script>
</body>
</html>
