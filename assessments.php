<html>
    <head>

    </head>
    <body>

        <p><a href="index.php">Начало</a></p>

        <h1>Оценочные средства</h1>

        <form method="post">
        <p>Описание оценочных средств:<br/><textarea cols="100" rows="10" name="data"><?php echo $_REQUEST['data'] ?></textarea>
        <p>Учебный план:<br/><textarea cols="100" rows="10" name="curriculum"><?php echo $_REQUEST['curriculum'] ?></textarea>
        <p>Матрица компетенеций:<br/><textarea cols="100" rows="10" name="matrix"><?php echo $_REQUEST['matrix'] ?></textarea>
        <p>Примечание. Учебный план нужен для распределения оценочных средств по семестрам и указания средств промежуточной аттестации.
        Средства промежуточной аттестации (зачет, зачет с оценкой, экзамен) добавляются автоматически. 
        Если какое-либо из этих средств описано в перечне, то из описания берется только список компетенций.
        <p>Примечание 2. Матрица компетенций нужна для уточнения компетенций, закрепленных за оценочными средствами. Если для
        оценочного средства компетенции не указаны, а дисциплина содержит одну или две компетенции, то они за оценочными средством 
        и закрепляются.
        <p><input type="submit" name="arr" value="Массив">
        <input type="submit" name="html" value="HTML">

        </form>


        <?php
        
            include_once dirname( __FILE__ ) . '/classes/assessments-class.php';
            
            $cn = new assessments( $_REQUEST['data'], $_REQUEST['curriculum'], $_REQUEST['matrix'] );

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