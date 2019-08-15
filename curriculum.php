<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Учебный план</h1>

        <form method="post">
        <p>Описание дисциплин, семестров, часов и видов аттестации:<br/><textarea cols="100" rows="10" name="opop"><?php echo $_REQUEST['opop'] ?></textarea>
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/curriculum-class.php';
            
            $c = new curriculum( $_REQUEST['opop'] );


            if ( isset( $_REQUEST['arr'] ) ) {

                $arr = $c->get_arr();
                p( $arr );

            } elseif ( isset( $_REQUEST['html'] ) ) {

                $html = $c->get_html();
                echo $html;
             
            }


        ?>



    </body>
</html>