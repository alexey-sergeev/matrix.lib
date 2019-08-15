<?php

//
// Класс для работы с описанием образовательных программ
// А. Н. Сергеев, Волгоград
// Июль 2019
// 

class parser {

    //
    // Инициализация 
    //

    function __construct()
    {
    }



    function get_arr( $text, $args = array() )
    {
        $out = array();
      
        // Установить параметры

        $r = array(
            'section' => 'default', // Название секции, куда сохраняются данные
            'att_name' => false,     // Искать ли атрибуте в имени? 
                                    // Полезно, когда имя может заканчиваться на скобки, но это не атрибут
            'att_parts' => true,    // Искать ли атрибуты в заголовках разделов?
            'nomarker' => false,    // Отсутствуют ли маркеры в начале заголовков? 
                                    // Если нет, то каждая строка считается заголовком.
                                    // Полезно при анализе простых списков дисциплин и их атрибутов
            'default' => false,     // Учитывать ли данные без заголовка или в пустых заголовках? 
                                    // Если да, то это относится к секции __default__
        );

        extract( $r );
        extract( $args );

        // Разбить текст на массив строк
        $arr = preg_split( '/\\r\\n?|\\n/', $text );
        $arr = array_map( array( $this, 'strim' ), $arr );
        
        // Удалить комментарии
        foreach ( $arr as $n => $item ) if ( preg_match( '/^#/', $arr[$n] ) ) unset( $arr[$n] );

        // Добавить маркеры, если их нет
        if ( $nomarker ) foreach ( $arr as $n => $item ) if ( $arr[$n] ) $arr[$n] = '== ' . $arr[$n];
        
        // Добавить в начало секцию по умолчанию, если это предусмотрено
        
        $arr2 = array_diff( $arr, array( '' ) );
        if ( $default && preg_match( "/^==/", array_shift( $arr2 ) ) == false ) $arr = array_merge( array( "== " . $this->default_name ), $arr );

        // Разобрать массив строк на части
        $key = NULL;
        $subkey = NULL;
        $param = '';

        foreach ( $arr as $item ) {

            // Началось описание новой дисциплины (раздела)
            // Взять название и атрибуты

            if ( preg_match( '/^==/', $item ) ) {

                $name_data = $this->parse_name( $item, $att_name, $default );
                $key = mb_strtoupper( $name_data['name'] );
                $out[$key][$section] = $name_data;
                $subkey = NULL;
                continue;

            }

            // Пока еще нет названия дисциплины (раздела)
            // Формировать общее описание (параметры)
            
            if ( ! $key ) {

                $param .= $item . "\n";
                continue;

            }

            // Началось описание нового подраздела
            // Взять название и атрибуты

            if ( preg_match( '/^=/', $item ) ) {

                $name_data = $this->parse_name( $item, $att_parts );
                $subkey = mb_strtoupper( $name_data['name'] );
                $out[$key][$section]['parts'][$subkey] = $name_data;
                continue;

            }

            // Если нет подраздела (но учитывая, что есть дисциплина (раздел))
            // Формировать данные раздела

            if ( ! $subkey ) {

                // if ( !isset( $out[$key][$section]['data'] ) ) $out[$key][$section]['data'] = '';
                $out[$key][$section]['data'] .= $item . "\n";
                continue;

            }

            // Есть подраздел
            // Формировать его данные

            // if ( ! isset( $out[$key][$section][$parts][$subkey]['data'] ) ) $out[$key][$section][$parts][$subkey]['data'] = '';
            $out[$key][$section]['parts'][$subkey]['data'] .= $item . "\n";
            
        }

        // Заменить текстовые блоки на массивы

        foreach ( $out as $key => $item ) {

            $out[$key][$section]['data'] = $this->parse_content( $out[$key][$section]['data'] );
            if ( empty( $out[$key][$section]['data'] ) ) unset( $out[$key][$section]['data'] );

            if ( ! isset( $out[$key][$section]['parts'] ) ) continue;

            foreach ( $out[$key][$section]['parts'] as $key2 => $item2 ) {
                
                $out[$key][$section]['parts'][$key2]['data'] = $this->parse_content( $out[$key][$section]['parts'][$key2]['data'] );
                // if ( empty( $out[$key][$section]['parts'][$key2]['data'] ) ) unset( $out[$key][$section]['parts'][$key2]['data'] );
                
            } 
            

        }



        // p( $arr );
        return $out;
    }
    

    // 
    // Извлекает имя и атрибуты из строки заголовка
    //
    //  Примеры таких строк: 
    //      == Информационные технологии
    //      == Алгебра (ОК-1)
    //      == Геометрия (2, 18, 0, 18, 36) (3, 18, 0, 36, 36)
    //      = Раздел 1 (18, 0, 18)
    //      = Раздел 2 (ОПК-2-4)
    // 
    // Отделить скобки в имени от параметров можно любой строкой, либо точкой (она удаляется):
    //      == Педагогическая (воспитательная) практика (ПК-1)
    //      == Учебная практика (пленер). (ПК-3)
    // 

    private function parse_name( $name, $att_exist = true, $default = false )
    {
        $att = array();

        $name = $this->strim( $name ); 

        if ( $att_exist ) {

            $s = $name;
            $name_raw = $name;

            while ( preg_match( "/\)$/", $s ) ) {

                // Пока строка заканчивается ) справа ...

                // Удалить ) справа
                $s = preg_replace( "/\)$/", "", $s );

                // Искать парную ей (

                while ( ! preg_match( "/\($/", $s ) ) {

                    // Удалить справа всякие символы, которые не скобки
                    $s = preg_replace( "/[^()]*$/", "", $s );
                    
                    // Удалить справка скобки, но если они парные и внутри нет скобок
                    $s = preg_replace( "/\([^()]*\)$/", "", $s );
                    
                    // Выйти, если строка уже пустая
                    if ( $s === '' ) break;
                    
                }
        
                if ( $s === '' ) break;

                // Удалить справа ( и пробелы
                $s = preg_replace( "/\($/", "", $s );
                $s = trim ( $s );

            }

            // Если что-то осталось, то это имя
            if ( $s ) $name = $s;

            // Строка, где искать атрибуты
            $a = trim( str_replace( $name, '', $name_raw ) );

            // Убрать пробелы между ) и (
            $a = preg_replace( "/\) *\(/", ")(", $a );

            // Дополнить до )( скобки в начале и в конце
            $a = ')' . $a . '(';

            // Разложить массив по )(
            $att = explode( ")(", $a );
    
            // Навести порядок в массиве атрибутов
            
            $att = array_map( 'trim', $att );
            $att = array_values( array_diff( $att, array( '' ) ) );
            
        }
        
        // Навести порядок в имени дисциплины

        $name = trim( $name, '=. ' );
    
        // // ***
        // // Другой вариант алгоритма выше.
        // // Здесь всё здорово, но проблемы, когда скобки внутри скобок.

        // if ( $att_exist ) {

            // // Последовательно ищем одну запись в круглых скобках в самом конце строки.
        
            // $regex = '/\([^()]*\)$/';

            // while ( preg_match( $regex, $name, $data ) ) {
                
            //     $att[] = trim( $data[0], '()' );
            //     $name = trim( preg_replace( $regex, '', $name ) );
                
            // }

            // $att = array_reverse( $att );
            
        // }

        // // ***

        if ( $default && $name == '' ) $name = $this->default_name;

        $arr = array( 'name' => $name );
        if ( ! empty( $att ) ) $arr['att'] = $att;
        $arr['data'] = '';
        
        return $arr;
    }


    // 
    // Преобразует текстовое описание в массив
    // Элементы массива:
    //      - если текст отделен как минимум одной пустой строкой
    //      - если текст начинается с черточки
    // 
    
    private function parse_content( $text )
    {
        // // Всевозможные черточки тоже считаем началом новых блоков
        // $text = preg_replace( '/\n-/', "\n\n", $text );
        // $text = preg_replace( '/\n–/', "\n\n", $text );
        // $text = preg_replace( '/\n—/', "\n\n", $text );

        // Если много пустых строк, то считаем как одну

        $text = preg_replace( '/\n\n\n+/', "\n\n", $text );

        // Получить массив и навести в нем порядок
        $arr = explode( "\n\n", trim( $text ) );
        // $arr = array_map( array( $this, 'strim'), $arr );
        $arr = array_diff( $arr, array('') );

        return $arr;
    }



    //
    // Удаляет в строке лишние пробелы и всякие табуляции
    //

    private function strim( $str )
    {
        $str = trim( $str );
        $str = preg_replace( '/\s+/', ' ', $str );

        return $str;
    }

    public $default_name = '__default__';

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