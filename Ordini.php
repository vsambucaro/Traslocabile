<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/11/14
 * Time: 9:55 PM
 */

class Ordini
{

    private $lista_ordini = array();



    public function getListaOrdini($filter = null, $filtro_cliente = null, $filtro_trasportatore=null, $filtro_traslocatore=null,
                                       $filtro_agenzia = null, $filtro_depositario = null)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT id_preventivo FROM preventivi WHERE tipo=".Ordine::TIPO_ORDINE;

        $first = false;
        if ($filter || $filtro_cliente || $filtro_trasportatore || $filtro_traslocatore || $filtro_agenzia || $filtro_depositario)
        {

            if (is_array($filter) && array_key_exists('id',$filter))
            {
                if ($first)
                {
                    $sql .=" id_preventivo=".$filter['id'];
                    $first = false;
                }
                else
                {
                    $sql .=" AND id_preventivo=".$filter['id'];
                    $first = false;
                }

            }

            if ( is_array($filter) && (array_key_exists('dal', $filter)) && (array_key_exists('al', $filter)))
            {
                if ($first)
                {
                    $sql .=" data BETWEEN '".$filter['dal']."' AND '".$filter['al']."'";
                    $first = false;
                }
                else
                {
                    $sql .=" AND data BETWEEN '".$filter['dal']."' AND '".$filter['al']."'";
                    $first = false;
                }
            }
            else if (is_array($filter) && array_key_exists('dal', $filter))
            {
                if ($first)
                {
                    $sql .=" data >= '".$filter['dal']."'";
                    $first = false;
                }
                else
                {
                    $sql .=" AND data >= '".$filter['dal']."'";
                    $first = false;
                }
            }
            else if (is_array($filter) && array_key_exists('al', $filter))
            {
                if ($first)
                {
                    $sql .=" data <= '".$filter['al']."'";
                    $first = false;
                }
                else
                {
                    $sql .=" AND data <= '".$filter['al']."'";
                    $first = false;
                }
            }
            if (is_array($filter) && array_key_exists('status', $filter))
            {
                if ($first)
                {
                    $sql .=" stato='".$filter['status']."'";
                    $first = false;
                }
                else
                {
                    $sql .=" AND stato='".$filter['status']."'";
                    $first = false;
                }
            }

            //indica se voglio vedere solo quelli saldati oppure no
            if (is_array($filter) && array_key_exists('saldato_cliente', $filter))
            {
                //Se saldato_cliente = 1; visualizza solo ordini saldati;
                //Se saldato_cliente = 0; visualizza solo ordini non saldati;
                if ($first)
                {
                    $sql .=" saldato_cliente='".$filter['saldato_cliente']."'";
                    $first = false;
                }
                else
                {
                    $sql .=" AND saldato_cliente='".$filter['saldato_cliente']."'";
                    $first = false;
                }
            }

        }

        //filtro per cliente
        if ($filtro_cliente)
        {
            if ($first)
            {
                $sql .=" id_cliente=".$filtro_cliente;
                $first = false;
            }
            else
            {
                $sql .=" AND id_cliente=".$filtro_cliente;
                $first = false;
            }
        }

        //filtro per trasportatore
        if ($filtro_trasportatore)
        {
            if ($first)
            {
                $sql .=" id_trasportatore=".$filtro_trasportatore;
                $first = false;
            }
            else
            {
                $sql .=" AND id_trasportarore=".$filtro_trasportatore;
                $first = false;
            }
        }

        //filtro per trasportatore
        if ($filtro_depositario)
        {
            if ($first)
            {
                $sql .=" id_depositario=".$filtro_depositario;
                $first = false;
            }
            else
            {
                $sql .=" AND id_depositario=".$filtro_depositario;
                $first = false;
            }
        }

        //filtro per traslocatore
        if ($filtro_traslocatore)
        {
            if ($first)
            {
                $sql .=" (id_traslocatore_partenza=".$filtro_traslocatore." OR id_traslocatore_destinazione=".$filtro_traslocatore.")";
                $first = false;
            }
            else
            {
                $sql .=" AND (id_traslocatore_partenza=".$filtro_traslocatore." OR id_traslocatore_destinazione=".$filtro_traslocatore.")";
                $first = false;
            }
        }

        //filtro per agenzia
        if ($filtro_agenzia)
        {
            if ($first)
            {
                $sql .=" id_agenzia=".$filtro_agenzia;
                $first = false;
            }
            else
            {
                $sql .=" AND id_agenzia=".$filtro_agenzia;
                $first = false;
            }
        }


       //echo "\nSQL : ".$sql;

        $res = mysql_query($sql);

        while ($row=mysql_fetch_object($res))
        {

            $ordine = new Ordine($row->id_preventivo);
            $this->lista_ordini[] = $ordine;

        }
        DBUtils::closeConnection($con);
        /*
                return array('preventivi'=> $this->lista_preventivi,
                    'history_trasportatori'=> $this->_getHistoryTrasportatori($this->lista_preventivi) ,
                    'history_traslocatori'=> $this->_getHistoryTraslocatori($this->lista_preventivi)
                );
        */
        return $this->lista_ordini;
    }
}