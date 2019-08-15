<?php

//
// Класс для определение места дисциплины в ОПОП (порядок следования дисциплин)
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/curriculum-class.php';
include_once dirname( __FILE__ ) . '/matrix-class.php';
include_once dirname( __FILE__ ) . '/cmp-class.php';

class ordering {

    //
    // Инициализация 
    // На входе - данные для определения учебного плана и матрицы компетенций
    //

    function __construct( $curriculum, $matrix, $cmp = '' )
    {
        // Построить массив учебного плана

        $с = new curriculum( $curriculum );
        $this->curriculum_arr = $с->get_arr();


        // Построить массив компетенций

        $m = new matrix( $matrix, $cmp );
        $this->matrix_arr = $m->get_arr();

    }


    // 
    // Отображает массив связанных по матрице компетененций дисциплин
    // 

    function get_related( $course )
    {
        $arr = array();

        // Если запрашиваемого курса нет, то вернуть false 
        // Замечание. Если курс есть, но он ни с кем не связан, то вернется пустой массив.

        if ( ! isset( $this->matrix_arr[$course] ) ) return false;

        $cmp = $this->matrix_arr[$course];
        $c = new cmp();
    
        foreach ( $this->matrix_arr as $key => $item ) {

            if ( $key == $course ) continue;
            if ( $c->intersection( $cmp, $item ) ) $arr[] = $key;

        }

        return $arr;
    }


    // 
    // Вернуть курсы, которые являются основой
    // 

    function get_prev( $course )
    {
        return $this->get_prev_next( $course, true );
    }



    // 
    // Вернуть курсы, которые опираются на
    // 

    function get_next( $course )
    {
        return $this->get_prev_next( $course, false );
    }



    // 
    // Вернуть курсы, которые являются основой или последующими
    // 

    private function get_prev_next( $course, $prev = true )
    {
        $arr = array();

        if ( empty( $this->curriculum_arr ) ) return;

        $related_courses = $this->get_related( $course );

        if ( $related_courses === false ) return false;

        // Семестры, где изучается запрошенная дисциплина

        $semesters_course = array_keys( $this->curriculum_arr[$course]['semesters'] );
        sort( $semesters_course );
        
        // Провести сравнение

        foreach ( $related_courses as $item ) {
            
            if ( $course == $item ) continue;
            if ( empty( $this->curriculum_arr[$item] ) ) continue;

            $semesters_item = array_keys( $this->curriculum_arr[$item]['semesters'] );
            sort( $semesters_item );

            if ( $prev ) {

                // Если первый семестр одной дисциплины меньше последнего семестра второй, то она она в основе
                if ( $semesters_item[0] < $semesters_course[ count( $semesters_course ) - 1 ] ) $arr[] = $item;
                
            } else {
                
                // Если последний семестр одной дисциплины больше первого семестра второй, то она она поседующая
                if ( isset( $semesters_item[ count( $semesters_course ) - 1 ] ) && $semesters_item[ count( $semesters_course ) - 1 ] > $semesters_course[0] ) $arr[] = $item;

            }

        }

        return $arr;
    }

    private $matrix_arr = array();
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