<?php
require '../koneksi.php';

if(isset($_POST['verify'])){
    $username = $_POST['username'];
    $email = $_POST['email'];

    if(empty($username)||empty($email)){
        die("Harap isi username dan email");
    }

    $query = "SELECT user_id FROM users WHERE username = ? AND email = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        session_start();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $_SESSION['reset_password'] = $user_id;
        header("location: reset_password.php");
    }else{
        $error = "Username atau email salah.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Account Verification</title>
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
        color: rgba(41, 43, 187,0.7);
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

        .button {
            display: flex;
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
            <h3 class="text-center mb-4">Account Verification</h3>
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
                        name="username"
                        type="text"
                        placeholder="Type here"
                        autocomplete="off"
                        required
                    />
                    <label class="input-text-label" for="text">Username</label>
                    </div>

                </div>
                <br>
                <div class="mb-3">
                <div class="input-group">
                    <input
                        class="input-text"
                        name="email"
                        type="email"
                        placeholder="Type here"
                        autocomplete="off"
                    />
                    <label class="input-text-label">Email</label>
                    </div>
                </div>
                <div class="button">
                        <button class="bt w-50" type="button" name="loginbtn" onclick="back()">Cancel</button>
                        <button class="bt w-50" type="submit" name="verify">verify</button><br>
                </div>

            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function back(){
            window.location = "login.php";
        }
    </script>
</body>
</html>