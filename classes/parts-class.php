<?php

//
// Класс для работы с описанием планиуремых достижений по разделам дисциплин
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/functions.php';
include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/cmp-class.php';
include_once dirname( __FILE__ ) . '/matrix-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class parts {

    //
    // Инициализация 
    // На входе - массив или текстовое описание всех дисциплин и информации по разделам - компетенции, знать, уметь, владеть
    // $matrix, $cmp - информация для построения матрицы компетенций 
    // 

    function __construct( $data, $matrix = '', $acceptable_cmp = '' )
    {

        if ( is_string( $data ) ) {
            
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'parts', 'att_name' => false ) );
            
        }

        // Построить массив матрицы компетенций или взять матрицу, если это уже массив        

        if ( is_string( $matrix ) ) {

            $m = new matrix( $matrix, $acceptable_cmp );
            $this->matrix_arr = $m->get_arr();
            
        } else {
            
            $this->matrix_arr = $matrix;

        }

        $c = new cmp();

        foreach ( $data as $item ) {
            
            $cmp_stat = array();

            // Планируемые результаты обучения

            foreach ( (array) $item['parts']['parts'] as $part ) {

                // Оставить у раздела только допустимые для дисциплины компетенции
                
                $course_cmp = ( isset( $this->matrix_arr[$item['parts']['name']] ) ) ? $this->matrix_arr[$item['parts']['name']] : '';
                $cmp = $c->intersection( $c->get_cmp( $part['att'][0] ), $course_cmp, 'arr' );
                
                // // Если у дисциплины только одна компетенция, то ее за разделом и закреплять
                // if ( count( $course_cmp ) == 1 ) $cmp = $course_cmp;

                // Если компетенции не указаны, а у дисциплины только одна или две компетенции, то их за разделом и закрепить
                if ( empty( $cmp ) && ( count( $course_cmp ) < 3 ) ) $cmp = $course_cmp;

                


                $this->parts_arr[$item['parts']['name']]['parts'][$part['name']]['cmp'] = $cmp;
                $this->parts_arr[$item['parts']['name']]['parts'][$part['name']]['outcomes'] = $this->get_outcomes( $part['data'] );

                $cmp_stat = array_merge( $cmp_stat, $cmp );

            }

            // Статистика по присутствующим компетенциям
            
            $this->parts_arr[$item['parts']['name']]['cmp_stat'] = $c->get_cmp( $cmp_stat, 'arr' );
            
            // Статистика отсутсвующим компетенциям

            $cmp_missing = ( isset( $this->matrix_arr[$item['parts']['name']] ) ) ? $c->difference( $this->matrix_arr[$item['parts']['name']], $cmp_stat, 'arr' ) : array();
            if ( ! empty( $cmp_missing ) ) $this->parts_arr[$item['parts']['name']]['cmp_missing'] = $cmp_missing;

        }

    }


    // 
    // Оформить массив "знать", "уметь", "владеть"
    // 

    private function get_outcomes( $data )
    {
        $arr = array();

        $data = implode( "\n", $data );
        $data = explode( "\n-", $data );

        foreach ( $data as $key => $item ) {

            $txt = trim( strim( $item ), ' -.,;' );

            if ( $key % 3 === 0 && $txt != '' ) $arr['z'][] = $txt;
            if ( $key % 3 === 1 && $txt != '' ) $arr['u'][] = $txt;
            if ( $key % 3 === 2 && $txt != '' ) $arr['v'][] = $txt;

        }

        return $arr;
    }

    
    // 
    // Функция возвращает массив разработчиков
    // 
    
    function get_arr()
    {
        return $this->parts_arr;
    }
 



    // 
    // Функция возвращает информацию о разделах в виде HTML-таблицы
    // 

    function get_html( $full = true )
    {
        $html = '';
        
        $h = new html();

        // Если полный формат - добавляем заголовок таблицы

        if ( $full ) {

            $html .= $h->table_header();
            
        }
        
        $n = 1;

        foreach ( $this->parts_arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n );

            $m = 1;

            foreach ( $data['parts'] as $part => $part_data ) {
                
                $row = "<p><strong>Раздел $m.</strong> $part\n";
                
                if ( isset( $part_data['outcomes']['z'] ) ) {

                    $row .= "<p><em>знать:</em></p>\n";
                    $row .= "<ul>\n<li>" . implode( "</li>\n<li>", $part_data['outcomes']['z'] ) . "</li>\n</ul>\n";

                }

                if ( isset( $part_data['outcomes']['u'] ) ) {

                    $row .= "<p><em>уметь:</em></p>\n";
                    $row .= "<ul>\n<li>" . implode( "</li>\n<li>", $part_data['outcomes']['u'] ) . "</li>\n</ul>\n";

                }

                if ( isset( $part_data['outcomes']['v'] ) ) {

                    $row .= "<p><em>владеть:</em></p>\n";
                    $row .= "<ul>\n<li>" . implode( "</li>\n<li>", $part_data['outcomes']['v'] ) . "</li>\n</ul>\n";

                }

                $cmp = implode( ', ', $part_data['cmp'] );
                $row .= "<p><span>Компетенции: $cmp</span></p>\n";

                $html .= $h->course_data_tr( $row );

                $m++;

            }

            $cmp_stat = implode( ", ", $data['cmp_stat'] );
            $row = "<p><span>ИТОГО по дисциплине:</span> $cmp_stat</p>\n";
            
            if ( ! empty( $data['cmp_missing'] ) ) {
                
                $cmp_missing = implode( ", ", $data['cmp_missing'] );
                $row .= "<p><span class=\"error\">Отсутствуют:</span> $cmp_missing</p>\n";

            }

            $html .= $h->course_data_tr( $row );

            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }

    private $parts_arr = array();
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