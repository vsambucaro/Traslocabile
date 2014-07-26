<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/11/14
 * Time: 9:55 PM
 */

class OrdiniClienti
{

    private $lista_ordini = array();
    const TIPOLOGIA_CLIENTE_CONSUMER = 0;
    const TIPOLOGIA_CLIENTE_BUSINESS = 1;

    public function getListaOrdini($filter = null, $filtro_cliente = null, $filtro_trasportatore=null, $filtro_traslocatore=null,
                                   $filtro_agenzia = null, $filtro_depositario = null, $filtro_tipologia_cliente = null)
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

            if (is_array($filter) && array_key_exists('tipologia_cliente', $filter))
            {
                if ($first)
                {
                    $sql .=" tipologia_cliente='".$filter['tipologia_cliente']."'";
                    $first = false;
                }
                else
                {
                    $sql .=" AND tipologia_cliente='".$filter['tipologia_cliente']."'";
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

        //filtro per tipologia cliente
        if ($filtro_tipologia_cliente)
        {
            if ($first)
            {
                $sql .=" tipologia_cliente=".$filtro_tipologia_cliente;
                $first = false;
            }
            else
            {
                $sql .=" AND tipologia_cliente=".$filtro_tipologia_cliente;
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

            if ($row->tipologia_cliente == Ordini::TIPOLOGIA_CLIENTE_BUSINESS)
                $ordine = new OrdineClienteBusiness($row->id_preventivo);
            else
                $ordine = new OrdineCliente($row->id_preventivo);

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