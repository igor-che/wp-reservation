<?php
    $prices = $context['price'];
    $aparts = $context['aparts'];
    $diaps = $context['diaps'];
    $admin_page = $context['admin_page'];
    var_dump($prices);
    echo "<h2>Цены за апартаменты (доп. место)</h2>
            <form name='' action='".$_SERVER['PHP_SELF'].$admin_page."' method='get'>
            <table class='tblPrices'>
                <tr><td></td>";
            
    foreach($diaps as $diap){
        echo "<td>".date('d-m-Y', strtotime($diap->date_start))." <br> ".(date('d-m-Y', strtotime($diap->date_finish)))."</td>";
    }
    echo "</tr>";
    foreach($aparts as $apart){
        echo "<tr><td>".$apart->name."</td>";
        foreach($diaps as $diap) {
            foreach($prices as $price){
                if($price->numbersID == $apart->id && $price->diapasonesID == $diap->id){
                    echo "<td>
                        <input id='' class='' name='price_".$price->id."' value='".$price->price."' style='width:30px'/>
                        <input id='' class='' name='add_price_".$price->id."' value='".$price->additional_place_price."' style='width:30px'/>
                        </td>";
                }
            }
        }
        echo "</tr>";
    }
    echo "</table>
        <input type='submit' name='btnSavePrices' value='Сохранить изменения'>
        </form>"; 

?>