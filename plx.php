<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Загрузка учебного плана</h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <p><input type="file" name="file"></p>
            <p><input type="submit" name="submit" value="Загрузить"></p>
        </form>


        <?php

            include_once dirname( __FILE__ ) . '/classes/plx-class.php';

            $plx = new plx();

            $att = $plx->get_att();
            $courses = $plx->get_courses();
            $curriculum = $plx->get_curriculum();
            $matrix = $plx->get_matrix();
            $cmp = $plx->get_cmp();
            $kaf = $plx->get_kaf();

        ?>

        <p>Атрибуты образовательной программы:<br/><textarea cols="100" rows="10"><?php echo $att ?></textarea>
        <p>Модули и дисциплины:<br/><textarea cols="100" rows="10"><?php echo $courses ?></textarea>
        <p>Учебный план:<br/><textarea cols="100" rows="10"><?php echo $curriculum ?></textarea>
        <p>Матрица компетенций:<br/><textarea cols="100" rows="10"><?php echo $matrix ?></textarea>
        <p>Компетенции:<br/><textarea cols="100" rows="10"><?php echo $cmp ?></textarea>
        <p>Кафедры:<br/><textarea cols="100" rows="10"><?php echo $kaf ?></textarea>

    </body>
</html>