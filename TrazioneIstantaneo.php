<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 9:40 AM
 */

class TrazioneIstantaneo {

    /**
     * Ritorna il costo della trazione
     * @param $mc metri cubi
     * @param $km km su cui calcolare il costo
     */
    public static function getCostoMC($mc, $km) {
        $km = str_replace(',','',$km);
        $con = DBUtils::getConnection();
        $sql = "SELECT costo FROM trazione_istantaneo WHERE mc>=$mc  AND km>=$km ORDER BY mc ASC , km ASC LIMIT 1";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);
        $costo = 0;
        while ($row=mysql_fetch_object($res)) {
            $costo = $row->costo;

        }
        DBUtils::closeConnection($con);
        return $costo;
    }

} 