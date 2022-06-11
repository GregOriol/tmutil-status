# tmutil-status
PHP code for Apple's Time Machine status parsing and nice commandline display.

# Why
There is no user-friendly way to get the status of a Time Machine backup from the command line on macOS.
But there is a hidden `tmutil status` command that provides some details in a not pretty way.

This script parses that non-user-friendly output and displays it nicely, so a status can be retrieved for example through ssh.

# How
Processing code is in php 7.1+. It runs a check every 10s when a backup is running and display the new status.

This script doesn't depend on any other script/vendor/...

# Running it
```
php tmutil-status.php
```

# NB
It should be easy to convert this script to any other scripting language (node, ...), since we first convert the output from `tmutil` into json, then make the display of the data.

# Thanks to
* this StackOverflow question & answers: https://apple.stackexchange.com/questions/162464/time-machine-progress-from-the-command-line
