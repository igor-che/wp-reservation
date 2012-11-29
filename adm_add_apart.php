<?php
    global $context;
    echo "<h3>Добавление аппартаментов</h3>
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
?>