<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Компетенции</h1>

        <form method="post">
        <p>Наборы индексов компетенций в любом формате:<br /><textarea cols="100" rows="10" name="cmp1"><?php echo $_REQUEST['cmp1'] ?></textarea>
        <p><textarea cols="100" rows="10" name="cmp2"><?php echo $_REQUEST['cmp2'] ?></textarea>
        <p><input type="submit" value="Отправить">

        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/cmp-class.php';

            // $cmp = new cmp( "OК-1-8, ОПК-1-6, ПК-1-10" );
            $cmp = new cmp();
            $str1 = $cmp->get_cmp( $_REQUEST['cmp1'] );
            $str1_full = $cmp->get_cmp( $_REQUEST['cmp1'], 'full' );
            $str1_arr = $cmp->get_cmp( $_REQUEST['cmp1'], 'arr' );


            $str2 = $cmp->get_cmp( $_REQUEST['cmp2'] );


        ?>

        <p><strong>Оформление набора 1</strong>

        <p>Краткий формат:
        <?php p( $str1 ); ?>

        <p>Подробный формат:
        <?php p( $str1_full ); ?>

        <p>Массив:
        <?php p( $str1_arr ); ?>

        <p><strong>Операции над наборами</strong>

        <p>Набор 1:
        <?php p( $str1 ); ?>

        <p>Набор 2:
        <?php p( $str2 ); ?>

        <p>Объединение:
        <?php p( $cmp->union( $str1, $str2 ) ); ?>

        <p>Пересечение:
        <?php p( $cmp->intersection( $str1, $str2 ) ); ?>

        <p>Разность:
        <?php p( $cmp->difference( $str1, $str2 ) ); ?>




    </body>
</html>