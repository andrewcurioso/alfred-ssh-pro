## SSH Pro for Alfred

**An [Alfred](http://www.alfredapp.com/) workflow for SSH power users.**

SSH Pro uses standard SSH configuration files to put access to all your remote systems at your fingertips with the help of the Alfred Power Pack.

### Features

* Uses `~/.ssh/known_hosts` and `~/.ssh/config` for auto-complete
* Smart handling of wildcards in the SSH configuration
* Quick editing of SSH configuration
* Set your favorite editor (GUI or CLI) to edit your SSH configuration in
* Works with both SSH and SFTP

### Instructions

`ssh {hostname}` opens a new connection to a remote machine. The hostname can be autocompleted based on your existing SSH configuration, no additional configuration necessary.

`sftp {hostname}` behaves the same as SSH but opens up a Secure FTP connection to the remote machine instead.

`ssh-config` opens up the `~/.ssh/config` file for editing or creates a new one if none exists.

`ssh-config {editor}` opens up the SSH configuration in the editor of your choice. It can be a Mac App or a Command Line tool. For example:

* `ssh-config vim`
* `ssh-config TextWrangler`

If you have iTerm then the Command Line tools will be opened in that, otherwise they will open in the default system terminal. Regardless of your Alfred settings.

### License

This Alfred Power Pack workflow is licenced under the GPLv3 license.

### Contributing

If you would like to contribute to SSH Pro please use the Github issue tracker to submit bugs, feature requests, and patches.
