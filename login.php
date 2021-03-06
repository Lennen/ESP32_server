﻿<?
// Страница авторизации

include("_dbconnect.php");
include("_localization.php");

// To generate random String
function generateCode($length=6) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
    $code = "";
    $clen = strlen($chars) - 1;
    while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,$clen)];
    }
    return $code;
}

if(isset($_POST['submit']))
{
    // Вытаскиваем из БД запись, у которой логин равняеться введенному
    $query = mysqli_query($link,"SELECT user_id, user_password FROM users WHERE user_login='".mysqli_real_escape_string($link,$_POST['login'])."' LIMIT 1");
    $data = mysqli_fetch_assoc($query);

    // Сравниваем пароли
    if($data['user_password'] === md5(md5($_POST['password'])))
    {
        // Генерируем случайное число и шифруем его
        $hash = md5(generateCode(10));

        if(!empty($_POST['not_attach_ip']))
        {
            // Если пользователя выбрал привязку к IP
            // Переводим IP в строку
            $insip = ", user_ip=INET_ATON('".$_SERVER['REMOTE_ADDR']."')";
        }

        // Записываем в БД новый хеш авторизации и IP
        mysqli_query($link, "UPDATE users SET user_hash='".$hash."' ".$insip." WHERE user_id='".$data['user_id']."'");

        // Ставим куки
        setcookie("id", $data['user_id'], time()+60*60*24*30);
        setcookie("hash", $hash, time()+60*60*24*30,null,null,null,true); // httponly !!!

        // Переадресовываем браузер на страницу проверки нашего скрипта
        header("Location: ./check.php"); exit();
    }
    else
    {
        print "Вы ввели неправильный логин/пароль";
    }
}
?>
<html>
    <body>
        <link rel="stylesheet" href="./styles/auth.css">
        <H1 width = 300px><?=$intro[$cl]?></H1>
        <form method="POST" background-color="lightblue">
            <ul class="form-style-1">
                <center><table>
                    <tr><td><?=$login[$cl]?></td><td><input name="login" type="text" required></td></tr>
                    <td><?=$pass[$cl]?></td><td><input name="password" type="password" required></td></tr>
                </table></center>
                <?=$ipcheck[$cl]?><input type="checkbox" name="not_attach_ip" checked="true"><br>
                <center>
                    <input name="submit" type="submit" value="<?=$log_in[$cl]?>">
                    <input name="submit" type="submit" value="<?=$no_login[$cl]?>" onclick="document.location='no_login.php?val=50&login=kra'">
                    <input name="submit" type="submit" value="<?=$register[$cl]?>" onclick="document.location='register.php'">
                </center>
            </ul>
        </form>
    </body>
</html>
