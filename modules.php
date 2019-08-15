<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Модули и дисциплины</h1>

        <form method="post">
        <p>Описание модулей и дисциплин:<br/><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p><input type="submit" name="arr_modules" value="Массив модулей">
        <input type="submit" name="arr_courses" value="Массив дисциплин">
        <input type="submit" name="tree_courses" value="Дерево дисциплин">
        <p><input type="submit" name="html_modules" value="HTML модулей">
        <input type="submit" name="html_courses" value="HTML массива дисциплин">
        <input type="submit" name="html_tree" value="HTML дерева дисциплин">

        </form>


        <?php
        
            include_once dirname( __FILE__ ) . '/classes/modules-class.php';
            
            $m = new modules( $_REQUEST['data'] );

            if ( isset( $_REQUEST['arr_modules'] ) ) {

                $arr = $m->get_modules();
                p( $arr );
               
            } elseif ( isset( $_REQUEST['arr_courses'] ) ) {

                $arr = $m->get_courses();
                p( $arr );

            } elseif ( isset( $_REQUEST['tree_courses'] ) ) {

                $arr = $m->get_tree();
                p( $arr );

            } elseif ( isset( $_REQUEST['html_modules'] ) ) {

                $html = $m->get_html();
                echo $html;

            } elseif ( isset( $_REQUEST['html_courses'] ) ) {

                $arr = $m->get_courses();
                $html = $m->get_html( $arr );
                echo $html;

            } elseif ( isset( $_REQUEST['html_tree'] ) ) {

                $arr = $m->get_tree();
                $html = $m->get_html( $arr );
                echo $html;

            }


        ?>



    </body>
</html>