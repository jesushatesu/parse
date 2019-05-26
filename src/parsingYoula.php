<?php
/**
 * Created by PhpStorm.
 * User: JesusHatesU
 * Date: 26.05.2019
 * Time: 15:28
 */

namespace jesushatesu;


class parsingYoula
{
    private $__youlaAdsArr;
    public $__fileName;
    public $__html;

    function __construct($_fileName = '')
    {
        $this->initialArr();
        $this->initialFileName($_fileName);
        $this->__html = '';
    }

    function initialFileName($_fileName = 'youlaParsingAds.txt'){
        $this->__fileName = $_fileName;
    }

    function initialArr(){
        fclose(fopen($this->__fileName, 'a+t'));
        $file = fopen($this->__fileName, "a+t") or die();
        flock($file, LOCK_SH);
        $str = fread($file, 1000000);
        flock($file, LOCK_UN);
        fclose($file);

        $this->__youlaAdsArr = json_decode($str, true);
    }

    function getAdsArr(){
        return $this->__youlaAdsArr;
    }



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

    function getFooter(){
        return '</div>
        </body>
        </html>';
    }

    //кастомная мини-фильтрация входного значения, чтобы при разном регистре и дополнительных пробелах не создавалось
    //много лишних записей с одинаковыми объявлениями
    function customTrim($__str)
    {
        $__str = mb_strtolower($__str);
        $__str = preg_replace('/([\S]+)[\s]+/usi', '$1_', $__str);
        return $__str;
    }

    //ищет айди объявления в массиве объявлений
    //$value - айди которое ищем
    //$youlaArr - массив ОБЪЯВЛЕНИЙ, в котором ищем совпадения, не массив значений, а именно объявлений
    //return - index нужного объявления или FALSE, если не найдено
    function in_array_youla($value, array $youlaArr){

        foreach ($youlaArr as $key => $val){
            if ($val ['__id'] == $value)
                return $key;
        }

        return false;
    }

    function isDomainAvailable($page, $val)
    {


        $response = file_get_contents('https://youla.ru/moskva?city=576d0612d53f3d80945f8b5d&page='.$page.'&q='.$val.'&serpId=3757f796e40529');

        return (stristr($response, "Увы, мы&nbsp;не&nbsp;нашли&nbsp;то, что вы&nbsp;искали."))
            ? false
            : $response;
    }
}