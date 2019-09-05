<?php
$userdata = $dbf->queryToAssoc( "SELECT * FROM users WHERE userid='$userid' LIMIT 1" );
?>
<div class="innercontent" id="account">
    <fieldset>
        <legend>Account Information</legend>

        <label for="username">Username:</label>
        <input id="username" name="username" type="text" value="<?php echo $userdata[ 'Username' ]; ?>" disabled="disabled">

        <label for="datejoined">Date Joined:</label>
        <input id="datejoined" name="datejoined" type="text" disabled="disabled" value="<?php echo date( 'l, F jS Y', strtotime( $userdata[ 'JoinDate' ] ) ); ?>">

        <label for="profileviews">Profile Views:</label>
        <input name="profileviews" id="profileviews" type="text" disabled="disabled" value="<?php echo $userdata[ 'ProfileViews' ]; ?>">

        <label for="timessearched">Times Searched:</label>
        <input id="timessearched" name="timessearched" type="text" disabled="disabled"
               value="<?php echo $dbf->queryToText( "SELECT COUNT(*) FROM searches WHERE RSN='" . $userdata[ 'RSN' ] . "'" ); ?>">


        <?php
            if(strtotime("now") - strtotime($userdata['LastCacheOverride']) > (3600 * 4)) {
                ?>

                <button id="ovrbtn" style="float:left; margin-left:5px;" onclick="overrideCache();">Override Cache</button>

            <?php
            } else {
                ?>
                <button id="ovrbtn" style="float:left; margin-left:5px;" disabled="disabled"><?php echo round(( 14400 - (strtotime("now") - strtotime($userdata['LastCacheOverride']))) / 3600, 0, PHP_ROUND_HALF_DOWN); ?> hours remaining</button>
            <?php
            }
        ?>

        <button style="float:right; clear:right;" onclick="logout();">Logout</button>
    </fieldset>

    <form id="acct" method="post" action="<?php echo $dbf->basefilepath; ?>ucp/saveacct.php">
        <fieldset>
            <legend>Account Settings</legend>

            <label for="rsn">RSN:</label>
            <input id="rsn" name="rsn" type="text" value="<?php echo $userdata[ 'RSN' ] ?>" required="required">

            <label for="email">Email:</label>
            <input id="email" name="email" type="email" value="<?php echo $userdata[ 'Email' ] ?>" required="required">

            <label>Profile Visibility:</label>
            <div class="optgroup">
                <label><input type="radio" name="visibility" value="1" <?php if ( $userdata[ 'ProfileVisible' ] != 0 ) {
                        echo "checked='checked'";
                    } ?> required="required">Public
                </label>
                <label><input type="radio" name="visibility" value="0" <?php if ( $userdata[ 'ProfileVisible' ] == 0 ) {
                        echo "checked='checked'";
                    } ?> required="required">Private
                </label>
            </div>
            <button id="acctsave">Save Changes</button>
        </fieldset>
    </form>

    <form id="pswd" method="post" action="<?php echo $dbf->basefilepath; ?>ucp/changepswd.php">
        <fieldset>
            <legend>Change Password</legend>

            <label for="currentpswd">Current Password:</label>
            <input id="currentpswd" name="currentpswd" type="password" required="required">

            <label for="newpswd">New Password:</label>
            <input id="newpswd" name="newpswd" type="password" required="required">

            <label for="confirmnewpswd">Confirm New Password:</label>
            <input id="confirmnewpswd" name="confirmnewpswd" type="password" required="required">

            <button id="changepswd">Change Password</button>
        </fieldset>
    </form>
</div>