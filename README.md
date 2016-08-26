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

## Overview

<!-- template: prologue.md -->
<!-- template-end -->

A Plugin implementing simple _Sign_ based warps.

Basic Usage:

Place a Sign with the following text:

	[SWARP]
	x y z

Where `x`, `y` and `z` are numbers containing the target warp
coordinates.

Or for a warp between worlds:

	[WORLD]
	world_name
	x y z

Where `world_name` is the world to warp to, and *optionally* the
`x`, `y` and `z` warp location.

## Documentation

This plugin implements _warps_ through the placement of _signs_.  You
need to create a sign with the text:

	[SWARP]
	x y z

`x`, `y` and `z` are integers containing the target coordinates for
this warp.

To activate a _warp_ the player must touch a sign.  That will teleport
the player to the new location described by the `x`, `y`, `z`
coordinates.

The third and four lines of the sign are ignored and can be used to
describe the _warp_.

To teleport between worlds, the sign text should look like:

	[WORLD]
	world_name
	x y z
	Players:

`world_name` is the target world to teleport to.  `x`, `y`, `z` is the
target location.  If not specified it defaults to the `spawn` world.

If dynamic updates are enabled, the fourth line can contain the text
`Players:`, which will get updated dynamically with the number of
players on that world.  Otherwise the ine is  ignored and can
contain any descriptive text.

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
*  text: Text displayed on the different signs
 *  transfer: Fast transfer signs
 *  world: World teleport signs
 *  warp: Local world teleport signs
 *  players: Text to use when displaying player counts


### Permission Nodes

<!-- template: permissions.md -->
<!-- end-include -->

## Translations

<!-- template: mctxt.md -->
<!-- end-include -->

# Changes

* 1.6.0: 
  * Updated to API 2.0.0
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
