<!-- template: startup.md -->
<!-- end-include -->
<img id="SignWarp-icon.png" />
<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/signwarp.7276/"; -->
<!-- php: $copyright="2016"; -->
<!-- meta: Categories = Teleportation -->
<!-- template: header.md -->
<!-- end-include -->

<!-- template: prologue.md -->
<!-- end-include -->

## Documentation

A Plugin implementing simple _Sign_ based teleports.

To activate a _teleport_ the player must touch a sign.  That will
teleport the player to the new location described by the sign.

Basic Usage:

Place a Sign with the following text:

	[POS]
	x y z

Where `x`, `y` and `z` are numbers containing the teleport target
coordinates.

To teleport between worlds:

	[WORLD]
	world_name
	x y z
	Players:

Where `world_name` is the world to teleport to, and *optionally* the
`x`, `y` and `z` teleport location.  If not specified it defaults to
the world's `spawn` location.

If dynamic updates are enabled, the fourth line can contain the text
`Players:`, which will get updated dynamically with the number of
players on that world.  Otherwise the line is ignored and can
contain any descriptive text.

To teleport to warp points:

	[WARP]
	warp_name

To help identify potential _warp_ targets, the command `xyz` is
provided.  Entering `/xyz` in-game will display the current
coordinates of the player.

### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### config.yml

*  settings: configurable variables
 *  dynamic updates: Signs will be udpated with the number of players in a world
 *  xyz.cmd: If true, the **xyz** command will be available

### Permission Nodes

<!-- template: permissions.md -->
<!-- end-include -->

## Translations

<!-- template: mctxt.md -->
<!-- end-include -->

# Changes

* 2.0.0: 
  * Updated to API 2.0.0
  * Added more [libcommon](http://github.com/Muirfield/libcommon).
  * Added Warps
  * Sign text configuration rolled into translation.
  * Removed FastTransfer
* 1.5.1:
  * Removed a nasty crash in BreakSign
  * Signs now show max players in world.
* 1.4.0:
  * Clean-up and use library stuff
  * Removed broadcast setting
  * Translations: Spanish
* 1.3.2:
  * Removed CallbackTask deprecation warnings
  * removed onLoad... All initialization happens onEnable
  * FastTransfer support
  * /xyz can now be disabled
  * cleaned up the code
* 1.2.2:
  * Fixed errors reported by [Crash Archive](http://crash.pocketmine.net/)
  * Added broadcast-tp setting.
  * Small changes on the way ManyWorlds API is used.
  * Sign texts can be configured.  Useful for localization.
* 1.1.1 :
  * Fixed /xyz command.
  * Will not teleport if you are holding a sign.
  * Prevents blocks to be placed when teleporting.
  * Use ManyWorlds teleport functionality when available.
  * Added dynamic sign updates.
* 1.0.0 : First release

## FAQ

* Q: How do I create additional worlds?
* You can use a plugin like `ManyWorlds` or modify the `worlds` secion
  in your `pocketmine.yml` file.

## TODO

- [ ] Add libcommon Warps
- [ ] Remove fast transfer
- [ ] Review localization mechanisms
- [ ] config doc template
- [ ] move getmaxplayers to libcommon
- [ ] load level and teleport to libcommon (see also ManyWorlds)

<!-- template: license/gpl2.md -->
<!-- end-include -->
