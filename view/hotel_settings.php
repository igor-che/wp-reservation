<?php
    $hotel = $context['hotel'];
    $fotos = $context['fotos'];
    $admin_page = $context['admin_page'];
        
    echo "Гостиница ".($hotel->name);
    echo "<br>";
    echo $hotel->country."(".$hotel->city.")";
    echo "<br> Описание <br>";
    echo "<textarea class='' name='' value=''>".$hotel->desc."</textarea>";
    echo "Фото <br>";
    echo "<table>";

    foreach ($fotos as $foto) {
        echo "<tr>
                <td><img src='http://otelnika.ru/upload/iblock/b4d/140.jpg' width='320' height='240' alt=''></td>
                <td><a href='".$_SERVER['PHP_SELF'].$admin_page."?deletefoto&fotoid=".$foto->id."'>Удалить</a></td>
              </tr>";    
    }

    echo "</table><br><hr>";
    
    echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF'].$admin_page."?showhotel&hotel-id=".$hotel->id."' method='post'>
            <input type='hidden' name='MAX_FILE_SIZE' value='30000'>
            <input type='hidden' name='hotel-id' value='".$hotel->id."'>
            <input name='file' type='file'>
            <input type='submit' name='uploadfoto' value='Добавить Фото'>
          </form>";
          
    echo "<br><hr>";
    echo "<a href='".$_SERVER['PHP_SELF'].$admin_page."?showaparts&hotel-id=$hotel_id'>Просмотр апартаментов</a>";
    echo "<br><hr>";
    echo "<a href='".$_SERVER['PHP_SELF'].$admin_page."?showbron&hotel-id=$hotel_id'>Таблица бронирований</a>";

?>