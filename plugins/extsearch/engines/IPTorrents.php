<?php

class IPTorrentsEngine extends commonEngine
{
	public function action($what,$cat,&$ret,$limit)
	{
		$added = 0;
		$url = 'http://www.iptorrents.com';
		$categories = array( 'all'=>'', 'movies'=>'&c6=1&c70=1&c38=1&c68=1&c7=1&c48=1', 'tv'=>'&c66=1&c4=1&c65=1', 'music'=>'&c37=1&c3=1', 'games'=>'&c2=1&c47=1&c43=1&c45=1&c39=1&c40=1&c67=1&c50=1&c42=1&c44=1', 'anime'=>'&c60=1', 'software'=>'&c1=1', 'pictures'=>'&c36=1', 'books'=>'&c64=1&c35=1' );
		if(!array_key_exists($cat,$categories))
			$cat = 'all';
		for($pg = 0; $pg<10; $pg++)
		{
			$cli = $this->fetch( $url.'/browse.php?search='.$what.'&sort=seeders&d=DESC&incldead=0&page='.$pg.$categories[$cat] );
			if( ($cli==false) || (strpos($cli->results, "<h3>Nothing found!</h3>")!==false) ||
				(strpos($cli->results, "<h1>Not logged in!</h1>")!==false))
				break;
			$res = preg_match_all('/<img class=".*" border="0" width="50" src=.* alt="(?P<cat>.*)" \/><\/a>'.
				'.*<a href="details.php\?id=(?P<id>\d+)">(?P<name>.*)<\/a>.*<td .*>.*<\/td>'.
				'<td .*>.*href="download.php\/\d+\/(?P<tname>.*)".*<\/a><\/td><td .*>.*<\/td>.*<td .*>(?P<date>.*)<\/td>.*<td .*>(?P<size>.*)<\/td>'.
				'.*<td .*>.*<\/td>.*<td .*>(?P<seeds>.*)<\/td>.*<td .*>(?P<leech>.*)<\/td>/siU', $cli->results, $matches);
			if(($res!==false) && ($res>0) &&
				count($matches["id"])==count($matches["cat"]) &&
				count($matches["cat"])==count($matches["name"]) && 
				count($matches["name"])==count($matches["size"]) &&
				count($matches["size"])==count($matches["seeds"]) &&
				count($matches["seeds"])==count($matches["date"]) &&
				count($matches["seeds"])==count($matches["tname"]) &&
				count($matches["date"])==count($matches["leech"]) )
			{
				for($i=0; $i<count($matches["id"]); $i++)
				{
					$link = $url."/download.php/".$matches["id"][$i]."/".$matches["tname"][$i];
					if(!array_key_exists($link,$ret))
					{
						$item = $this->getNewEntry();
						$item["cat"] = self::removeTags($matches["cat"][$i]);
						$item["desc"] = $url."/details.php?id=".$matches["id"][$i];
						$item["name"] = self::removeTags($matches["name"][$i]);
						$item["size"] = self::formatSize(str_replace("<br>"," ",$matches["size"][$i]));
						$item["time"] = strtotime(self::removeTags($matches["date"][$i]));
						$item["seeds"] = intval(self::removeTags($matches["seeds"][$i]));
						$item["peers"] = intval(self::removeTags($matches["leech"][$i]));
						$ret[$link] = $item;
						$added++;
						if($added>=$limit)
							return;
					}
				}
			}
			else
				break;
		}
	}
}

?>