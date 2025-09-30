<?php

/**
 * To use this class, you can create an instance of the InstagramMediaRetriever class and call the get_media method to retrieve the user's media. The $access_token parameter should be the user's access token for the Instagram Basic Display API, and the $cache_folder parameter should be the path to the folder where you want to save the cached media files. The $count parameter should be the number of media items that you want to retrieve.
 */
// Here's an example of how you could use the class to retrieve the user's media and display them in an HTML gallery:
/*
$access_token = "YOUR_ACCESS_TOKEN";
$cache_folder = "path/to/cache/folder";
$media_retriever = new InstagramMediaRetriever($access_token, $cache_folder);
$media_items = $media_retriever->get_media(10);
if ($media_items) {
	echo "<div class='gallery'>";
	foreach ($media_items as $media) {
		echo "<a href='{$media['url']}'><img src='{$media['thumbnail']}' alt=''></a>";
	}
	echo "</div>";
}
*/
// In this example, the get_media method retrieves the user's 10 most recent media items and saves them to the cache folder. The media items are then displayed in an HTML gallery using the img element with the thumbnail URL as the src attribute and the media URL as the href attribute. You can modify this example to fit your needs and integrate it into your WordPress plugin.

class InstagramMediaRetriever {
	private $access_token;
	private $cache_folder;

	public function __construct($access_token, $cache_folder) {
		$this->access_token = $access_token;
		$this->cache_folder = $cache_folder;
	}

	public function get_media($count) {
		$url = "https://graph.instagram.com/me/media?fields=id,media_type,media_url,thumbnail_url&access_token={$this->access_token}&limit={$count}";
		$response = file_get_contents($url);
		$data = json_decode($response);
		if (!isset($data->data)) {
			return null;
		}
		foreach ($data->data as $media) {
			$media_id = $media->id;
			$media_type = $media->media_type;
			$media_url = $media->media_url;
			$thumbnail_url = $media->thumbnail_url;
			$file_extension = pathinfo($media_url, PATHINFO_EXTENSION);
			$filename = "{$this->cache_folder}/{$media_id}.{$file_extension}";
			if (!file_exists($filename)) {
				file_put_contents($filename, file_get_contents($media_url));
			}
			$media_items[] = array(
				'id' => $media_id,
				'type' => $media_type,
				'url' => $media_url,
				'thumbnail' => $thumbnail_url,
				'filename' => $filename
			);
		}
		return $media_items;
	}
}
