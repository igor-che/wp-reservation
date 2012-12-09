<?php
    global $context;
    $aparts = $context['aparts'];
    
    echo "Апартаменты<br>
        
          <table>
          <tr>
            <td>ID</td>
            <td>Название</td>
            <td>Количество комнат</td>
            <td>Количество мест</td>
            <td></td>
            <td></td>
          </tr>";
          
    foreach($aparts as $apart) {
        echo "<tr>
              <td>$apart->id</td>
              <td>$apart->name</td>
              <td>$apart->rooms</td>
              <td>$apart->places</td>
              <td><a href='".$_SERVER['PHP_SELF']."'>Править</a></td>
              <td><a href='".$_SERVER['PHP_SELF']."'>Удалить</a></td>
              </tr>";
    }
    
    echo "</table>";
?>