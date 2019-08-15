<?php

//
// Класс для работы со списками литературы
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class biblio {

    //
    // Инициализация 
    // На входе - массив дисциплин ОПОП или текстовое описание дисциплин и закрепленная литература
    //

    function __construct( $data )
    {

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'biblio' ) );
    
        }
    
        foreach ( $data as $item ) {

            $this->biblio_arr[$item['biblio']['name']]['basic'] = $this->get_basic( $item['biblio']['data'] );
            $this->biblio_arr[$item['biblio']['name']]['additional'] = $this->get_additional( $item['biblio']['data'] );

        }


    }



    // 
    // Вернуть массив базовой литературы
    // 

    private function get_basic( $data )
    {
        // Основная - та, которая в первом элементе массива

        $arr = explode( "\n", $data[0] );
        $arr = array_map( 'trim', $arr );
        $arr = array_unique( $arr );

        return $arr;
    }



    // 
    // Вернуть массив дополнительной литературы
    // 

    private function get_additional( $data )
    {
        $basic = $this->get_basic(  $data  );

        // Дополнительная - та, которая выбирается из всех элементов массива, кроме первого

        unset( $data[0] );
        $data = implode( "\n", $data );
        $arr = explode( "\n", $data );

        $arr = array_map( 'trim', $arr );

        // Удалить из дополнительной те книги, которые уже указаны в базовой

        $arr = array_diff( $arr, $basic );

        return $arr;
    }
    

    
    // 
    // Функция возвращает массив списков литературы
    // 
    
    function get_arr()
    {
        return $this->biblio_arr;
    }


    
    // 
    // Функция возвращает список всех книг
    // 
    
    function get_all()
    {
        $arr = array();

        foreach ( $this->biblio_arr as $item )
        {
            $arr = array_merge( $arr, $item['basic'] );
            $arr = array_merge( $arr, $item['additional'] );
        }

        $arr = array_unique( $arr );
        sort( $arr );

        return $arr;
    }
        

    // 
    // Функция возвращает объединенный список литературы
    // 
    // $compose - ключ в итоговом массиве (название модуля или ГЭК)
    // 
    // $method =    'strict' - простое объединение списков
    //              'balanced' - сбалансированное объединение
    // 

    function compose( $data, $compose = 'compose', $method = 'strict' )
    {
        $arr = array();

        if ( ! is_array( $data ) ) $data = explode( "\n", $data );
        $data = array_map( 'trim', $data );
        $data = array_diff( $data, array( '' ) );

        if ( $method == 'balanced' ) {

            // Сбалансированное объединение. Книга попадает в основную литературу, если чаще встречается в основной

            $index = array();

            foreach ( $data as $name ) {

                if ( empty( $this->biblio_arr[$name] ) ) continue;

                foreach ( $this->biblio_arr[$name]['basic'] as $item ) $index[$item]['basic'] = ( isset( $index[$item]['basic'] ) ) ? ++ $index[$item]['basic'] : 1;
                foreach ( $this->biblio_arr[$name]['additional'] as $item ) $index[$item]['additional'] = ( isset( $index[$item]['additional'] ) ) ? ++ $index[$item]['additional'] : 1;

            }
            
            foreach ( $index as $item => $frequency ) {

                $basic = ( isset( $frequency['basic'] ) ) ? $frequency['basic'] : 0;
                $additional = ( isset( $frequency['additional'] ) ) ? $frequency['additional'] : 0;

                $key = ( $basic >= $additional ) ? 'basic': 'additional';
                $arr[$compose][$key][] = $item;

            }

        } else {

            // Простое объединение списков литературы
            // Объединить парами списки основных источников и списки дополнительных. Удалить повторы.

            $arr[$compose]['basic'] = array();
            $arr[$compose]['additional'] = array();

            foreach ( $data as $name ) {

                if ( empty( $this->biblio_arr[$name] ) ) continue;

                $arr[$compose]['basic'] = array_merge( $arr[$compose]['basic'], $this->biblio_arr[$name]['basic'] );
                $arr[$compose]['additional'] = array_merge( $arr[$compose]['additional'], $this->biblio_arr[$name]['additional'] );

            }

            $arr[$compose]['basic'] = array_unique( $arr[$compose]['basic'] );
            $arr[$compose]['additional'] = array_unique( $arr[$compose]['additional'] );

            $arr[$compose]['additional'] = array_diff( $arr[$compose]['additional'], $arr[$compose]['basic'] );

        }
        
        @ sort( $arr[$compose]['basic'] );
        @ sort( $arr[$compose]['additional'] );

        return $arr;
    }



    // 
    // Функция возвращает списки литературы в виде HTML-таблицы
    // 

    function get_html( $arr = NULL, $full = true )
    {
        $html = '';
        
        $h = new html();

        // Если полный формат - добавляем заголовок таблицы

        if ( $full ) {

            $html .= $h->table_header();
            // $html .= $this->get_html_header();
            
        }
        
        $n = 1;

        if ( $arr === NULL ) $arr = $this->biblio_arr;

        foreach ( $arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n );

            $row = '';

            $row .= "<p><strong>Основная литература:</strong></p>\n";

            $row .= "<ol>\n";
            $row .= $this->get_list( $data['basic'] );
            $row .= "</ol>\n";

            $row .= "<p><strong>Дополнительная литература:</strong></p>\n";
            
            $row .= "<ol>\n";
            $row .= $this->get_list( $data['additional'] );
            $row .= "</ol>\n";

            $html .= $h->course_data_tr( $row );

            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }

    
    // 
    // Возвращает оформленный HTML-список литературы
    // 

    private function get_list( $arr )
    {
        $out = '';
        
        foreach ( (array) $arr as $item ) {

            $pattern = "/(https?:\/\/)([\w\.\=\+\&\?\-\/]+)/i";
            $replacement = '<a href="\\1\\2">\\1\\2</a>';
            $item = preg_replace( $pattern, $replacement, $item );

            $out .= "<li>$item</li>\n";

        }

        return $out;
    }



    private $biblio_arr = array();

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