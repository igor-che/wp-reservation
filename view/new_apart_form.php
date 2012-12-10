<?php

    $admin_page = $context['admin_page'];
    $hotel_id = $context['hotel_id'];

    echo "<form action='".$_SERVER['PHP_SELF'].$admin_page."' method='get'>
            <span>Название</span>
            <input type='text' class='adm_add_name' name='adm_add_name' value=''/><br>
            <span>Количество комнат</span>
            <select type='text' class='adm_add_rooms' name='adm_add_rooms' value=''>
                <option value='1'>1</option>
                <option value='2'>2</option>
                <option value='3'>3</option>
                <option value='4'>4</option>
            </select><br>
            <span>Количество мест</span>
            <select type='text' class='adm_add_places' name='adm_add_places' value=''>
                <option value='1'>1</option>
                <option value='2'>2</option>
                <option value='3'>3</option>
                <option value='4'>4</option>
            </select><br>
            <input type='hidden' name='adm_add_hotel_id' value='$hotel_id'>
            <input type='submit' class='adm_add_apart' name='adm_add_apart' value='Добавить'/>
        </form>";

?>