<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/23/14
 * Time: 11:13 PM
 */

class TariffeSnaidero {

    public static function getCostoScaricoRicaricoHub($mc_mese, $mc, $tipo_algoritmo)
    {
        return TariffeSnaidero::_getCostoServizioBusiness($mc_mese , $mc, 'SCARICO_RICARICO_MERCE_PRESSO_HUB');
    }


    public static function getCostoMontaggio($mc_mese, $mc, $tipo_algoritmo)
    {
        return TariffeSnaidero::_getCostoServizioBusiness($mc_mese , $mc, 'MONTAGGIO');
    }

    public static function getCostoTrazione($mc_mese, $km, $mc, $tipo_algoritmo)
    {
        if ($km<=50)
            return TariffeSnaidero::_getCostoServizioBusiness($mc_mese , $mc, 'TRAZIONE_DISTRIBUZIONE_HUB_CLIENTE_ENTRO_50KM');
        else
            return TariffeSnaidero::_getCostoServizioBusiness($mc_mese , $mc, 'TRAZIONE_DISTRIBUZIONE_HUB_CLIENTE_OLTRE_50KM');
    }


    public static function getCostoScarico($mc_mese, $mc, $tipo_algoritmo)
    {
        return TariffeSnaidero::_getCostoServizioBusiness($mc_mese , $mc, 'SCARICO');
    }

    public static function getCostoSalitaPiano($mc_mese, $mc, $tipo_algoritmo)
    {
        return TariffeSnaidero::_getCostoServizioBusiness($mc_mese , $mc, 'SALITA_PIANO');
    }


    private static function _getCostoServizioBusiness($mc_mese, $mc, $tipo_servizio)
    {

        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM tariffe_servizi_snaidero WHERE descrizione = '$tipo_servizio' AND mc>=$mc_mese ORDER BY mc ASC";
        $res = mysql_query($sql);
        $costo = 0;
        $mc_tmp = $mc;
        while ($row = mysql_fetch_object($res))
        {
            if ( ($mc_mese + $mc_tmp) > $row->mc )
            {
                $costo += ( ($row->mc - $mc_mese) * $row->costo);

                //echo "\nR: ".($row->mc - $mc_mese);

                $mc_tmp = $mc_tmp - ($row->mc - $mc_mese);
                $mc_mese = ($row->mc );

                //echo "\nMc_mese:".$mc_mese." mc: ".$mc_tmp;
            }
            else
            {
                $costo += ($mc_tmp * $row->costo);
                //echo "\nF: ".$mc_tmp;
                break;
            }
        }

        DBUtils::closeConnection($con);
        return $costo;

    }

    private static function _getCostoServizioBusinessOLD($mc_mese, $mc, $tipo_servizio)
    {

        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio' AND mc>=$mc_mese ORDER BY mc ASC LIMIT 1";
        $res = mysql_query($sql);
        $costo = 0;
        while ($row = mysql_fetch_object($res))
        {
                $costo = $row->costo;
        }

        DBUtils::closeConnection($con);
        return $costo;

    }
} 