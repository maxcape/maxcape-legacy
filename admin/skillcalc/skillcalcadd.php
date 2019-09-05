<?
$selectedSkill = $_GET['skill'];
$skills = array('Overall', 'Attack', 'Defence', 'Strength', 'Hitpoints', 'Ranged','Prayer', 'Magic', 'Cooking', 'Woodcutting', 'Fletching', 'Fishing', 'Firemaking','Crafting', 'Smithing', 'Mining', 'Herblore', 'Agility', 'Thieving', 'Slayer', 'Farming', 'Runecraft', 'Hunter', 'Construction', 'Summoning', 'Dungeoneering');

require_once("calc/dbfunctions.php");
$dbf = new dbfunctions;

$db = $dbf->connectToDatabase("evanr_maxcompcape");

if($db['found']) {

	if(isset($_POST['submit'])) {
		$skill = $_POST['skill'];
		$level = $_POST['level'];
		$name = $_POST['method'];
		$xp = $_POST['expea'];
		
		$dbf->query("INSERT INTO trainingmethods (skill, level, name, xp) VALUES ('$skill', '$level', '$name', '$xp')");
		
		if(!mysql_error()) {
			echo "Successfully added $name";
		} else {
			echo "Error: " . mysql_error();
		}
	}
?>

<form method="post">
	<label>Skill</label>
	<select name="skill">
<?
	for($i = 1; $i < count($skills); $i++) {
		if($selectedSkill != $skills[$i]) {
?>
		<option value="<? echo $skills[$i]; ?>"><? echo $skills[$i]; ?></option>
<?
		} else {
?>
		<option value="<? echo $skills[$i]; ?>" selected="selected"><? echo $skills[$i]; ?></option>
<?	
		}
	}
?>
	</select>
	<br>
	<label>Level</label>
	<input type="text" name="level">
	<br>
	<label>Method</label>
	<input type="text" name="method">
	<br>
	<label>XP Each</label>
	<input type="text" name="expea">
	<br>
	<input type="submit" name="submit">
</form>

<?
}