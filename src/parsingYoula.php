<?php
/**
 * Created by PhpStorm.
 * User: JesusHatesU
 * Date: 26.05.2019
 * Time: 15:28
 */

use jesushatesu;

require '../vendor/autoload.php';

class parsingYoula
{
    private $__youlaAdsArr;
    public $__fileName;
    public $__value;
    private $__mysql;

    //конструктор: инициализирует имя файла и массив объявлений
    function __construct($_value, $_mysql, $_fileName = '')
    {
        $this->initialFileName($_fileName);
        $this->initialArr();
        $this->__value = $this->customTrim($_value);
        $this->__mysql = $_mysql;
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
        return $this->__youlaAdsArr[$this->__value];
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

    //производит парсинг всех объявлений на Юле, по заданному значению и заданному массиву
    //$__value - значение, по которому ищутся объявления
    //$__youlaAdsArr - массив, который формируется из файла с предыдущими результатами поиска по ВСЕМ значениям (может быть пустым)
    //в него будут добавляться новые объявления, которые были добавлены после последнего парсинга (при этом они будут выводиться сверху)
    //старые объявления (которые были найдены при предыдущих парсингах) остаются нетронутыми
    //$return - HTML-код в виде блоков, в которых есть превью-картинка товара, краткое описание, стоимость, дата публикации,
    //месторасположение (город) и ссылка на само объявление, при этом оригинальное объявление могло быть удалено, но ссылка
    //останется, чтобы посмотреть в БД всех объявлений на сторонних ресурсах
    function parsing(){
        $count = 1;                                         //счётчик по страницам
        $arr = [];                                          //массив для парсинга

        //регулярка, которая парсит одну страницу
        {
            $basicPattern = '/(
            <li \s* 
            class=\"product_item\" \s* 
            data-id=\"(?<youla_id>.+?)\" .*? 
            <a \s* href=\"(?<youla_ad_link>.*?)\" \s*
            title=\"(?<youla_title>.*?)\" .*?
            <span \s* class=\"gallery_counter__value\">(?<count_images>\d+) < .*?
            <img \s* src=\"(?<image_link> .*? )\" .*?
            <\/div [a-z \s = \" _ < >]* > \s* (?<location> [а-я А-Я ё Ё]+)  .*?
            <div \s* class=\"product_item__description \s .*? \"> \s*
            (?<cost> [Бесплатно \d \s]+) .*?
            <span \s* class=\"(?<currency> \w+)\" .*?
            <span \s* class=\"hidden-xs\">  (?<time> .*?) <\/span> \s*
            <span \s* class=\"visible-xs\"> \s* (?<date> \d{1,2} \. \d{1,2} \. \d{2,4} ) <\/span>
            )/usix';
        }

        //цикл по страницам поиска
        while (($html = $this->isDomainAvailable($count, $this->__value)) !== false) {

            //парсинг
            preg_match_all($basicPattern, $html, $arr);

            //ликвидируем лишние карманы, необязательно, но удобно для дебагинга через принты
            $countOfArr = count($arr);
            for ($j = 0; $j < $countOfArr; $j++) {
                if (isset($arr[$j])) {
                    unset($arr[$j]);
                }
            }

            //заполняем массив объектов и накапливаем результат в переменную
            foreach ($arr['youla_id'] as $key => $val) {

                //если не существовало записей по этому значению, то добавлять без вопросов
                //или если не было записей именно по этому результату (по id объявления)
                if (is_array($this->__youlaAdsArr[$this->__value]) === false || ($index = $this->in_array_youla($val)) === false){

                    //обрезаем лишние символы, которые добавляет сама Юла
                    $arr['youla_title'] [$key] = mb_substr($arr['youla_title'] [$key], 0, -11);

                    //добавляем в "неполную" ссылку объявления протокол и домены
                    $arr['youla_ad_link'][$key] = 'https://youla.ru' . $arr['youla_ad_link'][$key];

                    //обрезаем пробелы внутри цены
                    $arr['cost'][$key] = preg_replace('/[^\d]+/', '', $arr['cost'][$key]);

                    //заполняем массив объявления
                    $youlaAd ['__link'] = $arr['youla_ad_link'][$key];
                    $youlaAd ['__location'] = $arr['location'][$key];
                    $youlaAd ['__cost'] = $arr['cost'][$key] . ' ' . $arr['currency'][$key];
                    $youlaAd ['__date'] = $arr['date'][$key];
                    $youlaAd ['__shortDescription'] = $arr['youla_title'][$key];
                    $youlaAd ['__image'] = $arr['image_link'][$key];
                    $youlaAd ['__id'] = $arr['youla_id'][$key];

                    //добавляем объявление в общий массив объявлений по данному значению
                    $this->__youlaAdsArr[$this->__value] [] = $youlaAd;

                    //как вы хотели - MYSQL
                    mysqli_query($this->__mysql, 'INSERT INTO "ad"("link", "id", "img", "location", "cost", "date", "shortDescription") VALUES ('.$youlaAd ['__link'].','.$youlaAd ['__id'].','.$youlaAd ['__image'].','.$youlaAd ['__location'].','.$youlaAd ['__cost'].','.$youlaAd ['__date'].','.$youlaAd ['__shortDescription'].')');
                }

            }

            //некст степ
            $arr = [];
            $count++;
        }

        //записываем в файл обновленный массив
        $this->setArr();
    }

}

