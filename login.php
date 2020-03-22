<?
// Страница авторизации

include("_dbconnect.php");

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
        header("Location: check.php"); exit();
    }
    else
    {
        print "Вы ввели неправильный логин/пароль";
    }
}
?>
<html>
    <meta http-equiv="Content-Type" content="text/html; charset=cp1251" />
    <body>
        <link rel="stylesheet" href="./styles/auth.css">
        <H1>Вход в смарт-систему</H1>
        <form method="POST" background-color="lightblue">
            <ul class="form-style-1">
                <center><table>
                    <tr><td>Логин</td><td><input name="login" type="text" required></td></tr>
                    <td>Пароль</td><td><input name="password" type="password" required></td></tr>
                </table></center>
                Не прикреплять к IP(не безопасно) <input type="checkbox" name="not_attach_ip" checked="true"><br>
                <center><input name="submit" type="submit" value="Войти"></center>
            </ul>
        </form>
    </body>
</html>
