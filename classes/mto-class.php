<?php

//
// Класс для работы с материально-техничесикм обеспечением
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/functions.php';
include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/curriculum-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class mto {

    //
    // Инициализация 
    // На входе - массив или текстовое описание всех дисциплин и их материально-технического обеспечения
    // $curriculum - данные для построения учебного плана

    function __construct( $data, $curriculum = '' )
    {

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'mto', 'att_name' => false ) );
    
        }
    
        // Построить массив учебного плана или взять план, если это уже массив

        if ( is_string( $curriculum ) ) {

            $c = new curriculum( $curriculum );
            $this->curriculum_arr = $c->get_arr();
            
        } else {
            
            $this->curriculum_arr = $curriculum;

        }

        foreach ( $data as $item ) {

            $this->mto_arr[$item['mto']['name']]['mto'] = $this->get_mto( $item['mto'] );

        }


    }
 

    // 
    // Формирует список материально-технического обеспечения
    // 

    private function get_mto( $data )
    {
        $course = $data['name'];
        
        // Определить стандартные формулировки про аудитории

        $arr_std = array();
        foreach ( $this->standart_mto as $key => $value ) if ( isset( $this->curriculum_arr[$course]['course_stat'][$key] ) ) $arr_std[] = $value;

        // Определить формулировки из списка

        $data = implode( "\n", $data['data'] );
        $arr = explode( "\n", $data );
        $arr = array_map( 'trim', $arr );
        $arr = array_diff( $arr, array( '' ) );

        $arr = array_merge( $arr_std, $arr );

        return $arr;
    }
    

    // 
    // Функция возвращает массив материально-технического обеспечения
    // 
    
    function get_arr()
    {
        return $this->mto_arr;
    }
 
        

    // 
    // Функция возвращает объединенный список материально-технического обеспечения
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

        $arr[$compose]['mto'] = array();

        foreach ( $data as $name ) {

            if ( empty( $this->mto_arr[$name] ) ) continue;
            $arr[$compose]['mto'] = array_merge( $arr[$compose]['mto'], $this->mto_arr[$name]['mto'] );

        }

        $arr[$compose]['mto'] = array_unique( $arr[$compose]['mto'] );
        @ sort( $arr[$compose]['mto'] );

        return $arr;
    }



    // 
    // Функция возвращает материально-техническое обеспечение дисциплин в виде HTML-таблицы
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

        if ( $arr === NULL ) $arr = $this->mto_arr;

        foreach ( $arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n );

            $row = "<p><strong>Материально-техническое обеспечение</strong></p>\n";
            $row .= "<ol>\n<li>";
            $row .= implode( "</li>\n<li>", $data['mto'] );
            $row .= "</li>\n</ol>";

            $html .= $h->course_data_tr( $row );
            
            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }


    private $mto_arr = array();
    private $curriculum_arr = array();

    private $standart_mto = array(
        'lec' => 'Аудитория для проведения лекционных занятий',
        'lab' => 'Аудитория для проведения лабораторных занятий',
        'prac' => 'Аудитория для проведения практических занятий',
        'srs' => 'Аудитория для проведения самостоятельной работы студентов'
    );

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