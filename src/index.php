<?php
/**
 * Created by PhpStorm.
 * User: JesusHatesU
 * Date: 26.05.2019
 * Time: 16:46
 */


//стили и шапка
function getHead(){
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Parse Youla</title>
        <style>
            * {padding:0px; margin:0px; border-collapse:collapse; font: 20px Italic;}
        </style>
        </head>
        <body>
        <div style=" position: absolute; width: 800px; left: 300px;">';
}

//форма для ввода запроса, подаётся значение для поиска, чтобы не очищалось после нажатия "Найти"
function getInputForm($__val){
    $value = (($__val != '')
            ? 'value="'
            : '').$__val.'"';

    return '<div style=" margin: 5px 5px; padding: 5px;  border: 1px #999 solid; background-color: #ccc; width: 780px;">
        <span>Введите запрос:</span>
        <br />
        <form action="" method="post">
        <input style=" margin: 10px 0; height: 40px; width: 765px; padding: 5px; border: 1px #999 solid;" type="text" name="request" placeholder="Что вы хотите найти?" '.$value.'>
        <input style=" margin: 10px 0; height: 40px; width: 765px; padding: 5px; border: 1px #999 solid;" type="submit" value="Найти">
        </form>
    </div>';
}

//выводит блок одного объявления с информацией (ниже подробнее описано)
function getAd($val){
    return '<div style=" position: relative; width: 780px; margin: 30px auto; padding: 5px; height: 220px; border: 1px #999 solid; background-color: #ccc;">
    	<img style="clear:both;  width: 200px; height: 215px;" src="'.$val['__image'].'" alt="'.$val['__shortDescription'].'" />
        <div style=" float: right; width: 550px;">
            <a href="'.$val['__link'].'" title="'.$val['__shortDescription'].'" >'.$val['__shortDescription'].'</a><br />
            <span>Location: '.$val['__location'].'</span><br />
            <span>Price: '.$val['__cost'].'</span><br />
            <span>Date: '.$val['__date'].'</span>
        </div>
    </div>';
}

//подвальчик
function getFooter(){
    return '</div>
        </body>
        </html>';
}

