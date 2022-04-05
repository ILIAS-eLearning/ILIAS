/**
 * This fixes the responsive behaviour of the players
 * in the ILIAS grid. It appears (ILIAS 7) when the window
 * gets narrower (getting wider worked  without workaround)
 * see https://mantis.ilias.de/view.php?id=32162
 */
window.addEventListener('resize', (event) => {
  if (mejs && mejs.players) {
    let modifiedPlayers = [];
    for (const [key, player] of Object.entries(mejs.players)) {
      //console.log(`${key}: ${player}`);
      // reduce all players with stretching set to "auto" to 1x1 size
      if (player.options.stretching == 'auto') {
        player.options.stretching = 'none';
        player.setPlayerSize(1, 1);
        modifiedPlayers.push(player);
      }
    }
    // now for the modified players, set stretching back to auto
    // and trigger the responsive resizing
    modifiedPlayers.forEach((player, key) => {
      player.options.stretching = 'auto';
      player.setPlayerSize();
    });
  }
});
