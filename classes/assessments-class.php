<?php

//
// Класс для работы с описанием оцночных средств
// А. Н. Сергеев, Волгоград
// Август 2019
// 

include_once dirname( __FILE__ ) . '/functions.php';
include_once dirname( __FILE__ ) . '/parser-class.php';
include_once dirname( __FILE__ ) . '/matrix-class.php';
include_once dirname( __FILE__ ) . '/curriculum-class.php';
include_once dirname( __FILE__ ) . '/html-class.php';

class assessments {

    //
    // Инициализация 
    // На входе - массив или текстовое описания всех дисциплин и их оценочных средств
    // Далее - данные для построения учебного плана и матрицы компетенций
    //

    function __construct( $data, $curriculum = '', $matrix = '', $acceptable_cmp = '' )
    {

        if ( is_string( $data ) ) {
    
            $p = new parser();
            $data = $p->get_arr( $data, array( 'section' => 'assessments', 'att_name' => false ) );
    
        }

        // Построить массив учебного плана или взять план, если это уже массив

        if ( is_string( $curriculum ) ) {

            $c = new curriculum( $curriculum );
            $this->curriculum_arr = $c->get_arr();
            
        } else {
            
            $this->curriculum_arr = $curriculum;

        }

        // Построить массив матрицы компетенеций или взять матрицу, если это уже массив

        if ( is_string( $matrix ) ) {

            $m = new matrix( $matrix, $acceptable_cmp );
            $this->matrix_arr = $m->get_arr();
            
        } else {
            
            $this->matrix_arr = $matrix;

        }

        foreach ( $data as $item ) {

            $this->assessments_arr[$item['assessments']['name']] = $this->get_assessments( $item['assessments'] );

        }

    }



    // 
    // Оформляет сведения об оценочных средствах в виде массива по семестрам
    // 

    private function get_assessments( $data )
    {
        $assessments = array();

        $course = $data['name'];
        $ass_raw = $data['data'];

        // Определить семестры, когда изучается эта дисциплина (взять из описания и уточнить по учебному плану, если эти данные есть)

        $num_semesters = array_keys( $ass_raw );
        if ( isset( $this->curriculum_arr[$course]['semesters'] ) ) $num_semesters = array_keys( $this->curriculum_arr[$course]['semesters'] );
        
        // Распределить оценочные средства по семестрам
        // Если они прописаны для всех семестров, то так и есть
        // Если только для части - в хвосте повторяются средства последнего описанного семестра

        $ass_prev = '';

        foreach ( $num_semesters as $key => $num ) {

            $assessments[$num] = ( isset( $ass_raw[$key] ) ) ? $this->get_ass_arr( $ass_raw[$key] ) : $ass_prev;
            $ass_prev = $assessments[$num];

        }

        // По каждому семестру привести оценочные средства в порядок
        
        $course_cmp = ( isset( $this->matrix_arr[$course] ) ) ? $this->matrix_arr[$course] : '';
        $c = new cmp( $course_cmp );

        foreach ( $assessments as $num => $semester ) {

            // 
            // Определить для семестра оценочные средства промежуточной аттестации 
            // 

            // Выбирается последнее оценочное средство из списка. 
            // Если оно "зачет", "экзамен" и др., то считается, что это промежуточная аттестация

            $exam_raw = end( $semester );
            $exam_cmp = $course_cmp;

            if ( preg_match( "/^зачет|^зачёт|^экзамен|^аттестация с оценкой|^контрольная работа/", mb_strtolower( $exam_raw['name'] ) ) ) {

                // Если оценочное средство промежуточной аттестации нашлось, 
                // то запомнить его компетенции, а само средство из списка удалить

                if ( ! empty( $exam_raw['cmp'] ) ) $exam_cmp = $c->get_cmp( $exam_raw['cmp'], 'arr' );
                array_pop( $semester );
                // array_pop( $assessments[$num] );

            }

            // Определить виды промежуточной аттестации по учебному плану

            $exam_list = array();
            
            if (isset( $this->curriculum_arr[$course]['semesters'][$num]['att'] ) ) {

                foreach( $this->curriculum_arr[$course]['semesters'][$num]['att'] as $item ) {

                    if ( in_array( mb_strtolower( $item ), array_keys( $this->exam_names ) ) ) $exam_list[mb_strtolower( $item )] = true;

                }

            }

            $exam = array();

            foreach ( $exam_list as $key => $value ) {

                $exam[] = array(
                            'name' => $this->exam_names[$key],
                            'rating' => 40,
                            'cmp' => $exam_cmp 
                            );

            }
            
            // p($exam);
        
            // 
            // Определить для семестра оценочные средства текущего контроля 
            // 

            $current = array();

            foreach ( $semester as $item ) {

                $cmp = $c->get_cmp( $item['cmp'], 'arr' );
                
                // Если для оценочного средства компетенция не указана, а у дисциплины 1 или 2 компетенеции, то их и присвоить

                if ( empty( $cmp ) && ( count( $this->matrix_arr[$course] ) < 3 ) ) $cmp = $this->matrix_arr[$course];

                $assesment = array(
                    'name' => $item['name'],
                    'rating' => $item['rating'],
                    'cmp' => $cmp 
                    );

                if ( isset( $item['url'] ) ) $assesment['url'] = $item['url'];

                $current[] = $assesment;

                // p($item);

            }

            // Если в семестре совсем нет никаких занятий (даже СРС), то текущий контроль отменить

            $h = ( isset( $this->curriculum_arr[$course]['semesters'][$num] ) ) ? $this->curriculum_arr[$course]['semesters'][$num] : array();
            
            $hours = 0;
            if ( isset( $h['lec'] ) ) $hours += (int) $h['lec'];
            if ( isset( $h['lab'] ) ) $hours += (int) $h['lab'];
            if ( isset( $h['prac'] ) ) $hours += (int) $h['prac'];
            if ( isset( $h['srs'] ) ) $hours += (int) $h['srs'];
            
            if ( $hours === 0 ) $current = array();
            
            // p($current);

            $assessments[$num] = array( 'current' => $current, 'exam' => $exam );

            // Статистика по семестрам

            // Статистика рейтинга. Считается по текущему контролю. Баллы промежуточной аттестации учитываются только один раз

            $rating_stat = array( 'current' => 0, 'all' => 0 );
            foreach ( $assessments[$num]['current'] as $item ) $rating_stat['current'] += $item['rating'];
            $rating_stat['all'] = $rating_stat['current'];
            if ( isset( $assessments[$num]['exam'][0] ) ) $rating_stat['all'] += $assessments[$num]['exam'][0]['rating'];

            $assessments[$num]['rating_stat'] = $rating_stat;
            
            // Статистика компетенций
            
            $cmp_stat = array();
            foreach ( $assessments[$num]['current'] as $item ) $cmp_stat = array_merge( $cmp_stat, $item['cmp'] );
            foreach ( $assessments[$num]['exam'] as $item ) $cmp_stat = array_merge( $cmp_stat, $item['cmp'] );
            $cmp_stat = $c->get_cmp( $cmp_stat, 'arr' );
            
            $assessments[$num]['cmp_stat'] = $cmp_stat;
                
        }

        // Статистика компетеенций по всему семестру в целом

        $cmp_stat = array();

        foreach ( $assessments as $num => $semester ) {
            
            $cmp_stat = array_merge( $cmp_stat, $semester['cmp_stat'] );
            $cmp_stat = $c->get_cmp( $cmp_stat, 'arr' );

        }

        $cmp_all = ( isset( $this->matrix_arr[$course] ) ) ? $this->matrix_arr[$course] : '';
        $cmp_missing = $c->difference( $cmp_all, $cmp_stat );

        $out = array();
        $out['semesters'] = $assessments;
        $out['cmp_stat'] = $cmp_stat;
        if ( ! empty( $cmp_missing ) ) $out['cmp_missing'] = $cmp_missing;

        return $out;
    }


    // 
    // Вернуть данные об оценочных средствах в виде массива
    // 

    private function get_ass_arr( $assessment )
    {
        $ass_arr = array();

        // Разобрать оценочные средства

        $p = new parser();
        $assessment_arr = $p->get_arr( $assessment, array( 'nomarker' => true, 'att_name' => true ) );

        foreach ( $assessment_arr as $item ) {

            $arr = array();

            $arr['name'] = $item['default']['name'];
            $arr['rating'] = ( isset( $item['default']['att'][0] ) ) ? (int) $item['default']['att'][0] : 0;

            $arr['cmp']  = ( isset( $item['default']['att'][1] ) ) ? $item['default']['att'][1] : '';
                        
            $url = ( isset( $item['default']['att'][2] ) ) ? $item['default']['att'][2] : '';
            if ( preg_match( "/^https?:/", $url ) ) $arr['url'] = $url;

            $ass_arr[] = $arr;

        }

        return $ass_arr;
    }


    
    // 
    // Функция возвращает массив описания оценочных средств
    // 
    
    function get_arr()
    {
        return $this->assessments_arr;
    }
 



    // 
    // Функция возвращает описание оценочных средств в виде HTML-таблицы
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

        $c = new cmp();

        foreach ( $this->assessments_arr as $course => $data ) {
            
            $html .= $h->course_name_tr( $course, $n, 3 );

            foreach ( $data['semesters'] as $num => $semester ) {

                $html .= $h->course_data_tr( "<strong>Семестр $num</strong>", 'head', 3 );
                
                $assesments = array_merge( $semester['current'], $semester['exam'] );
                
                foreach ( $assesments as $assesment ) {
                    
                    $title = $assesment['name'];
                    if ( isset( $assesment['url'] ) ) $title .= ' (<a href="' . $assesment['url'] . '">ссылка</a>)';
                    $row = $title . "</td>\n";
                    $row .= "<td class=\"rating\">" . $assesment['rating'] . "</td>\n";
                    $row .= "<td class=\"cmp\">" . $c->get_cmp( $assesment['cmp'] );
                    
                    $html .= $h->course_data_tr( $row, "assesment_tool" );
                    
                }
                
                $row = "<strong>ИТОГО в семестре</strong></td>\n";
                $row .= "<td><strong>" . $semester['rating_stat']['all']  . "</strong></td>\n";
                $row .= "<td><strong>" . $c->get_cmp( $semester['cmp_stat'] )  . "</strong>";
                
                $html .= $h->course_data_tr( $row, "assessment_stat" );
                
            }
            
            $row = "<strong>ИТОГО по всем семестрам</strong></td>\n";
            $row .= "<td></td>\n";
            $row .= "<td><strong>" . $c->get_cmp( $data['cmp_stat'] )  . "</strong>";

            $html .= $h->course_data_tr( $row, "assessment_stat" );
            
            if ( ! empty( $data['cmp_missing'] ) ) {
                
                $row = "<strong>Не хватает компетенций</strong></td>\n";
                $row .= "<td></td>\n";
                $row .= "<td><strong>" . $c->get_cmp( $data['cmp_missing'] )  . "</strong>";
    
                $html .= $h->course_data_tr( $row, "assessment_stat error" );

            }

            $n++;
        }
        
        // Если полный формат - добавляем низ таблицы

        if ( $full ) {
            
            $html .= $h->table_footer();
            
        }
        
        return $html;
    }




    private $assessments_arr = array();
    private $curriculum_arr = array();
    private $matrix_arr = array();

    private $exam_names = array(
                            'зч' => 'Зачёт',
                            'зчо' => 'Аттестация с оценкой',
                            'эк' => 'Экзамен',
                            'кр' => 'Контрольная работа'
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