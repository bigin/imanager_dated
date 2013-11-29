<?php
# get correct id for plugin
$thisfile = basename(__FILE__, ".php");

// Recent Pages - version 0.2.1 [beta]
// plugin for GetSimple CMS 2.0x - 3.0)
// display latest updated pages
//
// NOTE: requires PagesXML plugin 2.3 ( http://get-simple.info/extend/plugin/page-caching/65/ )
// 
// recent_pages - displays latest updated pages in reverse chronological order.
// note: pages with publication date set in the future will not be listed (since v0.2.1)
//
// USAGE: recent_pages(numberofpages, parentpage); 
// 		numberofpages: default 0 = all pages
// 		parentpage: select only children of a page. If empty, "any parent"
// EXAMPLES:
// 		recent_pages() - all pages in the site
// 		recent_pages(7) - latest 7 pages in the site
// 		recent_pages(10,'blog') - latest 10 subpages of 'blog'
// 		recent_pages(0,'news') - all subpages of 'news'

# register plugin
register_plugin(
	$thisfile, 
	'Recent Pages', 	
	'0.2.1 beta', 		
	'Carlos Navarro',
	'http://www.cyberiada.org/cnb/', 
	'Display latest/recently updated pages using the recent_page function.',
	'',
	''  
);
	
// compare_date_recent - function used to reverse sort by date
// based on: http://www.the-art-of-web.com/php/sortarray/
function compare_date_recent($a, $b) {
	return strnatcmp(strtotime($b['pubDate']), strtotime($a['pubDate']));
} 

//	main function:	
function recent_pages($max=0, $parent='') {
	global $digi_pagesArray;
	$returnArray = array();
	foreach ($digi_pagesArray as $key => $value) {
			if ( ($digi_pagesArray[$key]['private']!='Y') && (intval(strtotime(date('r'))>intval(strtotime($digi_pagesArray[$key]['pubDate']))))
				&& ( ($parent=='') || ($digi_pagesArray[$key]['parent']==$parent) ) ) {
				$returnArray[]=array( 'slug' => $key , 'pubDate' => returnPageField($key,'pubDate') );
			}
	}
	usort($returnArray,'compare_date_recent');
	if ($max!=0){
		if (count($returnArray)>$max) {
			$returnArray = array_slice($returnArray,0,$max);
		}
	}
	
	foreach ($returnArray as $a) {
		$s=$a['slug'];
		$PAGEURL = find_url($s,returnPageField($s,'parent'));
		$PAGETITLE = returnPageField($s,'title');
		$PAGEDATE = date('Y-m-d',strtotime(returnPageField($s,'pubDate'))); // change 'Y-m-d' to your favourite datetime format
		// edit next lines to customize output:
		?>
		<p>
			<a href="<?php echo $PAGEURL; ?>"><?php echo $PAGETITLE; ?></a>
			<br />
			<?php echo $PAGEDATE; ?>
		</p>
		<?php
	}
}

?>