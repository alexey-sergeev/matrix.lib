<?php

//
// Класс для работы с целями и содержанием дисциплин
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/functions.php';
include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/curriculum-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class content {

    //
    // Инициализация 
    // На входе - массив или текстовое описание всех дисциплин, их целей и содержания, а также данные для построения учебного плана
    //

    function __construct( $data, $curriculum = '' )
    {

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'content', 'att_name' => false ) );
    
        }
    
        // Построить массив учебного плана или взять план, если это уже массив

        if ( is_string( $curriculum ) ) {

            $c = new curriculum( $curriculum );
            $this->curriculum_arr = $c->get_arr();
            
        } else {
            
            $this->curriculum_arr = $curriculum;

        }


        // p( $this->curriculum_arr );
        // p( $data );

        foreach ( $data as $item ) {

            // Цель дисциплины
            $this->content_arr[$item['content']['name']]['target'] = arr_to_text( $item['content']['data'] );

            $hours = $this->get_hours( $item['content'] );

            // Содержание разделов
            foreach ( (array) $item['content']['parts'] as $part ) {

                $this->content_arr[$item['content']['name']]['parts'][$part['name']]['content'] = arr_to_text( $part['data'] );
                $this->content_arr[$item['content']['name']]['parts'][$part['name']]['hours'] = $hours[$part['name']]['clean'];

            }

            // p($item['content']);

        }

        // p($this->content_arr);

    }
    
    // 
    // Уточнение часов разделов (приведение к правдоподобным значениям)
    // 

    private function get_hours( $data )
    {
        $arr = array();

        // Записать в массив данные "как есть"

        foreach ( $data['parts'] as $item ) {


            $raw = array();
            $att = ( isset( $item['att'][0] ) ) ? $item['att'][0] : 1;
            $h = explode( ',', $att );

            $raw['lec'] = ( isset( $h[0] ) ) ? (int) $h[0] : 1;
            $raw['lab'] = ( isset( $h[1] ) ) ? (int) $h[1] : $raw['lec'];
            $raw['prac'] = ( isset( $h[2] ) ) ? (int) $h[2] : $raw['lab'];
            $raw['srs'] = ( isset( $h[3] ) ) ? (int) $h[3] : $raw['prac'];

            $arr[$item['name']]['raw'] = $raw;
           
        }
        
        // Рассчитать часы по данным разделов

        $h_raw = array( 0, 0, 0, 0 );
        
        foreach ( $arr as $item ) {

            $h_raw[0] += $item['raw']['lec'];
            $h_raw[1] += $item['raw']['lab'];
            $h_raw[2] += $item['raw']['prac'];
            $h_raw[3] += $item['raw']['srs'];
            
        }
        
        // p($h_raw);
        
        // Рассчитать часы по данным учебного плана
        
        $h_curriculum = array( 0, 0, 0, 0 );

        if ( isset( $this->curriculum_arr[$data['name']]['semesters'] ) ) {

            foreach ( $this->curriculum_arr[$data['name']]['semesters'] as $item )
            {
                if ( isset( $item['lec'] ) ) $h_curriculum[0] += $item['lec'];
                if ( isset( $item['lab'] ) ) $h_curriculum[1] += $item['lab'];
                if ( isset( $item['prac'] ) ) $h_curriculum[2] += $item['prac'];
                if ( isset( $item['srs'] ) ) $h_curriculum[3] += $item['srs'];
            }

        }
        
        // p($h_curriculum);

        // Рассчитать чистые часы для разделов
        
        foreach ( $data['parts'] as $item ) {

            $arr[$item['name']]['clean']['lec'] = ( $h_raw[0] === 0 ) ? round( $h_curriculum[0] / count( $arr ) ) : round( $h_curriculum[0] * $arr[$item['name']]['raw']['lec'] / $h_raw[0] );
            $arr[$item['name']]['clean']['lab'] = ( $h_raw[1] === 0 ) ? round( $h_curriculum[1] / count( $arr ) ) : round( $h_curriculum[1] * $arr[$item['name']]['raw']['lab'] / $h_raw[1] );
            $arr[$item['name']]['clean']['prac'] = ( $h_raw[2] === 0 ) ? round( $h_curriculum[2] / count( $arr ) ) : round( $h_curriculum[2] * $arr[$item['name']]['raw']['prac'] / $h_raw[2] );
            $arr[$item['name']]['clean']['srs'] = ( $h_raw[3] === 0 ) ? round( $h_curriculum[3] / count( $arr ) ) : round( $h_curriculum[3] * $arr[$item['name']]['raw']['srs'] / $h_raw[3] );

            // p($arr[$item['name']]['clean']);
        }

        // Уточнить, если из-за округления часы не сходятся

        $h_clean = array( 0, 0, 0, 0 );

        foreach ( $arr as $item ) {

            $h_clean[0] += $item['clean']['lec'];
            $h_clean[1] += $item['clean']['lab'];
            $h_clean[2] += $item['clean']['prac'];
            $h_clean[3] += $item['clean']['srs'];

        }

        // p($h_clean);

        // Рассчитать разницу из-за округления

        $h_delta = array();
        foreach ( $h_curriculum as $key => $item ) $h_delta[$key] = $h_curriculum[$key] - $h_clean[$key];

        // p($h_delta);
        
        // Пытаться компенсировать эту разницу в первом ненулевом значении
        
        foreach ( $arr as $key => $item ) {
            
            if ( $arr[$key]['clean']['lec'] != 0 ) { $arr[$key]['clean']['lec'] += $h_delta[0]; $h_delta[0] = 0; }
            if ( $arr[$key]['clean']['lab'] != 0 ) { $arr[$key]['clean']['lab'] += $h_delta[1]; $h_delta[1] = 0; }
            if ( $arr[$key]['clean']['prac'] != 0 ) { $arr[$key]['clean']['prac'] += $h_delta[2]; $h_delta[2] = 0; }
            if ( $arr[$key]['clean']['srs'] != 0 ) { $arr[$key]['clean']['srs'] += $h_delta[3]; $h_delta[3] = 0; }
            
        }
        
        // Компенсировать разницу в первом значении, если ненулевых не нашлось
        
        foreach ( $arr as $key => $item ) {
            
            $arr[$key]['clean']['lec'] += $h_delta[0];
            $arr[$key]['clean']['lab'] += $h_delta[1];
            $arr[$key]['clean']['prac'] += $h_delta[2];
            $arr[$key]['clean']['srs'] += $h_delta[3];
            
            break;
        }

        return $arr;
    }


    
    // 
    // Функция возвращает массив целей и содержания
    // 
    
    function get_arr()
    {
        return $this->content_arr;
    }
 



    // 
    // Функция возвращает содержание дисциплин в виде HTML-таблицы
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

        foreach ( $this->content_arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n );

            $row = "<p><strong>Цель освоения дисциплины:</strong></p>\n";
            $row .= $data['target'];

            $html .= $h->course_data_tr( $row );
            
            $m = 1;

            foreach ( $data['parts'] as $part => $part_data ) {
                
                $row = "<p><strong>Раздел $m.</strong> $part\n";
                $row .= "<p>" . $part_data['content'] . "</p>\n";
                
                $hours = implode( ', ', $part_data['hours'] );
                $row .= "<p><span>Трудоемкость: ($hours)</span></p>\n";

                $html .= $h->course_data_tr( $row );

                $m++;

            }

            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }




    private $content_arr = array();
    private $curriculum_arr = array();

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