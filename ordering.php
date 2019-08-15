<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Порядок следования дисциплин</h1>

        <form method="post">
        <p>Матрица компетенций:<br/><textarea cols="100" rows="10" name="matrix"><?php echo $_REQUEST['matrix'] ?></textarea>
        <p>Учебный план:<br/><textarea cols="100" rows="10" name="curriculum"><?php echo $_REQUEST['curriculum'] ?></textarea>
        <p>Дисциплина: <input type="text" name="course" size="80" value="<?php echo $_REQUEST['course'] ?>">
        <p>Примечание. Дисциплины связаны, если они имеют какие-либо общие компетенции. Порядок следования уточняется по учебному 
        плану - одна раньше другой, если первый семестр первой меньше последнего семетра второй (и наоборот).
        <p><input type="submit" value="Отправить">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/ordering-class.php';

            $o = new ordering( $_REQUEST['curriculum'], $_REQUEST['matrix'] );

            p( 'Список связанных курсов:' );

            $arr = $o->get_related( $_REQUEST['course'] );
            p( $arr );
            
            p( 'Опирается на следующие дисциплины:' );
            
            $arr = $o->get_prev( $_REQUEST['course'] );
            p( $arr );

            p( 'Является основой для следующих дисциплин:' );
            
            $arr = $o->get_next( $_REQUEST['course'] );
            p( $arr );

            // $matrix = $m->get_arr();
            // $html = $m->get_html();

        ?>



    </body>
</html> 