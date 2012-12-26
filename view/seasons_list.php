<?php

    $seasons = $context['seasons'];
    $admin_page = $context['admin_page'];
    
    echo "<h2>Сезоны</h2>";
    foreach ($seasons as $season) {
        echo "<form method='GET' action='".$_SERVER['PHP_SELF'].$admin_page."'>
            <div>
                <input type='text' size='3' readonly='readonly' name='diap_id' value='".$season->id."'/>
                <input type='text' size='10' name='start' value='".date('d-m-Y', strtotime($season->date_start))."'/>
                <input type='text' size='10' name='finish' value='".date('d-m-Y', strtotime($season->date_finish))."'/>
                <input type='submit' name='btnSaveDiap' value='Сохранить'/>
                <input type='submit' name='btnDelDiap' value='Удалить'/>
            </div></form>";
    }
?>