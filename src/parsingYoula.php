<?php
/**
 * Created by PhpStorm.
 * User: JesusHatesU
 * Date: 26.05.2019
 * Time: 15:28
 */

namespace jesushatesu;

require '../vendor/autoload.php';

class parsingYoula
{
    private $__youlaAdsArr;
    public $__fileName;

    //конструктор: инициализирует имя файла и массив объявлений
    function __construct($_fileName = '')
    {
        $this->initialFileName($_fileName);
        $this->initialArr();
        $this->__html = '';
    }

    //инициализация имени файла
    function initialFileName($_fileName = 'youlaParsingAds.txt'){
        $this->__fileName = $_fileName;
    }

    //инициализация массива объявлений
    function initialArr(){
        fclose(fopen($this->__fileName, 'a+t'));
        $file = fopen($this->__fileName, "a+t") or die();
        flock($file, LOCK_SH);
        $str = fread($file, 1000000);
        flock($file, LOCK_UN);
        fclose($file);

        $this->__youlaAdsArr = json_decode($str, true);
    }

    //записывает в файл массив
    function setArr(){
        $file = fopen($this->__fileName, "w+t") or die();
        flock($file, LOCK_EX);
        fwrite($file, json_encode($this->__youlaAdsArr));
        flock($file, LOCK_UN);
        fclose($file);
    }

    //получение массива объявлений
    function getAdsArr(){
        return $this->__youlaAdsArr;
    }

    //кастомная мини-фильтрация входного значения, чтобы при разном регистре и дополнительных пробелах не создавалось
    //много лишних записей с одинаковыми объявлениями
    function customTrim($__str){
        $__str = mb_strtolower($__str);
        $__str = preg_replace('/([\S]+)[\s]+/usi', '$1_', $__str);
        return $__str;
    }

    //ищет айди объявления в массиве объявлений
    //$value - айди которое ищем
    //$youlaArr - массив ОБЪЯВЛЕНИЙ, в котором ищем совпадения, не массив значений, а именно объявлений
    //return - index нужного объявления или FALSE, если не найдено
    function in_array_youla($value){
        foreach ($this->__youlaAdsArr as $key => $val){
            if ($val ['__id'] == $value)
                return $key;
        }

        return false;
    }

    //подаётся номер страницы и значение, проверяет присутствие объявлений на этой странице
    function isDomainAvailable($page, $val)
    {
        /*
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://youla.ru/moskva?city=576d0612d53f3d80945f8b5d&page='.$page.'&q='.$val.'&serpId=3757f796e40529'
        ]);

        try{
            $response = $client->request('GET', '');
            $statusCode = $response->getStatusCode();
        } catch (\Exception $e){
            $statusCode = 500;
        }

        if ($statusCode >= 400)
            return false;

        return $response->getBody();

        */


        $response = file_get_contents('https://youla.ru/moskva?city=576d0612d53f3d80945f8b5d&page='.$page.'&q='.$val.'&serpId=3757f796e40529');

        return (stristr($response, "Увы, мы&nbsp;не&nbsp;нашли&nbsp;то, что вы&nbsp;искали."))
            ? false
            : $response;
    }

    function parsing(){



    }

}

