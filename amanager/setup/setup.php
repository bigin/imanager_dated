<?php
class AmSetupDb
{
    public static function setup($ampdo, $dbschema)
    {
        try
        {
            // Set errormode to exceptions
            $ampdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // create system settings table
            $ampdo->exec(file_get_contents(GSPLUGINPATH.AMPLUGIN.'/setup/db/'.$dbschema));
            return true;

        } catch(PDOException $e)
        {
            echo $e->getMessage();
            return false;
        }
    }
}
?>
