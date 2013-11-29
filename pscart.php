<?php 
// include the model class of the Item Manager
include(GSPLUGINPATH.'imanager/class/im.model.class.php');

class PsCart
{ 
    private $value = array();
    private $im;

    public function __construct($input) {
        $this->im = new ImModel($input);
    }

    public function insertItem($item) 
    {
        if (isset($this->value[$item])) 
        {
            $this->value[$item]++;
        } else 
        { 
            $this->value[$item] = 1; 
        }
    }


    public function countItems() 
    { 
        return  count($this->value); 
    } 

    public function removeItem($artikel) 
    { 
        if (isset($this->value[$artikel])) 
        { 
            if($this->value[$artikel]<=1) 
                unset($this->value[$artikel]); 
            else $this->value[$artikel]--; 
        } 
    } 

    public function emptyCart() 
    { 
        $this->value = Array(); 
    } 

    public function getValue() 
    { 
        return $this->value; 
    }


} 

session_start(); 


if(!isset($_SESSION['pscart'])) 
  $_SESSION['pscart'] = new PsCart;

elseif(!($_SESSION['pscart'] instanceof PsCart))
  throw new Exception('Warenkorb kaputt (schon mit was anderm belegt)'); 

if(isset($_GET['item'])) 
  $_SESSION['pscart']->insertItem($_GET['item']);

if (isset($_GET['remove'])) 
  $_SESSION['pscart']->removeItem($_GET['remove']);

if (isset($_GET['empty']))  
   $_SESSION['pscart']->emptyCart(); 

?> 
<html> 
    <body> 
        <h1>Warenkorb</h1> 
        <a href="?loeschen=1">Warenkorb löschen</a> <br> 
<?php 
if(count($_SESSION['korb'])) 
{ 
  print "Sie haben folgende Artikel ausgewählt:<br> "; 
  foreach ($_SESSION['korb']->gibkorb() as $artikel => $anzahl) 
  { 
    print "Artikel $artikel Anzahl: $anzahl  "; 
    print "<a href='?entf=$artikel'>"; 
    print "Artikel entfernen</a><br>"; 
  } 
}else 
{ 
  print "Bisher nichts ausgewählt"; 
} 

print "    <h1>Folgende Artikel können Sie bestellen</h1>"; 
foreach($vorrat as $ding) 
 print "<a href='?artikel=$ding'>$ding in Warenkorb legen</a><br>"; 
?> 

