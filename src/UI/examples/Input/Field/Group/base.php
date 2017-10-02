<?php
function base() {
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();


    $part1 = $ui->input()->field()->text("First Name", "Part 1 of group");
    $part2 = $ui->input()->field()->text("Last Name", "Part 1 of group");

    //Todo
}
