<?php

// 
// Удаляет из строки переводы строк и двойные пробелы
// 

function strim( $str )
{
    $str = trim( $str );
    $str = preg_replace( '/\s+/', ' ', $str );
    
    return $str;
}
    
// 
// Оформляет все строки массива в виде одного блока текста
// 

function arr_to_text( $arr )
{
    return strim( implode( ' ', $arr ) );
}


// 
// Вывод отладочной информации
// 

if ( ! function_exists( "p") ) {
    
    function p( $t )
    {
        print_r( "<pre>" );
        print_r( $t );
        print_r( "</pre>" );
    }

}


?>
