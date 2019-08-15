<?php

//
// Класс для отрисовки таблиц с данными ОПОП
// А. Н. Сергеев, Волгоград
// Август 2019
// 

class html {

    //
    // Инициализация 
    //

    function __construct()
    {

    }

    
    // 
    // Вывод заголовка дисциплины
    // 

    function course_name_tr( $name, $n = false, $colspan = '' )
    {
        $html = '';

        if ( $colspan ) $colspan = " colspan=\"$colspan\"";

        $html .= "<tr>\n";
        $html .= "<th>$n</th>\n";
        $html .= "<th$colspan>$name</th>\n";
        $html .= "</tr>\n";

        return $html;
    }

    
    // 
    // Вывод заголовка раздела таблицы
    // 

    function part_tr( $title, $colspan = '' )
    {
        $html = '';

        if ( $colspan ) $colspan = " colspan=\"$colspan\"";

        $html .= "<tr class=\"part_header\">\n";
        $html .= "<th$colspan>$title</th>\n";
        $html .= "</tr>\n";

        return $html;
    }


    // 
    // Вывод содержимого дисциплины
    // 

    function course_data_tr( $data, $class = '', $colspan = '', $n = '' )
    {
        $html = '';

        if ( $class ) $class = " class=\"$class\"";
        if ( $colspan ) $colspan = " colspan=\"$colspan\"";

        $html .= "<tr>\n";
        $html .= "<td$class>$n</td>\n";
        $html .= "<td$class$colspan>\n";
        $html .= $data;
        $html .= "</td>\n";
        $html .= "</tr>\n";

        return $html;
    }

    
    
    // 
    // Вывод шапки таблицы
    // 

    function table_header()
    {
        $html = '';

        $html .= "<table border=\"1\">\n";

        return $html;
    }

    
    
    // 
    // Вывод низа таблицы
    // 

    function table_footer()
    {
        $html = '';

        $html .= "</table>\n";

        return $html;
    }



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