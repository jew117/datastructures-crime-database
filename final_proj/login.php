<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';

$error_message = '';

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    $username = $_POST['full_name_signup'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $action_type = $_POST['action'] ?? '';

    if ($action_type === 'signup') {
        if (!empty($username) && !empty($email) && !empty($password)) {
            
            if (register_user($mysqli, $username, $email, $password)) {
                $error_message = "Registration successful! You can now sign in.";
            } else {
              
                $error_message = "User already exists. Please Sign in.";
            }
        } else {
            $error_message = "All fields are required for sign up.";
        }
    } else {

        if (!empty($email) && !empty($password)) {

            $user_data = authenticate_user($mysqli, $email, $password);

            if ($user_data) {
    
                $_SESSION['user_id'] = $user_data['user_id'];
                
                $_SESSION['username'] = $user_data['username']; 
                
                $_SESSION['is_admin'] = $user_data['is_admin']; 
                
                header("Location: index.php");
                die;
            } else {
                $error_message = "Wrong email or password!";
            }
        } else {
            $error_message = "Email and password are required for sign in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head> 
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Oswald:wght@200..700&family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<script src="https://kit.fontawesome.com/your-fontawesome-kit-id.js" crossorigin="anonymous"></script> 
<link rel="stylesheet" href="login-style.css">

<title>Crime Database</title>
</head>

<body> 
    <div class="login-page"> 
        <main> 
            <div class="form-box">
                <h1 id="title">Sign In</h1> 
                
                <?php if (!empty($error_message)): ?>
                    <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm"> 
                    
                    <input type="hidden" name="action" id="actionField" value="signin"> 

                    <div class="input-group">
                        <div class="input-field" id="nameField"> 
                            <i class="fa-solid fa-user"></i>
                            <input type="text" placeholder="Name" name="full_name_signup"> 
                        </div>

                        <div class="input-field"> 
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" placeholder="Email" name="email" required> 
                        </div>

                        <div class="input-field"> 
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="Password" name="password" required> 
                        </div>
                        <p>Lost Password?<a href="#">Well, that sucks.</a> </p>
                    </div>
                    
                    <div class="btn-field">
                        <button type="submit" id="signupBtn" class="disable">Sign up</button> 
                        <button type="submit" id="signinBtn">Sign in</button> 
                    </div>
                </form>
            </div>
        </main>
    </div> <script> 
        let signupBtn = document.getElementById("signupBtn");
        let signinBtn = document.getElementById("signinBtn");
        let nameField = document.getElementById("nameField");
        let title = document.getElementById("title");
        let form = document.getElementById("loginForm");
        let actionField = document.getElementById("actionField");

    
        signinBtn.addEventListener('click', function(e){
            actionField.value = "signin";
            nameField.style.maxHeight = "0";
            title.innerHTML = "Sign In"; 
            signupBtn.classList.add("disable");
            signinBtn.classList.remove("disable");
        });

     
        signupBtn.addEventListener('click', function(e){
            actionField.value = "signup";
            nameField.style.maxHeight = "60px";
            title.innerHTML = "Sign Up"; 
            signupBtn.classList.remove("disable");
            signinBtn.classList.add("disable");
        });


        function initializeView() {
       
            const hasError = "<?php echo !empty($error_message); ?>"; 
            

            if (hasError && actionField.value === 'signup') {
                 nameField.style.maxHeight = "60px";
                 title.innerHTML = "Sign Up";
                 signupBtn.classList.remove("disable");
                 signinBtn.classList.add("disable");
                 actionField.value = "signup";
            } else {
                 
                 nameField.style.maxHeight = "0";
                 title.innerHTML = "Sign In";
                 signupBtn.classList.add("disable");
                 signinBtn.classList.remove("disable");
                 actionField.value = "signin";
            }
        }

        initializeView();
    </script>
</body>
</html>