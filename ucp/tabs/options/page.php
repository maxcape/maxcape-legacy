<?php
    $miscoptions = $dbf->queryToAssoc("SELECT * FROM users WHERE UserID='$userid'");
    $hidersn = $dbf->queryToText("SELECT HideRSN FROM users WHERE UserID='$userid'");
?>
<div class="innercontent">
    <form action="#" method="post">
        <fieldset>
            <legend>Misc Options</legend>
            <label>Show RSN in Recent Searches:</label>

            <div class="optgroup">
                <label><input type="radio" name="showrsn" value="0" <?php echo $hidersn == 0 ? "checked=\"checked\"" : ""; ?>>Yes</label>
                <label><input type="radio" name="showrsn" value="1" <?php echo $hidersn == 1 ? "checked=\"checked\"" : ""; ?>>No</label>
            </div>

            <label>Signature Background Color:</label>
            <input id="sigbgcolor" type="text" class="color" value="<?php echo $miscoptions['SigBGColor']; ?>">

            <label>Signature Text Color:</label>
            <input id="sigtxtcolor" type="text" class="color" value="<?php echo $miscoptions['SigTxtColor']; ?>">

            <label>Comment Header Background Color:</label>
            <input id="commentbgcolor" type="text" class="color" value="<?php echo $miscoptions['CommentBGColor']; ?>">

            <button id="savemiscbtn" type="button" onclick="savemisc();">Save</button>
        </fieldset>
    </form>
</div>