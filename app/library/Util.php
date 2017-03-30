<?php
use Phalcon\DI;

class Util {

    /**
     * Converte um objeto para array
     * @param  object $d Objeto a ser convertido
     * @return array    Array convertido
     */     
    public function objectToArray($d) {
        if (is_object($d))
            $d = get_object_vars($d);

        return is_array($d) ? array_map(__METHOD__, $d) : $d;
    }

    /**
     * Converter array para objeto
     * @param  array $d Array a ser convertido
     * @return object Objeto convertido     
     */
    public function arrayToObject($d) {
        return is_array($d) ? (object) array_map(__METHOD__, $d) : $d;
    }

    //Method for generate msg id (ugid)
    public function generateUGID($length = 2)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $random_string = substr( str_shuffle( $chars ), 0, $length );
        return '13'.$random_string.uniqid();
    }

    public function hexUnicodeConvert($content)
    {
        $str = preg_replace_callback('/([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $content);
        return $str;
    }
}
?>