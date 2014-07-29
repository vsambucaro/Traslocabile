<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/30/14
 * Time: 8:09 AM
 */

class Preventivi {

    private $lista_preventivi = array();



    public function getListaPreventivi($filter = null, $filtro_cliente = null, $filtro_trasportatore=null, $filtro_traslocatore=null,
    $filtro_agenzia = null, $filtro_depositario = null)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM preventivi WHERE tipo=".Preventivo::TIPO_PREVENTIVO;
        //$sql = "SELECT id_preventivo FROM preventivi ";

        //$first = true;
        $first = false;
        if ($filter || $filtro_cliente || $filtro_trasportatore || $filtro_traslocatore || $filtro_agenzia || $filtro_depositario)
        {
            //$sql .=" WHERE ";

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
                $sql .=" AND id_trasportatore=".$filtro_trasportatore;
                $first = false;
            }
        }

        //filtro per depositario
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
            
            //echo "\nLoading:".$row->id_preventivo;

            if ($row->tipologia_cliente == Ordini::TIPOLOGIA_CLIENTE_BUSINESS)
            {
                $preventivo = new PreventivoBusiness();
                $preventivo->load($row->id_preventivo);
                $this->lista_preventivi[] = $preventivo;

            }
            else
            {
                $preventivo = new Preventivo();
                $preventivo->load($row->id_preventivo);
                $this->lista_preventivi[] = $preventivo;
            }
        }

        DBUtils::closeConnection($con);
/*
        return array('preventivi'=> $this->lista_preventivi,
            'history_trasportatori'=> $this->_getHistoryTrasportatori($this->lista_preventivi) ,
            'history_traslocatori'=> $this->_getHistoryTraslocatori($this->lista_preventivi)
        );
*/
        return $this->lista_preventivi;
    }


    private function _getHistoryTrasportatori($lista_preventivi)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT id FROM history_trasportatori";

        $history_trasportatori = array();

        foreach ($lista_preventivi as $preventivo)
        {
            $assegnazione = new AssegnazioneTrasportatore();
            $assegnazione->load($preventivo->getId());
            $this->history_trasportatori[] = $assegnazione;
        }

        return $history_trasportatori;

    }

    private function _getHistoryTraslocatori($lista_preventivi)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT id FROM history_trasportatori";

        $history_traslocatori = array();

        foreach ($lista_preventivi as $preventivo)
        {
            $assegnazione = new AssegnazioneTraslocatore();
            $assegnazione->load($preventivo->getId());
            $this->history_traslocatori[] = $assegnazione;
        }

        return $history_traslocatori;
    }


} 