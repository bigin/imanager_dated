<?php
class PspController
{

    private $input;

    public function __construct($i) 
    {
        $this->input = $i;
    }

    public function listen()
    {
        
    }

    /** shows backend menu */
    public function backend()
    {
        
    }


    /**** ~Filter Methoden~ ****/


    public function is_value_int($value)
    {
        return is_int($value);
    }

    public function is_value_numeric($value)
    {
        return is_numeric($value);
    }

    public function is_value_float($value)
    {
        return is_float($value);        
    }

    public function format_number($number, $dec, $dec_pit, $ths_sep)
    {
        return number_format($number, $dec, $dec_pit, $ths_sep);
        //echo number_format($number, 2, ',', '.') . '&nbsp;€';
    }

    public function number2float($number)
    {
	    $string = trim((string) $number);
        // looking for a number
		if(!preg_match('/([0-9\.,-]+)/', $string, $matches))
        {
            return false;
        }
		
		$string = $matches[0];

		if(preg_match('/^[0-9.-\s]*[\,]{1}[0-9-]{0,2}$/', $string))
        {
		    /* Coma decimal separator
               Deletes dotts and convert comma to a dott */
			$string = str_replace(' ', '', $string);
			$string = str_replace('.', '', $string);
			$string = str_replace(',', '.', $string);
			return floatval($string);

		} elseif(preg_match('/^[0-9,-\s]*[\.]{1}[0-9-]{0,2}$/', $string))
        {
		    /* Dott as decimal separator
			   Delete commas */
			$string = str_replace(' ', '', $string);
			$string = str_replace(',', '', $string);
			return floatval($string);

		} elseif(preg_match('/^[0-9.-\s]*[\.]{1}[0-9-]{0,3}$/', $string))
        {
			/* Thousand separator detected
			   Delete dotts */
			$string = str_replace(' ', '', $string);
			$string = str_replace('.', '', $string);
			return floatval($string);

		} elseif(preg_match('/^[0-9,-\s]*[\,]{1}[0-9-]{0,3}$/', $string))
        {
			/* Thousand separator detected
			   Delete commas */
			$string = str_replace(' ', '', $string);
			$string = str_replace(',', '', $string);
			return floatval($string);
		}

		return floatval($string);
	}

}
?>
