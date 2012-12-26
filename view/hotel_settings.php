<?php
    $hotel = $context['hotel'];
    $fotos = $context['fotos'];
    $foto_dir = $context['foto_dir'];
    $admin_page = $context['admin_page'];
        
    echo "Гостиница ".($hotel->name)."<br>";
    echo $hotel->country."(".$hotel->city.")";
    
    echo "<br> Описание <br>
        <form action='".$_SERVER['PHP_SELF'].$admin_page."' method='GET'>
            <input type='hidden' name='showhotel' value=''>
            <input type='hidden' name='hotel-id' value='".$hotel->id."'>
            <textarea class='' name='desc' value=''>".$hotel->desc."</textarea>
            <input type='submit' name='save_desc' value='Сохранить'>
        </form>";
    
    echo "Фото <br>
          <table>";

    foreach ($fotos as $foto) {
        echo "<tr>
                <td><img src='".$foto_dir.$foto->path."' width='320' height='240' alt=''></td>
                <td><a href='".$_SERVER['PHP_SELF'].$admin_page."?showhotel&hotel-id=".$hotel->id."&deletefoto&foto-id=".$foto->id."'>Удалить</a></td>
              </tr>";    
    }

    echo "</table><br><hr>";
    
    echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF'].$admin_page."?showhotel&hotel-id=".$hotel->id."' method='post'>
            <input type='hidden' name='hotelID' value='".$hotel->id."'>
            <input name='file' type='file'>
            <input type='submit' name='uploadfoto' value='Добавить Фото'>
          </form>";
          
    echo "<br><hr>";
    echo "<a href='".$_SERVER['PHP_SELF'].$admin_page."?showaparts&hotel-id=$hotel_id'>Просмотр апартаментов</a>";
    echo "<br><hr>";
    echo "<a href='".$_SERVER['PHP_SELF'].$admin_page."?showbron&hotel-id=$hotel_id'>Таблица бронирований</a>";
    echo "<br><hr>";
    echo "<a href='".$_SERVER['PHP_SELF'].$admin_page."?showseasons&hotel-id=$hotel_id'>Сезоны</a>";
    echo "<br><hr>";
    echo "<a href='".$_SERVER['PHP_SELF'].$admin_page."?showprices&hotel-id=$hotel_id'>Цены</a>";
    echo "<br><hr>";
?>