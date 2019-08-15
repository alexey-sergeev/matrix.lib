<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Списки литературы</h1>

        <form method="post">
        <p>Список дисциплин и литературы<br /><textarea cols="100" rows="10" name="biblio"><?php echo $_REQUEST['biblio'] ?></textarea>
        <p>Дисциплины (для их общего списка):<br /><textarea cols="100" rows="10" name="courses"><?php echo $_REQUEST['courses'] ?></textarea>
        <p>Примечание. Если указаны дисциплины, то строится их общий список литературы. Строгий - объединяются парами списки основной 
        и списки дополнительной литературы. Если в основной и дополнительной оказываются одинаковые книги, то они учитываются в основной.
        Сбалансированный - книга попадает в основной или в дополнительный список в зависимости от частоты там встречаемости.
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">
        <input type="submit" name="all" value="Все книги">

        </form>


        <?php
        
            include_once dirname( __FILE__ ) . '/classes/biblio-class.php';
            
            $b = new biblio( $_REQUEST['biblio'] );

            if ( isset( $_REQUEST['arr'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $biblio = $b->get_arr();
                    p( $biblio );
                    
                } else {
                    
                    $arr = $b->compose( $_REQUEST['courses'], 'Общий список (строгий)' );
                    p( $arr );
                    
                    $arr = $b->compose( $_REQUEST['courses'], 'Общий список (сбалансированный)', 'balanced' );
                    p( $arr );
                    
                }
                

            } elseif ( isset( $_REQUEST['html'] ) ) {

                if ( empty( $_REQUEST['courses'] ) ) {
                    
                    $html = $b->get_html();
                    echo $html;
                    
                } else {
                    
                    $arr = $b->compose( $_REQUEST['courses'], 'Общий список (строгий)' );
                    $html = $b->get_html( $arr );
                    echo $html;
    
                    $arr = $b->compose( $_REQUEST['courses'], 'Общий список (сбалансированный)', 'balanced' );
                    $html = $b->get_html( $arr );
                    echo $html;
                    
                }

            } elseif ( isset( $_REQUEST['all'] ) ) {

                $arr = $b->get_all();
                p($arr);

            }


        ?>



    </body>
</html>