<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>ОПОП-парсер</h1>

        <form method="post">
        <p>Текстовое описание чего-либо:<br /><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p><input type="submit" name="full" value="Полный анализ">
        <input type="submit" name="no_att" value="Без атрибутов">
        <input type="submit" name="no_marker" value="Без маркеров">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/parser-class.php';
            
            $p = new parser();

            if ( isset( $_REQUEST['full'] ) ) {

                $arr = $p->get_arr( $_REQUEST['data'], array( 'att_name' => true ) );
                p( $arr );

            } elseif ( isset( $_REQUEST['no_att'] ) ) {
                
                $arr = $p->get_arr( $_REQUEST['data'], array( 'att_name' => false ) );
                p( $arr );

            } elseif ( isset( $_REQUEST['no_marker'] ) ) {
                
                $arr = $p->get_arr( $_REQUEST['data'], array( 'att_name' => true, 'nomarker' => true ) );
                p( $arr );

            }

        ?>



    </body>
</html>