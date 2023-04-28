<?php
        if (!in_array($_SERVER['SERVER_NAME'], array('192.168.2.150', 'localhost')) && (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '192.168.2.150', 'fe80::1', '::1')) || php_sapi_name() === 'cli-server'))) {
            $host = "http://3.20.3.85/petscare/api/";
        } else {
            $host = "http://192.168.2.150:8989/api/";
        }
 ?>
 
<html>
<head>
<style>
/* Bordered form */
form {
    border: 3px solid #f1f1f1;
}

/* Full-width inputs */
input[type=text], input[type=password] {
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

/* Set a style for all buttons */
button {
    background-color: #4c98af;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    cursor: pointer;
    width: 100%;
}

/* Add a hover effect for buttons */
button:hover {
    opacity: 0.8;
}

/* Add padding to containers */
.container {
    padding: 16px;
	width:50%;
	margin:auto;
}
span{
    color:red;
}
</style>
</head>
<body>
<form method="post" action="<?php if(isset($path)){echo $host.$path;}?>">
   <div class="container">
    <span><?php if(isset($message)){echo $message;}?></span>
    </br>
    </br>
    <?php if(!isset($message)) {?>
    <label><b>New Password</b></label>
    <input type="password" placeholder="Enter Password" name="new_password" required>

    <label><b>Confirm Password</b></label>
    <input type="password" placeholder="Enter Password agian" name="confirm_password" required>

    <button type="submit">Reset Password</button>
    <?php }?>
    </div>
</form>
</body>
</html>