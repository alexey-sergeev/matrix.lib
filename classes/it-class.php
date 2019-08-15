<?php

//
// Класс для работы со списками интернет-источников и ПО
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class it {

    //
    // Инициализация 
    // На входе - массив дисциплин ОПОП или текстовое описание дисциплин, интернет-источники и ПО
    //

    function __construct( $data )
    {

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'it' ) );
    
        }
    
        foreach ( $data as $item ) {

            $this->it_arr[$item['it']['name']]['inet'] = $this->get_inet( $item['it']['data'] );
            $this->it_arr[$item['it']['name']]['app'] = $this->get_app( $item['it']['data'] );

        }


    }



    // 
    // Вернуть массив интернет-источников
    // 

    private function get_inet( $data )
    {
        // Интернет-источники - в первом элементе массива

        $arr = explode( "\n", $data[0] );
        $arr = array_map( 'trim', $arr );
        $arr = array_unique( $arr );

        return $arr;
    }



    // 
    // Вернуть массив программного обеспечения
    // 

    private function get_app( $data )
    {
        // Программное обеспечение выбирается из всех элементов массива, кроме первого

        unset( $data[0] );
        $data = implode( "\n", $data );
        $arr = explode( "\n", $data );

        $arr = array_map( 'trim', $arr );

        return $arr;
    }
    

    
    // 
    // Функция возвращает массив списков литературы
    // 
    
    function get_arr()
    {
        return $this->it_arr;
    }


    
    // 
    // Функция возвращает список всех интетнет-источников или ПО
    // 
    
    function get_all( $key = 'inet' )
    {
        $arr = array();

        foreach ( $this->it_arr as $item )
        {
            $arr = array_merge( $arr, $item[$key] );
        }

        $arr = array_unique( $arr );
        sort( $arr );

        return $arr;
    }
        

    // 
    // Функция возвращает объединенный список интернет-источников и ПО по серии дисциплин
    // $compose - ключ в итоговом массиве (название модуля или ГЭК)
    // 

    function compose( $data, $compose = 'compose' )
    {
        $arr = array();

        if ( ! is_array( $data ) ) $data = explode( "\n", $data );
        $data = array_map( 'trim', $data );
        $data = array_diff( $data, array( '' ) );

        // Простое объединение списков
        // Объединить парами списки интернет-источников и ПО. Удалить повторы.

        $arr[$compose]['inet'] = array();
        $arr[$compose]['app'] = array();

        foreach ( $data as $name ) {

            if ( empty( $this->it_arr[$name] ) ) continue;

            $arr[$compose]['inet'] = array_merge( $arr[$compose]['inet'], $this->it_arr[$name]['inet'] );
            $arr[$compose]['app'] = array_merge( $arr[$compose]['app'], $this->it_arr[$name]['app'] );

        }

        $arr[$compose]['inet'] = array_unique( $arr[$compose]['inet'] );
        $arr[$compose]['app'] = array_unique( $arr[$compose]['app'] );

        @ sort( $arr[$compose]['inet'] );
        @ sort( $arr[$compose]['app'] );

        return $arr;
    }



    // 
    // Функция возвращает ссылки и ПО в виде HTML-таблицы
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

        if ( $arr === NULL ) $arr = $this->it_arr;

        foreach ( $arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n );

            $row = '';

            $row .= "<p><strong>Интернет-источники:</strong></p>\n";

            $row .= "<ol>\n";
            $row .= $this->get_list( $data['inet'] );
            $row .= "</ol>\n";

            $row .= "<p><strong>Программное обеспечение:</strong></p>\n";
            
            $row .= "<ol>\n";
            $row .= $this->get_list( $data['app'] );
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



    private $it_arr = array();

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