<?php

//
// Класс для работы с матрицей компетенеций
// А. Н. Сергеев, Волгоград
// Июль 2019
// 

include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/cmp-class.php';

class matrix {

    //
    // Инициализация 
    // На входе - массив дисциплин ОПОП или текстовое описание дисциплин и компетенеций
    // Параметр $cmp - перечень в принципе допустимых компетенций
    //

    function __construct( $data, $cmp = '' )
    {
        // Запомнить перечень допустимых компетенеций

        $this->acceptable_cmp = $cmp;

        // Если данные - в виде текстового описания, то оформить в виде массива

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'competence', 'nomarker' => true, 'att_name' => true ) );
    
        }
    
        // Построить массив матрицы компетенций

        $c = new cmp( $this->acceptable_cmp );
    
        foreach ( $data as $item ) {
    
            if ( empty( $item['competence']['att'] ) ) continue;
            $this->matrix_arr[$item['competence']['name']] = $c->get_cmp( $item['competence']['att'][0], 'arr' );
    
        }

    }


    // 
    // Функция возвращает массив матрицы компетенций
    // 

    function get_arr()
    {
        return $this->matrix_arr;
    }


    // 
    // Функция возвращает все возможные компетенции
    // 

    function get_cmp()
    {
        $arr = array();
        $arr[] = $this->acceptable_cmp;
        foreach ( $this->matrix_arr as $item ) $arr = array_merge( $arr, $item );

        $c = new cmp();
        $arr2 = $c->get_cmp( $arr, 'arr' );

        return $arr2;
    }


    // 
    // Функция возвращает статистику компетенций
    // 

    function get_cmp_stat()
    {
        $stat = array();
        $cmp = $this->get_cmp();

        foreach ( $cmp as $item ) $stat[$item] = 0;

        foreach ( $this->matrix_arr as $line ) 
        foreach ( $line as $item ) $stat[$item]++;
        
        return $stat;
    }


    // 
    // Функция возвращает матрицу компетенций в виде HTML-таблицы
    // 

    function get_html( $full = true )
    {
        $html = '';
        
        // Если полный формат - добавляем заголовок таблицы

        if ( $full ) {

            $html .= "<table border='1'>\n";
            $html .= $this->get_html_header();
            
        }
        
        $n = 1;
        $cmp = $this->get_cmp();

        foreach ( $this->matrix_arr as $name => $row ) {
            
            $html .= "<tr>\n";
            $html .= "<td>$n</td>\n";
            $html .= "<td>$name</td>\n";
            
            foreach ( $cmp as $item ) {
                
                $marker = ( in_array( $item, $row ) ) ? '1': '';
                $marker_class = ( in_array( $item, $row ) ) ? 'yes': 'no';
                $html .= "<td class=\"$marker_class\">$marker</td>\n";
                
            }
            
            $html .= "</tr>\n";
            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $this->get_html_stat();            
            $html .= "</table>";
            
        }
        
        return $html;
    }
    
    
    
    //  
    // Возвращает строку со статистикой для таблицы HTML
    // 
    
    private function get_html_stat()
    {
        $html = '';

        $stat = $this->get_cmp_stat();

        $html .= "<tr>\n";
        $html .= "<th></th>\n";
        $html .= "<th>ИТОГО:</th>\n";

        foreach ( $stat as $item ) $html .= "<th>$item</th>\n";
        
        return $html;
    }
    

    //  
    // Возвращает заголовок с компетенциями для таблицы HTML
    // 
    
    private function get_html_header()
    {
        $html = '';
        
        $cmp = $this->get_cmp();
        $index = array();

        foreach ( $cmp as $item ) {

            $data = explode( '-', $item );
            $index[$data[0]][] = $data[1];

        }

        $html .= "<tr>\n";
        $html .= "<th rowspan=\"2\"></th>\n";
        $html .= "<th rowspan=\"2\"></th>\n";

        $row2 = '';

        foreach ( $index as $key => $numerics ) {
            
            $c = count( $numerics );
            $html .= "<th colspan=\"$c\">$key</th>\n";
            foreach ( $numerics as $item ) $row2 .= "<th>$item</th>\n";
            
        }
        
        $html .= "</tr>\n";
 
        $html .= "<tr>\n";
        $html .= $row2;
        $html .= "</tr>\n";

        return $html;
    }



    private $acceptable_cmp = '';
    private $matrix_arr = array();

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