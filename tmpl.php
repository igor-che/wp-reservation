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
    в настройки админки годы отображения , для юзера и для админа
    Счет должен приходить не только Гостю, но и администратору гостиницы!
    Необходимо чтобы Гость имел возможность выбрать срок
    проживания + половина суток, но не менее чем 2,5 суток.

    Статус заказа может быть = (заказан, оплачен, проживают, устарел)
*/
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
    
    if(isset($_GET['btnAddNewDiap'])){
        echo "<h4>Диапазон добавлен</h4>";
        $start = $_GET['start'];
        $finish = $_GET['finish'];
        $wpdb->insert(
                $wpdb->prefix.get_option('table_diapasones'), 
                array('date_start' => $start, 'date_finish' => $finish),
                array('%s', '%d', '%d', '%d')
               );
    }
    
    if(isset($_GET['btnDelDiap'])){
        echo "<h4>Аппартаменты удалены</h4>";
        $id = $_GET['diap_id'];
        $wpdb->query("DELETE FROM ".$wpdb->prefix.get_option('table_diapasones')." WHERE id = $id");
    }

    if(isset($_GET['btnSaveDiap'])){
        echo "<h4>Изменения сохранены</h4>";
        $id = $_GET['diap_id'];
        $start = $_GET['start'];
        $finish = $_GET['finish'];
        $wpdb->update(
                    $wpdb->prefix.get_option('table_diapasones'), 
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
    
    if(isset($_GET['btnSavePrices'])){
        echo "<h3>Цены сохранены</h3>";
        $prices = $wpdb->get_results('SELECT id FROM '.$wpdb->prefix.get_option('table_numbers2diapasones'));
        foreach($prices as $price){
            if(isset($_GET['price_'.$price->id])){
                $wpdb->update(
                    $wpdb->prefix.get_option('table_numbers2diapasones'),
                    array('price' => $_GET['price_'.$price->id], 'additional_place_price' => $_GET['add_price_'.$price->id]),
                    array('id' => $price->id)
                );
            
            }
        }
    }
    
    echo "<h2>Существующие диапазоны</h2>";
    $diaps = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.get_option('table_diapasones'));
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

    $aparts = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.get_option('table_numbers'));
    $price = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.get_option('table_numbers2diapasones'));

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
                if(($price[$c]->numbersID == $apart->id)&&($price[$c]->diapasonesID == $diap->id)){
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
                $wpdb->prefix.get_option('table_numbers'), 
                array('name' => $name, 'rooms' => $rooms, 'places' => $places, 'additional_places' => $add_place),
                array('%s', '%d', '%d', '%d')
               );
    }
    
    if(isset($_GET['btnDelApart'])){
        echo "<h4>Аппартаменты удалены</h4>";
        $id = $_GET['apart_id'];
        $sql = "DELETE FROM ".$wpdb->prefix.get_option('table_numbers')." WHERE id = $id"; 
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
                    $wpdb->prefix.get_option('table_numbers'), 
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
    
    $sql = "SELECT * FROM ".$wpdb->prefix.get_option('table_numbers');

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
        $tbl_or = $wpdb->prefix.get_option('table_orders');
        $tbl_busy = $wpdb->prefix.get_option('table_busy');
        $wpdb->query("DELETE FROM $tbl_or WHERE `$tbl_or`.`id` = ".$_GET['order_id']);
        $wpdb->query("DELETE FROM $tbl_busy WHERE `$tbl_busy`.`ordersID` = ".$_GET['order_id']);
        $headers = 'From: Гостиница Москва <info@hotel-alushta.com.ua>' . "\r\n";
        wp_mail($_GET['mail'], 'Отмена заказа', "Ваш заказ в гостинице 'Москва' был отменен", $headers);
    }

    if(isset($_GET['sendMsg'])){
        echo "<h3>Напоминание отправлено</h3>";
        $tbl_or = $wpdb->prefix.get_option('table_orders');
        $wpdb->update(
            $tbl_or,
            array('state' => 'remindsend'),
            array('id' => $_GET['order_id']));
        $headers = 'From: Гостиница Москва <info@hotel-alushta.com.ua>' . "\r\n";
        wp_mail($_GET['mail'], 'Напоминание о заказе', "Ваш заказ в гостинице 'Москва' все еще не оплачен", $headers);
    }

    if(isset($_GET['setPay'])){
        echo "<h3>Заказ № ".$_GET['order_id']." оплачен</h3>";
        $tbl_or = $wpdb->prefix.get_option('table_orders');
        $wpdb->update(
            $tbl_or,
            array('state' => 'paid'),
            array('id' => $_GET['order_id']));
    }

    $numbers = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.get_option('table_numbers'));
    $months = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
    $busy = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.get_option('table_busy'));

    $tbl_cl = $wpdb->prefix.get_option('table_clients');
    $tbl_bus = $wpdb->prefix.get_option('table_busy');
    $tbl_or = $wpdb->prefix.get_option('table_orders');
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
                        if($style == '') {
                            $style = 'background-color: red';
                        } elseif ($style == 'background-color: blue') {
                            $style = 'background-color: red';
                        } elseif($style == 'background-color: red') {
                            $style = 'background-color: blue';
                        }
                    }

                    if ($class == 'busy') { 
                        echo "<td id='".$day."' orderid='".$res->ordersID."' fio='".$res->name."' mail='".$res->mail."' phone='".$res->phone."' state='".$res->state."' date='".$res->date."' class='$class' style='$style'></td>";
                    } else {
                        echo "<td id='".$day."' class='$class'></td>";
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
    $tbl_diap = $wpdb->prefix.get_option("table_diapasones");
    $tbl_n2d = $wpdb->prefix.get_option("table_numbers2diapasones");
    $tbl_numb = $wpdb->prefix.get_option("table_numbers");
    $tbl_cl = $wpdb->prefix.get_option('table_clients');
    $tbl_busy = $wpdb->prefix.get_option('table_busy');
    $tbl_or = $wpdb->prefix.get_option('table_orders');
    $tbl_groups = $wpdb->prefix.get_option('table_number_groups');
    
    if(isset($_GET['btnCreateOrder']))
    { //2й шаг   ===========================================
        
        $data = $wpdb->get_results("SELECT * FROM $tbl_diap, $tbl_n2d, $tbl_numb WHERE $tbl_n2d.diapasonesID = $tbl_diap.id AND $tbl_numb.id = $tbl_n2d.numbersID");
        echo "<form method='POST' action='".$_SERVER['PHP_SELF']."/bronirovanie/'>";
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
        
        //прибавляем последний день как половина (МОЖНО ОПТИМИЗИРОВАТЬ АЛГОРИТМ)
        if(isset($_GET['full_day'])) {
            $lastday = Date('Y-m-d', strtotime($_GET['fromDate']) + $days * 24 * 3600);
            for($i=0; $i<count($data); $i++){
                if(($date >= $data[$i]->date_start) && ($date <= $data[$i]->date_finish) && ($_GET['apartNum'] == $data[$i]->numbersID)){
                    $price = $price + ($data[$i]->price + $addplace * $data[$i]->additional_place_price) / 2;
                }
            }
        }

        echo "Стоимость заказа составляет $price грн. <br>
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
        
        $full_day = $_POST['full_day'] === 'true' ? -1 : 0; //вечерний выезд
        $days = (strtotime($_POST['toDate']) - strtotime($_POST['fromDate'])) / (24 * 3600);

        for($c = 0; $c < $days - $full_day; $c++) {
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
                            if(($busy[$busyIdx]->date == "$yearIdx-$m-$d") && ($busy[$busyIdx]->numbersID == $number->id))
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

function bron_show_price($attr) {
    //в attr id Апартаментов
    global $wpdb;
    $sql = '';
    $wpdb->get_results($sql);
    
    // отрисовываем цены
    echo '
        <div class="room-block__price">
        <p>28.04-11.05, 01.06-30.06,<br>
        01.09-30.09, 29.12-10.01</p>
        : <span class="set-price">630<input class="get-price" type="hidden" value="630"></span>
        <a label="грн." value="hrn" class="currency text-price text-price_state_active" name="630">грн</a>
        <a label="руб." value="rub" class="currency text-price" name="630">руб</a>
        <a label="долл." value="usd" class="currency text-price" name="630">$</a>
    </div>
    <div class="room-block__price">
        <p>01.07-31.08</p> : <span class="set-price">950<input class="get-price" type="hidden" value="950"></span>
        <a label="грн." value="hrn" class="currency text-price text-price_state_active" name="950">грн</a>
        <a label="руб." value="rub" class="currency text-price" name="950">руб</a>
        <a label="долл." value="usd" class="currency text-price" name="950">$</a>
    </div>
    <div class="room-block__price">
        <p>Остальные месяца</p> : <span class="set-price">450<input class="get-price" type="hidden" value="450"></span>
        <a label="грн." value="hrn" class="currency text-price text-price_state_active" name="450">грн</a>
        <a label="руб." value="rub" class="currency text-price" name="450">руб</a>
        <a label="долл." value="usd" class="currency text-price" name="450">$</a>
    </div>
    ';
}

function bron_user_init(){
    add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
    add_shortcode( 'createBron', 'bron_create_form' );
    add_shortcode( 'showPrices', 'bron_show_price' );
    wp_enqueue_script( 'script',  plugins_url( '/script.js', __FILE__ ));
    wp_enqueue_style( 'style',  plugins_url( '/style.css', __FILE__ ));
    cron_event_proc();
}

//============================================================================
function bron_install(){
    global $wpdb;

    add_option('table_numbers', 'numbers');
    add_option('table_busy', 'busy');
    add_option('table_orders', 'orders');
    add_option('table_clients', 'clients');
    add_option('table_diapasones', 'diapasones');
    add_option('table_numbers2diapasones', 'numbers2diapasones');
    add_option('table_number_groups', 'number_groups');
    
    add_option('maintext', "<p>Заказ № %order_id% от <u>%date%</u></p>
    <p>Заезд: <u>%date_start%</u></p>
    <p>Выезд: <u>%date_finish%</u></p>
    <p>Турист:<strong>%FIO%</strong></p>
    <p>Тел.:%phone%</p>
    <table border='1' cellspacing='0' cellpadding='0' width='756'>
        <tbody>
            <tr>
                <td width='175'>
                    <p align='center'>Наименование</p>
                    <p align='center'>услуги</p>
                </td>
                <td width='139'>
                    <p align='center'>Количество забронированных</p>
                    <p align='center'>номеров данной</p>
                    <p align='center'>категории</p>
                </td>
                <td width='139'>
                    <p align='center'>Общая сумма,</p>
                    <p align='center'>грн/ долл.</p>
                </td>
                <td width='139'>
                    <p align='center'>Предоплата,</p>
                    <p align='center'>грн/ долл.</p>
                    <p align='center'><strong>Внести в течении</strong></p>
                    <p align='center'><strong> 3 дней после оформления заявки</strong></p>
                </td>
                <td width='163'>
                    <p align='center'>Оплатить на месте,</p>
                    <p align='center'>грн/долл.</p>
                </td>
            </tr>
            <tr>
                <td width='175' valign='top'>
                    <p><strong>Проживание в гостинице «Москва»</strong></p>
                    <p>Категория:</p>
                    <p>Кол-во взрослых:</p>
                    <p>Кол-во детей:</p>
                    <p>- до 7 лет</p>
                    <p>- от 7 лет</p>
                </td>
                <td width='139' valign='top'>1</td>
                <td width='139' valign='top'>%price%</td>
                <td width='139' valign='top'>%predoplata%</td>
                <td width='163' valign='top'>%oplata%</td>
            </tr>
        </tbody>
    </table>");
    
    add_option('inostext', '
        <p>РЕКВИЗИТЫ ДЛЯ ВНЕСЕНИЯ ПРЕДОПЛАТЫ:</p>
        <p><b> Снигурская Жанна Анатольевна</b></p>
        <table border="1" cellspacing="0" cellpadding="0" align="left" > 
        <tbody>
        <tr>
            <td><p>Для валюты</p><p><b>«гривня»</b></p></td>
            <td><p>Приват Банк</p><p>№ счета: 29244825509100 </p><p>МФО: 305299, ОКПО: 14360570</p><p><b>5218572205191518</b></p></td>
            <td valign="top" ><p>Назначение платежа: (за что платеж (зарплата, оплата товара и т.п);на основании чего(№ договора, лицевой счет); кому(Ф.И.О., ИНН); для пополнения на карту(№ карты клиента)</p></td>
        </tr>
        <tr>
            <td rowspan="3" ><p> Для валюты</p><p><b>«доллары США»</b></p></td>
            <td valign="top" ><p>CHASE Manhattan bank, Brooklyn,</p><p>SWIFT-код –CHASUS33, </p><p>acc.001-1-000080,</p><p>№ карты (или № счета <b>5218572205191518</b>)</p></td>
            <td rowspan="3" ><p>Назначение платежа:</p><p>replenishment card № <b>5218572205191518</b></p><p><b> (указывать обязательно)</b></p></td>
        </tr>
        <tr>
            <td valign="top" ><p>BANK OF NEW YORK, NEW YORK, USA,</p><p>SWIFT-код –IRVTUS3N, </p><p>acc.890-0085-754,</p><p>№ карты (или № счета <b>5218572205191518</b>)</p></td>
        </tr>
        <tr>
            <td valign="top" ><p>Deutsche Bank Trust Company Americas, NEW YORK, USA,</p><p>SWIFT-код –BKTRUS33, </p><p>acc.04182358,</p><p>№ карты (или № счета <b>5218572205191518</b>)</p></td>
        </tr>
        <tr>
            <td rowspan="3" ><p>Для валюты</p><p><b>«евро»</b></p></td>
            <td valign="top" ><p>DEUTSCHE BANK AG, Frankfurt/ main, Germany</p><p>SWIFT-код –DEUTDEFF, </p><p>acc. 947 01221 10,</p><p>№ карты (или № счета <b>5218572205191518</b>)</p></td>
            <td rowspan="3" ><p>Назначение платежа:</p><p>replenishment card № <b>5218572205191518</b></p><p><b>(указывать обязательно)</b></p></td>
        </tr>
        <tr>
            <td valign="top" ><p>Commerzbank AG Frankfurt am main, Germany,</p><p>SWIFT-код –COBADEFF, </p><p>acc.400886700401,</p><p>№ карты (или № счета <b>5218572205191518</b>)</p></td>
        </tr>
        <tr>
            <td valign="top" ><p>J.P. CHASE Bank, Frankfurt am main, Germany,</p><p>SWIFT-код –СHASDEFX, </p><p>acc.6231605145,</p><p>№ карты (или № счета <b>5218572205191518</b>)</p></td>
        </tr>
        </tbody>
        </table>

        ');
    
    add_option('ukrtext', '
        <p>РЕКВИЗИТЫ ДЛЯ ВНЕСЕНИЯ ПРЕДОПЛАТЫ:</p>
        <p></br>
        ОКПО 2423416067 </br>
        Расчетный счет № 26006060922531 </br>
        ФЛП Снигурская Ж.А. </br>
        МФО	384436 </br>
        Банк "ПриватБанк" г.Симферополь </br>
        </p>
        <p>
            <u>
                Если по истечении <strong>трех суток</strong> номер остаётся без денежного обеспечения (предоплаты), то администрация гостиницы «Москва» сохраняет
                право передать зарезервированный номер другому клиенту.
            </u>
        </p>
        <p>
            <strong>Расчетное время: </strong>
            заезд после 13:00, выезд до 12:00<strong></strong>
        </p>
        <p>
            <strong>Условия аннуляции: </strong>
            В случае <u>досрочного освобождения</u> оплаченного Вами номера либо <u>отказа от брони меньше чем за 7 дней</u> до планируемой даты заезда - сумма
            предоплаты переходит гостинице в виде неустойки.
        </p>
        <p align="center">
            <strong>С уважением администрация гостиницы «Москва» !</strong>
        </p>');

    wp_schedule_event(time(), 'hourly', 'cron_event');

    $table_numbers = $wpdb->prefix.get_option('table_numbers');
    $table_busy = $wpdb->prefix.get_option('table_busy');
    $table_orders = $wpdb->prefix.get_option('table_orders');
    $table_clients = $wpdb->prefix.get_option('table_clients');
    $table_diapasones = $wpdb->prefix.get_option('table_diapasones');
    $table_numbers2diapasones = $wpdb->prefix.get_option('table_numbers2diapasones');
    $table_number_groups = $wpdb->prefix.get_option('table_number_groups');

    
    $sql = "CREATE TABLE IF NOT EXISTS $table_number_groups (
              `idx` int(11) NOT NULL AUTO_INCREMENT,
              `id_group` int(11) NOT NULL,
              `id_number` int(11) NOT NULL,
              `group_rus_name` varchar(30) NOT NULL,
              `group_en_name` varchar(30) NOT NULL,
              PRIMARY KEY (`idx`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8  AUTO_INCREMENT=100";

    $wpdb->query($sql);
    
    $sql = "INSERT INTO $table_number_groups VALUES
            (1, 1, 1, 'Одноместный', 'Одноместный'),
            
            (2, 2, 2, 'Стандарт', 'Стандарт'),
            (3, 2, 3, 'Стандарт', 'Стандарт'),
            (4, 2, 4, 'Стандарт', 'Стандарт'),
            (5, 2, 5, 'Стандарт', 'Стандарт'),
            (6, 2, 6, 'Стандарт', 'Стандарт'),
            (7, 2, 7, 'Стандарт', 'Стандарт'),
            (8, 2, 8, 'Стандарт', 'Стандарт'),
            
            (9, 3, 9, 'Стандарт', 'Стандарт'),
            (10, 3, 10, 'Стандарт', 'Стандарт'),
            (11, 3, 11, 'Стандарт', 'Стандарт'),
            (12, 3, 12, 'Стандарт', 'Стандарт'),
            (13, 3, 13, 'Стандарт', 'Стандарт'),
            
            (14, 4, 14, 'Полулюкс', 'Полулюкс'),
            (15, 4, 15, 'Полулюкс', 'Полулюкс'),
            
            (16, 5, 16, 'Полулюкс «Восточный»', 'Полулюкс «Восточный»'),
            (17, 6, 17, 'Люкс категории «Б»', 'Люкс категории «Б»'),
            (18, 7, 18, 'Люкс категории «А»', 'Люкс категории «А»'),
            (19, 8, 25, 'Люкс «Классика»', 'Люкс «Классика»'),
            (20, 9, 19, 'Апартаменты 1 этаж', 'Апартаменты 1 этаж'),
            (21, 10, 20, 'Апартаменты «Б»', 'Апартаменты «Б»'),
            (22, 11, 21, 'Апартаменты «А»', 'Апартаменты «А»'),
            (100, 7, 26, 'Люкс категории «А»', 'Люкс категории «А»'),
            (101, 7, 27, 'Люкс категории «А»', 'Люкс категории «А»'),
            (102, 7, 28, 'Люкс категории «А»', 'Люкс категории «А»'); ";
            
    $wpdb->query($sql);
  
    $sql = "CREATE TABLE IF NOT EXISTS $table_numbers (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(30) NOT NULL,
              `rooms` tinyint(4) NOT NULL,
              `places` tinyint(4) NOT NULL,
              `additional_places` tinyint(4) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
    
    $wpdb->query($sql);

    $sql = "INSERT INTO $table_numbers (`id`, `name`, `rooms`, `places`, `additional_places`) VALUES
            (1, '№ 15 (одноместный)', 1, 1, 1),
            (2, '№ 1', 1, 2, 0),
            (3, '№ 2', 1, 2, 0),
            (4, '№ 3', 1, 2, 0),
            (5, '№ 6', 1, 2, 0),
            (6, '№ 9', 1, 2, 0),
            (7, '№ 18', 1, 2, 0),
            (8, '№ 21', 1, 2, 0),
            (9, '№ 7', 1, 2, 1),
            (10, '№ 8', 1, 2, 1),
            (11, '№ 10', 1, 2, 1),
            (12, '№ 19', 1, 2, 1),
            (13, '№ 20', 1, 2, 1),
            (14, 'Полулюкс № 11', 1, 2, 1),
            (15, 'Полулюкс № 12', 1, 2, 1),
            (16, 'Полулюкс Восточный', 1, 2, 2),
            (17, 'Люкс категории Б № 4', 2, 2, 2),
            
            (18, 'Люкс категории А № 5', 2, 2, 2),
            
            (19, 'Апартаменты 1 этаж', 1, 2, 1),
            (20, 'Апартаменты Б №24', 2, 3, 2),
            (21, 'Апартаменты А №5', 2, 3, 2),
            (25, 'Люкс Классика №17', 2, 2, 2),
            
            (26, 'Люкс категории А №14', 2, 2, 2),
            (27, 'Люкс категории А №16', 2, 2, 2),
            (28, 'Люкс категории А №22', 2, 2, 2);";

    
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_busy (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `numbersID` int(11) NOT NULL,
              `date` date NOT NULL,
              `ordersID` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_clients (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL,
              `mail` varchar(30) NOT NULL,
              `phone` varchar(15) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_diapasones (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `date_start` date NOT NULL,
              `date_finish` date NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $wpdb->query($sql);
    
    $sql = "INSERT INTO $table_diapasones (`id`, `date_start`, `date_finish`) VALUES
            (1, '2012-01-01', '2012-01-10'),
            (2, '2012-01-11', '2012-04-27'),
            (3, '2012-04-28', '2012-05-11'),
            (4, '2012-05-12', '2012-05-31'),
            (5, '2012-06-01', '2012-06-30'),
            (6, '2012-07-01', '2012-08-31'),
            (7, '2012-09-01', '2012-09-30'),
            (8, '2012-10-01', '2012-12-28'),
            (10, '2012-12-29', '2012-12-31');";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_numbers2diapasones (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `numbersID` int(11) NOT NULL,
              `diapasonesID` int(11) NOT NULL,
              `price` int(11) NOT NULL,
              `additional_place_price` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $wpdb->query($sql);
    
    $sql = "INSERT INTO $table_numbers2diapasones (`id`, `numbersID`, `diapasonesID`, `price`, `additional_place_price`) VALUES
(172, 2, 10, 370, 130), 
(171, 1, 10, 200, 130),
(3, 1, 1, 200, 130),
(4, 1, 2, 200, 100),
(5, 1, 3, 200, 130),
(6, 1, 4, 200, 100),
(7, 1, 5, 200, 130),
(8, 1, 6, 350, 150),
(9, 1, 7, 200, 130),
(10, 1, 8, 200, 100),
(11, 2, 1, 370, 130),
(12, 2, 2, 250, 100),
(13, 2, 3, 370, 130),
(14, 2, 4, 250, 100),
(15, 2, 5, 370, 130),
(16, 2, 6, 600, 150),
(17, 2, 7, 370, 130),
(18, 2, 8, 250, 100),
(19, 3, 1, 370, 130),
(20, 3, 2, 250, 100),
(21, 3, 3, 370, 130),
(22, 3, 4, 250, 100),
(23, 3, 5, 370, 130),
(24, 3, 6, 600, 150),
(25, 3, 7, 370, 130),
(26, 3, 8, 250, 100),
(27, 4, 1, 370, 130),
(28, 4, 2, 250, 100),
(29, 4, 3, 370, 130),
(30, 4, 4, 250, 100),
(31, 4, 5, 370, 130),
(32, 4, 6, 600, 150),
(33, 4, 7, 370, 130),
(34, 4, 8, 250, 100),
(35, 5, 1, 370, 130),
(36, 5, 2, 250, 100),
(37, 5, 3, 370, 130),
(38, 5, 4, 250, 100),
(39, 5, 5, 370, 130),
(40, 5, 6, 600, 150),
(41, 5, 7, 370, 130),
(42, 5, 8, 250, 100),
(43, 6, 1, 370, 130),
(44, 6, 2, 250, 100),
(45, 6, 3, 370, 130),
(46, 6, 4, 250, 100),
(47, 6, 5, 370, 130),
(48, 6, 6, 600, 150),
(49, 6, 7, 370, 130),
(50, 6, 8, 250, 100),
(51, 7, 1, 370, 130),
(52, 7, 2, 250, 100),
(53, 7, 3, 370, 130),
(54, 7, 4, 250, 100),
(55, 7, 5, 370, 130),
(56, 7, 6, 600, 150),
(57, 7, 7, 370, 130),
(58, 7, 8, 250, 100),
(59, 8, 1, 370, 130),
(60, 8, 2, 250, 100),
(61, 8, 3, 370, 130),
(62, 8, 4, 250, 100),
(63, 8, 5, 370, 130),
(64, 8, 6, 600, 150),
(65, 8, 7, 370, 130),
(66, 8, 8, 250, 100),
(67, 9, 1, 370, 130),
(68, 9, 2, 250, 100),
(69, 9, 3, 370, 130),
(70, 9, 4, 250, 100),
(71, 9, 5, 370, 130),
(72, 9, 6, 600, 150),
(73, 9, 7, 370, 130),
(74, 9, 8, 250, 100),
(75, 10, 1, 370, 130),
(76, 10, 2, 250, 100),
(77, 10, 3, 370, 130),
(78, 10, 4, 250, 100),
(79, 10, 5, 370, 130),
(80, 10, 6, 600, 150),
(81, 10, 7, 370, 130),
(82, 10, 8, 250, 100),
(83, 11, 1, 370, 130),
(84, 11, 2, 250, 100),
(85, 11, 3, 370, 130),
(86, 11, 4, 250, 100),
(87, 11, 5, 370, 130),
(88, 11, 6, 600, 150),
(89, 11, 7, 370, 130),
(90, 11, 8, 250, 100),
(91, 12, 1, 370, 130),
(92, 12, 2, 250, 100),
(93, 12, 3, 370, 130),
(94, 12, 4, 250, 100),
(95, 12, 5, 370, 130),
(96, 12, 6, 600, 150),
(97, 12, 7, 370, 130),
(98, 12, 8, 250, 100),
(99, 13, 1, 370, 130),
(100, 13, 2, 250, 100),
(101, 13, 3, 370, 130),
(102, 13, 4, 250, 100),
(103, 13, 5, 370, 130),
(104, 13, 6, 600, 150),
(105, 13, 7, 370, 130),
(106, 13, 8, 250, 100),
(107, 14, 1, 420, 130),
(108, 14, 2, 300, 100),
(109, 14, 3, 420, 130),
(110, 14, 4, 300, 100),
(111, 14, 5, 420, 130),
(112, 14, 6, 650, 150),
(113, 14, 7, 420, 130),
(114, 14, 8, 300, 100),
(115, 15, 1, 420, 130),
(116, 15, 2, 300, 100),
(117, 15, 3, 420, 130),
(118, 15, 4, 300, 100),
(119, 15, 5, 420, 130),
(120, 15, 6, 650, 150),
(121, 15, 7, 420, 130),
(122, 15, 8, 300, 100),
(123, 16, 1, 470, 130),
(124, 16, 2, 330, 100),
(125, 16, 3, 470, 130),
(126, 16, 4, 330, 100),
(127, 16, 5, 470, 130),
(128, 16, 6, 700, 150),
(129, 16, 7, 470, 130),
(130, 16, 8, 330, 100),
(131, 17, 1, 500, 130),
(132, 17, 2, 350, 100),
(133, 17, 3, 500, 130),
(134, 17, 4, 350, 100),
(135, 17, 5, 500, 130),
(136, 17, 6, 720, 150),
(137, 17, 7, 500, 130),
(138, 17, 8, 350, 100),
(139, 18, 1, 530, 130),
(140, 18, 2, 380, 100),
(141, 18, 3, 530, 130),
(142, 18, 4, 380, 100),
(143, 18, 5, 530, 130),
(144, 18, 6, 750, 150),
(145, 18, 7, 530, 130),
(146, 18, 8, 380, 100),
(147, 19, 1, 630, 130),
(148, 19, 2, 450, 100),
(149, 19, 3, 630, 130),
(150, 19, 4, 450, 100),
(151, 19, 5, 630, 130),
(152, 19, 6, 950, 150),
(153, 19, 7, 630, 130),
(154, 19, 8, 450, 100),
(155, 20, 1, 750, 130),
(156, 20, 2, 480, 100),
(157, 20, 3, 750, 130),
(158, 20, 4, 480, 100),
(159, 20, 5, 750, 130),
(160, 20, 6, 1200, 150),
(161, 20, 7, 750, 130),
(162, 20, 8, 480, 100),
(163, 21, 1, 750, 130),
(164, 21, 2, 500, 100),
(165, 21, 3, 750, 130),
(166, 21, 4, 500, 100),
(167, 21, 5, 750, 130),
(168, 21, 6, 1300, 150),
(169, 21, 7, 750, 130),
(170, 21, 8, 500, 100),
(173, 3, 10, 370, 130),
(174, 4, 10, 370, 130),
(175, 5, 10, 370, 130),
(176, 6, 10, 370, 130),
(177, 7, 10, 370, 130),
(178, 8, 10, 370, 130),
(179, 9, 10, 370, 130),
(180, 10, 10, 370, 130),
(181, 11, 10, 370, 130),
(182, 12, 10, 370, 130),
(183, 13, 10, 370, 130),
(184, 14, 10, 420, 130),
(185, 15, 10, 420, 130),
(186, 16, 10, 470, 130),
(187, 17, 10, 500, 130),
(188, 18, 10, 530, 130),
(189, 19, 10, 630, 130),
(190, 20, 10, 750, 130),
(191, 21, 10, 750, 130),
(192, 25, 1, 580, 130),
(193, 25, 2, 400, 100),
(194, 25, 3, 580, 130),
(195, 25, 4, 400, 100),
(196, 25, 5, 580, 130),
(197, 25, 6, 800, 150),
(198, 25, 7, 400, 130),
(199, 25, 8, 580, 100),
(200, 25, 10, 400, 130),

(1139, 26, 1, 530, 130),
(1140, 26, 2, 380, 100),
(1141, 26, 3, 530, 130),
(1142, 26, 4, 380, 100),
(1143, 26, 5, 530, 130),
(1144, 26, 6, 750, 150),
(1145, 26, 7, 530, 130),
(1146, 26, 8, 380, 100),
(1188, 26, 10, 530, 130),

(2139, 27, 1, 530, 130),
(2140, 27, 2, 380, 100),
(2141, 27, 3, 530, 130),
(2142, 27, 4, 380, 100),
(2143, 27, 5, 530, 130),
(2144, 27, 6, 750, 150),
(2145, 27, 7, 530, 130),
(2146, 27, 8, 380, 100),
(2188, 27, 10, 530, 130),

(3139, 28, 1, 530, 130),
(3140, 28, 2, 380, 100),
(3141, 28, 3, 530, 130),
(3142, 28, 4, 380, 100),
(3143, 28, 5, 530, 130),
(3144, 28, 6, 750, 150),
(3145, 28, 7, 530, 130),
(3146, 28, 8, 380, 100),
(3188, 28, 10, 530, 130);";

    $wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS $table_orders (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `state` varchar(10) NOT NULL,
              `mail_sent` tinyint(4) NOT NULL,
              `date` date NOT NULL,
              `additional_place` tinyint(4) NOT NULL,
              `clientID` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    
    $wpdb->query($sql);
}

function bron_uninstall(){
    global $wpdb;
    
    wp_clear_scheduled_hook('cron_event'); 
    
    $table_numbers = $wpdb->prefix.get_option('table_numbers');
    $table_busy = $wpdb->prefix.get_option('table_busy');
    $table_orders = $wpdb->prefix.get_option('table_orders');
    $table_clients = $wpdb->prefix.get_option('table_clients');
    $table_diapasones = $wpdb->prefix.get_option('table_diapasones');
    $table_numbers2diapasones = $wpdb->prefix.get_option('table_numbers2diapasones');
    $table_number_groups = $wpdb->prefix.get_option('table_number_groups');
    
    delete_option('bron_status_url');
    delete_option('table_numbers');
    delete_option('table_busy');
    delete_option('table_orders');
    delete_option('table_clients');
    delete_option('table_diapasones');
    delete_option('table_numbers2diapasones');
    delete_option('table_number_groups');
    
    delete_option('maintext');
    delete_option('inostext');
    delete_option('ukrtext');
        
    $sql = "DROP TABLE IF EXISTS $table_numbers";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE IF EXISTS $table_busy";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_orders";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_clients";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_diapasones";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE IF EXISTS $table_numbers2diapasones";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_number_groups";
    $wpdb->query($sql);
}

function cron_event_proc(){
    global $wpdb;
    $tbl_or = $wpdb->prefix.get_option('table_orders');
    $tbl_cl = $wpdb->prefix.get_option('table_clients');
    $orders = $wpdb->get_results("SELECT * FROM $tbl_or, $tbl_cl WHERE `$tbl_or`.`clientID` = `$tbl_cl`.`id`");
    foreach($orders as $order){
        if(($order->state == 'create')&&($order->date - Date('Y-m-d') > 3)){
            $tbl_or = $wpdb->prefix.get_option('table_orders');
            $wpdb->update(
                $tbl_or,
                array('state' => 'remindsend'),
                array('id' => $_GET['order_id']));
            $headers = 'From: Гостиница Москва <info@hotel-alushta.com.ua>' . "\r\n";
            wp_mail($order->mail, 'Напоминание о заказе', "Ваш заказ в гостинице 'Москва' все еще не оплачен", $headers);
        }
    }
}

function bind_files(){
    wp_enqueue_script( 'script',  plugins_url( '/script.js', __FILE__ ));
    wp_enqueue_style( 'style',  plugins_url( '/style.css', __FILE__ ));
}

register_activation_hook(__FILE__, 'bron_install');
register_deactivation_hook(__FILE__, 'bron_uninstall');

add_action('admin_menu', 'bron_admin_init');
add_action('init', 'bron_user_init');
add_action('cron_event', 'cron_event_proc');
add_action('wp_enqueue_scripts', 'bind_files' ); 
?>