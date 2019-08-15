<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Информационные технологии</h1>

        <form method="post">
        <p>Список дисциплин и информационных технологий<br /><textarea cols="100" rows="10" name="it"><?php echo $_REQUEST['it'] ?></textarea>
        <p>Дисциплины (для их общего списка):<br /><textarea cols="100" rows="10" name="courses"><?php echo $_REQUEST['courses'] ?></textarea>
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">
        <input type="submit" name="inet" value="Все источники">
        <input type="submit" name="app" value="Всё ПО">

        </form>


        <?php
        
            include_once dirname( __FILE__ ) . '/classes/it-class.php';
            
            $i = new it( $_REQUEST['it'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $arr = $i->get_arr();
                    p( $arr );
                    
                } else {

                    $arr = $i->compose( $_REQUEST['courses'], 'Общий список' );
                    p( $arr );

                }


            } elseif ( isset( $_REQUEST['html'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $html = $i->get_html();
                    echo $html;
                    
                } else {
                    
                    $arr = $i->compose( $_REQUEST['courses'], 'Общий список' );
                    $html = $i->get_html( $arr );
                    echo $html;

                }


            } elseif ( isset( $_REQUEST['inet'] ) ) {

                $arr = $i->get_all( 'inet' );
                p( $arr );

            } elseif ( isset( $_REQUEST['app'] ) ) {

                $arr = $i->get_all( 'app' );
                p( $arr );

            }


        ?>



    </body>
</html>