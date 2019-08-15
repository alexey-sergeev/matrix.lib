<?php

//
// Класс для работы с описанием модулей и дисциплин
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/functions.php';
include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class modules {

    //
    // Инициализация 
    // На входе - массив или текстовое описание модулей и их дисциплин
    // 

    function __construct( $data )
    {

        $p = new parser();
        $this->default_name = $p->default_name;

        if ( is_string( $data ) ) {
    
            $data = $p->get_arr( $data, array( 'section' => 'modules', 'att_name' => true, 'default' => true ) );
    
        }
    
        // Построить массив модулей

        foreach ( $data as $item ) {

            $arr = array();

            if ( isset( $item['modules']['att'] ) ) $arr['att'] = $this->get_att( $item['modules']['att'] );
            if ( isset( $item['modules']['data'] ) ) $arr['courses'] = $this->get_module_courses( $item['modules']['data'], false );

            $this->modules_arr[$item['modules']['name']] = $arr;

        }

        // Сортировать дисциплины и практики вне модулей

        // if ( isset( $this->modules_arr[$this->default_name] ) ) $this->modules_arr[$this->default_name]['courses'] = $this->sort_courses( $this->modules_arr[$this->default_name]['courses'] );

        // Найти вариативные модули

        $index = array();

        foreach ( $this->modules_arr as $key => $item ) {

            if ( isset( $item['att']['code'] ) ) {

                // Если код заканчивается двумя числовыми секциями, то последнее число заменить признаком модуля по выбору
                $code = explode( '.', $item['att']['code'] );
                if ( is_numeric( $code[count( $code ) - 1] ) && is_numeric( $code[count( $code ) - 2] ) ) $code[count( $code ) - 1] = '__elective__';
                $code = implode( '.', $code );

                $index[$code][] = $key;
            }

        };

        foreach ( $index as $modules ) {

            if ( count( $modules ) > 1 ) {

                foreach ( $modules as $item ) {

                    $this->modules_arr[$item]['elective'] = $modules;

                }

            }
        }

        // Построить массив дисциплин

        foreach ( $this->modules_arr as $module => $data ) {

            if ( ! isset( $data['courses'] ) ) continue;

            foreach ( $data['courses'] as $course => $item ) {

                $this->courses_arr[$course] = $item;
                if ( $module != $this->default_name ) $this->courses_arr[$course]['module'] = $module;

            }

        }

        $this->courses_arr = $this->sort_courses( $this->courses_arr );

        // Построить дерево дисциплин

        foreach ( $this->courses_arr as $course => $data ) {

            $key = ( isset( $data['unit'] ) ) ? $data['unit'] : 'other';

            if ( isset( $this->items_name[$key] ) ) $this->courses_tree[$key]['att'] = $this->items_name[$key];
            $this->courses_tree[$key]['courses'][$course] = $data;
     
        }

    }

    

    //
    // Построить список курсов для модуля
    //

    private function get_module_courses( $data, $sort = true )
    {
        $arr = array();
        $data = implode( "\n", $data );

        $p = new parser();
        $data = $p->get_arr( $data, array( 'section' => 'courses', 'att_name' => true, 'nomarker' => true ) );

        foreach ( $data as $item ) {

            $att = ( isset( $item['courses']['att'] ) ) ? $item['courses']['att'] : array();
            $arr[$item['courses']['name']] = $this->get_att( $att, 'ОД' );

        }

        if ( $sort ) $arr = $this->sort_courses( $arr );

        return $arr;
    }



    //
    // Взять атрибуты дисциплины или модуля
    //

    private function get_att( $data, $default_item = false )
    {
        $arr = array();

        $data = array_map( 'trim', (array) $data );

        foreach ( $data as $item ) {

            // Проверка на номера кафедры
            // Если удалить цифры, запятые, пробелы и ничего не останется, то это номер кафедры

            $st = preg_replace( "/[\d,; ]/", "", $item );
            if ( $st == '' ) {
                
                $arr['kaf'] = array_map( 'trim', explode( ",", $item ) );
                continue;

            }
            
            // Проверка на указание вида элемента
            // Если удалить буквы и ничего не останется, то это указание вида элемента
            
            $st = preg_replace( "/\pL/iu", "", $item );
            if ( $st == '' ) {
                
                $arr['unit'] = mb_strtoupper( $item );
                continue;

            }

            // Проверка на указание кода элемента
            // Если удалить буквы, цифры, точки, скобки и ничего не останется, то это указание кода элемента

            $st = preg_replace( "/[\pL\d.()]/iu", "", $item );
            if ( $st == '' ) $arr['code'] = mb_strtoupper( $item );
 
        }

        if ( $default_item && empty( $arr['unit'] ) ) $arr['unit'] = $default_item;

        return $arr;
    }



    // 
    // Функция возвращает массив модулей и дисциплин
    // 
    
    function get_arr()
    {
        return $this->modules_arr;
    }
    
    
    
    // 
    // Функция возвращает массив модулей и дисциплин (алиас get_arr)
    // 

    function get_modules()
    {
        return $this->get_arr();
    }
    
    
    
    // 
    // Функция возвращает дерево дисциплин
    // 

    function get_tree()
    {
        return $this->courses_tree;
    }
    
    
    
    // 
    // Функция возвращает массив дисциплин
    //      $module - уточнить, для какого модуля?
    //      если $module = false, то выводятся все дисциплины
    //      если $module = '__default__', то выводятся для дисциплин без модуля
    // 

    function get_courses( $module = false )
    {
        $arr = array();

        if ( $module ) {

            foreach ( $this->courses_arr as $course => $data ) {

                if ( isset( $data['module'] ) && $data['module'] === $module ) $arr[$course] = $data;
                if ( empty( $data['module'] ) && $module == $this->default_name ) $arr[$course] = $data;

            }

        } else {

            $arr = $this->courses_arr;

        }

        // $arr = $this->sort_courses( $arr );

        return $arr;
    }



    // 
    // Сортирует массив дисциплин
    // 

    function sort_courses( $courses )
    {
        $arr = array();

        // Сначала общая сортировка списка (англоязычные названия - внизу)

        $prefix = 'яяя-';
        $index = array();
        foreach ( $courses as $course => $data ) $index[] = ( preg_match( "/^[a-zA-Z]/", $course ) ) ? $prefix . $course : $course;

        sort( $index );

        $courses_sorted = array();

        foreach ( $index as $key => $item ) {

            $course = preg_replace( "/^" . $prefix . "/", "", $item );
            $courses_sorted[$course] = $courses[$course];

        }

        // Перебрать все дисциплины по порядку следования

        $sort_order = array_keys( $this->items_name );

        foreach ( $sort_order as $item ) {

            foreach ( $courses_sorted as $course => $data ) {
                
                if ( isset( $data['unit'] ) && $data['unit'] == $item ) {
                
                    $arr[$course] = $data;
                    unset( $courses[$course] );

                }

            }
        }

        // Записать данные и тех дисциплин, для которых тип не был определен

        $arr = array_merge( $arr, $courses );

        return $arr;
    }



    // 
    // Функция возвращает модули и дисциплины в виде HTML-таблицы
    // 

    function get_html( $arr = NULL, $full = true )
    {
        $html = '';
        
        $h = new html();

        // Если полный формат - добавляем заголовок таблицы

        if ( $full ) {

            $html .= $h->table_header();
            
        }
        
        // Определить данные для отображения и режим просмотра
        
        if ( $arr === NULL ) {
            
            $arr = $this->modules_arr;
            $mode = 'modules';
            
        } else {
            
            $mode = 'courses';

            $first = current( $arr );

            if ( isset( $first['att'] ) ) $mode = 'modules';
            if ( isset( $first['att']['singular'] ) ) $mode = 'tree';

        }

        // Если просто дисциплины, то привести массив к виду модулей или дерева

        if ( $mode == 'courses' ) $arr = array( 'courses' => array( 'courses' => $arr ) );

        // Строим таблицу

        $n = 1;
        $m = 1;

        $code = ( $mode == 'modules' ) ? 'Код' : '№';
        $title = ( $mode == 'modules' ) ? 'Модули' : 'Дисциплины и практики';

        $html .= $h->course_name_tr( "$title</th><th>Каф.", $code );

        foreach ( $arr as $key => $module ) {

            // Вывести название модуля или блока дисциплин

            if ( $mode == 'modules' ) {
                
                $code = ( isset( $module['att']['code'] ) ) ? $module['att']['code'] : '';
                $title = ( $key == $this->default_name ) ? 'Дисциплины и практики вне модулей' : $key;
                $title .= "</th><th>";
                $html .= $h->course_name_tr( $title, $code );
                
            } 
            
            if ( $mode == 'tree' ) {
                
                $title = ( isset( $module['att']['plural'] ) ) ? $module['att']['plural'] : $key;
                $html .= $h->part_tr( $title, 3 );

            }

            // Вывести дисциплины

            if ( isset( $module['courses'] ) ) {

                foreach ( $module['courses'] as $course => $data ) {
                    
                    $row = '';
                    $row .= $course . "</td>\n";
                    $kaf = ( isset( $data['kaf'] ) ) ? implode( ", ", $data['kaf'] ) : '';
                    $row .= "<td>$kaf";
                    
                    $code = ( $mode == 'modules' && isset( $data['code'] ) ) ? $data['code'] : $m;
                    
                    $html .= $h->course_data_tr( $row, '', '', $code );
                    
                    $m++;
                    
                }
                
            }
            
            $n++;

        }
        


        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }

    private $modules_arr = array();
    private $courses_arr = array();
    private $courses_tree = array();

    private $default_name = '';
    
    public $items_name = array( 
        'ОД' => array( 'singular' => 'Обязательная дисциплина', 'plural' => 'Обязательные дисциплины' ), 
        'ВД' => array( 'singular' => 'Вариативная дисциплина', 'plural' => 'Вариативные дисциплины' ), 
        'УП' => array( 'singular' => 'Учебная практика', 'plural' => 'Учебные практики' ), 
        'ПП' => array( 'singular' => 'Производственная практика', 'plural' => 'Производственные практики' ), 
        'НИР' => array( 'singular' => 'Научно-исследовательская работа', 'plural' => 'Научно-исследовательская работа' ), 
        'КР' => array( 'singular' => 'Курсовая работа', 'plural' => 'Курсовые работы' ), 
        'КП' => array( 'singular' => 'Курсовой проект', 'plural' => 'Курсовые проекты' ), 
        'К' => array( 'singular' => 'Контрольная работа', 'plural' => 'Контрольные работы' ), 
        'ЗЧ' => array( 'singular' => 'Зачёт', 'plural' => 'Зачёты' ), 
        'ЗЧО' => array( 'singular' => 'Зачёт с оценкой', 'plural' => 'Зачёты с оценкой' ), 
        'ЭК' => array( 'singular' => 'Экзамен', 'plural' => 'Экзамены' ), 
        'ГИА' => array( 'singular' => 'Государственная итоговая аттестация', 'plural' => 'Государственная итоговая аттестация' ), 
        'ФТД' => array( 'singular' => 'Факультативная дисциплина', 'plural' => 'Факультативные дисциплины' ) 
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