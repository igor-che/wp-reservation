<?php
    $admin_page = $context['admin_page'];
    
    echo "Авторизация <br>
          <form action='".$_SERVER['PHP_SELF'].$admin_page."' method='POST'>
            Логин<br>
            <input type='text' class='' name='login' value=''/><br>
            Пароль<br>
            <input type='password' class='' name='pass' value=''/><br>
            <input type='submit' class='' name='authorization' value='Авторизация'>
          </form><hr>";
?>