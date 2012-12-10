<?php
    $admin_page = $context['admin_page'];
    
    echo "Регистрация <br>
          <form action='".$_SERVER['PHP_SELF'].$admin_page."' method='POST'>
            Логин<br>
            <input type='text' class='' name='login' value=''/><br>
            Пароль<br>
            <input type='password' class='' name='pass' value=''/><br>
            Повторите пароль<br>
            <input type='password' class='' name='pass2' value=''/><br>
            <input type='submit' class='' name='registration' value='Регистрация'>
          </form><hr>";
?>