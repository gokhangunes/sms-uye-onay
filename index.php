<?php
/**
 * Created by PhpStorm.
 * User: zahidefe
 * Date: 07/08/16
 * Time: 14:56
 */

try {
    $db = new PDO("mysql:hostname=localhost; dbname=sms; charset=utf8;", "root", "");
}catch(PDOException $e) {
    $e->getMessage();
}

require "php-uk/textlocal.class.php";

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP SMS Ile Uye Dogrulama</title>
    <style>
        form {
            width:350px;
            margin:50px auto;
            border:1px solid #ddd;
            border-radius:8px;
            padding:20px;
            box-sizing:border-box;
        }

        form label {
            line-height:26px;
        }

        form label input {
            margin-bottom:10px;
            width:100%;
            padding:7px 5px;
            box-sizing:border-box;
        }

        form button {
            padding:10px 8px;
            border:1px solid #ddd;
            color:#333;
            text-decoration:none;
            width:100%;
            display:block;
            text-align:center;
            box-sizing:inherit;
            margin:10px auto;
        }

        form button:hover {
            background:#f1f1f1;
        }
    </style>
</head>
<body>

<?php
if(isset($_POST["register_form"])) {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Onay kodumuzu olusturalim.
    $code = substr(str_shuffle("1234567890"), 0, 6);

    $insertUser = $db->exec("INSERT INTO users SET
    u_name = '$name',
    u_password = '$password',
    u_email = '$email',
    u_confirm_code = $code,
    u_phone = '$phone'");

    if($insertUser) {
        $tl = new Textlocal("mailadresiniz@domaininiz.com", "TextLocal_HashKey");
        $sendSms = $tl->sendSms(["Telefon", "Numaraları", "Buraya"], "Onay Kodunuz: ".$code, "Uye Onay");
        if($sendSms->status == "success") {
            //SMS Gönderildi Demektir.
            header("Location:index.php?step=2&phone=".$phone);
        }else {
            echo "SMS Gönderilirken Bir Sorun Oluştu";
        }
    }else {
        echo "Kullanici olusturulamadi.";
    }
}

if(isset($_POST["confirmation_form"])) {
    $confCode = $_POST["confirmation"];
    $phone = "+".trim($_GET["phone"]);
    $query = $db->query("SELECT * FROM users WHERE u_confirm_code = $confCode && u_phone = '$phone'");
    if($query->rowCount()) {
        $update = $db->exec("UPDATE users SET u_status = '1' WHERE u_phone = '$phone'");
        if($update) {
            echo "Hesabınız Onaylandı";
        }else {
            echo "Hesabınız Onaylanamadı";
        }
    }else {
        echo "Onay Kodunuz veya Telefon Numaranız Yanlış";
    }
}
?>

<?php if(!isset($_GET["step"]) || $_GET["step"] == "1"): ?>
    <form action="" method="POST">
        <label>Name: <br><input type="text" name="name"></label><br>
        <label>Password: <br><input type="password" name="password"></label><br>
        <label>Email: <br><input type="email" name="email"></label><br>
        <label>Phone: <br><input type="tel" name="phone"></label><br>
        <button type="submit" name="register_form">Continue</button>
    </form>
<?php elseif(isset($_GET["step"]) && $_GET["step"] == "2" && $_GET["phone"]): ?>
    <form action="" method="POST">
        <label>Confirmation Code: <br><input type="text" name="confirmation"></label>
        <button type="submit" name="confirmation_form">Confirm Account</button>
    </form>
<?php endif; ?>
</body>
</html>
