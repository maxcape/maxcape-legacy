<?php
    set_time_limit(0);
    require_once("../dbfunctions.php");
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database);

    function curl_req($url, $querystring) {
        $ch = curl_init( $url . $querystring);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $result = curl_exec( $ch );
        curl_close( $ch );

        return $result;
    }

    $delay = 15; //seconds between each request
    $noOfCategories = 37; //number of category pages, 0 - 37
    $maxItemsPerPage = 12; //number of items displayed per page.

    $requestCount = 0; //number of requests to GE API, Counter

    $categoriesURL = "http://services.runescape.com/m=itemdb_rs/api/catalogue/category.json"; //?category=__
    $itemListURL = "http://services.runescape.com/m=itemdb_rs/api/catalogue/items.json"; //?category=__&alpha=__&page=__

    for($catCount = 0; $catCount <= $noOfCategories; $catCount++) {
        $categoryList = json_decode(curl_req($categoriesURL, "?category=$catCount"), true);
        $requestCount++;

        echo "\n<br>Sending request #$requestCount for category #$catCount";
        ob_flush();
        flush();

        foreach($categoryList["alpha"] as $alpha) {
            $itemCount = intval($alpha["items"]);
            if($itemCount > 0) {
                $letter = $alpha["letter"] == "#" ? "%23" : $alpha["letter"];
                $numberOfPages = ceil($itemCount / $maxItemsPerPage);

                for($pageCount = 1; $pageCount <= $numberOfPages; $pageCount++) {
                    //Force wait between requests.
                    sleep($delay);

                    $itemList = json_decode(curl_req($itemListURL, "?category=$catCount&alpha=$letter&page=$pageCount"), true);
                    $requestCount++;

                    echo "\n<br> Sending request #$requestCount for item letter $letter";
                    ob_flush();
                    flush();

                    $items = $itemList["items"];

                    foreach($items as $item) {
                        $imageURL = mysql_real_escape_string($item['icon_large']);
                        $itemID = mysql_real_escape_string($item["id"]);
                        $itemName = mysql_real_escape_string($item["name"]);
                        $itemDescription = mysql_real_escape_string($item["description"]);
                        $itemPrice = $item["current"]["price"];

                        $itemPrice = str_replace(",", "", $itemPrice);

                        if(substr($itemPrice, -1) == "k") {
                            $itemPrice = rtrim($itemPrice, "k");
                            $itemPrice = $itemPrice * 1000;
                        } elseif(substr($itemPrice, -1) == "m") {
                            $itemPrice = rtrim($itemPrice, "m");
                            $itemPrice = $itemPrice * 1000000;
                        } elseif(substr($itemPrice, -1) == "b") {
                            $itemPrice = rtrim($itemPrice, "b");
                            $itemPrice = $itemPrice * 1000000000;
                        }

                        $itemPrice = mysql_real_escape_string($itemPrice);

                        $dbf->query("INSERT INTO grandexchange (CategoryNumber, ItemID, ItemName, ItemDescription, ItemPrice, DateFetched, IconURL) VALUES ('$catCount', '$itemID', '$itemName', '$itemDescription', '$itemPrice', NOW(), '$imageURL') ON DUPLICATE KEY UPDATE ItemPrice='$itemPrice', IconURL='$imageURL'");
                        echo "\n<br> ---- Inserted Item '$itemName' into DB With ItemID $itemID";
                        ob_flush();
                        flush();
                    }
                }
            }
        }

        //Force Wait between requests.
        sleep($delay);
    }

echo "\n<br>Completed GE Scrape with $requestCount requests.";