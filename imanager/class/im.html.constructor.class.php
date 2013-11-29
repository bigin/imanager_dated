<?php 
/**
* ~HTML-Constructor~
*/
class ImHtmlConstructor
{

	private $imo;

	public function __construct(&$imo)
	{
		$this->imo = $imo;
	}

	public function tpl_init() 
	{

	}

	public function item_list_generator(array $item_data) 
	{
		$tpl = ImModel::getTplKit('itemregister');
        $tpl[4] = ImModel::getTplKit('contentwrapper');
		// buffered templates
        $globbuf = $tpl[1];
		$tmp_row_o = '';
		$i = 0;
    	foreach($item_data['pagedata']['itemkeys'] as $key)
        {
            $globbuf = $tpl[1];
            $tmp_vic_o = $tpl[2];
            $tmp_pic_o = $tpl[3];

            //$tmp_row_o .= $tpls[1];

            $id = $item_data['struct'][$key]['slug'];
		    $file = ImModel::getProp('paths', 'uploaddir').'/'.$item_data['struct'][$key]['name'];
		    $date = $item_data['struct'][$key]['date'];
		    $title = html_entity_decode($item_data['struct'][$key]['title'], ENT_QUOTES, 'UTF-8');
            //$cat = html_entity_decode($this->im->items_ordered_struct[$key]['category'], ENT_QUOTES, 'UTF-8');
            
            // coloring the rows
            if($i > 0)
            {
                $globbuf = $this->imo->output($globbuf, array('count' => 'im-arow'));
                $i = 0;
            } else 
            {
                $globbuf = $this->imo->output($globbuf, array('count' => 'im-brow'));
                $i = 1;
            }
            
            $globbuf = $this->imo->output($globbuf, array(
                    'page' => isset($item_data['pagedata']['page']) ? 
                            '&page='.$item_data['pagedata']['page'] : '',
                    'item-id' => $id,
                    'item-title' => $title,
                    'item-category' => $item_data['struct'][$key]['category'],
                    'item-date' =>  $date
            ), true);
            // Prepair visible icon template & link
            $cssclass = 'redoff';          
            if(!isset($item_data['struct'][$key]['visible']) || 
               (int)$item_data['struct'][$key]['visible'] == 1)
		        $cssclass = 'redon';
            $tmp_vic_o = $this->imo->output($tmp_vic_o, array('visible-class' => $cssclass));
            $globbuf = $this->imo->output($globbuf, array('visible-icon' => $tmp_vic_o));    
             
            // Prepair promo icon template & link
            $cssclass = 'redoff';
            if(!isset($item_data['struct'][$key]['promo']) || 
               (int)$item_data['struct'][$key]['promo'] == 1)
            {
		        $cssclass = 'redon';
            }
            $tmp_pic_o = $this->imo->output($tmp_pic_o, array('promo-class' => $cssclass));
            $globbuf = $this->imo->output($globbuf, array('promo-icon' => $tmp_pic_o), true);
            
            // buffered tpl
            $tmp_row_o .=  $globbuf;
        }

        return $tmp_row_o;
	}

}