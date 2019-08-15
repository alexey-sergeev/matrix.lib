<?php

//
// Класс для работы с индексами компетенций
// А. Н. Сергеев, Волгоград
// Июль 2019
// 

class cmp {

    //
    // Инициализация 
    // В качестве параметра можно передать массив или строку в принципе допустимых компетенций
    // Если параметра нет, то все компетенеции допустимы
    //

    function __construct( $cmp = NULL )
    {
        // Инициализация массива допустимых компетенеций
        $this->acceptable_cmp_arr = $this->get_arr( $cmp );

    }


    //
    // Получить чистый набор компетенций
    //
    // На входе ($cmp) может быть:
    //    -- подробная или свернутая строка компетенций
    //    -- массив компетенций
    //    -- массив строк компетенций
    //    -- мусор (он игнорируется)
    // 
    // Пример строки: ОК-1, ОПК-2, ПК-3-5
    // Разделители - запятые, точки с запятыми, пробелы, переводы строк
    // Черточки - обычные, средние и длинные
    // 
    // Стиль вывода ($out):
    //     -- arr или a - массив 
    //     -- full или f - подробное перечисление компетенций 
    //     -- quick или q - свернутое перечисление компетенций (по умолчанию)
    //
    // При инициализации объекта можно задать массив допустимых компетенций. 
    // Если он задан, то все остальные игнорируются
    //

    function get_cmp( $cmp, $out = 'quick' )
    {
        if ( $out == 'arr' || $out == 'a' ) return $this->get_arr( $cmp );
        
        return $this->get_str( $cmp, $out );
    }


    //
    // Объединение двух наборов компетенций
    //
    // Пример:
    //      строка 1 - ОК-1, ОК-2, ОПК-1, ПК-5
    //      строка 2 - ОК-2, ОПК-4, ПК-5
    // 
    //      результат - ОК-1-2, ОПК-1, ОПК-4, ПК-5
    //     

    function union( $cmp1, $cmp2, $out = 'q' )
    {
        $arr1 = $this->get_arr( $cmp1 );
        $arr2 = $this->get_arr( $cmp2 );

        $arr3 = array_merge( $arr1, $arr2 );

        return $this->get_cmp( $arr3, $out );
    }


    //
    // Пересечение двух наборов компетенций
    //
    // Пример:
    //      строка 1 - ОК-1, ОК-2, ОПК-1, ПК-5
    //      строка 2 - ОК-2, ОПК-4, ПК-5
    // 
    //      результат - ОК-2, ПК-5
    //     

    function intersection( $cmp1, $cmp2, $out = 'q' )
    {
        $arr1 = $this->get_arr( $cmp1 );
        $arr2 = $this->get_arr( $cmp2 );

        $arr3 = array_intersect( $arr1, $arr2 );

        return $this->get_cmp( $arr3, $out );
    }


    //
    // Разность двух наборов компетенций
    //
    // Пример:
    //      строка 1 - ОК-1, ОК-2, ОПК-1, ПК-5
    //      строка 2 - ОК-2, ОПК-4, ПК-5
    // 
    //      результат - ОК-1, ОПК-1
    //     

    function difference( $cmp1, $cmp2, $out = 'q' )
    {
        $arr1 = $this->get_arr( $cmp1 );
        $arr2 = $this->get_arr( $cmp2 );

        $arr3 = array_diff( $arr1, $arr2 );

        return $this->get_cmp( $arr3, $out );
    }




    // 
    // Приватные сервисные функции
    // 


    //
    // Получить массив компетенций из исходных данных
    // 

    private function get_arr( $cmp )
    {
        // Если передан массив, то сделать длинную строку
        $cmp = implode( ' ', (array) $cmp );

        // Разные разделители компетенций заменить одним пробелом
        $cmp = preg_replace ( "/\s/", " ", $cmp );
        $cmp = preg_replace ( "/[,;]/", " ", $cmp );
        $cmp = preg_replace ( "/  /", " ", $cmp );
        
        // Привести в порядок тире
        $cmp = preg_replace ( "/–/", "-", $cmp );
        $cmp = preg_replace ( "/—/", "-", $cmp );
        
        // Провести очистку букв
        $cmp = mb_strtoupper( $cmp );
        $map = $this->get_letter_map();
        $cmp = preg_replace ( array_keys( $map ), $map, $cmp );

        // Снова сделать массив
        // В этом массиве будут обычные компетенции, строки свернутых компетенций и мусор
        $cmp = explode( " ", $cmp );
        
        // Сформировать массив

        $arr = array();

        foreach ( (array) $cmp as $item ) {

            // Любое количество букв, тире, любое количество цифр 
            // - обычная компетенция (взять ее)
            if ( preg_match( "/^[А-Я]+-\d+$/", $item ) ) $arr[] = $item;

            // Любое количество букв, тире, любое количество цифр, тире, любое количество цифр 
            // - свернутое описание серии компетенций (взять каждую компетенцию серии)
            if ( preg_match( "/^[А-Я]+-\d+-\d+$/", $item ) ) {
             
                $a = explode( "-", $item );
                for ( $i=$a[1]; $i<= $a[2]; $i++ ) $arr[] = $a[0] . '-' . $i;

            }

        }

        // Удалить повторы, недопустимые компетенеции и сортировать
        $arr = array_unique( $arr );
        if ( ! empty( $this->acceptable_cmp_arr ) ) $arr = array_intersect( $arr, $this->acceptable_cmp_arr );
        $arr = $this->sort_cmp( $arr );

        return $arr;
    }


    //
    // Получить чистую строку компетенций
    //

    private function get_str( $cmp, $out = 'quick' )
    {
        $arr = $this->get_arr( $cmp );

        // Если нужна подробная строка, то сразу и вернуть

        if ( $out == 'full' || $out == 'f' ) return implode( ", ", $arr );

        // Если нужна компактная строка, то вычислить ее

        // Построить индекс
        $index = array();
        foreach ( $arr as $item ) {
            
            $a = explode( "-", $item );
            $index[$a[0]][] = $a[1];
            
        }
        
        // По индексу вычислить массив отрезков компетенций
        $arr2 = array();
        foreach ( $index as $key => $item ) {

            $n = $item[0];

            foreach ( $item as $k => $i ) {

                if ( ! isset( $item[$k+1] ) || $item[$k+1] != $i + 1 ) {

                    $arr2[] = ( $n == $i ) ? $key . '-' . $n : $key . '-' . $n . '-' . $i;
                    if ( isset( $item[$k+1] ) ) $n = $item[$k+1];

                }

            };

        };
        
        return implode( ", ", $arr2 );
    }


    // 
    // Сортирует массив компетенций
    // 

    private function sort_cmp( $arr )
    {
        // Построить индекс компетенеций
        
        $index = array();
        
        foreach ( $arr as $item ) {
            
            $a = explode( "-", $item );
            $index[$a[0]][] = $a[1];
            
        }
        
        // Ключи компетенций, которые в принципе встречаются
        $keys = array_keys( $index );
        sort( $keys );
        
        // Порядок сортировки   
        $order = $this->get_sort_order();
        $order = array_intersect( $order, $keys );
        $keys = array_diff( $keys, $order );
        $order = array_merge( $order, $keys );

        // Сформировать итог
    
        $arr2 = array();

        foreach ( $order as $key ) {

            $a = $index[$key];
            sort( $a );

            foreach ( $a as $i ) $arr2[] = $key . '-' . $i;

        }

        return $arr2;
    }


    // 
    // Задает порядок сортировки компетенций
    // Если компетенции имеют другие индексы, то они добавляются после этих в алфавитном порядке
    // 

    private function get_sort_order()
    {
        $order = array(
            'УК',
            'ОК',
            'ОПК',
            'ПК',
            'ПСК',
            'СК',
        );

        return $order;
    }


    // 
    // Задает массив соответсвия английских и русских букв
    // 

    private function get_letter_map()
    {
        $map = array(
            '/E/' => 'Е',
            '/T/' => 'Т',
            '/Y/' => 'У',
            '/O/' => 'О',
            '/P/' => 'Р',
            '/A/' => 'А',
            '/D/' => 'Д',
            '/H/' => 'Н',
            '/K/' => 'К',
            '/X/' => 'Х',
            '/C/' => 'С',
            '/B/' => 'В',
            '/N/' => 'Н',
            '/M/' => 'М',
        );

        return $map;
    }


    // 
    // Свойства класса
    // 

    private $acceptable_cmp_arr = array();

}



if ( ! function_exists( "p") ) {
    
    function p( $t )
    {
        print_r( "<pre>" );
        print_r( $t );
        print_r( "</pre>" );
    }

}



?>