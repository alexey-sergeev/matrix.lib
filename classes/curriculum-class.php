<?php

//
// Класс для работы с учебным планом
// А. Н. Сергеев, Волгоград
// Июль 2019
// 

include_once dirname( __FILE__ ) . '/parser-class.php';

class curriculum {

    //
    // Инициализация 
    // На входе - массив дисциплин ОПОП или текстовое описание дисциплин, семестров и часов
    //

    function __construct( $data )
    {
        // Если данные - в виде текстового описания, то оформить в виде массива

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'curriculum', 'nomarker' => true, 'att_name' => true ) );

        }
    
        // Построить массив учебного плана

        $sem_data = array();
        $course = '';

        foreach ( $data as $item ) {
    
            if ( empty( $item['curriculum']['att'][0] ) ) continue;
            
            // Запомнить номер последнего семестра

            if ( (int) $item['curriculum']['att'][0] > $this->num_semesters ) $this->num_semesters = (int) $item['curriculum']['att'][0];

            // Составить массив учебного плана

            if ( in_array( $item['curriculum']['att'][0], array( '-', '–', '—' ) ) ) {

                // Найден последующий курс по выбору - учесть старые данные

                $this->curriculum_arr[$item['curriculum']['name']]['semesters'] =  $sem_data;

                // Уточнить список дисциплин для этой серии курсов по выбору

                if ( empty( $this->curriculum_arr[$course]['elective'] ) ) $this->curriculum_arr[$course]['elective'][] = $course;
                $this->curriculum_arr[$course]['elective'][] = $item['curriculum']['name'];
                foreach ( $this->curriculum_arr[$course]['elective'] as $name ) $this->curriculum_arr[$name]['elective'] = $this->curriculum_arr[$course]['elective'];

            } else {

                // Обычный курс или пока не ясно, что он по выбору
                
                $sem_data = $this->get_semesters( $item['curriculum']['att'] );
                $course = $item['curriculum']['name'];
                $this->curriculum_arr[$item['curriculum']['name']]['semesters'] =  $sem_data;

            }

            // Вычислить общие данные по курсу

            $course_stat = array( 'lec' => 0, 'lab' => 0, 'prac' => 0, 'srs' => 0, 'exam' => 0 );
            
            // Статистика по часам
            
            foreach ( $sem_data as $sem => $hours_arr ) {

                foreach ( $course_stat as $key => $value ) if ( isset( $hours_arr[$key] ) ) $course_stat[$key] += $hours_arr[$key];
            
            }
                
            // Статистика по промежуточной аттестации
            
            foreach ( $sem_data as $sem => $hours_arr ) {

                if ( ! isset( $hours_arr['att'] ) ) continue;
                foreach ( $hours_arr['att'] as $att_item ) $course_stat['att'][$att_item][] = $sem;
                    
            }

            $this->curriculum_arr[$item['curriculum']['name']]['course_stat'] =  $course_stat;

        }

        // Сколько семестров (сессий) в учебном году?
        // Пока так. Но это плохой способ - скорее всего будет ошибаться на заочных магистратурах

        $this->num_semesters_per_year = ( $this->num_semesters % 3 == 0 ) ? 3 : 2;

    }


    // 
    // Функция возвращает массив учебного плана
    // 

    function get_arr()
    {
        return $this->curriculum_arr;
    }


    // 
    // Получает массив семестров из сырых данных
    // 

    private function get_semesters( $data )
    {
        $semesters = array();

        // Перебрать информацию по каждому семестру

        foreach ( $data as $semester ) {

            $arr = explode( ',', $semester );
            $arr = array_map( 'trim', $arr );

            // Взять все данные по семестру

            $sem = array();

            // Разные часы

            if ( isset( $arr[1] ) && is_numeric( $arr[1] ) ) $sem['lec'] = $arr[1];
            if ( isset( $arr[2] ) && is_numeric( $arr[2] ) ) $sem['lab'] = $arr[2];
            if ( isset( $arr[3] ) && is_numeric( $arr[3] ) ) $sem['prac'] = $arr[3];
            if ( isset( $arr[4] ) && is_numeric( $arr[4] ) ) $sem['srs'] = $arr[4];
            if ( isset( $arr[5] ) && is_numeric( $arr[5] ) ) $sem['exam'] = $arr[5];

            // Дополнительные атрибуты (промежуточная аттестация или др.)

            foreach ( $arr as $item ) {

                if ( ! is_numeric( $item ) ) $sem['att'][] = $item;

            }

            $semesters[ (int) $arr[0] ] = $sem;

        }

        return $semesters;
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
        $titles = array();

        foreach ( $this->curriculum_arr as $name => $data ) {
            
            // Определить заголовок и не повторяться

            $title = ( isset( $data['elective'] ) ) ? implode( ' / ', $data['elective'] ) : $name;
            if ( in_array( $title, $titles ) ) continue;
            $titles[] = $title;

            // Сформировать строку

            $html .= "<tr>\n";
            $html .= "<td>$n</td>\n";

            $html .= "<td>$title</td>\n";
            $html .= $this->get_row( $data['semesters'] );
            
            // p($data);
            
            $html .= "</tr>\n";

            $n++;

        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= "</table>\n";
            
        }
        
        return $html;
    }
    
    
    
    //  
    // Возвращает строку учебного плана для дисциплины
    // 
    
    private function get_row( $semesters )
    {
        $html = '';
        $arr = array();

        for ( $i = 1; $i <= $this->num_semesters; $i++ ) {

            if ( empty( $semesters[$i] ) ) {

                $arr = array( '', '', '', '', '' );

            } else {

                $arr[0] = ( isset($semesters[$i]['lec']) && (int) $semesters[$i]['lec'] ) ? (int) $semesters[$i]['lec'] : '';
                $arr[1] = ( isset($semesters[$i]['lab']) && (int) $semesters[$i]['lab'] ) ? (int) $semesters[$i]['lab'] : '';
                $arr[2] = ( isset($semesters[$i]['prac']) && (int) $semesters[$i]['prac'] ) ? (int) $semesters[$i]['prac'] : '';
                $arr[3] = ( isset($semesters[$i]['srs']) && (int) $semesters[$i]['srs'] ) ? (int) $semesters[$i]['srs'] : '';
                $arr[4] = ( isset( $semesters[$i]['att'] ) ) ? implode( ', ', $semesters[$i]['att'] ) : ''; // Здесь уточнять - все ли относится к контролю?

            }

            $odd = ( $i % 2 == 0 ) ? 'odd' : 'even';
            
            $html .= "<td class=\"$odd\">$arr[0]</td>\n"; // Здесь и ниже - можно писать дополнительные пояснения
            $html .= "<td class=\"$odd\">$arr[1]</td>\n"; 
            $html .= "<td class=\"$odd\">$arr[2]</td>\n";
            $html .= "<td class=\"$odd\">$arr[3]</td>\n";
            $html .= "<td class=\"$odd\">$arr[4]</td>\n";

        }
        
        return $html;
    }
    

    //  
    // Возвращает заголовок с компетенциями для таблицы HTML
    // 
    
    private function get_html_header()
    {
        $html = '';
        
        $html .= "<tr>\n";
        $html .= "<th rowspan=\"2\"></th>\n";
        $html .= "<th rowspan=\"2\"></th>\n";
        
        for ( $i = 1; $i <= $this->num_semesters; $i++ ) {
            
            $title = ( $this->num_semesters_per_year == 3 ) ? "Сессия" : "Семестр";
            $html .= "<th colspan=\"5\">$title $i</th>\n";
            
        }

        $html .= "</tr>\n";
        
        $html .= "<tr>\n";
        
        for ( $i = 1; $i <= $this->num_semesters; $i++ ) {
            
            $html .= "<th>л</th><th>л</th><th>п</th><th>с</th><th>к</th>\n";
            
        }
        
        $html .= "</tr>\n";

        $html .= "</th>\n";

        return $html;
    }


    private $curriculum_arr = array();
    private $num_semesters = 0;
    private $num_semesters_per_year = 2;

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