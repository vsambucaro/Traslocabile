<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 9:41 AM
 */

class DBUtils {
    //private static $dbhost = "127.0.0.1:8889";
    private static $dbhost = "64.64.7.217";
    //private static $dbname = "traslocabile";
    //private static $dbusername="root";
    //private static $dbpassword = "root";

    private static $dbname = "traslocabile_preventivi";
    private static $dbusername="traslocabile_pre";
    private static $dbpassword = "WL4-Ye4-TXY-xTc";

    public static function getConnection() {
        //watchdog("TEAM_LOGIN","CONNECTION ".self::$dbhost, array(), WATCHDOG_DEBUG);

        $con =@mysql_pconnect(self::$dbhost, self::$dbusername, self::$dbpassword) or die(mysql_error());
        //$db = new mysqli(self::$dbhost, self::$dbusername, self::$dbpassword, self::$dbname) or die(mysql_error());
        @mysql_select_db(self::$dbname, $con) or die(mysql_error());
        $sql = "SET NAMES 'utf8'";
        @mysql_query($sql);
        //$db->query($sql);
        return $con;
    }

    public static function closeConnection($con) {
        if ($con != null) {
            mysql_close($con);
            //$con->close();
        }
    }
} 