<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Цели и содержание дисциплин</h1>

        <form method="post">
        <p>Описание целей, содержания и трудоемкости разделов:<br/><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Учебный план:<br/><textarea cols="100" rows="10" name="curriculum"><?php echo $_REQUEST['curriculum'] ?></textarea>
        <p>Примечание. Учебный план нужен для уточнения часов по разделам. Эти часы приводятся к правдоподобным показателям - сумма
        по разделам совпадает с общим колличеством часов по дисциплине. При этом указанные в разделах часы используются как весовые
        коэффициенты. В разделах можно указывать просто одно число - вес раздела для автоматического расчета запланированных часов.
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">
        </form>



        <?php
        
            include_once dirname( __FILE__ ) . '/classes/content-class.php';
            
            $cn = new content( $_REQUEST['data'], $_REQUEST['curriculum'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                $arr = $cn->get_arr();
                p( $arr );
               
            } elseif ( isset( $_REQUEST['html'] ) ) {

                $html = $cn->get_html();
                echo $html;

            }


        ?>



    </body>
</html>