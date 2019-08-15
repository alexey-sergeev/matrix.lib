<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Методические указания</h1>

        <form method="post">
        <p>Дисциплины и метдические указания:<br/><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Учебный план:<br/><textarea cols="100" rows="10" name="curriculum"><?php echo $_REQUEST['curriculum'] ?></textarea>
        <p>Примечание. Если методические указания для дисциплины не указаны, то они составляются автоматически на основе данных
        учебного плана.
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/guidelines-class.php';

            $g = new guidelines( $_REQUEST['data'], $_REQUEST['curriculum'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                $arr = $g->get_arr();
                p( $arr );
               
            } elseif ( isset( $_REQUEST['html'] ) ) {

                $html = $g->get_html();
                echo $html;

            }

        ?>



    </body>
</html> 