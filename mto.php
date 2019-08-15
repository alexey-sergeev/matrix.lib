<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Материально-техническое обеспечение</h1>

        <form method="post">
        <p>Описание материально-технического обеспечения:<br/><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Учебный план:<br/><textarea cols="100" rows="10" name="curriculum"><?php echo $_REQUEST['curriculum'] ?></textarea>
        <p>Дисциплины (для их общего списка):<br /><textarea cols="100" rows="10" name="courses"><?php echo $_REQUEST['courses'] ?></textarea>        
        <p>Примечание. Если указан учебный план, то в список МТО добавляется информация по необходимым аудиториям (по видам занятий).
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php
        
            include_once dirname( __FILE__ ) . '/classes/mto-class.php';
            
            $m = new mto( $_REQUEST['data'], $_REQUEST['curriculum'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $arr = $m->get_arr();
                    p( $arr );
                    
                } else {

                    $arr = $m->compose( $_REQUEST['courses'], 'Общий список' );
                    p( $arr );

                }
               
            } elseif ( isset( $_REQUEST['html'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $html = $m->get_html();
                    echo $html;
                    
                } else {
                    
                    $arr = $m->compose( $_REQUEST['courses'], 'Общий список' );
                    $html = $m->get_html( $arr );
                    echo $html;

                }

            }


        ?>



    </body>
</html>