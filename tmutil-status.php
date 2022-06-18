<?php

set_time_limit(0);

$first = true;
$lastPhase = null;
$lastPc = null;
$lastProgress = null;

function float_pad(float $val): string {
	$whole = floor($val);
	$fraction = round(($val - $whole) * 100, 0);

	return str_pad(''.$whole, 2, ' ', STR_PAD_LEFT).'.'.str_pad(''.$fraction, 2, '0', STR_PAD_LEFT);
}

function formatBytes(int $bytes, bool $si = true): string {
	$unit = $si ? 1000 : 1024;
	if ($bytes <= $unit) {
		return $bytes.' B';
	}
	$exp = intval((log($bytes) / log($unit)));
	$pre = ($si ? 'kMGTPE' : 'KMGTPE');
	$pre = $pre[$exp - 1].($si ? '' : 'i');

	return sprintf('%.2f %sB', $bytes / pow($unit, $exp), $pre);
}

function formatDuration(?int $duration): string {
	if ($duration === null) {
		return '∞';
	}

	$negative = false;

	if ($duration < 0) {
		$negative = true;
		$duration = abs($duration);
	}

	// inspired by: https://stackoverflow.com/questions/8273804/convert-seconds-into-days-hours-minutes-and-seconds
	$secondsInAMinute = 60;
	$secondsInAnHour  = 60 * $secondsInAMinute;

	// extract hours
	$hourSeconds = $duration;
	$hours = floor($hourSeconds / $secondsInAnHour);

	// extract minutes
	$minuteSeconds = $duration % $secondsInAnHour;
	$minutes = floor($minuteSeconds / $secondsInAMinute);

	// extract the remaining seconds
	$remainingSeconds = $minuteSeconds % $secondsInAMinute;
	$seconds = ceil($remainingSeconds);

	$hours = (int)$hours;
	$minutes = (int)$minutes;
	$seconds = (int)$seconds;

	$res = '';
	if ($hours !== 0) {
		$res .= ' '.$hours.'h';
	}
	if ($minutes !== 0) {
		$res .= ' '.$minutes.'min';
	}
	if ($seconds !== 0 || ($hours === 0 && $minutes === 0)) {
		$res .= ' '.$seconds.'s';
	}

	return ($negative ? '-' : '').trim($res);
}

while (true) {
	exec('tmutil status | grep -v \'Backup session status\' > /tmp/tmutil-status.plist; plutil -convert json /tmp/tmutil-status.plist');
	$statusData = file_get_contents('/tmp/tmutil-status.plist');
	unlink('/tmp/tmutil-status.plist');

	$status = json_decode($statusData, true);
	// var_dump($status);

	if ($status['Running'] !== '1') {
		echo 'Backup not running'.PHP_EOL;
		echo PHP_EOL;

		exec('tmutil listbackups | tail -n 1', $lastBackup);
		if (count($lastBackup) === 1) {
			$lastBackup = str_replace('.backup', '', $lastBackup[0]);
			echo 'Last backup: '.$lastBackup.PHP_EOL;
		}

		exit();
	}

	if ($first) {
		echo 'Backup running on: '.$status['DestinationMountPoint'].PHP_EOL;
		echo PHP_EOL;

		$first = false;
	}

	if ($lastPhase !== $status['BackupPhase']) {
		switch ($status['BackupPhase']) {
			case 'FindingChanges':
				echo '# Finding changes'.PHP_EOL;
				break;
			case 'Copying':
				echo '# Copying files'.PHP_EOL;
				break;
			case 'ThinningPostBackup':
				echo '# Thinning'.PHP_EOL;
				break;
		}

		$lastPhase = $status['BackupPhase'];
	}

	switch ($status['BackupPhase']) {
		case 'FindingChanges':
			if (array_key_exists('FractionDone', $status)) {
				$pc = round(floatval($status['FractionDone']) * 100, 2);
			} else {
				$pc = 0;
			}
			if (array_key_exists('ChangedItemCount', $status)) {
				$changedItemCount = intval($status['ChangedItemCount']);
			} else {
				$changedItemCount = 0;
			}

			$progress = '  '.float_pad($pc).'% ('.$changedItemCount.' items)';
			if ($lastProgress !== $progress) {
				echo $progress.PHP_EOL;
			}
			$lastProgress = $progress;

			break;
		case 'Copying':
		case 'ThinningPostBackup':
			$pc = round(floatval($status['Progress']['Percent']) * 100, 2);
			$files = intval($status['Progress']['files']);
			$totalFiles = intval($status['Progress']['totalFiles']);
			$bytes = intval($status['Progress']['bytes']);
			$totalBytes = intval($status['Progress']['totalBytes']);
			if (array_key_exists('TimeRemaining', $status['Progress'])) {
				$timeRemaining = round(floatval($status['Progress']['TimeRemaining']), 0);
			} else {
				$timeRemaining = null;
			}

			$progress = '  '.float_pad($pc).'% ('.$files.' files | '.formatBytes($bytes).' | '.formatDuration($timeRemaining).')';
			if ($lastProgress !== $progress) {
				echo $progress.PHP_EOL;
			}
			$lastProgress = $progress;

			break;
		default:
			var_dump($status);
	}

	sleep(10);
}
