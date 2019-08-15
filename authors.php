<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Списки разработчиков</h1>

        <form method="post">
        <p>Дисциплины и разработчики:<br /><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Дисциплины (для общего списка разработчиков):<br /><textarea cols="100" rows="10" name="courses"><?php echo $_REQUEST['courses'] ?></textarea>
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/authors-class.php';
            
            $a = new authors( $_REQUEST['data'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $arr = $a->get_arr();
                    p( $arr );
                    
                } else {

                    $arr = $a->compose( $_REQUEST['courses'], 'Общий список' );
                    p( $arr );

                }


            } elseif ( isset( $_REQUEST['html'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $html = $a->get_html();
                    echo $html;
                    
                } else {
                    
                    $arr = $a->compose( $_REQUEST['courses'], 'Общий список' );
                    $html = $a->get_html( $arr );
                    echo $html;

                }
             
            }


        ?>



    </body>
</html>