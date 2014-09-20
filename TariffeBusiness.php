<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/23/14
 * Time: 11:13 PM
 */

class TariffeBusiness {

    private $id_cliente = null;
    private $tipologia_cliente = null;

    public function __construct($id_cliente, $tipologia_cliente)
    {
        $this->id_cliente = $id_cliente;
        $this->tipologia_cliente = $tipologia_cliente;
    }

    public function getCostoScaricoRicaricoHub($mc_mese, $mc)
    {
        return $this->_getCostoServizioBusiness($mc_mese , $mc, 'SCARICO_RICARICO_MERCE_PRESSO_HUB');
    }


    public function getCostoMontaggio($mc_mese, $mc)
    {
        return $this->_getCostoServizioBusiness($mc_mese , $mc, 'MONTAGGIO');
    }

    public function getCostoTrazione($mc_mese, $km, $mc)
    {
        if ($km<=50)
            return $this->_getCostoServizioBusiness($mc_mese , $mc, 'TRAZIONE_DISTRIBUZIONE_HUB_CLIENTE_ENTRO_50KM');
        else
            return $this->_getCostoServizioBusiness($mc_mese , $mc, 'TRAZIONE_DISTRIBUZIONE_HUB_CLIENTE_OLTRE_50KM');
    }


    public  function getCostoScarico($mc_mese, $mc)
    {
        return $this->_getCostoServizioBusiness($mc_mese , $mc, 'SCARICO');
    }

    public  function getCostoSalitaPiano($mc_mese, $mc)
    {
        return $this->_getCostoServizioBusiness($mc_mese , $mc, 'SALITA_PIANO');
    }


    private  function _getCostoServizioBusiness($mc_mese, $mc, $tipo_servizio)
    {

        $con = DBUtils::getConnection();
        $id_cliente = $this->id_cliente;
        $tipologia_cliente = $this->tipologia_cliente;
        $numero_record = 0;
        if ($id_cliente != null)
        {
            $tmp_sql = "SELECT count(*) as numero_record FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio' AND id_cliente='$id_cliente'";
            $res = mysql_query($tmp_sql);

            while ($row = mysql_fetch_object($res))
            {
                $numero_record = $row->numero_record;
            }

        }

        //se ci sono record per quello specifico id_utente allora ha delle tariffe personalizzate
        //altrimenti seleziona le tariffe in base alla categoria (mobilieri, cucinieri, etc.)
        $sql = '';
        if ($numero_record>0)
            $sql = "SELECT * FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio' AND mc>=$mc_mese and id_cliente='$id_cliente' ORDER BY mc ASC";
        else
            $sql = "SELECT * FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio' AND mc>=$mc_mese and tipologia_cliente='$tipologia_cliente' ORDER BY mc ASC";

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

        echo "\nmc_mese: $mc_mese, mc: $mc, servizio: $tipo_servizio, costo:$costo\n";
        return $costo;

    }

    public static function getListaTipologie()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT DISTINCT tipologia_cliente FROM tariffe_servizi_business";
        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
        {
            $lista[] = $row->tipologia_cliente;
        }

        DBUtils::closeConnection($con);
        return $lista;

    }

    public function getTabella($tipo_servizio)
    {
        $con = DBUtils::getConnection();
        $id_cliente = $this->id_cliente;
        $tipologia_cliente = $this->tipologia_cliente;
        $numero_record = 0;
        if ($id_cliente != null)
        {
            $tmp_sql = "SELECT count(*) as numero_record FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio' AND id_cliente='$id_cliente'";
            $res = mysql_query($tmp_sql);

            while ($row = mysql_fetch_object($res))
            {
                $numero_record = $row->numero_record;
            }

        }

        //se ci sono record per quello specifico id_utente allora ha delle tariffe personalizzate
        //altrimenti seleziona le tariffe in base alla categoria (mobilieri, cucinieri, etc.)
        $sql = '';
        if ($numero_record>0)
            $sql = "SELECT * FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio'  AND id_cliente='$id_cliente' ORDER BY mc ASC";
        else
            $sql = "SELECT * FROM tariffe_servizi_business WHERE descrizione = '$tipo_servizio'  AND tipologia_cliente='$tipologia_cliente' ORDER BY mc ASC";

        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
        {
            $lista[] = $row;
        }

        DBUtils::closeConnection($con);
    }

    public function updateTabella($lista)
    {
        if ($lista==null) return; //niente da aggiornare
        $con = DBUtils::getConnection();
        foreach ($lista as $item)
        {
            $sql ='';
            if ($item->id != null)
            {
                $sql = "UPDATE tariffe_servizi_business SET
                mc = $item->mc,
                costo = $item->costo,
                id_cliente = $item->id_cliente,
                tipologia_cliente = $item->tipologia_cliente";
            }
            else
            {
                $sql = "INSERT INTO tariffe_servizi_business (descrizione, mc, costo, id_cliente, tipologia_cliente)
                VALUES ('$item->descrizione',$item->mc, $item->costo,$item->id_cliente,'$item->tipologia_cliente')";

            }

            $res = mysql_query($sql);
        }

        DBUtils::closeConnection($con);
    }

} 