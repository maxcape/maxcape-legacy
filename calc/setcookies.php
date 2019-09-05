<?
	
$username = $_POST['name'];
$type = $_POST['type'];

if($type == "set") {
	setcookie('maxcompcapename', $username, time() + (86400 * 100), "/");
	
	echo "success";
} else if ($type == "clear") {
	setcookie('maxcompcapename', "", time() - (86400 * 100), "/");
	
	echo "success";
}
