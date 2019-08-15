<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Описание разделов</h1>

        <form method="post">
        <p>Описание разделов:<br/><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Матрица компетенций:<br/><textarea cols="100" rows="10" name="matrix"><?php echo $_REQUEST['matrix'] ?></textarea>
        <p>Примечание. Матрица компетенций нужна для уточнения компетенций, назначенных для разделов. Лишние компетенции удаляются,
        выводится информация о недостающих компетенциях. Если для раздела компетенции не указаны, но за дисциплиной закреплены
        одна или две компетенции, то они назначаются разделу автоматически.
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php
        
            include_once dirname( __FILE__ ) . '/classes/parts-class.php';
            
            $pr = new parts( $_REQUEST['data'], $_REQUEST['matrix'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                $arr = $pr->get_arr();
                p( $arr );
               
            } elseif ( isset( $_REQUEST['html'] ) ) {

                $html = $pr->get_html();
                echo $html;

            }


        ?>



    </body>
</html>