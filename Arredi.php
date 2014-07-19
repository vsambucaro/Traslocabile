<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 6:58 PM
 */

class Arredi {

    const TIPO_ARREDI_PREVENTIVO_ISTANTANEO = 1;
    const TIPO_ARREDI_PREVENTIVO_DETTAGLIO = 0;

    public function getArredi($tipo = Arredi::TIPO_ARREDI_PREVENTIVO_DETTAGLIO, $ambiente=null)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT id FROM arredi WHERE preventivatore_istantaneo=$tipo";

        if ($ambiente)
            $sql .= " AND ambiente='$ambiente'";

        if (!$ambiente)
            $sql .=" ORDER BY ambiente ASC";
        else
            $sql .=" ORDER BY arredo ASC";

        $res = mysql_query($sql);
        $lista_arredi = array();
        while ($row=mysql_fetch_object($res))
        {
            if ($tipo == Arredi::TIPO_ARREDI_PREVENTIVO_ISTANTANEO)
                $lista_arredi[] = new ArredoIstantaneo($row->id);
            else
               $lista_arredi[] = new ArredoDettagliato($row->id);

        }
        DBUtils::closeConnection($con);

        return $lista_arredi;
    }

}
