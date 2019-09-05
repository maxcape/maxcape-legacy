<?php
    $mycapes = $dbf->getAllAssocResults("SELECT * FROM capes WHERE UserID='$userid'");
?>
<div class="innercontent">
    <h1 class="headerh1">My Capes</h1>

    <table class="fancy" cellspacing="0">
        <tr>
            <th>Name</th>
            <th>Colors</th>
            <th>Statistics</th>
            <th>Options</th>
        </tr>
        <?php
            foreach ($mycapes as $cape) {
                $capeid = $cape['CapeID'];
                $colors = $dbf->getAllAssocResults("SELECT * FROM capecolors WHERE CapeID='$capeid' ORDER BY ColorNumber");
                ?>
                <tr>
                    <td>
                        <a href="<?php echo $dbf->basefilepath; ?>designer/<?php echo $capeid; ?>"><?php echo $cape['Title']; ?></a>
                    </td>
                    <td>
                        <?php
                            foreach ($colors as $color) {
                                $colorid = $color['ColorID'];
                                $hsl = $dbf->queryToAssoc("SELECT * FROM colors WHERE ColorID='$colorid'");
                                ?>
                                <div class="minicolor" style="background-color:hsl(<?php echo $hsl['H']; ?>, <?php echo $hsl['S']; ?>%, <?php echo $hsl['L']; ?>%)"></div>
                            <?php
                            }
                        ?>
                    </td>
                    <td>
                        <?php
                            $upvotes = $dbf->queryToText("SELECT COUNT(*) FROM capevotes WHERE CapeID='$capeid' AND Direction > 0");
                            $dnvotes = $dbf->queryToText("SELECT COUNT(*) FROM capevotes WHERE CapeID='$capeid' AND Direction < 0");
                            $favorites = $dbf->queryToText("SELECT COUNT(*) FROM capefavorites WHERE CapeID='$capeid'");
                        ?>
                        <span class="upvote"><?php echo $upvotes; ?></span>
                        <span class="downvote"><?php echo $dnvotes; ?></span>
                        <span class="favorited"><?php echo $favorites; ?></span>
                    </td>
                    <td>
                        <a href="javascript:void(0);" onclick="deleteCape(<?php echo $capeid; ?>);">Delete</a>
                    </td>
                </tr>
            <?php
            }
        ?>
    </table>
</div>