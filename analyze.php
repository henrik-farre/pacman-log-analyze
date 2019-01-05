<?php

$logfile = file('/var/log/pacman.log');

$output = [];

for ($i = 9; $i < 20; $i++) {
  $key = sprintf( "20%'02d", $i );
  $output[$key] = [
    'installed'   => 0,
    'reinstalled' => 0,
    'upgraded'    => 0,
    'downgraded'  => 0,
    'removed'     => 0,
    'sync'        => 0,
    'other'       => 0,
  ];
}

foreach ($logfile as $line)
{
  /**
   * Notes:
   * - Some of these loglines are localized strings in Danish
   * - Last entry I have without an [SOMETHING] prefix is: [2013-04-07 16:56] upgraded pacman (4.0.3-7 -> 4.1.0-2)
   */

  /**
   * Ignore multiline output from "warning: directory permissions differ on..."
   */
  if (preg_match('/^filesystem: /', $line)) {
    continue;
  }

  $date = substr($line, 1, 16);
  $ts = strtotime($date);
  $outputKey = date('Y', $ts);
  $line = trim(substr($line, 18));

  switch (true)
  {
    // Installed
    case preg_match("/^installed /", $line):
    case preg_match("/^\[ALPM\] installed /", $line):
      $output[$outputKey]['installed']++;
      break;

    // Reinstalled
    case preg_match("/^\[ALPM\] reinstalled /", $line):
      $output[$outputKey]['reinstalled']++;
      break;

    // Sync
    case preg_match("/^synchronizing package lists/", $line):
    case preg_match("/^\[PACMAN\] synchronizing package lists/", $line):
      $output[$outputKey]['sync']++;
      break;

    // Upgraded
    case preg_match("/^upgraded /", $line):
    case preg_match("/^\[ALPM\] upgraded /", $line):
      $output[$outputKey]['upgraded']++;
      break;

    // Downgraded
    case preg_match("/^\[ALPM\] downgraded /", $line):
      $output[$outputKey]['downgraded']++;
      break;

    // Removed
    case preg_match("/^removed /", $line):
    case preg_match("/^\[ALPM\] removed /", $line):
      $output[$outputKey]['removed']++;
      break;

    // Blank lines
    case preg_match("/^$/", $line):
      $output[$outputKey]['other']++;
      break;

    /**
     * ALPM
     * Transactions that failed should probably not be counted, but this is left as an exercise for the reader :)
     */
    case preg_match("/^\[ALPM\] transaction started/", $line):
    case preg_match("/^\[ALPM\] transaction failed/", $line):
    case preg_match("/^\[ALPM\] transaction completed/", $line):
    case preg_match("/^\[ALPM\] running '/", $line):
    case preg_match("/^\[ALPM\] warning: /", $line):
      $output[$outputKey]['other']++;
      break;


    // Locales, newer pacman prefixes these with ALPM-SCRIPTLET
    case preg_match("/^Generating locales.../", $line):
    case preg_match("/^[a-z]{2}_[A-Z]{2}.UTF-8... done/", $line):
    case preg_match("/^Generation complete./", $line):
      $output[$outputKey]['other']++;
      break;

    /**
      * ALPM-SCRIPTLET
      */
    case preg_match("/^\[ALPM-SCRIPTLET\]/", $line):
      $output[$outputKey]['other']++;
      break;

    // Warnings, newer pacman prefixes these with ALPM
    case preg_match("/^warning: .* installed as/", $line):
    case preg_match("/^warning: directory permissions differ on /", $line):
      $output[$outputKey]['other']++;
      break;

    // gpg, newer pacman prefixes these with ALPM-SCRIPTLET
    case preg_match("/^gpg: /", $line):
    case preg_match("/^-> Signerer nøglen /", $line):
    case preg_match("/^>>> Run  `pacman-key --init; pacman-key --populate archlinux`/", $line):
    case preg_match("/^>>> to import the data required by pacman for package verification./", $line):
    case preg_match("#^>>> See: https://www.archlinux.org/news/having-pacman-verify-packages#", $line):
      $output[$outputKey]['other']++;
      break;

    // Kernel, newer pacman prefixes these with ALPM-SCRIPTLET
    case preg_match("/^-> Running build hook: /", $line):
    case preg_match("/^-> Parsing hook: \[/", $line):
    case preg_match("/^:: Parsing hook \[/", $line):
    case preg_match("/^>>> Updating module dependencies. Please wait .../", $line):
    case preg_match("/^:: Generating module dependencies/", $line):
    case preg_match("#^:: Generating image '/boot/kernel26#", $line):
    case preg_match("/^:: Begin build/", $line):
    case preg_match("/^>>> Generating initial ramdisk, using mkinitcpio.  Please wait.../", $line):
    case preg_match("#^-> -k /boot/vmlinuz-linux -c /etc/mkinitcpio.conf -g /boot/initramfs-#", $line):
    case preg_match("#^to /etc/mkinitcpio.conf and regenerate your images before rebooting#", $line):
      $output[$outputKey]['other']++;
      break;

    // Pacman, with separate matches for different pacman commands for further datamining
    case preg_match("/^Running 'pacman -Syu/", $line):
    case preg_match("/^Running 'pacman -S /", $line):
    case preg_match("/^Running 'pacman -Sc /", $line):
    case preg_match("/^Running 'pacman -U /", $line):
    case preg_match("/^Running 'pacman -Rn/", $line):
    case preg_match("/^starting full system upgrade/", $line):
    case preg_match("/^\[PACMAN\] Running '/", $line):
    case preg_match("/^\[PACMAN\] reinstalled /", $line):
    case preg_match("/^\[PACMAN\] installed /", $line):
    case preg_match("/^\[PACMAN\] upgraded /", $line):
    case preg_match("/^\[PACMAN\] downgraded /", $line):
    case preg_match("/^\[PACMAN\] removed /", $line):
    case preg_match("/^\[PACMAN\] starting full system upgrade/", $line):
      $output[$outputKey]['other']++;
      break;

    // Random output spanning multiple lines
    case preg_match("/^==>/", $line):
    case preg_match("/^->/", $line):
    case preg_match("/^-->/", $line):
    case preg_match("/^>>> /", $line):
    case preg_match("/^:: /", $line):
    case preg_match("/^########/", $line):
      $output[$outputKey]['other']++;
      break;

    // Lines that are text or something not matched by anything else
    default:
      $output[$outputKey]['other']++;
      break;
  }
}

print_r($output);
