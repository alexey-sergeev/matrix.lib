<?php

//
// Класс для работы со списками разработчиков дисциплин
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class authors {

    //
    // Инициализация 
    // На входе - массив или текстовое описание всех дисциплин и авторов
    //

    function __construct( $data )
    {

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'authors', 'att_name' => false ) );
    
        }

        foreach ( $data as $item ) {

            $authors = implode( "\n", $item['authors']['data'] );
            $authors = explode( "\n", $authors );
            $authors = array_map( 'trim', $authors );
            
            $this->authors_arr[$item['authors']['name']]['authors'] = $authors;

        }

    }
    
        

    // 
    // Функция возвращает объединенный список разработчиков
    // $compose - ключ в итоговом массиве (название модуля или ГЭК)
    // 

    function compose( $data, $compose = 'compose' )
    {
        $arr = array();

        if ( ! is_array( $data ) ) $data = explode( "\n", $data );
        $data = array_map( 'trim', $data );
        $data = array_diff( $data, array( '' ) );

        // Простое объединение списков

        $arr[$compose]['authors'] = array();

        foreach ( $data as $name ) {

            if ( empty( $this->authors_arr[$name] ) ) continue;
            $arr[$compose]['authors'] = array_merge( $arr[$compose]['authors'], $this->authors_arr[$name]['authors'] );

        }

        $arr[$compose]['authors'] = array_unique( $arr[$compose]['authors'] );
        @ sort( $arr[$compose]['authors'] );

        return $arr;
    }
    


    // 
    // Функция возвращает массив разработчиков
    // 
    
    function get_arr()
    {
        return $this->authors_arr;
    }
 



    // 
    // Функция возвращает списки разработчиков в виде HTML-таблицы
    // 

    function get_html( $arr = NULL, $full = true )
    {
        $html = '';
        
        $h = new html();

        // Если полный формат - добавляем заголовок таблицы

        if ( $full ) {

            $html .= $h->table_header();
            
        }
        
        $n = 1;
        if ( $arr === NULL ) $arr = $this->authors_arr;

        foreach ( $arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n );

            $row = '';
            $row .= "<p><strong>Разработчики:</strong></p>\n";
            $row .= "<ul>\n";
            
            foreach ( $data['authors'] as $item ) $row .= "<li>$item</li>\n";

            $row .= "</ul>\n";

            $html .= $h->course_data_tr( $row );

            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }




    private $authors_arr = array();

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