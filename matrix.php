<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Матрица компетенеций</h1>

        <form method="post">
        <p>Описание дисциплин и закрепленных компетенций<br /><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Разрешенные компетенции<br /><textarea cols="100" rows="10" name="cmp"><?php echo $_REQUEST['cmp'] ?></textarea>
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/matrix-class.php';
            
            $m = new matrix( $_REQUEST['data'], $_REQUEST['cmp'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                $arr = $m->get_arr();
                p( $arr );

            } elseif ( isset( $_REQUEST['html'] ) ) {

                $html = $m->get_html();
                echo $html;

            }

        ?>



    </body>
</html>