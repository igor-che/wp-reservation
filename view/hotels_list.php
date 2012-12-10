<?php
    $hotels = $context['hotels'];
    $admin_page = $context['admin_page'];

    echo "<table>";
    foreach($hotels as $hotel) {
        echo "<tr>
            <td>$hotel->name</td>
            <td><a href='".$_SERVER['PHP_SELF'].$admin_page."?showhotel&hotel-id=".$hotel->id."'>".$locale['edit']."</a></td>
            <td><a href='".$_SERVER['PHP_SELF'].$admin_page."?delhotel&hotel-id=".$hotel->id."'>".$locale['delete']."</a></td>
            </tr>";
    }
    echo "</table>";
?>