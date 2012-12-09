<?php
    global $context;
    global $locale;
    
    $hotels = $context['hotels'];
    $admin_page = $context['admin_page'];
    
    echo "РЎРѕР·РґР°РЅРёРµ РѕС‚РµР»СЏ.
        <form action='".$_SERVER['PHP_SELF'].$admin_page."?createhotel&hotel-id=$hotel->id' method='get'>
            <span>Название</span>
            <input type='text' class='adm_add_name' name='adm_add_name' value=''/><br>
            
            <span>Страна</span>
            <input type='text' class='adm_add_country' name='adm_add_country' value=''/><br>
            
            <span>Город</span>
            <input type='text' class='adm_add_city' name='adm_add_city' value=''/><br>
            
            <input type='hidden' name='adm_add_acc_id' value='$current_user->id'>
            <input type='submit' class='adm_add_hotel' name='adm_add_hotel' value='Добавить отель'/>
        </form>";
?>