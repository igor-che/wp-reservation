<?php
    echo "sdfsdf";
    $admin_page = $context['admin_page'];
    echo "
        <h2>Добавление сезона</h2>
        <form method='GET' action='".$_SERVER['PHP_SELF'].$admin_page."' name='addNewDiap'>
            <input type='hidden' name='page' value='bron_diap'/>
            <div>
                <input type='text' name='start' value=''/>
                <input type='text' name='finish' value=''/>
                <input type='submit' name='btnAddApart' value='Добавить'/>
            </div>
        </form>
    ";

?>