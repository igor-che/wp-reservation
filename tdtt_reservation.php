<?php
/**
 * @package Bron
 * @version 1.0
 */
 
/*
Plugin Name: Reservation (Бронирование)
Plugin URI: 
Description: Bron
Armstrong: My Plugin.
Author: Igor Chernyaev
Version 1.0
Author URI: 
*/

/**
    Статус заказа может быть = (заказан, оплачен, проживают, устарел)
*/

/*
    TODO
    Нужны шорткоды
        Для посетителей
            Просмотр информации про отель [user_hotel_info]
            Просмотр информауии про номер [user_apart_info]
            Оформление заказа номера      [user_ordering]
*/
$hotel_page = '/отель/';
$apart_page = '/номер/';
$admin_page = '/админка/';
$filter_page = '/фильтр/';
$foto_dir = '/fotos/';

function subject($type, $FIO, $phone, $date, $date_start, $date_finish, $order_id, $price, $oneday) {
    if($type = 'inos') {
        $subject = get_option('maintext').get_option('inostext');
    } else {
        $subject = get_option('maintext').get_option('ukrtext');
    } 
    //ЗАМЕНЫ %order_id% на $order_id
    $subject = str_replace("%order_id%", $order_id, $subject);
    $subject = str_replace("%FIO%", $FIO, $subject); 
    $subject = str_replace("%phone%", $phone, $subject);
    $subject = str_replace("%date%", $date, $subject);
    $subject = str_replace("%date_start%", $date_start, $subject);
    $subject = str_replace("%date_finish%", $date_finish, $subject);
    $subject = str_replace("%price%", $price, $subject);
    $subject = str_replace("%predoplata%", $oneday, $subject);
    $subject = str_replace("%oplata%", $price - $oneday, $subject);
    return $subject;
}


function bron_admin_init(){
    add_options_page('Бронирование номеров', 'Общие настройки(Брон.)', 8, 'bron_opt', 'bron_options_page');
    add_options_page('Бронирование номеров', 'Диапазоны(Брон.)', 8, 'bron_diap', 'bron_diap_page');
    add_options_page('Бронирование номеров', 'Аппартаменты(Брон.)', 8, 'bron_apart', 'bron_apart_page');
    add_options_page('Бронирование номеров', 'Заказы(Брон.)', 8, 'bron_orders', 'bron_order_page');
}

function bron_options_page(){
    //нужна форма правки mail гостиницы
    //правка спец функций типа заезда на N + половина дня за определенную сумму
    echo "<h3>Текст письма со счетом</h3>";

    if(isset($_POST['maintext'])){ 
        update_option( 'maintext', str_replace("\\", "", $_POST['maintext']) );
    }

    echo "<h3>Общая часть</h3>";
    echo "<form method='POST' action='".$_SERVER['PHP_SELF']."?page=bron_opt'>
            <textarea name='maintext' cols=20 rows=35>".get_option('maintext')."</textarea>
            <input type='submit' name='main' value='Сохранить'/>
        </form>";

    if(isset($_POST['inostext'])){ 
        update_option( 'inostext', str_replace("\\", "", $_POST['inostext']) );
    }

    echo "<h3>Реквизиты для иностранцев</h3>";
    echo "<form method='POST' action='".$_SERVER['PHP_SELF']."?page=bron_opt'>
            <textarea name='inostext' cols=20 rows=35>".get_option('inostext')."</textarea>
            <input type='submit' name='inos' value='Сохранить'/>
        </form>";

    if(isset($_POST['ukrtext'])){ 
        update_option( 'ukrtext', str_replace("\\", "", $_POST['ukrtext']) );
    }

    echo "<h3>Реквизиты для Украины</h3>";
    echo "<form method='POST' action='".$_SERVER['PHP_SELF']."?page=bron_opt'>
            <textarea name='ukrtext' cols=20 rows=35>".get_option('ukrtext')."</textarea>
            <input type='submit' name='ukr' value='Сохранить'/>
        </form>";
}

function bron_diap_page(){
    echo "<h2>Работа с диапазонами</h2>";
    
    global $wpdb;
    
    if(isset($_GET['btnAddNewDiap'])) {
        echo "<h4>Диапазон добавлен</h4>";
        $start = $_GET['start'];
        $finish = $_GET['finish'];
        $wpdb->insert(
                $wpdb->prefix.'diapasones', 
                array('date_start' => $start, 'date_finish' => $finish),
                array('%s', '%d', '%d', '%d')
               );
    }
    
    if(isset($_GET['btnDelDiap'])) {
        echo "<h4>Аппартаменты удалены</h4>";
        $id = $_GET['diap_id'];
        $wpdb->query("DELETE FROM ".$wpdb->prefix.'table_diapasones'." WHERE id = $id");
    }

    if(isset($_GET['btnSaveDiap'])) {
        echo "<h4>Изменения сохранены</h4>";
        $id = $_GET['diap_id'];
        $start = $_GET['start'];
        $finish = $_GET['finish'];
        $wpdb->update(
                    $wpdb->prefix.'table_diapasones', 
                    array('date_start' => $start, 'date_finish' => $finish),
                    array('id' => $id)
                    ); 
    }
    
    echo "
        <h2>Добавление нового диапазона цен</h2>
        <form method='GET' action='".$_SERVER['PHP_SELF']."' name='addNewDiap'>
            <input type='hidden' name='page' value='bron_diap'/>
            <div>
                <input type='text' name='start' value='$diap->date_start'/>
                <input type='text' name='finish' value='$diap->date_finish'/>
                <input type='submit' name='btnAddApart' value='Добавить'/>
            </div>
        </form>
    ";
    
    if(isset($_GET['btnSavePrices'])) {
        echo "<h3>Цены сохранены</h3>";
        $prices = $wpdb->get_results('SELECT id FROM '.$wpdb->prefix.'numbers2diapasones');
        foreach($prices as $price){
            if(isset($_GET['price_'.$price->id])){
                $wpdb->update(
                    $wpdb->prefix.'numbers2diapasones',
                    array('price' => $_GET['price_'.$price->id], 'additional_place_price' => $_GET['add_price_'.$price->id]),
                    array('id' => $price->id)
                );
            }
        }
    }
    
    echo "<h2>Существующие диапазоны</h2>";
    $diaps = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'table_diapasones');
    foreach ($diaps as $diap){
        echo "<form method='GET' action='".$_SERVER['PHP_SELF']."' name='diapList'>
            <input type='hidden' name='page' value='bron_diap'/>
            <div>
                <input type='text' readonly='readonly' name='diap_id' value='$diap->id' style='width:40px'/>
                <input type='text' name='start' value='".date('d-m-Y', strtotime($diap->date_start))."'/>
                <input type='text' name='finish' value='".date('d-m-Y', strtotime($diap->date_finish))."'/>
                <input type='submit' name='btnSaveDiap' value='Сохранить'/>
                <input type='submit' name='btnDelDiap' value='Удалить'/>
            </div></form>";
    }

    $aparts = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'table_numbers');
    $price = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'table_numbers2diapasones');

    echo "<h2>Цены за апартаменты (доп. место)</h2>";
    echo "<form name='' action='".$_SERVER['PHP_SELF']."' method='get'>";
    echo "<input type='hidden' name='page' value='bron_diap'/>";
    echo "<table class='tblPrices'>";
    echo "<tr><td></td>";
    foreach($diaps as $diap){
        echo "<td>".(date('d-m-Y', strtotime($diap->date_start)))." <br> ".(date('d-m-Y', strtotime($diap->date_finish)))."</td>";
    }
    echo "</tr>";
    foreach($aparts as $apart){
        echo "<tr><td>".$apart->name."</td>";
        foreach($diaps as $diap){
            for($c=0;$c<count($price);$c++){
                if($price[$c]->numbersID == $apart->id && $price[$c]->diapasonesID == $diap->id){
                    echo "<td>
                        <input id='' class='' name='price_".$price[$c]->id."' value='".$price[$c]->price."' style='width:30px'/>
                        <input id='' class='' name='add_price_".$price[$c]->id."' value='".$price[$c]->additional_place_price."' style='width:30px'/>
                        </td>";
                }
            }
        }
        echo "</tr>";
    }
    echo "</table>
        <input type='submit' name='btnSavePrices' value='Сохранить изменения'>
        </form>";
}

function bron_apart_page(){
    echo "<h2>Управление данными об аппартаментах</h2>";

    global $wpdb;
    
    if(isset($_GET['btnAddNewApart'])){
        echo "<h4>Аппартаменты добавлены</h4>";
        $name = $_GET['apart_name'];
        $rooms = $_GET['apart_rooms'];
        $places = $_GET['apart_places'];
        $add_place = (int)isset($_GET['apart_add_place']);
        $wpdb->insert(
                $wpdb->prefix.'numbers', 
                array('name' => $name, 'rooms' => $rooms, 'places' => $places, 'additional_places' => $add_place),
                array('%s', '%d', '%d', '%d')
               );
    }
    
    if(isset($_GET['btnDelApart'])){
        echo "<h4>Аппартаменты удалены</h4>";
        $id = $_GET['apart_id'];
        $sql = "DELETE FROM ".$wpdb->prefix.'table_numbers'." WHERE id = $id"; 
        $wpdb->query($sql);
    }

    if(isset($_GET['btnSaveApart'])){
        echo "<h4>Изменения сохранены</h4>";
        $id = $_GET['apart_id'];
        $name = $_GET['apart_name'];
        $rooms = $_GET['apart_rooms'];
        $places = $_GET['apart_places'];
        $add_place = (int)isset($_GET['apart_add_place']);	
        $wpdb->update(
                    $wpdb->prefix.'numbers', 
                    array('name' => $name, 'rooms' => $rooms, 'places' => $places, 'additional_places' => $add_place),
                    array('id' => $id),
                    array('%s', '%d', '%d', '%d'),
                    array('%d')
                    );
    }
    
    /*
        отрисовываем форму
    */
    echo "<h3>Добавление аппартаментов</h3>";
    echo "
    <form name='frmAddNewApart' method='GET' action='".$_SERVER['PHP_SELF']."'>
        <table>
        <tr>
            <td><i>Название</i></td>
            <td><input type='text' name='apart_name' value=''/></td>
            <td><i></i></td>
        </tr>
        <tr>
            <td><i>Количество комнат</i></td>
            <td>
                <select name='apart_rooms'>
                    <option value='1' selected='selected'>1</option>
                    <option value='2'>2</option>
                    <option value='3'>3</option>
                    <option value='4'>4</option>
                </select>
            </td>
            <td><i></i></td>
        </tr>
        <tr>
            <td><i>Количество мест</i></td><td>
                <select name='apart_places'>
                    <option value='1' selected='selected'>1</option>
                    <option value='2'>2</option>
                    <option value='3'>3</option>
                    <option value='4'>4</option>
                </select>
            </td><td><i></i></td>
        </tr>
        <tr>
            <td><i>Дополнительное место</i></td>
            <td><input type='checkbox' name='apart_add_place' value=''/></td>
            <td><i></i></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <input type='hidden' name='page' value='bron_apart'/>
                <input type='submit' name='btnAddNewApart' value='Добавить'/>
            </td>
            <td>&nbsp;</td>
        </tr>
        </table>
    </form>";
    
    echo "<br>";
    echo "<h3>Список доступных аппартаментов</h3>";
    
    $sql = "SELECT * FROM ".$wpdb->prefix.'numbers';

    $aparts = $wpdb->get_results($sql);

    foreach($aparts as $apart){
        echo "<form method='GET' action='".$_SERVER['PHP_SELF']."' name='apartList'>
            <input type='hidden' name='page' value='bron_apart'/>
            <div>
                <input type='text' readonly='readonly' name='apart_id' value='$apart->id' style='width:40px'/>
                <input type='text' name='apart_name' value='$apart->name'/>
                <i>Кол-во комнат - </i>
                <select name='apart_rooms' value='$apart->rooms'>";
                for($c=1; $c<6; $c++) {
                    echo "<option value='".$c."' ".($c==$apart->rooms?'selected=selected':'').">".$c."</option>";
                }
          echo "</select>
                <i>Кол-во мест - </i>
                <select name='apart_places' value='$apart->places'>";
                for($c=1; $c<6; $c++) {
                    echo "<option value='".$c."' ".($c==$apart->places?'selected=selected':'').">".$c."</option>";
                }
          echo "</select>
                <i>Доп. место</i>
                <input type='checkbox' name='apart_add_place' value='' ".($apart->additional_places == 1?'checked=checked':'').">
                <input type='submit' name='btnSaveApart' value='Сохранить'/>
                <input type='submit' name='btnDelApart' value='Удалить'/>
            </div></form>";
    }
}

function bron_order_page(){
    echo "<h2>Обработка заказов</h2>";
    global $wpdb;
    
    if(isset($_GET['delOrder'])){
        echo "<h3>Заказ удален. Клиенту отправлено уведомление</h3>";
        $tbl_or = $wpdb->prefix.'orders';
        $tbl_busy = $wpdb->prefix.'busy';
        $wpdb->query("DELETE FROM $tbl_or WHERE `$tbl_or`.`id` = ".$_GET['order_id']);
        $wpdb->query("DELETE FROM $tbl_busy WHERE `$tbl_busy`.`ordersID` = ".$_GET['order_id']);
        $headers = 'From: Гостиница Москва <info@hotel-alushta.com.ua>' . "\r\n";
        wp_mail($_GET['mail'], 'Отмена заказа', "Ваш заказ в гостинице 'Москва' был отменен", $headers);
    }

    if(isset($_GET['sendMsg'])){
        echo "<h3>Напоминание отправлено</h3>";
        $tbl_or = $wpdb->prefix.'orders';
        $wpdb->update(
            $tbl_or,
            array('state' => 'remindsend'),
            array('id' => $_GET['order_id']));
        $headers = 'From: Гостиница Москва <info@hotel-alushta.com.ua>' . "\r\n";
        wp_mail($_GET['mail'], 'Напоминание о заказе', "Ваш заказ в гостинице 'Москва' все еще не оплачен", $headers);
    }

    if(isset($_GET['setPay'])){
        echo "<h3>Заказ № ".$_GET['order_id']." оплачен</h3>";
        $tbl_or = $wpdb->prefix.'orders';
        $wpdb->update(
            $tbl_or,
            array('state' => 'paid'),
            array('id' => $_GET['order_id']));
    }

    $numbers = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'numbers');
    $months = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
    $busy = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'busy');

    $tbl_cl = $wpdb->prefix.'clients';
    $tbl_bus = $wpdb->prefix.'busy';
    $tbl_or = $wpdb->prefix.'orders';
    $busy = $wpdb->get_results("SELECT * FROM `$tbl_cl`, `$tbl_or`,	`$tbl_bus` WHERE `$tbl_bus`.`ordersID` = `$tbl_or`.`id`	and `$tbl_or`.`clientID` = `$tbl_cl`.`id`");
    echo "<div class='adminOrder'>
        <span style='right:0px'>
            <button class='prevMonth'><</button>";
        for($monthIdx=0; $monthIdx<count($months); $monthIdx++)
            echo "<span id='$monthIdx' class='monthName' style=".($monthIdx != (date('m')-1)?'display:none':' ').">".$months[$monthIdx]."</span>";
    
        echo "<button class='nextMonth'>></button>";
        
        echo "<button class='prevYear'>&nbsp;<&nbsp;</button>";
        for($yearIdx=2012; $yearIdx<2014; $yearIdx++)
            echo "<span id='$yearIdx' class='yearName' style=".($yearIdx != date('Y')?'display:none':' ').">$yearIdx</span>";
    
        echo "<span>&nbsp;год</span><button class='nextYear'>&nbsp;>&nbsp;</button>";
        
        echo "  <form name='frmCreateOrder' class='frmCreateOrder' method='GET' action='".$_SERVER['PHP_SELF']."'>
                    <input type='hidden' class='' name='page' value='bron_orders'/>";

    for($yearIdx=2012; $yearIdx<2014; $yearIdx++) {
        echo "<div class='yearWrapper' id='$yearIdx' style='".($yearIdx <> date('Y') ? "display:none" : "")."'>";
            
        for($monthIdx=0; $monthIdx < 12; $monthIdx++){
            echo "<table class='tblSelector' id='".$monthIdx."' ".($monthIdx != (date('m')-1)?"style='display:none'":"").">";
            echo "<tr><td></td>";
            for($day=1; $day < date("t", strtotime("$yearIdx-".($monthIdx+1))) + 1; $day++) echo "<td id='".$day."'>".$day."</td>";
            echo "</tr>";
            
            $oldID = -1;
            $style = '';
            
            foreach($numbers as $number){
                echo "<tr id='$number->id' class='place".$number->additional_places."'><td style='white-space:nowrap;'>".$number->name."</td>";
                for($day=1; $day < date("t", strtotime("$yearIdx-".($monthIdx+1))) + 1; $day++){
                    $class = 'free';
                    $res = '';
                    for($busyIdx=0; $busyIdx < count($busy); $busyIdx++){
                        $m = ($monthIdx+1<10?'0'.($monthIdx+1):$monthIdx+1);
                        $d = ($day<10?'0'.$day:$day);
                        if(($busy[$busyIdx]->date == "$yearIdx-$m-$d")&&($busy[$busyIdx]->numbersID == $number->id)) {
                            $class = 'busy';
                            $res = $busy[$busyIdx];
                        }
                    }
                    if($oldID != $res->ordersID) {
                        if (($style == '') || ($style == 'background-color: blue')) {
                            $style = 'background-color: red';
                        } elseif ($style == 'background-color: red') {
                            $style = 'background-color: blue';
                        }
                    }

                    if ($class == 'busy') { 
                        echo "<td id='$day' orderid='$res->ordersID' fio='$res->name' mail='$res->mail' phone='$res->phone' state='$res->state' date='$res->date' class='$class' style='$style'></td>";
                    } else {
                        echo "<td id='$day' class='$class'></td>";
                    }
                    
                    $oldID = $res->ordersID;
                }
                echo "</tr>";
            }
                
            echo "</table>";
        }
        echo "</div>";
    }
    
    echo "</form>
        <form method='GET' action='".$_SERVER['PHP_SELF']."'>
            <input type='hidden' class='orderID' name='order_id' value=''/>
            <input type='hidden' class='mail' name='mail' value=''/>
            <input type='hidden' class='' name='page' value='bron_orders'/>
            <div>
                <h3 class='orderIdx'>Информация о заказе №</h3>
                <span>ФИО клиента  - </span><span class='infFIO' style='color:red'></span><br/>
                <span>Телефон  - </span><span class='infPhone' style='color:red'></span><br/>
                <span>Почта  - </span><span class='infMail' style='color:red'></span><br/>
                <span>Даты заказа  - </span><span class='infOrderDate' style='color:red'></span><br/>
                <span>Состояние  - </span><span class='infState' style='color:red'></span><br/>
            </div><br/>
            <input type='submit' class='sendMsg' name='sendMsg' value='Отправить пользователю напоминание'/>
            <input type='submit' class='delOrder' name='delOrder' value='Отменить заказ'/>
            <input type='submit' class='setPay' name='setPay' value='Пометить оплаченным'/>
        </form>
    </div>";
}

//============================================================================
function bron_create_form($attr){
    global $wpdb;
    $tbl_diap = $wpdb->prefix.'diapasones';
    $tbl_n2d = $wpdb->prefix.'numbers2diapasones';
    $tbl_numb = $wpdb->prefix.'numbers';
    $tbl_cl = $wpdb->prefix.'clients';
    $tbl_busy = $wpdb->prefix.'busy';
    $tbl_or = $wpdb->prefix.'orders';
    $tbl_groups = $wpdb->prefix.'number_groups';
    
    if(isset($_GET['btnCreateOrder']))
    { //2й шаг   ===========================================
        
        $data = $wpdb->get_results("SELECT * FROM $tbl_diap, $tbl_n2d, $tbl_numb WHERE $tbl_n2d.diapasonesID = $tbl_diap.id AND $tbl_numb.id = $tbl_n2d.numbersID");
        
        $fromDate = $_GET['fromDate'];
        $toDate = $_GET['toDate'];
        
        $price = 0;
        $addplace = isset($_GET['need_add_place'])? 1 : 0;
        $days = (strtotime($_GET['toDate']) - strtotime($_GET['fromDate'])) / (24 * 3600);

        for($c = 0; $c < $days; $c++) {
            $date = Date('Y-m-d', strtotime($_GET['fromDate']) + $c * 24 * 3600);
            for($i=0; $i<count($data); $i++){
                if(($date >= $data[$i]->date_start) && ($date <= $data[$i]->date_finish) && ($_GET['apartNum'] == $data[$i]->numbersID)){
                    $price = $price + $data[$i]->price + $addplace * $data[$i]->additional_place_price;
                }
            }
        }

        if(isset($_GET['full_day'])) {
            $lastday = Date('Y-m-d', strtotime($_GET['fromDate']) + $days * 24 * 3600);
            for($i=0; $i<count($data); $i++){
                if(($date >= $data[$i]->date_start) && ($date <= $data[$i]->date_finish) && ($_GET['apartNum'] == $data[$i]->numbersID)){
                    $price = $price + ($data[$i]->price + $addplace * $data[$i]->additional_place_price) / 2;
                }
            }
        }
        echo "<form method='POST' action='".$_SERVER['PHP_SELF']."/bronirovanie/'>
                Стоимость заказа составляет $price грн. <br>
                <input type='hidden' name='inpFIO' value='".$_GET['inpFIO']."'/>
                <input type='hidden' name='inpMail' value='".$_GET['inpMail']."'/>
                <input type='hidden' name='inpPhone' value='".$_GET['inpPhone']."'/>
                <input type='hidden' name='fullPrice' value='$price'/>
                <input type='hidden' name='full_day' value='".$_GET['full_day']."'/>
                <input type='hidden' name='need_add_place' value='".$_GET['need_add_place']."'/><br>
                <input type='hidden' name='fromDate' value='".$_GET['fromDate']."'/>
                <input type='hidden' name='apartNum' value='".$_GET['apartNum']."'/>
                <input type='hidden' name='toDate' value='".$_GET['toDate']."'/><br>
                <input type='hidden' name='inos' value='".$_GET['inos']."'/><br>
                <input type='submit' name='btnConfirmOrder' value='Подтвердить заказ'/>
                <a href='".$_SERVER['SERVER_NAME']."'>Отменить заказ</a>
                </form>";
              
    } else if(isset($_POST['btnConfirmOrder']))
    { //3й шаг   ===========================================
        //Вносим данные о новом заказе в базу
        $wpdb->insert(
            $tbl_cl,
            array(
                'name' => $_POST['inpFIO'],
                'mail' => $_POST['inpMail'],
                'phone' => $_POST['inpPhone']
                ),
            array('%s', '%s', '%s')
        );
        
        $wpdb->insert(
            $tbl_or,
            array(
                'state' => 'create', 
                'mail_sent' => '', 
                'date' => Date('Y-m-d'), 
                'additional_place' => $_POST['need_add_place'],
                'clientID' => $wpdb->insert_id
            )
        );

        $orderId = $wpdb->insert_id;

        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];
        
        $is_full_day = $_POST['full_day'] === 'true' ? -1 : 0; //вечерний выезд
        $days = (strtotime($_POST['toDate']) - strtotime($_POST['fromDate'])) / (24 * 3600);

        for($c = 0; $c < $days - $is_full_day; $c++) {
            $day = strtotime($_POST['fromDate']) + $c * 24 * 3600;
            $wpdb->insert(
                $tbl_busy,
                array('numbersID' => $_POST['apartNum'], 'date' => Date('Y-m-d', $day), 'ordersID' => $orderId)
            );
        }

        //$sql = "SELECT * FROM $tbl_diap, $tbl_n2d, $tbl_numb WHERE $tbl_n2d.diapasonesID = $tbl_diap.id AND $tbl_numb.id = $tb";
        //$prices = $wpdb->get_results($sql);
        global $current_user;
        get_currentuserinfo();
        if ($current_user->user_login != 'admin') {
            $price = $_POST['fullPrice'];
            $clMail = $_POST['inpMail'];
            $inos = isset($_GET['inos']) ? 'inos' : 'ukr';
            $headers = 'From: Гостиница Москва <info@hotel-alushta.com.ua>' . "\r\n";

            wp_mail($clMail, 
                    'Инструкция по оплате', 
                    subject($inos, $_POST['inpFIO'], $_POST['inpPhone'], Date('Y-m-d'), 
                            $_POST['fromDate'], $_POST['toDate'], $orderId, $price, 250), // predoplata
                    $headers);
                    
            wp_mail('info@hotel-alushta.com.ua', 
                    'Инструкция по оплате', 
                    subject($inos, $_POST['inpFIO'], $_POST['inpPhone'], Date('Y-m-d'), 
                            $_POST['fromDate'], $_POST['toDate'], $orderId, $price, 250), // predoplata
                    $headers);
          
            echo "Вам на почту выслана инструкция для оплаты.";
            echo "<br/><a href='http://".$_SERVER['SERVER_NAME']."'>Вернуться на главную</a>";
        } else {
            echo "Операция успешно проведена.";
            echo "<br/><a href='http://".$_SERVER['SERVER_NAME']."'>Вернуться на главную</a>";
        }
    } else 
    { //1й шаг   ===========================================
        $numberType = $_GET['typeroom'];
        
        $sql = "SELECT * FROM $tbl_numb, $tbl_groups WHERE $tbl_numb.id = $tbl_groups.id_number AND $tbl_groups.group_rus_name = '$numberType'";
        $numbers = $wpdb->get_results($sql);

        $sql = "SELECT * FROM $tbl_busy, $tbl_groups WHERE $tbl_busy.numbersID = $tbl_groups.id_number";
        $busy = $wpdb->get_results($sql);
        
        

        echo "<div class='userOrder'><h3>".$numberType."</h3><span style='right:0px'><button class='prevMonth'>&nbsp;<&nbsp;</button>";
        $months = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
        for($monthIdx = 0; $monthIdx < count($months); $monthIdx++)
            echo "<span id='$monthIdx' class='monthName' style=".($monthIdx != (date('m')-1) ? "display:none" : ' ').">".$months[$monthIdx]."</span>";
    
        echo "<button class='nextMonth'>&nbsp;>&nbsp;</button>";
        
        echo "<button class='prevYear'>&nbsp;<&nbsp;</button>";
        for($yearIdx=2012; $yearIdx<2014; $yearIdx++)
            echo "<span id='$yearIdx' class='yearName' style=".($yearIdx != date('Y')?'display:none':' ').">$yearIdx</span>";
    
        echo "<span>&nbsp;год</span><button class='nextYear'>&nbsp;>&nbsp;</button>";

        for($yearIdx=2012; $yearIdx<2014; $yearIdx++) {
            echo "<div class='yearWrapper' id='$yearIdx' style='".($yearIdx <> date('Y') ? "display:none" : "")."'>";
            for($monthIdx=0; $monthIdx < 12; $monthIdx++) {
                echo "<table class='tblSelector' id='$monthIdx' ".($monthIdx != (date('m')-1)?"style='display:none'":"").">";
                echo "<tr><td></td>";
                for($day=1; $day < date("t", strtotime("$yearIdx-".($monthIdx+1))) + 1; $day++) 
                    echo "<td id='$day'>$day</td>";
                echo "</tr>";
    
                foreach($numbers as $number) {
                    echo "<tr id='$number->id' places='$number->places' addplace='$number->additional_places' class='apart'><td style='white-space:nowrap;'>$number->name</td>";
                    for($day=1; $day < date("t", strtotime("$yearIdx-".($monthIdx+1))) + 1; $day++){
                        $class = 'free';
                        for($busyIdx=0; $busyIdx < count($busy); $busyIdx++) {
                            $m = ($monthIdx+1<10?'0'.($monthIdx+1):$monthIdx+1);
                            $d = ($day<10?'0'.$day:$day);
                            if(($busy[$busyIdx]->date == "$yearIdx-$m-$d") 
                            && ($busy[$busyIdx]->numbersID == $number->id))
                                $class = 'busy';
                        }
                        echo "<td id='$day' class='$class'></td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            echo "</div>";
        }
        
        echo "<form name='frmCreateOrder' class='frmCreateOrder' method='GET' action='".$_SERVER['PHP_SELF']."/bronirovanie/'>
              <div>
                <label for='fromDate'>Дата заезда</label><input type='text' class='datepicker' name='fromDate' id='fromDate' /> <br>
                <label for='toDate'>Дата выезда</label><input type='text' class='datepicker' name='toDate' id='toDate'/><br>
                <div class='alert' style='display:none'>Все апартаменты на эти дни заняты</div>
                <div>
                    Дополнительное место
                    <input type='checkbox' name='need_add_place' class='need_add_place' />
                </div>
                <div>
                    Возможность выехать вечером
                    <input type='checkbox' class='full_day' name='full_day' />
                </div>
                <div>Номер </br>
                    <select class='myapart' name='myapart'>
                        <option id='0' value='0'>Не важно</option>";
                        foreach($numbers as $number) {
                            echo "<option id='$number->id' value='$number->id'>$number->name</option>";
                        }
                echo "</select>
                <div class='apartMsg'></div>
                </div>
                <div>
                    Ваше имя и фамилия <br>
                    <input type='text' class='inpFIO' name='inpFIO' value=''/>
                </div>
                <div>
                    Ваша электронная почта <br>
                    <input type='text' class='inpMail' name='inpMail' value=''/>
                </div>
                <div>
                    Ваш телефон<br>
                    <input type='text' class='inpPhone' name='inpPhone' value=''/>
                </div>
                <div>
                    Иностранец
                    <input type='checkbox' class='inos' name='inos' />
                </div>
                <input type='hidden' name='canOrder' class='canOrder' value='false'>
                <input type='hidden' name='apartNum' class='apartNum' value=''>
                <input type='submit' name='btnCreateOrder' class='btnCreateOrder' value='Оформить заказ'/>";
                
                global $current_user;
                get_currentuserinfo();
                if ($current_user->user_login == 'admin') {
                    echo "<input type='hidden' class='rights' value='admin' />";
                } else {
                    echo "<input type='hidden' class='rights' value='user' />";
                }
        echo "</div>
        </form></div>";
    }

}

function bron_show_admin_panel() {
    global $wpdb;
    global $admin_page;
    global $foto_dir; 
    global $current_user;
    get_currentuserinfo();
    
    //Подключаем локализацию
    include (dirname(__FILE__).'/localization/RU.php');

    $tbl_accs = $wpdb->prefix.'accs';
    $tbl_hotels = $wpdb->prefix.'hotels';
    $tbl_hotel_foto = $wpdb->prefix.'hotel_foto';
    $tbl_diap = $wpdb->prefix.'diapasones';
    $tbl_n2d = $wpdb->prefix.'numbers2diapasones';
    $tbl_numb = $wpdb->prefix.'numbers';
    $tbl_cl = $wpdb->prefix.'clients';
    $tbl_busy = $wpdb->prefix.'busy';
    $tbl_or = $wpdb->prefix.'orders';
    $tbl_groups = $wpdb->prefix.'number_groups';
    
    //ОБРАБОТКА ЛОГИНА
    //==================================================================
    //Авторизация
    if(isset($_POST['authorization'])) {
        $login = $_POST['login'];
        $pass = $_POST['pass'];
        
        $creds = array();
        $creds['user_login'] = $login;
        $creds['user_password'] = $pass;
        $creds['remember'] = true;
        $user = wp_signon( $creds, false );

        if (is_wp_error($user)) {
            echo $user->get_error_message();
        }

    }
    //==================================================================
    //Регистрация
    if(isset($_POST['registration'])) {
        $login = $_POST['login'];
        $pass = $_POST['pass'];
        $pass2 = $_POST['pass2'];

        $user_id = username_exists( $login );
        if ( !$user_id and ($pass == $pass2)) {
            $user_id = wp_create_user( $login, $pass );
            
            $creds = array();
            $creds['user_login'] = $login;
            $creds['user_password'] = $pass;
            $creds['remember'] = true;
            $user = wp_signon( $creds, false );
            
            if ( is_wp_error($user) )
                echo $user->get_error_message();
        } else {
            //Такой пользователь существует или пароли не одинаковые
        }
    }
    //==================================================================
    //Если пользователь не авторизован в системе
    if (!is_user_logged_in()) {
        $context = array();
        $context['admin_page'] = $admin_page; 
        //include (dirname(__FILE__).'/view/auth_form.php');
        include (dirname(__FILE__).'/view/regis_form.php');
        return;
    }

    //ОБРАБОТКА ОПЕРАЦИЙ
    //==================================================================
    //Добавление нового сезона
    if(isset($_GET['btnAddDiap'])){
        echo "<h4>Диапазон добавлен</h4>";
        $start = $_GET['start'];
        $finish = $_GET['finish'];
        $wpdb->insert(
                $tbl_diap,
                array('date_start' => $start, 'date_finish' => $finish, 'accID' => $current_user->id)
                );
    }
    //==================================================================
    //Удаление сезона
    if(isset($_GET['btnDelDiap'])){
        echo "<h4>Аппартаменты удалены</h4>";
        $id = $_GET['diap_id'];
        $wpdb->query("DELETE FROM $tbl_diap WHERE `id` = $id AND `accID` = $current_user->id");
    }
    //==================================================================
    //Сохранение диапазонов
    if(isset($_GET['btnSaveDiap'])){
        echo "<h4>Изменения сохранены</h4>";
        $id = $_GET['diap_id'];
        $start = $_GET['start'];
        $finish = $_GET['finish'];
        $wpdb->update(
                    $tbl_diap, 
                    array('date_start' => $start, 'date_finish' => $finish),
                    array('id' => $id, 'accID' => $current_user->id)
                    ); 
        //TODO В цикле новый диаазон каждому номеру в ценах
    }
    //==================================================================
    //Сохранение описания Отеля
    if(isset($_GET['save_desc'])) {
        $hotel_id = $_GET['hotel-id'];
        $desc = $_GET['desc'];
        $wpdb->update(
                    $tbl_hotels, 
                    array('desc' => $desc),
                    array('id' => $hotel_id)
                    );
    }
    //==================================================================
    //Удаление фото
    if(isset($_GET['deletefoto'])) {
        $foto_id = $_GET['foto-id'];
        $sql = "DELETE FROM $tbl_hotel_foto WHERE `id` = $foto_id";
        $res = $wpdb->query($sql);
    }
    //==================================================================
    //Аплоад фото
    if(isset($_POST['uploadfoto'])) {
        $filename = $_FILES['file']['name'];
        $random_prefix = rand(); //Для предотвращения повторок
        $filename = $random_prefix.$filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], 
                               dirname(__FILE__).$foto_dir.$filename)) {
            $wpdb->insert(
                $tbl_hotel_foto, 
                array('hotelID' => $_POST['hotelID'],
                      'path' => $filename 
                      )
               );
        }
    }
    //==================================================================
    //Добавление отеля
    if(isset($_GET['createhotel'])) {
        $wpdb->insert(
            $tbl_hotels, 
            array('name' => $_GET['adm_add_name'],
                  'country' => $_GET['adm_add_country'], 
                  'city' => $_GET['adm_add_city'],             
                  'accID' => $_GET['adm_add_acc_id'])
           );
    }
    //==================================================================
    //Удаление отеля
    if(isset($_GET['delhotel'])) {
        echo "Отель удален";
        $hotel_id = (int)($_GET['hotel-id']);
        $sql = "DELETE FROM $tbl_hotels WHERE `id` = $hotel_id";
        $wpdb->query($sql);
    }
    //==================================================================
    //Добавление апартаментов
    if(isset($_GET['adm_add_apart'])) {
        echo "Апартаменты добавлены";
        $wpdb->insert(
            $tbl_numb, 
            array('name' => $_GET['adm_add_name'], 
                  'rooms' => $_GET['adm_add_rooms'], 
                  'places' => $_GET['adm_add_places'],
                  'hotelID' => $_GET['adm_add_hotel_id'])
           );
    }
    //==================================================================
    //Сохранение цен
    if(isset($_GET['btnSavePrices'])) {
        $prices = $wpdb->get_results("SELECT id FROM $tbl_n2d");
        foreach($prices as $price){
            if(isset($_GET['price_'.$price->id])){
                $wpdb->update(
                    $tbl_n2d,
                    array('price' => $_GET['price_'.$price->id], 'additional_place_price' => $_GET['add_price_'.$price->id]),
                    array('id' => $price->id)
                );
            }
        }
    }   
    echo "<h2>Панель управления отелями</h2><br>";
    //==================================================================
    //Показ шахматки
    if(isset($_GET['showbron'])) {
        bron_order_page();
    }
    //==================================================================
    //Показ и правка сезонов
    else if(isset($_GET['showseasons'])) {
        $hotel_id = $_GET['hotel-id'];
        $sql = "SELECT * FROM $tbl_diap WHERE `accID` = $hotel_id";
        $context = array();
        $context['admin_page'] = $admin_page; 
        $context['seasons'] = $wpdb->get_results($sql);
        include (dirname(__FILE__).'/view/seasons_list.php');
        include (dirname(__FILE__).'/view/new_season_form.php');
    }
    //==================================================================
    //Показ и правка цен
    else if(isset($_GET['showprices'])) {
        $hotel_id = $_GET['hotel-id'];
        $sql = "SELECT * FROM $tbl_diap WHERE `accID` = $hotel_id";
        $context = array();
        $context['admin_page'] = $admin_page; 
        $context['seasons'] = $wpdb->get_results($sql);
        $context['diaps'] = $wpdb->get_results("SELECT * FROM $tbl_diap");
        $context['aparts'] = $wpdb->get_results("SELECT * FROM $tbl_numb");
        $context['price'] = $wpdb->get_results("SELECT * FROM $tbl_n2d");
        include (dirname(__FILE__).'/view/price_table.php');
    }
    //==================================================================
    //Показ списка апартаментов отеля
    else if(isset($_GET['showaparts'])) {
        $hotel_id = (int)($_GET['hotel-id']);
        $context = array();
        
        $sql = "SELECT * FROM $tbl_numb WHERE `hotelID` = $hotel_id";
        $context['aparts'] = $wpdb->get_results($sql);
        $context['admin_page'] = $admin_page;
        $context['hotel_id'] = $hotel_id;

        include (dirname(__FILE__).'/view/aparts_list.php');
        include (dirname(__FILE__).'/view/new_apart_form.php');
    }
    //==================================================================
    //Показ информации про определенный отель
    else if(isset($_GET['showhotel'])) {
        $hotel_id = (int)($_GET['hotel-id']);

        $sql = "SELECT * FROM $tbl_hotels WHERE `id` = $hotel_id";
        $hotel = $wpdb->get_results($sql);

        $sql = "SELECT * FROM $tbl_hotel_foto WHERE `hotelID` = $hotel_id";
        $fotos = $wpdb->get_results($sql);
        
        $context = array();
        $context['hotel'] = $hotel[0];
        $context['fotos'] = $fotos;
        $context['foto_dir'] = plugins_url( '/fotos/', __FILE__ );
        $context['admin_page'] = $admin_page;
        
        include (dirname(__FILE__).'/view/hotel_settings.php');
    }
    //==================================================================
    //Показ всех отелей пользователя
    else {
        $context = array();
        $context['acc_id'] = $current_user->id;
        $context['admin_page'] = $admin_page;

        $sql = "SELECT * FROM $tbl_hotels WHERE `accID` = $current_user->id";
        $hotels = $wpdb->get_results($sql);
        $context['hotels'] = $hotels;

        include (dirname(__FILE__).'/view/hotels_list.php');
        include (dirname(__FILE__).'/view/new_hotel_form.php');
    }
}

function bron_show_filter() {
    global $wpdb;
    global $filter_page;
    
    $tbl_accs = $wpdb->prefix.'accs';
    $tbl_hotels = $wpdb->prefix.'hotels';
    $tbl_diap = $wpdb->prefix.'diapasones';
    $tbl_n2d = $wpdb->prefix.'numbers2diapasones';
    $tbl_numb = $wpdb->prefix.'numbers';
    $tbl_cl = $wpdb->prefix.'clients';
    $tbl_busy = $wpdb->prefix.'busy';
    $tbl_or = $wpdb->prefix.'orders';
    $tbl_groups = $wpdb->prefix.'number_groups';
    
    // Узнаем пользователя
    // global $current_user;
    // get_currentuserinfo();
    // if ($current_user->id === 0) return;
    
    if (isset($_GET['filter'])) {
        //Выдаем результат поиска
        $sql = '';
        $aparts = $wpdb->get_results($sql);
        
        $context = array();
        
    
    }
    
    echo "Поиск отеля. <br>
         <form>
            <span>Страна</span>
            <input type='text' class='country' name='' value='' />
            <br>
            <span>Город</span>
            <input type='text' class='city' name='' value='' />
            <br>
            <span>Класс отеля</span>
            <select class='' name='hotel_class' value='1'>
                <option value='1'>*</option>
                <option value='2'>**</option>
                <option value='3'>***</option>
                <option value='4'>****</option>
                <option value='5'>*****</option>
            </select>
            <br>
            <span>Класс апартаментов</span>
            <select class='' name='apart_class' value='1'>
                <option value='1'>*</option>
                <option value='2'>**</option>
                <option value='3'>***</option>
                <option value='4'>****</option>
                <option value='5'>*****</option>
            </select>
            <br>
            <label for='fromDate'>Дата заезда</label><input type='text' class='datepicker' name='fromDate' id='fromDate' />
            <br>
            <label for='toDate'>Дата выезда</label><input type='text' class='datepicker' name='toDate' id='toDate'/><br>
            <input type='submit' class='' name=filter' value='Поиск'/>
          </form>";

}

function bron_show_favorites() {
    /*
        Выборка из базы лучших или случайных отелей и показ
    */
    
    global $wpdb;
    global $filter_page;
    
    $tbl_accs = $wpdb->prefix.'accs';
    $tbl_hotels = $wpdb->prefix.'hotels';
    $tbl_diap = $wpdb->prefix.'diapasones';
    $tbl_n2d = $wpdb->prefix.'numbers2diapasones';
    $tbl_numb = $wpdb->prefix.'numbers';
    $tbl_cl = $wpdb->prefix.'clients';
    $tbl_busy = $wpdb->prefix.'busy';
    $tbl_or = $wpdb->prefix.'orders';
    $tbl_groups = $wpdb->prefix.'number_groups';
    
    echo "тут будет выборка отелей";
}

function bron_user_init() {
    add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
    add_shortcode('createBron', 'bron_create_form');
    add_shortcode('show_admin_panel', 'bron_show_admin_panel');
    add_shortcode('bron_show_favorites', 'bron_show_favorites');
    add_shortcode('bron_show_filter', 'bron_show_filter');
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'script',  plugins_url( '/js/script.js', __FILE__ ));
    wp_enqueue_style( 'style',  plugins_url( '/styles/style.css', __FILE__ ));
}
//============================================================================
function bron_install(){

    add_option('maintext', ">");
    add_option('inostext', '');

    add_option('ukrtext', '');

    include "installdb.php";
}

function bron_uninstall(){
    global $wpdb;
    
    delete_option('maintext');
    delete_option('inostext');
    delete_option('ukrtext');   
    
    if (true) //перевести на параметр плагина
        include "deletedb.php";
}

function bind_files(){
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'script',  plugins_url( '/js/script.js', __FILE__ ));
    wp_enqueue_style( 'style',  plugins_url( '/styles/style.css', __FILE__ ));
}

register_activation_hook( __FILE__, 'bron_install');
register_deactivation_hook( __FILE__, 'bron_uninstall');

add_action('admin_menu', 'bron_admin_init');
add_action('init', 'bron_user_init');
add_action('wp_enqueue_scripts', 'bind_files' ); 
?>