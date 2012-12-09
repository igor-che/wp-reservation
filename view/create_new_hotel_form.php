<?php

    global $context;
    $acc_id = $context['acc_id'];
    $admin_page = $context['admin_page'];

    echo "<form action='".$_SERVER['PHP_SELF'].$admin_page."' method='get'>
            <span>Название</span>
            <input type='text' class='adm_add_name' name='adm_add_name' value=''/><br>
            <span>Страна</span>
            <input type='text' class='adm_add_country' name='adm_add_country' value=''/><br>
            <span>Город</span>
            <input type='text' class='adm_add_city' name='adm_add_city' value=''/><br>
            <input type='hidden' name='adm_add_acc_id' value='$acc_id'>
            <input type='submit' class='createhotel' name='createhotel' value='Добавить отель'/>
        </form>";

?>