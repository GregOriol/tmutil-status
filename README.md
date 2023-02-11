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

# Output
Produces output like this when a backup is running:
```
Backup running on: /Volumes/TM

# Mounting backup volume
# Preparing source volume
# Finding changes
   2.10% (23805 items)
  21.92% (240251 items)
  25.86% (301346 items)
  36.96% (425665 items)
  55.50% (632314 items)
  67.73% (780977 items)
  75.21% (880359 items)
  82.35% (976994 items)
  84.58% (1020393 items)
  88.12% (1076181 items)
  92.17% (1141944 items)
  94.32% (1190445 items)
  95.94% (1227456 items)
# Copying files
   0.45% (14 files | 3.66 MB | ∞)
   4.67% (127 files | 10.08 MB | ∞)
   9.64% (559 files | 81.65 MB | ∞)
  10.44% (944 files | 129.18 MB | ∞)
  10.67% (1102 files | 438.48 MB | ∞)
  10.74% (1469 files | 1.32 GB | ∞)
  10.78% (2462 files | 1.52 GB | ∞)
  10.89% (3152 files | 1.66 GB | ∞)
  16.93% (3329 files | 1.70 GB | 8min 38s)
[...]
  89.93% (111829 files | 23.60 GB | 2min 17s)
  90.63% (114775 files | 23.70 GB | 2min 8s)
  92.44% (116976 files | 23.76 GB | 1min 42s)
  92.44% (117175 files | 23.76 GB | 1min 43s)
# Thinning
Backup not running

Last backup: 2023-02-11-113715
```

or like this when no backup is running:
```
Backup not running

Last backup: 2023-02-11-113715
```

# NB
It should be easy to convert this script to any other scripting language (node, ...), since we first convert the output from `tmutil` into json, then make the display of the data.

# Thanks to
* this StackOverflow question & answers: https://apple.stackexchange.com/questions/162464/time-machine-progress-from-the-command-line
